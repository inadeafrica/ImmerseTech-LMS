# paygw_paystack

Moodle payment-gateway plugin for Paystack (spec [5.12](../../../../docs/SPECIFICATION.md#512-payments--enrollment)).

Modelled directly on Moodle core's bundled PayPal gateway (`payment/gateway/paypal`), which follows the same
`core_payment\gateway` contract — same shape, Paystack-specific fields and API calls.

## Status

Structurally complete; the actual Paystack API call is stubbed.

## What's here

- `classes/gateway.php` — `core_payment\gateway` implementation: supported currencies (NGN primary, plus GHS/ZAR/KES/USD), and the account configuration form (business name, public key, secret key, live/test).
- `classes/paystack_helper.php` — thin API client. `verify_transaction()` is a stub (throws) — see TODO in the file for the exact Paystack endpoint and header.
- `classes/external/get_config_for_js.php` — feeds the client-side Paystack Inline JS popup its public key, cost, and currency.
- `classes/external/transaction_complete.php` — called after the popup reports success; verifies server-side via `paystack_helper` before trusting the client, checks amount/currency match, then records the payment and delivers the order. Never trusts the client-reported status alone.
- `db/install.xml` — `paygw_paystack` table linking a Moodle payment record to its Paystack transaction reference.
- `tests/` — PHPUnit coverage: supported currencies, form validation, the account configuration form rendering against a real payment account, `get_config_for_js`, and that `transaction_complete` fails cleanly (not fatally) while `verify_transaction()` is a stub. Run against a real Moodle install — see `../../../README.md#running-the-tests`.

## Not yet built

- `paystack_helper::verify_transaction()` — the actual HTTP call to Paystack's `GET /transaction/verify/:reference`.
- `amd/src/gateways_modal.js` — the client-side Paystack Inline popup integration (loads `https://js.paystack.co/v1/inline.js`, opens the popup, calls `paygw_paystack_transaction_complete` on success).
- Corporate-sponsored batch enrollment / invoicing flows referenced in spec 5.12 — out of scope for the gateway plugin itself; that's core enrolment/cohort logic sitting on top of whichever gateway processed payment.
