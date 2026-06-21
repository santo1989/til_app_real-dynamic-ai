<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\IdpDevelopmentObjective;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class IdpDevelopmentObjectiveController extends Controller
{
    public function index()
    {
        $this->authorizeRole();

        $items = IdpDevelopmentObjective::orderBy('skill_area')
            ->paginate(25);

        return view('appraisal.hr_admin.idp_development_objectives.index', compact('items'));
    }

    public function create()
    {
        $this->authorizeRole();
        return view('appraisal.hr_admin.idp_development_objectives.create', [
            'item' => new IdpDevelopmentObjective(),
        ]);
    }

    public function edit(IdpDevelopmentObjective $idpDevelopmentObjective)
    {
        $this->authorizeRole();
        return view('appraisal.hr_admin.idp_development_objectives.edit', [
            'item' => $idpDevelopmentObjective,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeRole();

        $data = $request->validate([
            'skill_area' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $normalizedSkillArea = Str::upper(trim((string)$data['skill_area']));

        $skillArea = IdpDevelopmentObjective::query()->updateOrCreate(
            [
                'skill_area' => $normalizedSkillArea,
            ],
            [
                'is_active' => (bool)($data['is_active'] ?? true),
                'created_by' => auth()->id(),
            ]
        );

        return redirect()->route('idp-development-objectives.index')->with('success', 'IDP skill area saved.');
    }

    public function update(Request $request, IdpDevelopmentObjective $idpDevelopmentObjective): RedirectResponse
    {
        $this->authorizeRole();

        $data = $request->validate([
            'skill_area' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $exists = IdpDevelopmentObjective::query()
            ->where('id', '!=', $idpDevelopmentObjective->id)
            ->whereRaw('UPPER(skill_area) = ?', [strtoupper(trim((string)$data['skill_area']))])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'skill_area' => 'This skill area already exists.',
            ])->withInput();
        }

        $idpDevelopmentObjective->update([
            'skill_area' => Str::upper(trim((string)$data['skill_area'])),
            'is_active' => (bool)($data['is_active'] ?? false),
        ]);

        return redirect()->route('idp-development-objectives.index')->with('success', 'IDP skill area updated.');
    }

    public function destroy(IdpDevelopmentObjective $idpDevelopmentObjective): RedirectResponse
    {
        $this->authorizeRole();

        $idpDevelopmentObjective->delete();

        return redirect()->route('idp-development-objectives.index')->with('success', 'IDP skill area deleted.');
    }

    public function importCsv(Request $request): RedirectResponse
    {
        $this->authorizeRole();

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return back()->withErrors(['csv_file' => 'Unable to read CSV file.']);
        }

        $header = fgetcsv($handle) ?: [];
        $normalized = array_map(fn($v) => strtolower(trim((string)$v)), $header);
        $hasHeader = in_array('skill_area', $normalized, true);

        if (!$hasHeader) {
            rewind($handle);
            $normalized = [];
        }

        $processed = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row, fn($v) => trim((string)$v) !== ''))) {
                continue;
            }

            $data = [];
            if (!empty($normalized)) {
                foreach ($normalized as $idx => $key) {
                    $data[$key] = $row[$idx] ?? null;
                }
            }

            $skillArea = trim((string)($data['skill_area'] ?? ($row[0] ?? '')));
            if ($skillArea === '') {
                continue;
            }

            $isActiveRaw = $data['is_active'] ?? ($row[1] ?? '1');
            $isActive = in_array(strtolower(trim((string)$isActiveRaw)), ['1', 'true', 'yes', 'y', 'active'], true);

            IdpDevelopmentObjective::query()->updateOrCreate(
                ['skill_area' => Str::upper($skillArea)],
                [
                    'is_active' => $isActive,
                    'created_by' => auth()->id(),
                ]
            );
            $processed++;
        }
        fclose($handle);

        return redirect()->route('idp-development-objectives.index')->with('success', "CSV import completed. Processed {$processed} rows.");
    }

    public function exportCsv()
    {
        $this->authorizeRole();

        $items = IdpDevelopmentObjective::query()
            ->orderBy('skill_area')
            ->get(['skill_area', 'is_active']);

        $filename = 'idp_skill_areas_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = static function () use ($items): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['skill_area', 'is_active']);
            foreach ($items as $item) {
                fputcsv($out, [
                    $item->skill_area,
                    $item->is_active ? 1 : 0,
                ]);
            }
            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function authorizeRole(): void
    {
        $user = auth()->user();

        if (!$user || !in_array($user->role, ['hr_admin', 'line_manager', 'super_admin'], true)) {
            abort(403, 'Unauthorized.');
        }
    }
}
