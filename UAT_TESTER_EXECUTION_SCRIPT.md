# UAT Tester Execution Script

Date: 2026-03-27
Project: til_app_real-dynamic-ai
Environment: Local
Base URL: http://127.0.0.1:8000

## Tester Info
- Tester Name:
- Test Start Time:
- Test End Time:
- Build/Commit Reference:

## Evidence Rules
- For every test case, capture at least one screenshot.
- Use this file naming format:
  - UAT-<TCID>-<ROLE>-<NN>.png
  - Example: UAT-HR-001-HR-01.png
- Store screenshots in:
  - storage/app/public/uat-evidence/

## Pre-Run Checklist
- [ ] Server running at base URL.
- [ ] Active financial year confirmed from admin page.
- [ ] Seed users available and login credentials verified.
- [ ] Browser cache cleared.

## Execution Template (Use for each case)
- Test Case ID:
- Role:
- Steps Executed:
- Expected Result:
- Actual Result:
- Status: PASS / FAIL / BLOCKED
- Screenshot(s):
- Defect ID (if failed):

## HR Admin Test Cases

### UAT-HR-001: HR Login and Dashboard Access
- Role: HR Admin
- Steps:
  1. Login with hr.admin@ntg.com.bd / 12345678.
  2. Open dashboard.
- Expected:
  - Login succeeds.
  - Dashboard loads without 403/500.
- Screenshot Required:
  - UAT-HR-001-HR-01.png
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

### UAT-HR-002: User Management CRUD Visibility
- Role: HR Admin
- Steps:
  1. Open users list.
  2. Open create user form.
  3. Open edit user form for one existing user.
- Expected:
  - List, create, and edit screens load correctly.
- Screenshot Required:
  - UAT-HR-002-HR-01.png
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

### UAT-HR-003: Financial Year Administration
- Role: HR Admin
- Steps:
  1. Open financial years page.
  2. Confirm one financial year is active.
  3. Activate another FY if available, then re-check active tag.
- Expected:
  - Exactly one FY remains active after activation.
- Screenshot Required:
  - UAT-HR-003-HR-01.png
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

### UAT-HR-004: Reports and Audit Navigation
- Role: HR Admin
- Steps:
  1. Open reports page.
  2. Open audit log page.
- Expected:
  - Pages load with records, no permission errors.
- Screenshot Required:
  - UAT-HR-004-HR-01.png
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

## Board Test Cases

### UAT-BD-001: Board Login and Departmental Objective Page
- Role: Board
- Steps:
  1. Login with board@ntg.com.bd / 12345678.
  2. Open board objective-setting page.
- Expected:
  - Board can access departmental objective setup page.
- Screenshot Required:
  - UAT-BD-001-BD-01.png
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

### UAT-BD-002: Departmental Objective Validation
- Role: Board
- Steps:
  1. Attempt save with invalid count (<2 or >3).
  2. Attempt save with total weightage not equal to 30.
  3. Save valid set with 2-3 objectives and total 30.
- Expected:
  - Invalid attempts show validation errors.
  - Valid attempt saves successfully.
- Screenshot Required:
  - UAT-BD-002-BD-01.png (validation)
  - UAT-BD-002-BD-02.png (success)
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

## Line Manager Test Cases

### UAT-LM-001: Team Objectives Access
- Role: Line Manager
- Steps:
  1. Login with manager@ntg.com.bd / 12345678.
  2. Open team objectives page.
  3. Open one employee objective setting form.
- Expected:
  - Manager can access direct-report objective flows.
- Screenshot Required:
  - UAT-LM-001-LM-01.png
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

### UAT-LM-002: Individual Objective Submission
- Role: Line Manager
- Steps:
  1. Set individual objectives for one employee.
  2. Keep objective count and weights valid.
  3. Save.
- Expected:
  - Save succeeds and objectives appear for selected employee/FY.
- Screenshot Required:
  - UAT-LM-002-LM-01.png
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

### UAT-LM-003: Midterm Review Submission
- Role: Line Manager
- Steps:
  1. Open conduct midterm for one employee.
  2. Submit comments/review values.
- Expected:
  - Midterm submission is accepted and persisted.
- Screenshot Required:
  - UAT-LM-003-LM-01.png
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

### UAT-LM-004: Year-End Review and PIP Trigger
- Role: Line Manager
- Steps:
  1. Open conduct year-end for one employee.
  2. Submit low scores that should trigger below-threshold logic.
  3. Verify PIP generated.
- Expected:
  - Year-end appraisal saved.
  - PIP created for below-threshold result.
- Screenshot Required:
  - UAT-LM-004-LM-01.png (submission)
  - UAT-LM-004-LM-02.png (PIP evidence)
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

## Employee Test Cases

### UAT-EM-001: Employee Login and Objectives Visibility
- Role: Employee
- Steps:
  1. Login with employee@ntg.com.bd / 12345678.
  2. Open my objectives page.
- Expected:
  - Employee sees own objectives only.
- Screenshot Required:
  - UAT-EM-001-EM-01.png
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

### UAT-EM-002: Midterm Self-Assessment
- Role: Employee
- Steps:
  1. Open midterm review page.
  2. Submit self-assessment.
- Expected:
  - Submission succeeds and no permission issues.
- Screenshot Required:
  - UAT-EM-002-EM-01.png
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

### UAT-EM-003: Year-End Self-Assessment
- Role: Employee
- Steps:
  1. Open year-end self-assessment page.
  2. Submit entries.
- Expected:
  - Submission succeeds and data saved.
- Screenshot Required:
  - UAT-EM-003-EM-01.png
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

### UAT-EM-004: Signature Flow Participation
- Role: Employee
- Steps:
  1. Open appraisal sign flow when available.
  2. Submit employee signature.
- Expected:
  - Employee signature recorded successfully.
- Screenshot Required:
  - UAT-EM-004-EM-01.png
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

## Cross-Role Sequence Test Cases

### UAT-CR-001: Signature Order Enforcement
- Roles: Supervisor, Manager, Employee
- Steps:
  1. Try supervisor sign before manager sign.
  2. Confirm rejection.
  3. Complete manager sign.
  4. Re-try supervisor sign.
- Expected:
  - Step 1 blocked by validation.
  - Step 4 succeeds.
- Screenshot Required:
  - UAT-CR-001-CR-01.png (blocked)
  - UAT-CR-001-CR-02.png (success)
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

### UAT-CR-002: Dynamic Financial Year Switching
- Roles: HR Admin, Line Manager
- Steps:
  1. Activate a different financial year.
  2. Create new objective record.
  3. Verify saved record FY label.
- Expected:
  - Records saved against active FY only.
  - No hardcoded year behavior.
- Screenshot Required:
  - UAT-CR-002-CR-01.png
- Status: [ ] PASS [ ] FAIL [ ] BLOCKED

## Defect Log Section
- DEF-001:
  - Linked Test Case:
  - Severity:
  - Description:
  - Steps to Reproduce:
  - Screenshot:

- DEF-002:
  - Linked Test Case:
  - Severity:
  - Description:
  - Steps to Reproduce:
  - Screenshot:

## Final Tester Sign-Off
- Total Test Cases Executed:
- Passed:
- Failed:
- Blocked:
- Final Recommendation: GO / NO-GO
- Tester Name and Signature:
- Date:
