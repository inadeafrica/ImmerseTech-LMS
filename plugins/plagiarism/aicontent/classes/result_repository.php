<?php

namespace plagiarism_aicontent;

defined('MOODLE_INTERNAL') || die();

/**
 * Persistence for AI-content detection results (spec 5.2).
 */
class result_repository {

    /**
     * Records a detection result for one submission.
     */
    public static function record(int $cmid, int $userid, int $submissionid, float $likelihood, string $rawresponse = ''): \stdClass {
        global $DB;

        $record = (object) [
            'cmid' => $cmid,
            'userid' => $userid,
            'submissionid' => $submissionid,
            'likelihood' => $likelihood,
            'rawresponse' => $rawresponse,
            'timecreated' => time(),
        ];
        $record->id = $DB->insert_record('plagiarism_aicontent_result', $record);

        return $record;
    }

    /**
     * Most recent result for a trainee's submission to one activity, or
     * null if detection hasn't completed (or run) yet.
     */
    public static function get_latest(int $cmid, int $userid): ?\stdClass {
        global $DB;

        $records = $DB->get_records(
            'plagiarism_aicontent_result',
            ['cmid' => $cmid, 'userid' => $userid],
            'timecreated DESC',
            '*',
            0,
            1
        );

        return $records ? reset($records) : null;
    }
}
