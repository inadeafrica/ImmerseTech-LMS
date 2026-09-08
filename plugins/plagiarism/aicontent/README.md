# plagiarism_aicontent

Moodle plagiarism-type plugin for AI-generated-content detection (spec [5.2](../../../docs/SPECIFICATION.md#52-assignments--lesson-submissions)).

Distinct from similarity/plagiarism checking, which Moodle already integrates well via Turnitin/Copyleaks-style
plugins of this same type ([ADR 0001](../../../docs/decisions/0001-platform-foundation.md)). Modelled on real-world
plugins of this type (Turnitin's `plagiarism_turnitinsim`) for the base class and settings-page conventions.

## Status

Core plumbing implemented; the actual detection API call and text extraction are stubbed.

## What's here

- `lib.php` — `plagiarism_plugin_aicontent`, the required subclass of Moodle's `plagiarism_plugin`. `get_links()` shows the likelihood score (or a "pending" indicator) beside a submission; `print_disclosure()` shows the advisory disclosure spec 5.2 requires.
- `classes/observer.php` — on `\mod_assign\event\assessable_submitted`, queues an adhoc task rather than calling the detection API inline, so a slow/unavailable provider can never block or fail a trainee's submission.
- `classes/task/check_submission.php` — the adhoc task; the text-extraction step (pulling plain text out of a submission for scoring) is a TODO.
- `classes/detector.php` — API client. `score()` is a stub (throws) — no provider is chosen yet (spec doesn't name one, unlike the similarity checker), so this stays provider-agnostic until then.
- `classes/result_repository.php` — persistence and lookup for detection results.
- `classes/privacy/provider.php` — NDPR export/delete-my-data support (spec 5.13), including declaring the external data flow to the detection provider.
- `settings.php` — admin config (enable, API endpoint/key, display flag threshold). Plagiarism plugins are administered as a standalone external page rather than the `$settings` fragment other plugin types use, per `\core\plugininfo\plagiarism::load_settings()` — this follows that convention.
- `tests/` — PHPUnit coverage: `get_links()`/`print_disclosure()` across enabled/disabled/pending/flagged states, `result_repository`, the submission observer (including the relateduserid-over-actor case), and the adhoc task's current early-return behaviour. Run against a real Moodle install — see `../../README.md#running-the-tests`.

## Not yet built

- Provider selection and `detector::score()`'s actual HTTP call.
- Submission text extraction in `check_submission` (online text vs. uploaded file with extractable text vs. non-text file types that should be skipped entirely — spec 5.2 applies "primarily to text-based submissions").
- Configurable per-assignment auto-block/flag threshold and behaviour (spec 5.2 describes this as configurable per assignment; today only a site-wide display threshold exists in `settings.php`).
- Oral-defense/in-class-follow-up workflow for borderline cases (spec 5.2) — this is an instructor workflow feature, not something this plugin's data model blocks.
