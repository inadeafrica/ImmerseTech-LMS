<?php

namespace paygw_paystack\external;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for the Paystack transaction-complete callback (spec 5.12).
 *
 * @package paygw_paystack
 */
final class transaction_complete_test extends \advanced_testcase {

    /**
     * Sets up a payable enrol_fee instance backed by a real payment account
     * with the paystack gateway configured, and returns [$component,
     * $paymentarea, $itemid] the same way mod_enrol's own payment tests do.
     */
    protected function create_fixture_payable(): array {
        global $DB;

        // New payment gateway plugins are installed disabled by default
        // (Site administration > Plugins > Payment gateways); the account's
        // per-gateway config is otherwise invisible to core_payment\helper.
        \core\plugininfo\paygw::enable_plugin('paystack', 1);

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $feeplugin = enrol_get_plugin('fee');
        $generator = $this->getDataGenerator();

        $account = $generator->get_plugin_generator('core_payment')->create_payment_account(['gateways' => 'paystack']);
        \core_payment\helper::save_payment_gateway((object) [
            'accountid' => $account->get('id'),
            'gateway' => 'paystack',
            'enabled' => 1,
            'config' => json_encode([
                'businessname' => 'ImmerseTech',
                'publickey' => 'pk_test_x',
                'secretkey' => 'sk_test_x',
                'environment' => 'test',
            ]),
        ]);

        $course = $generator->create_course();
        $itemid = $feeplugin->add_instance($course, [
            'courseid' => $course->id,
            'customint1' => $account->get('id'),
            'cost' => 5000,
            'currency' => 'NGN',
            'roleid' => $studentrole->id,
        ]);

        return ['enrol_fee', 'fee', $itemid];
    }

    public function test_get_config_for_js_returns_gateway_config(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        [$component, $paymentarea, $itemid] = $this->create_fixture_payable();

        $result = get_config_for_js::execute($component, $paymentarea, $itemid);

        $this->assertEquals('pk_test_x', $result['publickey']);
        $this->assertEquals('ImmerseTech', $result['businessname']);
        $this->assertEquals('NGN', $result['currency']);
        $this->assertEqualsWithDelta(5000.0, $result['cost'], 0.01);
    }

    public function test_execute_fails_cleanly_while_verification_is_unimplemented(): void {
        // paystack_helper::verify_transaction() is a deliberate stub (see
        // its own tests) — this proves the callback degrades to a clean
        // failure response rather than a fatal when that call throws, and
        // that no payment is recorded.
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $student = $this->getDataGenerator()->create_user();
        $this->setUser($student);

        [$component, $paymentarea, $itemid] = $this->create_fixture_payable();

        $result = transaction_complete::execute($component, $paymentarea, $itemid, 'some-reference');
        $this->assertDebuggingCalled(
            'Exception while verifying Paystack transaction: '
                . 'Coding error detected, it must be fixed by a programmer: '
                . 'paystack_helper::verify_transaction() is not yet implemented'
        );

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['message']);
        $this->assertEquals(0, $DB->count_records('payments'));
        $this->assertEquals(0, $DB->count_records('paygw_paystack'));
    }
}
