# Custom Plugins

Per [ADR 0001](../docs/decisions/0001-platform-foundation.md), the core LMS runs on stock Moodle. This directory holds the ImmerseTech-specific plugins that fill the gaps Moodle doesn't cover natively. Each plugin is developed independently so it can be versioned, tested, and upstreamed/swapped without forking Moodle core.

Layout mirrors Moodle's own plugin-type directory structure (`plugins/<type-dir>/<name>` → bind-mounted to `moodle/<type-dir>/<name>`, i.e. `/var/www/html/<type-dir>/<name>` in the `webserver` container — see `docker-compose.yml`), so a plugin's location here doubles as documentation of which Moodle extension point it uses. Type directories are Moodle's own (per Moodle core's `lib/components.json`), not chosen freehand — e.g. availability conditions live under `availability/condition/<name>`, payment gateways under `payment/gateway/<name>`.

| Plugin | Component | Moodle plugin type | Spec section | Status |
|---|---|---|---|---|
| [`local/vrtracking`](local/vrtracking) | `local_vrtracking` | Local plugin | 5.4, 9.2 | Core structure implemented |
| [`availability/condition/safetygating`](availability/condition/safetygating) | `availability_safetygating` | Availability condition | 5.7 | Core structure implemented |
| [`payment/gateway/paystack`](payment/gateway/paystack) | `paygw_paystack` | Payment gateway | 5.12 | Core structure implemented |
| [`payment/gateway/flutterwave`](payment/gateway/flutterwave) | `paygw_flutterwave` | Payment gateway | 5.12 | Core structure implemented |
| [`plagiarism/aicontent`](plagiarism/aicontent) | `plagiarism_aicontent` | Plagiarism plugin | 5.2 | Core structure implemented |

"Core structure implemented" means: the plugin installs cleanly, implements the Moodle interfaces its type requires with real (not placeholder) logic, and has a working database schema, capabilities/services, language strings, a `tests/` suite exercising that logic, and — where the plugin handles personal data — a privacy provider. What's explicitly stubbed (a third-party API call, mostly) is marked `TODO` in the relevant class and listed in that plugin's own `README.md`. None of the five are feature-complete end to end yet; see each plugin's README for what's left.

## Why these plugin types

- **VR tracking (5.4, 9.2)** needs unrestricted access to Moodle's competency API and gradebook, which only a `local` plugin grants — see [`local/vrtracking`](local/vrtracking).
- **Safety & compliance gating (5.7)** maps to Moodle's *availability condition* extension point — the same mechanism Moodle uses to gate activities on prerequisites (5.17), so a custom condition class lets a safety induction gate a lab/VR practical using the standard UI trainees and instructors already see for other restrictions.
- **Paystack / Flutterwave (5.12)** map to Moodle's *payment gateway* plugin type (`paygw_*`), the same extension point Moodle's own bundled PayPal gateway uses.
- **AI-content detection (5.2)** maps to Moodle's *plagiarism plugin* type, alongside the Turnitin/Copyleaks-style similarity checking Moodle already integrates well — keeping both integrity checks under the same submission-review UI.

## Tests

Every plugin has a `tests/` suite (real Moodle PHPUnit tests, `\advanced_testcase`-based) covering the logic
described above — 51 tests, 137 assertions in total as of this writing. They were written and run against an
actual Moodle 5.0.2 (`MOODLE_502_STABLE`, matching the `moodle/` submodule) + PostgreSQL install, not just
syntax-checked or exercised against hand-written stubs — see "Running the tests" below. Running them caught one real
bug during development: `availability_safetygating\condition::is_available()` compared `completionstate` with a
strict `in_array(..., true)` against integer constants, but the DB layer can return that field as a numeric string,
so the strict comparison always failed and the condition silently never unlocked. Fixed in
`classes/condition.php`; see the `test_usage_no_expiry`/`test_expiry` tests in that plugin's `tests/condition_test.php`
for the regression coverage.

What each suite covers:

- **`local_vrtracking`** — `session_repository` persistence; the `local_vrtracking_record_completion` webhook end to
  end (success, missing capability, unknown status, unknown course module); the privacy provider's export/delete.
- **`availability_safetygating`** — constructor validation, `save()`/`get_json()` round-trip, `is_available()` and
  `get_description()` against a real course + completion-tracked activity (including the expiry/re-lock path, by
  backdating a real `course_modules_completion.timemodified` row), the fail-closed behaviour when the induction
  activity is missing, and `update_dependency_id()`.
- **`paygw_paystack` / `paygw_flutterwave`** — supported currencies, `validate_gateway_form()`, the account
  configuration form rendering against a real DB-backed payment account (via `core_payment`'s own test generator and
  an `enrol_fee` payable, the same pattern Moodle core's own `enrol_fee` payment tests use), `get_config_for_js`, and
  that `transaction_complete` degrades to a clean failure (not a fatal) while `verify_transaction()` is still a
  stub — plus a test on each helper class pinning that "not yet implemented" behaviour so a silent regression to
  "always succeeds" would be caught.
- **`plagiarism_aicontent`** — `get_links()`/`print_disclosure()` under the enabled/disabled/pending/flagged states,
  `result_repository`, the submission observer (queues the adhoc task with the right data, including the
  relateduserid-over-actor case, and stays quiet when disabled), and the adhoc task's current early-return behaviour
  while text extraction is unbuilt.

## Running the tests

Requires the `moodle` submodule checked out (`git submodule update --init`) and a Moodle PHPUnit test environment
initialised against it — see [Moodle's own PHPUnit docs](https://moodledev.io/general/development/tools/phpunit) for
the full setup. Two non-obvious environment requirements tripped this up in a fresh sandbox and are worth calling out:
Moodle's PHPUnit environment check requires the `en_AU.UTF-8` locale to be installed (`locale-gen en_AU.UTF-8` on
Debian/Ubuntu) and PHP's `max_input_vars` to be at least 5000 (the default is 1000). Once initialised, run e.g.:

```bash
vendor/bin/phpunit public/local/vrtracking/tests/session_repository_test.php
```

from the `moodle/` submodule root (paths are relative to wherever each plugin is mounted there — see the table
above).
