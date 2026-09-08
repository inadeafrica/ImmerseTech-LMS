<?php

namespace paygw_flutterwave;

defined('MOODLE_INTERNAL') || die();

/**
 * Thin wrapper around the Flutterwave v3 REST API
 * (https://developer.flutterwave.com/docs/verify-transactions/).
 *
 * Kept deliberately small: this plugin only ever needs to verify a
 * transaction the client-side Flutterwave Inline JS checkout already
 * collected payment for — the actual card/OTP flow happens on
 * Flutterwave's side, never on this server.
 */
class flutterwave_helper {

    public const TRANSACTION_STATUS_SUCCESSFUL = 'successful';

    protected string $secretkey;

    public function __construct(string $secretkey) {
        $this->secretkey = $secretkey;
    }

    /**
     * Verifies a transaction by its Flutterwave transaction id.
     *
     * @param string $transactionid The `transaction_id` returned to the
     *   client by the Flutterwave Inline checkout on completion.
     * @return array|null Decoded `data` object from Flutterwave's
     *   GET /transactions/:id/verify response, or null on failure.
     */
    public function verify_transaction(string $transactionid): ?array {
        // TODO: call GET https://api.flutterwave.com/v3/transactions/{$transactionid}/verify
        // with an "Authorization: Bearer {$this->secretkey}" header via
        // Moodle's \curl class, and return the decoded ['data'] object
        // (['status'], ['amount'], ['currency'], ['tx_ref'], ...) — or null
        // if the HTTP call fails, which the caller treats as "do not
        // deliver the order".
        throw new \coding_exception('flutterwave_helper::verify_transaction() is not yet implemented');
    }

    /**
     * Verifies the `verif-hash` header on an incoming Flutterwave webhook
     * against the configured secret hash, per Flutterwave's webhook
     * signature scheme.
     */
    public function verify_webhook_signature(string $receivedhash, string $secrethash): bool {
        return hash_equals($secrethash, $receivedhash);
    }
}
