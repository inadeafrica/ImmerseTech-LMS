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
        // recorded, and must not run the completion-only competency-update
        // path (verified by there being no linked competency at all here —
        // if it ran, add_evidence() would find nothing to do and no-op
        // anyway, so the real guard is test_end_to_end_logs_competency_
        // evidence_only_on_completed below).
        $this->resetAfterTest();
        $this->setAdminUser();

        [, $cm] = $this->create_fixture_course();
        $trainee = $this->getDataGenerator()->create_user();

        $result = record_completion::execute((int) $cm->id, $trainee->id, '', 'in_progress', 60, null, '');
        $this->assertEquals('ok', $result['status']);
    }

    public function test_end_to_end_logs_competency_evidence_only_on_completed(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        [$course, $cm] = $this->create_fixture_course();
        $lpg = $this->getDataGenerator()->get_plugin_generator('core_competency');
        $framework = $lpg->create_framework();
        $competency = $lpg->create_competency(['competencyframeworkid' => $framework->get('id')]);
        $lpg->create_course_module_competency(['cmid' => $cm->id, 'competencyid' => $competency->get('id')]);

        $trainee = $this->getDataGenerator()->create_user();

        // in_progress: recorded, but no competency evidence yet.
        record_completion::execute((int) $cm->id, $trainee->id, 'sess-1', 'in_progress', 120, null, '');
        $this->assertEquals(0, $DB->count_records('competency_evidence'));

        // completed: recorded, and now the linked competency has evidence
        // pending assessor review (spec 5.4, 5.6).
        record_completion::execute(
            (int) $cm->id,
            $trainee->id,
            'sess-1',
            'completed',
            600,
            92.5,
            'https://vr-partner.example/replay/1'
        );

        $usercompetency = \core_competency\user_competency::get_record([
            'userid' => $trainee->id,
            'competencyid' => $competency->get('id'),
        ]);
        $this->assertNotFalse($usercompetency);
        $this->assertEquals(
            \core_competency\user_competency::STATUS_WAITING_FOR_REVIEW,
            $usercompetency->get('status')
        );
    }
}
