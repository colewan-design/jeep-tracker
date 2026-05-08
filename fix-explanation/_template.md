# Fix: [Short Title]

**Date:** YYYY-MM-DD
**Files Changed:** list them here

---

## What Was Broken
Describe the symptom — what the user saw or what was failing. No code yet, just plain English.

## Root Cause
Why it was actually broken. This is the "aha" part.

## What Was Changed

### `path/to/file.php` (or whatever)
- **What:** what line/block was changed
- **Before:** (paste old code snippet if helpful)
- **After:** (paste new code snippet)
- **Why:** the reason this specific change fixes the root cause

*(repeat per file)*

## How to Test
Step-by-step — assume you're coming back to this cold.

1. Run `php artisan serve` (or however the server starts)
2. Hit endpoint `POST /api/...` with payload:
   ```json
   { "key": "value" }
   ```
3. Expected response: `200 OK` with `{ ... }`
4. Also test the edge case: [describe it]

## Watch Out For
- Any side effects or related areas that could break
- Config/env values that need to be set
- DB migrations that need to run (`php artisan migrate`)
