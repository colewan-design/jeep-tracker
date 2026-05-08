# Audit Log

Weekly codebase audits — checking for missed issues, hidden bugs, security gaps, or anything that could blow up later.

## Schedule
- Frequency: **1x per week** (recommended: every Monday or end of sprint)
- Scope: full codebase or per-module depending on recent changes

## Audit File Naming
```
YYYY-MM-DD-audit.md
```
Example: `2026-05-08-audit.md`

## What to Cover Per Audit
- [ ] Unhandled errors / missing try-catch
- [ ] Hardcoded values that should be in `.env`
- [ ] Missing validation on inputs (API routes, form data)
- [ ] N+1 query issues or missing eager loads
- [ ] Dead code / unused routes / orphaned files
- [ ] Auth/permission checks on protected routes
- [ ] Missing indexes on queried columns
- [ ] TODO/FIXME comments left in code
- [ ] Inconsistent naming conventions
- [ ] Any recent fix that introduced a regression

## Audit Entry Format
```md
## [Date] Audit

### Scope
What was reviewed (e.g., "all API routes", "auth module", "full codebase")

### Issues Found
| # | File | Line | Issue | Severity | Status |
|---|------|------|-------|----------|--------|
| 1 | app/Http/... | 42 | Description | high/med/low | open/fixed |

### Notes
Any observations that don't fit the table above.
```
