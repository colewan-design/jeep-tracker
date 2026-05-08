# Extra Issue Contexts

Reference materials, screenshots, and documentation for specific issues.
Each issue gets its own subfolder.

---

## Folder Structure

```
extra-issue-contexts/
├── README.md               ← you are here
├── issue-001-gradle-build/ ← one folder per issue
│   ├── context.md          ← what the issue is, what was tried
│   ├── screenshot-1.png    ← any screenshots / error logs saved
│   └── reference-links.md  ← docs / SO posts / GitHub issues that helped
└── issue-002-gps-timeout/
    └── ...
```

---

## How to Use

1. When you hit a new issue, create a subfolder: `issue-NNN-short-name/`
2. Inside it, create a `context.md` using the template below
3. Drop any screenshots, exported logs, or saved documentation in the same folder
4. Link the relevant `fix-explanation` log once the issue is resolved

---

## context.md Template

```markdown
# Issue: [Short Title]

## Summary
One paragraph describing what the problem is.

## Environment
- RN version:
- Device:
- Build type: debug / release

## Steps to Reproduce
1.
2.
3.

## Error Message / Log
\`\`\`
paste error here
\`\`\`

## What Was Already Tried
- Tried X → result
- Tried Y → result

## References
- [Link title](url)

## Status
- [ ] Open
- [ ] In progress
- [ ] Resolved → see fix-explanation/[filename].md
```

---

## Active Issues

| # | Folder | Summary | Status |
|---|--------|---------|--------|
| 001 | `issue-001-gradle-build/` | async-storage v3 maven artifact missing | Resolved |
| 002 | `issue-002-gps-fused-location/` | FusedLocationProviderClient IncompatibleClassChangeError | Resolved |
| 003 | `issue-003-gps-timeout/` | GPS timed out — watchPosition fix | Resolved |
| 004 | `issue-004-maps-white-screen/` | Google Maps white screen — missing API key | In progress |
