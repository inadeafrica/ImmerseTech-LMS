# local_vrtracking

Moodle local plugin scaffold for VR Simulation Tracking (spec [5.4](../../../docs/SPECIFICATION.md#54-vr-simulation-tracking-add-on-module), integration flow in [9.2](../../../docs/SPECIFICATION.md#92-integration-flow)).

## Status

Core structure implemented; partner-specific mapping still open. The webhook entry point, DB schema, capability/service definitions, and NDPR privacy provider are in place and installable; the course-module-to-competency mapping and the actual `core_competency` evidence write are stubbed pending a decision on how that mapping is authored (see TODOs in `classes/session_repository.php` and `classes/competency_updater.php`).

## What's here

- `db/services.php` + `classes/external/record_completion.php` — the `local_vrtracking_record_completion` external function the VR partner platform calls on completion/progress (webhook via Moodle's own webservice REST endpoint, authenticated with a token restricted to the `local_vrtracking_partner` service — see `db/access.php`).
- `db/install.xml` — `local_vrtracking_session` table: one row per completion/progress event, including time-on-task, score, and a session replay URL.
- `classes/session_repository.php` — persistence for session records; `find_linked_competency()` is the one open mapping decision (TODO).
- `classes/competency_updater.php` — on a `completed` event, hands the session off as pending evidence rather than auto-approving it, per spec 5.6's assessor-sign-off requirement (TODO: wire to `\core_competency\api::add_evidence()` once the mapping above lands).
- `classes/privacy/provider.php` — NDPR export/delete-my-data support (spec 5.13) for the session table.
- `settings.php` — admin settings page (enable/disable, webservice setup pointer).

## Not yet built

- Course-module ⇄ competency mapping authoring UI/convention.
- Actual `core_competency` evidence write and assessor sign-off notification.
- Session replay/performance log surfaced in the gradebook/competency UI for instructors and Qualified Assessors (data is captured; the review UI isn't built).

## Why a `local` plugin

VR completions need to write into Moodle's competency API and be visible from the gradebook/competency UI, which isn't a natural fit for any of Moodle's more specific plugin types (activity module, block, etc.) — a `local` plugin gives unrestricted access to core APIs, which this integration needs.
