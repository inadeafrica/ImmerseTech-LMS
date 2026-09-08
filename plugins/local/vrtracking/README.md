# local_vrtracking

Moodle local plugin for VR Simulation Tracking (spec [5.4](../../../docs/SPECIFICATION.md#54-vr-simulation-tracking-add-on-module), integration flow in [9.2](../../../docs/SPECIFICATION.md#92-integration-flow)).

## Status

The full VR-completion-to-competency-review pipeline works end to end: webhook in, session recorded, linked
competency(ies) logged as evidence and flagged for assessor review. What's left is partner-specific (the exact VR
platform's webhook payload shape isn't known yet) rather than structural.

## What's here

- `db/services.php` + `classes/external/record_completion.php` — the `local_vrtracking_record_completion` external function the VR partner platform calls on completion/progress (webhook via Moodle's own webservice REST endpoint, authenticated with a token restricted to the `local_vrtracking_partner` service — see `db/access.php`).
- `db/install.xml` — `local_vrtracking_session` table: one row per completion/progress event, including time-on-task, score, and a session replay URL.
- `classes/session_repository.php` — persistence for session records, plus `get_linked_competency_ids()`, which reads Moodle's own activity-level competency linking (`core_competency\course_module_competency` — the "Competencies" tab already present on every activity's edit form). No separate mapping table or authoring UI was needed: an instructor tags the VR practical with the competency(ies) it demonstrates exactly the same way they would for any other activity.
- `classes/competency_updater.php` — on a `completed` event, calls `\core_competency\api::add_evidence()` for every competency linked to the activity, using `evidence::ACTION_LOG` with `$recommend = true` (not `ACTION_COMPLETE`, which core's own API docs flag as "an action to use with automated systems" — the auto-approve path spec 5.6 explicitly rules out). This logs the session replay URL and a time-on-task/score note against the evidence, and flips the trainee's `user_competency` status to `STATUS_WAITING_FOR_REVIEW` — which surfaces in Moodle's own competency review screens for an Instructor or Qualified Assessor to act on, with no custom review UI needed.
- `classes/privacy/provider.php` — NDPR export/delete-my-data support (spec 5.13) for the session table.
- `settings.php` — admin settings page (enable/disable, webservice setup pointer).
- `tests/` — PHPUnit coverage for `session_repository` (including the competency-linking lookup), `competency_updater` (evidence logged, review status set, multiple linked competencies, competency subsystem disabled, deleted course module survived), the `record_completion` webhook end to end, and the privacy provider. Run against a real Moodle install — see `../README.md#running-the-tests`.

## Not yet built

- Expiry-approaching / sign-off-pending notifications (spec 5.9) — evidence is logged and flagged for review, but nobody is proactively told a sign-off is waiting; today an assessor finds it by visiting the competency review screen.
- Session replay/performance log surfaced *inline* in the gradebook (data is on the evidence record and clickable from the competency review screen, per the above — but not duplicated into the gradebook itself).
- A convention for a VR practical outcome to require full-marks/pass-only performance before evidence is logged at all (currently any `completed` event logs evidence regardless of `$score`) — the spec doesn't call for this, so it's not built rather than stubbed, but worth flagging if a future practical needs a pass threshold.

## Why a `local` plugin

VR completions need to write into Moodle's competency API and be visible from the gradebook/competency UI, which isn't a natural fit for any of Moodle's more specific plugin types (activity module, block, etc.) — a `local` plugin gives unrestricted access to core APIs, which this integration needs.
