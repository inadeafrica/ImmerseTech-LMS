# paygw_flutterwave

Moodle payment-gateway plugin for Flutterwave (spec [5.12](../../../../docs/SPECIFICATION.md#512-payments--enrollment)).

Same shape as [`paygw_paystack`](../paystack) (both modelled on Moodle core's bundled PayPal gateway), swapped for
Flutterwave's API and its broader West/Central/East African currency coverage — relevant given ImmerseTech's
multi-country reach (spec 5.19).

## Status

Structurally complete; the actual Flutterwave API call is stubbed.

## What's here

- `classes/gateway.php` — `core_payment\gateway` implementation: supported currencies (NGN primary, plus GHS/KES/UGX/TZS/ZAR/XAF/XOF/USD), and the account configuration form (business name, public key, secret key, webhook secret hash, live/test).
- `classes/flutterwave_helper.php` — thin API client. `verify_transaction()` is a stub (throws) — see TODO in the file for the exact Flutterwave endpoint and header. `verify_webhook_signature()` is implemented (constant-time comparison against the configured secret hash).
- `classes/external/get_config_for_js.php` — feeds the client-side Flutterwave Inline JS checkout its public key, cost, and currency.
- `classes/external/transaction_complete.php` — called after the checkout reports success; verifies server-side via `flutterwave_helper` before trusting the client, checks amount/currency match, then records the payment and delivers the order.
- `db/install.xml` — `paygw_flutterwave` table linking a Moodle payment record to its Flutterwave transaction id and reference.

## Not yet built

- `flutterwave_helper::verify_transaction()` — the actual HTTP call to Flutterwave's `GET /v3/transactions/:id/verify`.
- `amd/src/gateways_modal.js` — the client-side Flutterwave Inline checkout integration.
- A webhook receiver endpoint using `verify_webhook_signature()` — Flutterwave recommends confirming payment via both the redirect callback and an independent webhook; only the callback path (`transaction_complete`) is built so far.
