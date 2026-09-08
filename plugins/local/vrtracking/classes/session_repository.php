<?php

namespace local_vrtracking;

defined('MOODLE_INTERNAL') || die();

/**
 * Persistence for VR completion/progress events (spec 5.4).
 */
class session_repository {

    /**
     * Store one completion/progress event reported by the VR partner platform.
     *
     * @param array $data Validated payload from record_completion::execute().
     */
    public static function record(array $data): \stdClass {
        global $DB;

        $record = (object) [
            'userid' => $data['userid'],
            'cmid' => $data['cmid'],
            'competencyid' => self::find_linked_competency($data['cmid']),
            'externalsessionid' => $data['externalsessionid'] ?? '',
            'status' => $data['status'],
            'timeontasksecs' => $data['timeontasksecs'] ?? null,
            'score' => $data['score'] ?? null,
            'replayurl' => $data['replayurl'] ?? '',
            'rawpayload' => json_encode($data),
            'timecreated' => time(),
        ];
        $record->id = $DB->insert_record('local_vrtracking_session', $record);

        return $record;
    }

    /**
     * Resolve the competency (if any) that a VR-tracked course module satisfies.
     *
     * TODO: Moodle's course_modules_completion/competency linking convention
     * for this mapping hasn't been picked yet (course-module custom field
     * vs. a dedicated local_vrtracking mapping table) — returns null until
     * then, which record_completion::execute() and competency_updater both
     * already treat as "no linked competency to update".
     */
    protected static function find_linked_competency(int $cmid): ?int {
        return null;
    }

    /**
     * Sessions for one course module, most recent first — feeds the
     * instructor/assessor session-replay review view (spec 5.4, 5.6).
     */
    public static function get_sessions_for_module(int $cmid): array {
        global $DB;

        return $DB->get_records('local_vrtracking_session', ['cmid' => $cmid], 'timecreated DESC');
    }

    /**
     * Sessions for one trainee across all VR-tracked modules — feeds the
     * trainee-level analytics dashboard (spec 5.11).
     */
    public static function get_sessions_for_user(int $userid): array {
        global $DB;

        return $DB->get_records('local_vrtracking_session', ['userid' => $userid], 'timecreated DESC');
    }
}
