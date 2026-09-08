# availability_safetygating

Moodle availability-condition plugin for Safety & Compliance Gating (spec [5.7](../../../docs/SPECIFICATION.md#57-safety--compliance-gating-add-on-module)).

## Status

Core condition logic implemented; the "Add restriction" JS form is not yet built.

## What's here

- `classes/condition.php` — the actual gate. References a safety induction course-module id and an optional validity period (days). `is_available()` reads the induction activity's live completion state each time it's evaluated (via `completion_info::get_data()`), so a lapsed certification re-locks the gated activity automatically on the next page load — no cron/expiry job needed for the re-lock itself to take effect (a separate notification job for "your certification is expiring" per spec 5.9 is still TODO — see `plugins/README.md`).
- `classes/frontend.php` — server-side half of the "Add restriction" dialog (populates the list of candidate induction activities).
- `lang/en/availability_safetygating.php` — condition description strings, following the pattern of Moodle's own `availability_completion`.
- `tests/condition_test.php` — PHPUnit coverage against a real course and completion-tracked activity, including the expiry/re-lock path (backdating a real completion timestamp) and the fail-closed behaviour on a missing induction activity. Run against a real Moodle install — see `../../../README.md#running-the-tests`.

## Not yet built

- `amd/src/form.js` — the client-side form for the "Add restriction" dialog (`core_availability/form` subclass). Without it, this condition can be attached via restore/import or programmatically, but not yet picked from the course editor's "Add restriction" UI.
- Expiry-approaching notifications (spec 5.9) — currently the re-lock is silent; trainees/admins aren't proactively warned before a certification lapses.
- Equipment-specific safety briefings beyond a single induction activity (spec 5.7 mentions "any equipment-specific safety briefings") — today one condition instance gates on one induction activity; stacking multiple `safetygating` conditions (AND) already covers this without further plugin work, so this is a documentation/usage note more than a code gap.
