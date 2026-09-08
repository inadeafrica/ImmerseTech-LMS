<?php

namespace local_vrtracking;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for VR session persistence (spec 5.4).
 *
 * @package local_vrtracking
 */
final class session_repository_test extends \advanced_testcase {

    public function test_record_stores_all_fields(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();

        $session = session_repository::record([
            'userid' => $user->id,
            'cmid' => 123,
            'externalsessionid' => 'partner-session-1',
            'status' => 'completed',
            'timeontasksecs' => 600,
            'score' => 92.5,
            'replayurl' => 'https://vr-partner.example/replay/1',
        ]);

        $this->assertNotEmpty($session->id);

        $record = $DB->get_record('local_vrtracking_session', ['id' => $session->id], '*', MUST_EXIST);
        $this->assertEquals($user->id, $record->userid);
        $this->assertEquals(123, $record->cmid);
        $this->assertEquals('partner-session-1', $record->externalsessionid);
        $this->assertEquals('completed', $record->status);
        $this->assertEquals(600, $record->timeontasksecs);
        $this->assertEqualsWithDelta(92.5, $record->score, 0.001);
        $this->assertEquals('https://vr-partner.example/replay/1', $record->replayurl);
        $this->assertNull($record->competencyid);
        $this->assertNotEmpty($record->timecreated);

        $payload = json_decode($record->rawpayload, true);
        $this->assertEquals('completed', $payload['status']);
    }

    public function test_record_defaults_optional_fields(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();

        $session = session_repository::record([
            'userid' => $user->id,
            'cmid' => 5,
            'status' => 'in_progress',
        ]);

        $record = $DB->get_record('local_vrtracking_session', ['id' => $session->id], '*', MUST_EXIST);
        $this->assertEquals('', $record->externalsessionid);
        $this->assertEquals('', $record->replayurl);
        $this->assertNull($record->timeontasksecs);
        $this->assertNull($record->score);
    }

    public function test_get_sessions_for_module_orders_most_recent_first(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();

        $first = session_repository::record(['userid' => $user->id, 'cmid' => 10, 'status' => 'in_progress']);
        // Force a distinct, later timecreated for the second record.
        $this->waitForSecond();
        $second = session_repository::record(['userid' => $user->id, 'cmid' => 10, 'status' => 'completed']);
        // A session for a different module must not show up here.
        session_repository::record(['userid' => $user->id, 'cmid' => 11, 'status' => 'completed']);

        $sessions = array_values(session_repository::get_sessions_for_module(10));
        $this->assertCount(2, $sessions);
        $this->assertEquals($second->id, $sessions[0]->id);
        $this->assertEquals($first->id, $sessions[1]->id);
    }

    public function test_get_sessions_for_user(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $otheruser = $this->getDataGenerator()->create_user();

        session_repository::record(['userid' => $user->id, 'cmid' => 1, 'status' => 'completed']);
        session_repository::record(['userid' => $user->id, 'cmid' => 2, 'status' => 'in_progress']);
        session_repository::record(['userid' => $otheruser->id, 'cmid' => 1, 'status' => 'completed']);

        $sessions = session_repository::get_sessions_for_user($user->id);
        $this->assertCount(2, $sessions);
    }
}
