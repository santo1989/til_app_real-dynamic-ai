<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DepartmentalObjectiveMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepartmentalObjectiveMasterController extends Controller
{
    public function index()
    {
        $this->authorizeAccess();
        $items = DepartmentalObjectiveMaster::with('department')->orderBy('title')->paginate(20);
        return view('appraisal.hr_admin.departmental_objective_masters.index', compact('items'));
    }

    public function create()
    {
        $this->authorizeAccess();
        return view('appraisal.hr_admin.departmental_objective_masters.create', [
            'item' => new DepartmentalObjectiveMaster(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $normalizedTitle = Str::upper(trim((string)$data['title']));

        $exists = DepartmentalObjectiveMaster::whereRaw('UPPER(title) = ?', [$normalizedTitle])
            ->exists();
        if ($exists) {
            return back()->withErrors(['title' => 'This objective title already exists in the master library.'])->withInput();
        }

        DepartmentalObjectiveMaster::create([
            'title' => $normalizedTitle,
            'is_active' => (bool)($request->has('is_active')),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('departmental-objective-masters.index')->with('success', 'Dept/Team objective master added.');
    }

    public function edit(DepartmentalObjectiveMaster $departmental_objective_master)
    {
        $this->authorizeAccess();
        return view('appraisal.hr_admin.departmental_objective_masters.edit', [
            'item' => $departmental_objective_master,
        ]);
    }

    public function update(Request $request, DepartmentalObjectiveMaster $departmental_objective_master)
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $normalizedTitle = Str::upper(trim((string)$data['title']));

        $exists = DepartmentalObjectiveMaster::whereRaw('UPPER(title) = ?', [$normalizedTitle])
            ->where('id', '!=', $departmental_objective_master->id)
            ->exists();
        if ($exists) {
            return back()->withErrors(['title' => 'This objective title already exists.'])->withInput();
        }

        $departmental_objective_master->update([
            'title' => $normalizedTitle,
            'is_active' => (bool)($request->has('is_active')),
        ]);

        return redirect()->route('departmental-objective-masters.index')->with('success', 'Dept/Team objective master updated.');
    }

    public function destroy(DepartmentalObjectiveMaster $departmental_objective_master)
    {
        $this->authorizeAccess();
        $departmental_objective_master->delete();
        return redirect()->route('departmental-objective-masters.index')->with('success', 'Departmental objective master deleted.');
    }

    public function importCsv(Request $request)
    {
        $this->authorizeAccess();

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
        $hasHeader = in_array('title', $normalized, true);

        if (!$hasHeader) {
            rewind($handle);
            $normalized = [];
        }

        $inserted = 0;
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

            $title = Str::upper(trim((string)($data['title'] ?? ($row[0] ?? ''))));
            if ($title === '') {
                continue;
            }

            $departmentRaw = $data['department_id'] ?? ($data['department'] ?? ($row[1] ?? null));
            $departmentId = null;
            if ($departmentRaw !== null && trim((string)$departmentRaw) !== '') {
                if (is_numeric($departmentRaw)) {
                    $departmentId = (int)$departmentRaw;
                } else {
                    $departmentId = Department::where('name', trim((string)$departmentRaw))->value('id');
                }
            }

            $isActiveRaw = $data['is_active'] ?? ($row[2] ?? '1');
            $isActive = in_array(strtolower(trim((string)$isActiveRaw)), ['1', 'true', 'yes', 'y', 'active'], true);

            DB::table('departmental_objective_masters')->updateOrInsert(
                [
                    'department_id' => $departmentId,
                    'title' => $title,
                ],
                [
                    'is_active' => $isActive,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $inserted++;
        }
        fclose($handle);

        return redirect()->route('departmental-objective-masters.index')->with('success', "CSV import completed. Processed {$inserted} rows.");
    }

    public function options(Request $request)
    {
        $this->authorizeOptionsAccess();

        $departmentId = $request->query('department_id');

        $query = DepartmentalObjectiveMaster::query()
            ->where('is_active', true)
            ->orderBy('title');

        if (!empty($departmentId)) {
            $query->where(function ($q) use ($departmentId) {
                $q->whereNull('department_id')
                    ->orWhere('department_id', (int)$departmentId);
            });
        }

        $options = $query->pluck('title')->values();
        return response()->json(['options' => $options]);
    }

    private function authorizeAccess(): void
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if (!$user || (!$user->isHrAdmin() && !$user->isSuperAdmin() && !$user->isBoardMember())) {
            abort(403, 'Unauthorized.');
        }
    }

    private function authorizeOptionsAccess(): void
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if (!$user || (!$user->isHrAdmin() && !$user->isSuperAdmin() && !$user->isLineManager() && !$user->isBoardMember())) {
            abort(403, 'Unauthorized.');
        }
    }
}
