<?php

namespace plagiarism_aicontent;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for the assignment-submission observer (spec 5.2).
 *
 * Triggers the \mod_assign\event\assessable_submitted event directly rather
 * than driving mod_assign's full submission workflow, since what's under
 * test here is observer::assignment_submitted()'s own behaviour (does it
 * queue the adhoc task, with what data), not mod_assign's submission logic.
 *
 * @package plagiarism_aicontent
 */
final class observer_test extends \advanced_testcase {

    /**
     * Creates a course + assignment and returns [$assign, $cm].
     */
    protected function create_fixture_assign(): array {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $assignrow = $generator->create_module('assign', ['course' => $course->id]);
        $cm = get_coursemodule_from_id('assign', $assignrow->cmid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $assign = new \assign($context, $cm, $course);

        return [$assign, $cm];
    }

    /**
     * A submission stdClass shaped like a real assign_submission record, so
     * that assessable_submitted's record-snapshot doesn't complain about
     * missing fields.
     */
    protected function make_submission(int $id, int $userid): \stdClass {
        return (object) [
            'id' => $id,
            'assignment' => 0,
            'userid' => $userid,
            'timecreated' => time(),
            'timemodified' => time(),
            'timestarted' => null,
            'status' => 'submitted',
            'groupid' => 0,
            'attemptnumber' => 0,
            'latest' => 1,
        ];
    }

    public function test_queues_adhoc_task_when_enabled(): void {
        global $DB;
        $this->resetAfterTest();
        set_config('enabled', 1, 'plagiarism_aicontent');

        [$assign, $cm] = $this->create_fixture_assign();
        $student = $this->getDataGenerator()->create_user();

        $submission = $this->make_submission(12345, $student->id);
        \mod_assign\event\assessable_submitted::create_from_submission($assign, $submission, false)->trigger();

        $tasks = \core\task\manager::get_adhoc_tasks(task\check_submission::class);
        $this->assertCount(1, $tasks);

        $data = reset($tasks)->get_custom_data();
        $this->assertEquals($cm->id, $data->cmid);
        $this->assertEquals($student->id, $data->userid);
        $this->assertEquals(12345, $data->submissionid);
    }

    public function test_does_not_queue_task_when_disabled(): void {
        $this->resetAfterTest();
        set_config('enabled', 0, 'plagiarism_aicontent');

        [$assign,] = $this->create_fixture_assign();
        $student = $this->getDataGenerator()->create_user();

        $submission = $this->make_submission(1, $student->id);
        \mod_assign\event\assessable_submitted::create_from_submission($assign, $submission, false)->trigger();

        $tasks = \core\task\manager::get_adhoc_tasks(task\check_submission::class);
        $this->assertCount(0, $tasks);
    }

    public function test_uses_relateduserid_over_actor_when_present(): void {
        // relateduserid is set on the event when someone submits on behalf
        // of another user (e.g. a teacher); the task must track the
        // submitting trainee, not the acting user.
        $this->resetAfterTest();
        set_config('enabled', 1, 'plagiarism_aicontent');
        $this->setAdminUser();

        [$assign,] = $this->create_fixture_assign();
        $student = $this->getDataGenerator()->create_user();

        $submission = $this->make_submission(1, $student->id);
        \mod_assign\event\assessable_submitted::create_from_submission($assign, $submission, false)->trigger();

        $tasks = \core\task\manager::get_adhoc_tasks(task\check_submission::class);
        $this->assertCount(1, $tasks);
        $this->assertEquals($student->id, reset($tasks)->get_custom_data()->userid);
    }
}
