# UAT Checklist - Dynamic Performance Appraisal System

Base URL: http://127.0.0.1:8000

Detailed tester script with test IDs and screenshot naming:
- UAT_TESTER_EXECUTION_SCRIPT.md

QA submission report template:
- UAT_EXECUTION_REPORT_TEMPLATE.md

## Prerequisites
- Application running: `php artisan serve`
- Active financial year exists: `php artisan app:fy-check`
- Test data seeded: `php artisan db:seed`

## Suggested Test Accounts
- Super Admin: `admin@ntg.com.bd` / `12345678`
- HR Admin: `hr.admin@ntg.com.bd` / `12345678`
- Board: `board@ntg.com.bd` / `12345678`
- Line Manager: `manager@ntg.com.bd` / `12345678`
- Employee: `employee@ntg.com.bd` / `12345678`

## HR Admin UAT
1. Login as HR Admin.
2. Go to user management and verify create/edit user works.
3. Go to financial years and confirm one active FY is visible.
4. Create or edit departmental/team objective context (if assigned).
5. Open reports page and confirm appraisal list loads.
6. Verify audit-log entries are generated for key actions.

Expected:
- HR pages accessible.
- No authorization errors.
- Actions persist to DB and appear in reports/logs.

## Board UAT
1. Login as Board user.
2. Open board departmental objective page.
3. Create departmental objectives for one department.
4. Confirm rule checks:
   - 2 to 3 departmental objectives
   - total departmental weightage = 30
5. Save and verify objectives are visible to manager/department flow.

Expected:
- Board can set departmental objectives.
- Validation messages appear for invalid weightage/count.

## Line Manager UAT
1. Login as Line Manager.
2. Open team objectives page.
3. Open one employee objective setting page.
4. Set individual objectives ensuring total = 70 and valid weightages.
5. Submit midterm review comments.
6. Submit year-end scores for all employee objectives.
7. If score below threshold, verify PIP is auto-created.

Expected:
- Manager can access only direct-report workflows.
- Midterm/year-end submissions save successfully.
- PIP created automatically for below-threshold year-end result.

## Employee UAT
1. Login as Employee.
2. Open My Objectives and review assigned objectives for active FY.
3. Submit objective confirmation (if enabled by flow).
4. Submit midterm self-assessment.
5. Submit year-end self-assessment.
6. Sign appraisal when available.

Expected:
- Employee sees own objectives only.
- Cannot access manager/admin pages.
- Self-assessment and signature flow works.

## Signature Order UAT
1. Create a year-end appraisal for an employee.
2. Try supervisor signing before manager.
3. Confirm validation blocks this action.
4. Complete manager sign, then supervisor sign.

Expected:
- Supervisor-before-manager is blocked.
- Correct signing order is enforced.

## Dynamic Financial Year UAT
1. Create a new financial year in admin panel.
2. Activate the new FY.
3. Repeat objective creation for an employee.
4. Verify records are saved under the active FY label.

Expected:
- No hardcoded FY behavior.
- New records align with active FY automatically.

## Regression Smoke Checklist
- Login/logout works for all roles.
- Dashboard loads for each role.
- No 500 errors in browser during core flows.
- CSS/JS assets load correctly.
- PDF generation routes (if used) return downloadable output.

## Automated Validation Snapshot
- Feature test suite: passed 53, failed 0.
