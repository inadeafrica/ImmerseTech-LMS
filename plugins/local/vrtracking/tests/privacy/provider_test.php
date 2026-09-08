<?php

namespace local_vrtracking\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use context_system;

/**
 * Privacy provider tests for local_vrtracking (spec 5.13, NDPR).
 *
 * @package local_vrtracking
 */
final class provider_test extends \advanced_testcase {

    public function test_get_contexts_for_userid_returns_system_context(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();

        $contextlist = provider::get_contexts_for_userid($user->id);
        $contextids = $contextlist->get_contextids();

        $this->assertContains((string) context_system::instance()->id, $contextids);
    }

    public function test_export_user_data(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();

        \local_vrtracking\session_repository::record([
            'userid' => $user->id,
            'cmid' => 5,
            'status' => 'completed',
            'timeontasksecs' => 120,
            'score' => 75.0,
        ]);

        $approvedlist = new approved_contextlist($user, 'local_vrtracking', [context_system::instance()->id]);
        provider::export_user_data($approvedlist);

        $writer = writer::with_context(context_system::instance());
        $this->assertTrue($writer->has_any_data());

        $exported = $writer->get_data(['local_vrtracking']);
        $this->assertCount(1, $exported->sessions);
        $this->assertEquals('completed', $exported->sessions[0]->status);
    }

    public function test_delete_data_for_user(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $otheruser = $this->getDataGenerator()->create_user();

        \local_vrtracking\session_repository::record(['userid' => $user->id, 'cmid' => 1, 'status' => 'completed']);
        \local_vrtracking\session_repository::record(['userid' => $otheruser->id, 'cmid' => 1, 'status' => 'completed']);

        $approvedlist = new approved_contextlist($user, 'local_vrtracking', [context_system::instance()->id]);
        provider::delete_data_for_user($approvedlist);

        $this->assertEquals(0, $DB->count_records('local_vrtracking_session', ['userid' => $user->id]));
        $this->assertEquals(1, $DB->count_records('local_vrtracking_session', ['userid' => $otheruser->id]));
    }

    public function test_delete_data_for_all_users_in_context(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();

        \local_vrtracking\session_repository::record(['userid' => $user->id, 'cmid' => 1, 'status' => 'completed']);

        provider::delete_data_for_all_users_in_context(context_system::instance());

        $this->assertEquals(0, $DB->count_records('local_vrtracking_session'));
    }
}
