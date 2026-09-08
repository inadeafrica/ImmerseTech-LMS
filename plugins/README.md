# Custom Plugins

Per [ADR 0001](../docs/decisions/0001-platform-foundation.md), the core LMS runs on stock Moodle. This directory holds the ImmerseTech-specific plugins that fill the gaps Moodle doesn't cover natively. Each plugin is developed independently so it can be versioned, tested, and upstreamed/swapped without forking Moodle core.

Layout mirrors Moodle's own plugin-type directory structure (`plugins/<type-dir>/<name>` → mounts to `/bitnami/moodle/<type-dir>/<name>` in the container), so a plugin's location here doubles as documentation of which Moodle extension point it uses. Type directories are Moodle's own (per Moodle core's `lib/components.json`), not chosen freehand — e.g. availability conditions live under `availability/condition/<name>`, payment gateways under `payment/gateway/<name>`.

| Plugin | Component | Moodle plugin type | Spec section | Status |
|---|---|---|---|---|
| [`local/vrtracking`](local/vrtracking) | `local_vrtracking` | Local plugin | 5.4, 9.2 | Core structure implemented |
| [`availability/condition/safetygating`](availability/condition/safetygating) | `availability_safetygating` | Availability condition | 5.7 | Core structure implemented |
| [`payment/gateway/paystack`](payment/gateway/paystack) | `paygw_paystack` | Payment gateway | 5.12 | Core structure implemented |
| [`payment/gateway/flutterwave`](payment/gateway/flutterwave) | `paygw_flutterwave` | Payment gateway | 5.12 | Core structure implemented |
| [`plagiarism/aicontent`](plagiarism/aicontent) | `plagiarism_aicontent` | Plagiarism plugin | 5.2 | Core structure implemented |

"Core structure implemented" means: the plugin installs cleanly, implements the Moodle interfaces its type requires with real (not placeholder) logic, and has a working database schema, capabilities/services, language strings, and — where the plugin handles personal data — a privacy provider. What's explicitly stubbed (a third-party API call, mostly) is marked `TODO` in the relevant class and listed in that plugin's own `README.md`. None of the five are feature-complete end to end yet; see each plugin's README for what's left.

## Why these plugin types

- **VR tracking (5.4, 9.2)** needs unrestricted access to Moodle's competency API and gradebook, which only a `local` plugin grants — see [`local/vrtracking`](local/vrtracking).
- **Safety & compliance gating (5.7)** maps to Moodle's *availability condition* extension point — the same mechanism Moodle uses to gate activities on prerequisites (5.17), so a custom condition class lets a safety induction gate a lab/VR practical using the standard UI trainees and instructors already see for other restrictions.
- **Paystack / Flutterwave (5.12)** map to Moodle's *payment gateway* plugin type (`paygw_*`), the same extension point Moodle's own bundled PayPal gateway uses.
- **AI-content detection (5.2)** maps to Moodle's *plagiarism plugin* type, alongside the Turnitin/Copyleaks-style similarity checking Moodle already integrates well — keeping both integrity checks under the same submission-review UI.

## Verifying structural correctness without a running Moodle site

The sandbox this was built in has no Docker daemon, so none of this has been exercised against a live Moodle
install yet — `docker compose up` boots the plugins for the first time as part of review. Short of that, two checks
were done for every plugin:

1. Every PHP file is syntax-checked (`php -l`).
2. Each class implementing a Moodle-defined base class/interface (`core_availability\condition`/`frontend`,
   `core_payment\gateway`, `plagiarism_plugin`) was loaded and exercised against minimal stub classes built from the
   *actual* signatures read from Moodle core's own source (`MOODLE_405_STABLE` branch) — not from memory — with
   representative inputs run through `is_available()`, `get_description()`, `get_supported_currencies()`,
   `add_configuration_to_gateway_form()`, `get_links()`, and the `local_vrtracking_record_completion` webhook's
   full `execute()` path. This catches the class of bug the old scaffolding note in this file warned about (an
   unmet abstract method or incompatible signature throwing a fatal the moment Moodle tries to instantiate the
   class) without needing a full Moodle checkout. It does not catch a wrong assumption about a *non-abstract* core
   API's behaviour (e.g. `completion_info::get_data()`'s exact return shape) — that only a real install surfaces.
