<?php

namespace local_vrtracking\external;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for the VR partner webhook entry point (spec 5.4, 9.2).
 *
 * @package local_vrtracking
 */
final class record_completion_test extends \advanced_testcase {

    /**
     * Creates a course with one page activity, returning [$course, $cm].
     */
    protected function create_fixture_course(): array {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $page = $generator->get_plugin_generator('mod_page')->create_instance(['course' => $course->id]);
        $modinfo = get_fast_modinfo($course);

        return [$course, $modinfo->get_cm($page->cmid)];
    }

    public function test_admin_can_record_a_completion(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        [, $cm] = $this->create_fixture_course();
        $trainee = $this->getDataGenerator()->create_user();

        $result = record_completion::execute(
            (int) $cm->id,
            $trainee->id,
            'partner-session-42',
            'completed',
            300,
            88.0,
            'https://vr-partner.example/replay/42'
        );

        $this->assertEquals('ok', $result['status']);
        $this->assertNotEmpty($result['sessionid']);

        $record = $DB->get_record('local_vrtracking_session', ['id' => $result['sessionid']], '*', MUST_EXIST);
        $this->assertEquals($trainee->id, $record->userid);
        $this->assertEquals($cm->id, $record->cmid);
        $this->assertEquals('completed', $record->status);
    }

    public function test_user_without_capability_is_rejected(): void {
        $this->resetAfterTest();

        [, $cm] = $this->create_fixture_course();
        $trainee = $this->getDataGenerator()->create_user();
        $caller = $this->getDataGenerator()->create_user();
        $this->setUser($caller);

        $this->expectException(\required_capability_exception::class);
        record_completion::execute((int) $cm->id, $trainee->id, '', 'completed', null, null, '');
    }

    public function test_rejects_unknown_status(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        [, $cm] = $this->create_fixture_course();
        $trainee = $this->getDataGenerator()->create_user();

        $this->expectException(\invalid_parameter_exception::class);
        record_completion::execute((int) $cm->id, $trainee->id, '', 'sort-of-done', null, null, '');
    }

    public function test_rejects_unknown_course_module(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $trainee = $this->getDataGenerator()->create_user();

        $this->expectException(\dml_exception::class);
        record_completion::execute(999999, $trainee->id, '', 'completed', null, null, '');
    }

    public function test_in_progress_does_not_touch_competency_updater(): void {
        // Regression guard: an in_progress event must still succeed and be
        // recorded, without requiring the (unimplemented) competency-update
        // path that only runs for 'completed' events.
        $this->resetAfterTest();
        $this->setAdminUser();

        [, $cm] = $this->create_fixture_course();
        $trainee = $this->getDataGenerator()->create_user();

        $result = record_completion::execute((int) $cm->id, $trainee->id, '', 'in_progress', 60, null, '');
        $this->assertEquals('ok', $result['status']);
    }
}
