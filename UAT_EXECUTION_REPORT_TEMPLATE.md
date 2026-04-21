# UAT Execution Report Template

Date:
Project: til_app_real-dynamic-ai
Environment: Local / Staging / Production
Base URL: http://127.0.0.1:8000
Build/Commit:

## QA Team Details
- QA Lead:
- Tester(s):
- Test Window (Start - End):

## Scope
- Included Modules:
  - Financial Year Management
  - Objective Setting (Board / Line Manager / Employee)
  - Midterm Appraisal
  - Year-End Appraisal
  - Signature Flow
  - PIP Auto-Creation
- Excluded Modules:

## Preconditions Verification
- [ ] Server reachable
- [ ] Active financial year configured
- [ ] Seed users available
- [ ] Browser and cache prepared

Notes:

## Test Case Results

| TC ID | Role | Title | Expected Result | Actual Result | Status (PASS/FAIL/BLOCKED) | Defect ID | Evidence File |
|---|---|---|---|---|---|---|---|
| UAT-HR-001 | HR Admin | Login and dashboard | Login succeeds, dashboard loads |  |  |  |  |
| UAT-HR-002 | HR Admin | User management CRUD visibility | List/create/edit screens load |  |  |  |  |
| UAT-HR-003 | HR Admin | Financial year administration | Single active FY enforced |  |  |  |  |
| UAT-HR-004 | HR Admin | Reports and audit navigation | Pages load without authorization errors |  |  |  |  |
| UAT-BD-001 | Board | Board objective page access | Board can access departmental setup |  |  |  |  |
| UAT-BD-002 | Board | Departmental objective validation | Invalid blocked, valid save accepted |  |  |  |  |
| UAT-LM-001 | Line Manager | Team objectives access | Direct-report objective pages accessible |  |  |  |  |
| UAT-LM-002 | Line Manager | Individual objective submission | Save succeeds with valid rules |  |  |  |  |
| UAT-LM-003 | Line Manager | Midterm review submission | Midterm save succeeds |  |  |  |  |
| UAT-LM-004 | Line Manager | Year-end and PIP trigger | Year-end saved and PIP auto-created for low score |  |  |  |  |
| UAT-EM-001 | Employee | Employee objective visibility | Only own objectives visible |  |  |  |  |
| UAT-EM-002 | Employee | Midterm self-assessment | Submission succeeds |  |  |  |  |
| UAT-EM-003 | Employee | Year-end self-assessment | Submission succeeds |  |  |  |  |
| UAT-EM-004 | Employee | Signature participation | Employee signature recorded |  |  |  |  |
| UAT-CR-001 | Cross-role | Signature order enforcement | Supervisor-before-manager blocked |  |  |  |  |
| UAT-CR-002 | Cross-role | Dynamic FY switching | New records saved in active FY |  |  |  |  |

## Defect Log

### DEF-001
- Severity:
- Priority:
- Related TC ID:
- Description:
- Steps to Reproduce:
- Expected:
- Actual:
- Attachments:
- Current Status:

### DEF-002
- Severity:
- Priority:
- Related TC ID:
- Description:
- Steps to Reproduce:
- Expected:
- Actual:
- Attachments:
- Current Status:

## Summary Metrics
- Total Test Cases Planned:
- Total Executed:
- Passed:
- Failed:
- Blocked:
- Pass Rate (%):

## Risk Assessment
- High Risk Findings:
- Medium Risk Findings:
- Low Risk Findings:

## Go/No-Go Recommendation
- Recommendation: GO / NO-GO / CONDITIONAL GO
- Conditions (if conditional):

## Sign-Off
- QA Lead Name:
- QA Lead Signature:
- Product Owner Name:
- Product Owner Signature:
- Date:
