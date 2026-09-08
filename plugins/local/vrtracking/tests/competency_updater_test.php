<?php

namespace local_vrtracking;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for the VR completion -> competency evidence bridge (spec 5.4, 5.5, 5.6).
 *
 * @package local_vrtracking
 */
final class competency_updater_test extends \advanced_testcase {

    /**
     * Creates a course with one VR-tracked activity tagged with the given
     * number of competencies (via Moodle's own activity-level competency
     * linking), and returns [$cm, $competencyids].
     */
    protected function create_fixture(int $numcompetencies = 1): array {
        $generator = $this->getDataGenerator();
        $lpg = $generator->get_plugin_generator('core_competency');

        $course = $generator->create_course();
        // Any activity type can be the VR-tracked module — this plugin
        // tracks by cmid, not by a dedicated activity type.
        $activity = $generator->create_module('page', ['course' => $course->id, 'name' => 'VR: Pump Sizing']);

        $framework = $lpg->create_framework();
        $competencyids = [];
        for ($i = 0; $i < $numcompetencies; $i++) {
            $competency = $lpg->create_competency(['competencyframeworkid' => $framework->get('id')]);
            $lpg->create_course_module_competency(['cmid' => $activity->cmid, 'competencyid' => $competency->get('id')]);
            $competencyids[] = (int) $competency->get('id');
        }

        $cm = get_coursemodule_from_id('page', $activity->cmid, 0, false, MUST_EXIST);

        return [$cm, $competencyids];
    }

    public function test_no_op_when_no_competency_linked(): void {
        global $DB;
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $activity = $generator->create_module('page', ['course' => $course->id]);
        $user = $generator->create_user();

        $session = session_repository::record([
            'userid' => $user->id,
            'cmid' => $activity->cmid,
            'status' => 'completed',
        ]);

        competency_updater::mark_demonstrated($session);

        $this->assertEquals(0, $DB->count_records('competency_evidence'));
    }

    public function test_logs_evidence_and_flags_for_review(): void {
        $this->resetAfterTest();
        set_config('enabled', 1, 'core_competency');

        [$cm, $competencyids] = $this->create_fixture(1);
        $user = $this->getDataGenerator()->create_user();

        $session = session_repository::record([
            'userid' => $user->id,
            'cmid' => $cm->id,
            'status' => 'completed',
            'timeontasksecs' => 600,
            'score' => 92.5,
            'replayurl' => 'https://vr-partner.example/replay/1',
        ]);

        competency_updater::mark_demonstrated($session);

        $usercompetency = \core_competency\user_competency::get_record([
            'userid' => $user->id,
            'competencyid' => $competencyids[0],
        ]);
        $this->assertNotFalse($usercompetency);
        $this->assertEquals(
            \core_competency\user_competency::STATUS_WAITING_FOR_REVIEW,
            $usercompetency->get('status')
        );
        // Logging evidence must never set a grade itself — that's the
        // assessor's call on sign-off, not this webhook's.
        $this->assertNull($usercompetency->get('grade'));

        $evidences = \core_competency\evidence::get_records(['usercompetencyid' => $usercompetency->get('id')]);
        $this->assertCount(1, $evidences);
        $evidence = reset($evidences);
        $this->assertEquals('local_vrtracking', $evidence->get('desccomponent'));
        $this->assertEquals('evidence_vrcompletion', $evidence->get('descidentifier'));
        $this->assertEquals('https://vr-partner.example/replay/1', $evidence->get('url'));
        $this->assertStringContainsString('10', $evidence->get('note'));   // 600s -> 10.0 min.
        $this->assertStringContainsString('92.5', $evidence->get('note'));
    }

    public function test_logs_evidence_for_every_linked_competency(): void {
        $this->resetAfterTest();
        set_config('enabled', 1, 'core_competency');

        [$cm, $competencyids] = $this->create_fixture(2);
        $user = $this->getDataGenerator()->create_user();

        $session = session_repository::record([
            'userid' => $user->id,
            'cmid' => $cm->id,
            'status' => 'completed',
        ]);

        competency_updater::mark_demonstrated($session);

        foreach ($competencyids as $competencyid) {
            $usercompetency = \core_competency\user_competency::get_record([
                'userid' => $user->id,
                'competencyid' => $competencyid,
            ]);
            $this->assertNotFalse($usercompetency, "Expected evidence for competency $competencyid");
            $this->assertEquals(
                \core_competency\user_competency::STATUS_WAITING_FOR_REVIEW,
                $usercompetency->get('status')
            );
        }
    }

    public function test_no_op_when_competency_subsystem_disabled(): void {
        global $DB;
        $this->resetAfterTest();
        set_config('enabled', 0, 'core_competency');

        [$cm,] = $this->create_fixture(1);
        $user = $this->getDataGenerator()->create_user();

        $session = session_repository::record([
            'userid' => $user->id,
            'cmid' => $cm->id,
            'status' => 'completed',
        ]);

        competency_updater::mark_demonstrated($session);

        $this->assertEquals(0, $DB->count_records('competency_evidence'));
    }

    public function test_survives_deleted_course_module(): void {
        // If the module is deleted between the VR session being recorded and
        // the webhook processing it (edge case, but possible with async
        // delivery), mark_demonstrated() must not throw.
        global $DB;
        $this->resetAfterTest();

        [$cm,] = $this->create_fixture(1);
        $user = $this->getDataGenerator()->create_user();

        $session = session_repository::record([
            'userid' => $user->id,
            'cmid' => $cm->id,
            'status' => 'completed',
        ]);

        $DB->delete_records('course_modules', ['id' => $cm->id]);

        competency_updater::mark_demonstrated($session);
        $this->assertEquals(0, $DB->count_records('competency_evidence'));
    }
}
