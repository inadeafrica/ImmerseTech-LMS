<?php

namespace paygw_paystack;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for the Paystack API client (spec 5.12).
 *
 * @package paygw_paystack
 */
final class paystack_helper_test extends \advanced_testcase {

    public function test_verify_transaction_is_not_yet_implemented(): void {
        // No provider is wired up yet (see the TODO in paystack_helper::
        // verify_transaction()) — this pins the current, deliberate
        // "not implemented" behaviour so a silent regression to "always
        // succeeds" would be caught.
        $helper = new paystack_helper('sk_test_x');

        $this->expectException(\coding_exception::class);
        $helper->verify_transaction('some-reference');
    }
}
