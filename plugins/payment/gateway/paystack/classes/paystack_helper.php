<?php

namespace paygw_paystack;

defined('MOODLE_INTERNAL') || die();

/**
 * Thin wrapper around the Paystack REST API (https://paystack.com/docs/api/).
 *
 * Kept deliberately small: this plugin only ever needs to verify a
 * transaction the client-side Paystack Inline JS popup already collected
 * payment for — the actual card/OTP flow happens on Paystack's side, never
 * on this server.
 */
class paystack_helper {

    public const TRANSACTION_STATUS_SUCCESS = 'success';

    protected string $secretkey;

    public function __construct(string $secretkey) {
        $this->secretkey = $secretkey;
    }

    /**
     * Verifies a transaction by its reference.
     *
     * @param string $reference The transaction reference generated client-side
     *   and passed to the Paystack Inline popup.
     * @return array|null Decoded `data` object from Paystack's
     *   GET /transaction/verify/:reference response, or null on failure.
     */
    public function verify_transaction(string $reference): ?array {
        // TODO: call GET https://api.paystack.co/transaction/verify/{$reference}
        // with an "Authorization: Bearer {$this->secretkey}" header via
        // Moodle's \curl class, and return the decoded ['data'] object
        // (['status'], ['amount'] in kobo, ['currency'], ...) — or null if
        // the HTTP call fails or Paystack reports the request as
        // unsuccessful, which the caller treats as "do not deliver the
        // order".
        throw new \coding_exception('paystack_helper::verify_transaction() is not yet implemented');
    }
}
