# Custom Plugins

Per [ADR 0001](../docs/decisions/0001-platform-foundation.md), the core LMS runs on stock Moodle. This directory holds the ImmerseTech-specific plugins that fill the gaps Moodle doesn't cover natively. Each plugin is developed independently so it can be versioned, tested, and upstreamed/swapped without forking Moodle core.

Layout mirrors Moodle's own plugin-type directory structure (`plugins/<type-dir>/<name>` → bind-mounted to `moodle/<type-dir>/<name>`, i.e. `/var/www/html/<type-dir>/<name>` in the `webserver` container — see `docker-compose.yml`), so a plugin's location here doubles as documentation of which Moodle extension point it uses.

| Plugin | Component | Moodle plugin type | Spec section | Status |
|---|---|---|---|---|
| [`local/vrtracking`](local/vrtracking) | `local_vrtracking` | Local plugin | 5.4, 9.2 | Scaffolded |
| `availability/safetygating` | `availability_safetygating` | Availability condition | 5.7 | Planned |
| `payment/gw/paystack` | `paygw_paystack` | Payment gateway | 5.12 | Planned |
| `payment/gw/flutterwave` | `paygw_flutterwave` | Payment gateway | 5.12 | Planned |
| `plagiarism/aicontent` | `plagiarism_aicontent` | Plagiarism plugin | 5.2 | Planned |

## Why these plugin types

- **Safety & compliance gating (5.7)** maps to Moodle's *availability condition* extension point — the same mechanism Moodle uses to gate activities on prerequisites (5.17), so a custom condition class lets a safety induction gate a lab/VR practical using the standard UI trainees and instructors already see for other restrictions.
- **Paystack / Flutterwave (5.12)** map to Moodle's *payment gateway* plugin type (`paygw_*`), the same extension point Moodle's built-in Stripe/PayPal support uses.
- **AI-content detection (5.2)** maps to Moodle's *plagiarism plugin* type, alongside the Turnitin/Copyleaks-style similarity checking Moodle already integrates well — keeping both integrity checks under the same submission-review UI.

## Only `local_vrtracking` is scaffolded so far

Availability conditions, payment gateways, and plagiarism plugins all implement specific Moodle interfaces (e.g. `\core_availability\condition`, `\core_payment\gateway`); an incomplete implementation can throw fatal errors as soon as Moodle tries to instantiate it, rather than failing quietly. They're intentionally left as documentation-only entries here until each is built out properly, rather than scaffolded with unverified boilerplate.
