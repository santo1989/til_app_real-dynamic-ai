<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\IndividualObjectiveMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IndividualObjectiveMasterController extends Controller
{
    public function index()
    {
        $this->authorizeAccess();
        $items = IndividualObjectiveMaster::with('creator')->orderBy('title')->paginate(20);
        return view('appraisal.hr_admin.individual_objective_masters.index', compact('items'));
    }

    public function create()
    {
        $this->authorizeAccess();
        return view('appraisal.hr_admin.individual_objective_masters.create', [
            'item' => new IndividualObjectiveMaster(),
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
        $exists = IndividualObjectiveMaster::query()
            ->whereRaw('UPPER(title) = ?', [$normalizedTitle])
            ->exists();
        if ($exists) {
            return back()->withErrors(['title' => 'The title already exists.'])->withInput();
        }

        IndividualObjectiveMaster::create([
            'title' => $normalizedTitle,
            'is_active' => (bool)($request->has('is_active')),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('individual-objective-masters.index')->with('success', 'Individual objective master added.');
    }

    public function edit(IndividualObjectiveMaster $individual_objective_master)
    {
        $this->authorizeAccess();
        return view('appraisal.hr_admin.individual_objective_masters.edit', [
            'item' => $individual_objective_master,
        ]);
    }

    public function update(Request $request, IndividualObjectiveMaster $individual_objective_master)
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $normalizedTitle = Str::upper(trim((string)$data['title']));
        $exists = IndividualObjectiveMaster::query()
            ->whereRaw('UPPER(title) = ?', [$normalizedTitle])
            ->where('id', '!=', $individual_objective_master->id)
            ->exists();
        if ($exists) {
            return back()->withErrors(['title' => 'The title already exists.'])->withInput();
        }

        $individual_objective_master->update([
            'title' => $normalizedTitle,
            'is_active' => (bool)($request->has('is_active')),
        ]);

        return redirect()->route('individual-objective-masters.index')->with('success', 'Individual objective master updated.');
    }

    public function destroy(IndividualObjectiveMaster $individual_objective_master)
    {
        $this->authorizeAccess();
        $individual_objective_master->delete();
        return redirect()->route('individual-objective-masters.index')->with('success', 'Individual objective master deleted.');
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

            $isActiveRaw = $data['is_active'] ?? ($row[1] ?? '1');
            $isActive = in_array(strtolower(trim((string)$isActiveRaw)), ['1', 'true', 'yes', 'y', 'active'], true);

            DB::table('individual_objective_masters')->updateOrInsert(
                ['title' => $title],
                [
                    'is_active' => $isActive,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $inserted++;
        }
        fclose($handle);

        return redirect()->route('individual-objective-masters.index')->with('success', "CSV import completed. Processed {$inserted} rows.");
    }

    private function authorizeAccess(): void
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if (!$user || (!$user->isHrAdmin() && !$user->isSuperAdmin() && !$user->isBoardMember())) {
            abort(403, 'Unauthorized.');
        }
    }
}
