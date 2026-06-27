# AI Dynamic Performance Appraisal System Delivery

## New Project Folder
- `e:\Programming\www\www\PMS\til_app_real-dynamic-ai`

## What This Delivery Covers
Built from your unfinished Laravel codebase and aligned with the process rules extracted from the documents in `Referenc_Documents`:
1. Objective setting flow (HR details + board/departmental + employee individual)
2. Line manager objective finalization (timeline + weightage)
3. Midterm appraisal (6-month comments)
4. Year-end appraisal (12-month scoring + rating)
5. IDP tracking in objective, midterm, and year-end stages
6. Dynamic financial-year based processing (no fixed-year dependency in runtime logic)

## Key Dynamic Fixes Applied In This New Folder

### 1) Financial Year Hardcoding Removed From Runtime Flow
- Line-manager objective setting page now loads objectives by active FY from DB.
- Team objective form defaults to dynamic FY list from `financial_years` table.
- Super-admin objective create form defaults to available FY list (not fixed label).
- Objective/appraisal migrations now store `financial_year` as nullable dynamic value instead of hardcoded default.

### 2) Team/Departmental Objective Logic Corrected
- Fixed departmental total calculations using correct type `departmental` (was incorrectly using `team` in legacy checks).
- Team objective create/edit FY selector is DB-driven via `financial_years` records.

### 3) Seeder Data Made Dynamic
- `ObjectiveSeeder`, `AppraisalSeeder`, and `UsersAndDataSeeder` now derive FY labels from active/available financial years.
- Avoids lock-in to a single FY label.

### 4) FY Diagnostic Command Improved
- `php artisan app:fy-check {label?}` now accepts optional FY label.
- If no label is provided, it uses active FY or auto-generates a current-style label.

## Critical Business Rules Supported
- Departmental objectives: total 30%, count 2-3.
- Individual objectives: total 70%, count and weight validation via existing request/controller rules.
- Combined objective logic enforces 100% for employee-year context where configured.
- Midterm revision cutoff based on 9th month of the active FY.
- Year-end weighted scoring and rating flow with PIP trigger for below-threshold results.

## Setup (Laravel + MS SQL)

### 1. Configure Environment
- Copy `.env.example` to `.env`
- Set SQL Server connection fields:

```
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 2. Install and Build
```
composer install
npm install
npm run dev
php artisan key:generate
```

### 3. Initialize Database
```
php artisan migrate
php artisan db:seed
```

### 4. Ensure an Active FY Exists
```
php artisan app:fy-check
```
Or specify one:
```
php artisan app:fy-check 2026-27
```

### 5. Run the App
```
php artisan serve
```

## Files Updated In This Delivery
- `app/Http/Controllers/Appraisal/ObjectiveController.php`
- `resources/views/appraisal/line_manager/set_objectives.blade.php`
- `resources/views/appraisal/line_manager/team_objectives_form.blade.php`
- `resources/views/appraisal/super_admin/objectives_create.blade.php`
- `database/migrations/2025_10_19_000003_create_objectives_table.php`
- `database/migrations/2025_10_19_000004_create_appraisals_table.php`
- `app/Console/Commands/FyCheck.php`
- `database/seeders/ObjectiveSeeder.php`
- `database/seeders/AppraisalSeeder.php`
- `database/seeders/UsersAndDataSeeder.php`

## Validation Performed
- PHP syntax checks passed for all modified PHP files.
- Runtime/lint in full Laravel context requires dependency install (`composer install`) and database setup.

## Notes
- Existing docs from previous attempt remain in project for traceability.
- This delivery focuses on making FY behavior and appraisal workflow truly dynamic without breaking your current architecture.
