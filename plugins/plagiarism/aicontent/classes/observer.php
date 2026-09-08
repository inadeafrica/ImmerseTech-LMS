<?php

namespace plagiarism_aicontent;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer: kicks off asynchronous AI-content detection on submission.
 *
 * Runs via an adhoc task (classes/task/check_submission.php) rather than
 * inline in the request, since the detection API call must not block or
 * fail the trainee's submission (spec 5.2: instructors see the score
 * "alongside the submission", not gating it).
 */
class observer {

    public static function assignment_submitted(\mod_assign\event\assessable_submitted $event): void {
        if (empty(get_config('plagiarism_aicontent', 'enabled'))) {
            return;
        }

        $userid = $event->relateduserid ?? $event->userid;

        $task = new \plagiarism_aicontent\task\check_submission();
        $task->set_custom_data([
            'cmid' => $event->contextinstanceid,
            'userid' => $userid,
            'submissionid' => $event->objectid,
        ]);
        \core\task\manager::queue_adhoc_task($task);
    }
}
