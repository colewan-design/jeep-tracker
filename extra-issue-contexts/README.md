# Extra Issue Contexts

Reference materials attached to specific issues — screenshots, error logs, documentation snippets, API specs, etc.

## Structure
Each issue gets its own subfolder:
```
extra-issue-contexts/
├── issue-<id>-short-description/
│   ├── README.md        ← what the issue is and what resources are here
│   ├── screenshot-1.png
│   ├── error-log.txt
│   └── ref-docs.md
```

## Subfolder Naming
```
issue-<ticket-or-number>-<short-slug>
```
Examples:
- `issue-12-gps-not-updating`
- `issue-auth-token-expiry`
- `issue-jeep-route-map-blank`

## Issue README Format
Each subfolder should have a `README.md`:

```md
## Issue: [Short Title]

### Problem
What's going wrong. Include error messages verbatim if any.

### Resources in This Folder
| File | Description |
|------|-------------|
| screenshot-1.png | UI state when the bug occurs |
| error-log.txt | Laravel log dump |

### Relevant Code Areas
- `app/Http/Controllers/...`
- `routes/api.php` line X

### Notes
Anything else relevant — related issues, attempted fixes, etc.
```
