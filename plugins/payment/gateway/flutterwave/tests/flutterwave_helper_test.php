<?php

namespace paygw_flutterwave;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for the Flutterwave API client (spec 5.12).
 *
 * @package paygw_flutterwave
 */
final class flutterwave_helper_test extends \advanced_testcase {

    public function test_verify_transaction_is_not_yet_implemented(): void {
        // No provider is wired up yet (see the TODO in flutterwave_helper::
        // verify_transaction()) — this pins the current, deliberate
        // "not implemented" behaviour so a silent regression to "always
        // succeeds" would be caught.
        $helper = new flutterwave_helper('sk_test_x');

        $this->expectException(\coding_exception::class);
        $helper->verify_transaction('some-transaction-id');
    }

    public function test_verify_webhook_signature(): void {
        $helper = new flutterwave_helper('sk_test_x');

        $this->assertTrue($helper->verify_webhook_signature('secret123', 'secret123'));
        $this->assertFalse($helper->verify_webhook_signature('wrong', 'secret123'));
        $this->assertFalse($helper->verify_webhook_signature('', 'secret123'));
    }
}
