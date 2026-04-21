<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Idp;
use App\Models\IdpMilestone;
use App\Models\AuditLog;
use Illuminate\Support\Str;

class IdpMilestoneController extends Controller
{
    public function store(Request $request, Idp $idp)
    {
        $this->authorize('update', $idp);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'resource_required' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'progress' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:open,in_progress,completed,blocked',
        ]);

        $data = $this->normalizeMilestoneTextFields($data);

        if (!empty($data['title'])) {
            $exists = IdpMilestone::query()
                ->where('idp_id', $idp->id)
                ->whereRaw('UPPER(title) = ?', [$data['title']])
                ->exists();
            if ($exists) {
                $msg = 'Milestone title must be unique for this IDP.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['message' => $msg], 422);
                }
                return back()->withErrors(['title' => $msg])->withInput();
            }
        }

        $data['idp_id'] = $idp->id;
        $milestone = IdpMilestone::create($data);
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'idp_milestone_created',
            'table_name' => 'idp_milestones',
            'record_id' => $milestone->id,
            'details' => "IDP milestone created for IDP {$idp->id}: {$milestone->title}",
        ]);
        if ($request->wantsJson() || $request->ajax()) {
            $html = view('appraisal.idp.partials._milestone_row', compact('milestone', 'idp'))->render();
            return response()->json(['html' => $html, 'milestone' => $milestone], 201);
        }
        return redirect()->back()->with('success', 'Milestone created');
    }

    public function update(Request $request, Idp $idp, IdpMilestone $milestone)
    {
        $this->authorize('update', $idp);
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'resource_required' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'progress' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:open,in_progress,completed,blocked',
        ]);

        $data = $this->normalizeMilestoneTextFields($data);

        if (!empty($data['title'])) {
            $exists = IdpMilestone::query()
                ->where('idp_id', $idp->id)
                ->whereRaw('UPPER(title) = ?', [$data['title']])
                ->where('id', '!=', $milestone->id)
                ->exists();
            if ($exists) {
                $msg = 'Milestone title must be unique for this IDP.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['message' => $msg], 422);
                }
                return back()->withErrors(['title' => $msg])->withInput();
            }
        }

        $milestone->update($data);
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'idp_milestone_updated',
            'table_name' => 'idp_milestones',
            'record_id' => $milestone->id,
            'details' => "IDP milestone updated for IDP {$idp->id}: {$milestone->title}",
        ]);
        if ($request->wantsJson() || $request->ajax()) {
            $html = view('appraisal.idp.partials._milestone_row', compact('milestone', 'idp'))->render();
            return response()->json(['html' => $html, 'milestone' => $milestone], 200);
        }
        return redirect()->back()->with('success', 'Milestone updated');
    }

    public function destroy(Idp $idp, IdpMilestone $milestone)
    {
        $this->authorize('delete', $idp);
        $milestoneId = $milestone->id;
        $milestone->delete();
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'idp_milestone_deleted',
            'table_name' => 'idp_milestones',
            'record_id' => $milestoneId,
            'details' => "IDP milestone deleted for IDP {$idp->id}: {$milestoneId}",
        ]);
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['deleted' => true, 'id' => $milestoneId], 200);
        }
        return redirect()->back()->with('success', 'Milestone removed');
    }

    /**
     * Mark attainment on a milestone and optionally attach visible demonstration and HR input.
     */
    public function attain(Request $request, Idp $idp, IdpMilestone $milestone)
    {
        $this->authorize('attain', $idp);

        // Ensure revisions allowed
        $fy = \App\Models\FinancialYear::active();
        if ($fy && !$fy->isRevisionAllowed()) {
            return redirect()->back()->with('error', 'IDP milestone updates are closed for the active financial year.');
        }

        $data = $request->validate([
            'attainment' => 'nullable|boolean',
            'visible_demonstration' => 'nullable|string',
            'hr_input' => 'nullable|string',
        ]);

        $user = auth()->user();
        if (isset($data['attainment'])) {
            $milestone->attainment = (bool)$data['attainment'];
            $milestone->attained_by_id = $user->id;
            $milestone->attained_at = now();
        }
        if (isset($data['visible_demonstration'])) {
            $milestone->visible_demonstration = $this->normalizeText($data['visible_demonstration']);
        }
        if (isset($data['hr_input'])) {
            $milestone->hr_input = $this->normalizeText($data['hr_input']);
        }
        $milestone->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'idp_milestone_attained',
            'table_name' => 'idp_milestones',
            'record_id' => $milestone->id,
            'details' => "Milestone attainment updated for IDP {$idp->id} milestone {$milestone->id} by user {$user->id}",
        ]);
        if ($request->wantsJson() || $request->ajax()) {
            $html = view('appraisal.idp.partials._milestone_row', compact('milestone', 'idp'))->render();
            return response()->json(['html' => $html, 'milestone' => $milestone], 200);
        }

        return redirect()->back()->with('success', 'Milestone updated');
    }

    private function normalizeText($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }
        return Str::upper($trimmed);
    }

    private function normalizeMilestoneTextFields(array $data): array
    {
        foreach (['title', 'description', 'resource_required'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->normalizeText($data[$field]);
            }
        }
        return $data;
    }
}
