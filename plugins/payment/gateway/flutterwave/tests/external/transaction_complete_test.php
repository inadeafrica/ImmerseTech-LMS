<?php

namespace paygw_flutterwave\external;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for the Flutterwave transaction-complete callback (spec 5.12).
 *
 * @package paygw_flutterwave
 */
final class transaction_complete_test extends \advanced_testcase {

    /**
     * Sets up a payable enrol_fee instance backed by a real payment account
     * with the flutterwave gateway configured.
     */
    protected function create_fixture_payable(): array {
        global $DB;

        // New payment gateway plugins are installed disabled by default
        // (Site administration > Plugins > Payment gateways); the account's
        // per-gateway config is otherwise invisible to core_payment\helper.
        \core\plugininfo\paygw::enable_plugin('flutterwave', 1);

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $feeplugin = enrol_get_plugin('fee');
        $generator = $this->getDataGenerator();

        $account = $generator->get_plugin_generator('core_payment')
            ->create_payment_account(['gateways' => 'flutterwave']);
        \core_payment\helper::save_payment_gateway((object) [
            'accountid' => $account->get('id'),
            'gateway' => 'flutterwave',
            'enabled' => 1,
            'config' => json_encode([
                'businessname' => 'ImmerseTech',
                'publickey' => 'FLWPUBK_TEST-x',
                'secretkey' => 'FLWSECK_TEST-x',
                'secrethash' => 'whsec_x',
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

        $this->assertEquals('FLWPUBK_TEST-x', $result['publickey']);
        $this->assertEquals('ImmerseTech', $result['businessname']);
        $this->assertEquals('NGN', $result['currency']);
        $this->assertEqualsWithDelta(5000.0, $result['cost'], 0.01);
    }

    public function test_execute_fails_cleanly_while_verification_is_unimplemented(): void {
        // flutterwave_helper::verify_transaction() is a deliberate stub (see
        // its own tests) — this proves the callback degrades to a clean
        // failure response rather than a fatal when that call throws, and
        // that no payment is recorded.
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $student = $this->getDataGenerator()->create_user();
        $this->setUser($student);

        [$component, $paymentarea, $itemid] = $this->create_fixture_payable();

        $result = transaction_complete::execute($component, $paymentarea, $itemid, '123456');
        $this->assertDebuggingCalled(
            'Exception while verifying Flutterwave transaction: '
                . 'Coding error detected, it must be fixed by a programmer: '
                . 'flutterwave_helper::verify_transaction() is not yet implemented'
        );

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['message']);
        $this->assertEquals(0, $DB->count_records('payments'));
        $this->assertEquals(0, $DB->count_records('paygw_flutterwave'));
    }
}
