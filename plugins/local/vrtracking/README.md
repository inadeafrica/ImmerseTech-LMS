# local_vrtracking

Moodle local plugin scaffold for VR Simulation Tracking (spec [5.4](../../../docs/SPECIFICATION.md#54-vr-simulation-tracking-add-on-module), integration flow in [9.2](../../../docs/SPECIFICATION.md#92-integration-flow)).

## Status

Scaffolded, not yet implemented. `version.php` and the language string file are in place so the plugin installs cleanly; no functional code yet.

## Planned scope

- A webhook endpoint that receives completion status, time-on-task, and performance score from the VR partner platform (xAPI/SCORM package or JSON webhook, per [Section 9.1](../../../docs/SPECIFICATION.md#91-partner-selection-criteria)).
- Logic to update the relevant trainee's competency matrix entry for the linked skill once a VR module is marked complete, so it can satisfy competency requirements the same way a physical lab sign-off does (5.5, 5.6).
- Session replay/performance log links surfaced to instructors and Qualified Assessors for competency sign-off review.

## Why a `local` plugin

VR completions need to write into Moodle's competency API and be visible from the gradebook/competency UI, which isn't a natural fit for any of Moodle's more specific plugin types (activity module, block, etc.) — a `local` plugin gives unrestricted access to core APIs, which this integration needs.
