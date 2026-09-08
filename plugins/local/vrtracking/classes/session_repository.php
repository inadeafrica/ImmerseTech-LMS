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
     * Uses Moodle's own activity-level competency linking (the
     * "Competencies" tab every course-module edit form already has, via
     * core_competency\course_module_competency) rather than inventing a
     * separate mapping — an instructor tags the VR practical with the
     * competency(ies) it demonstrates exactly the same way they would for
     * any other activity.
     *
     * @return int|null The first linked competency, for the session
     *   record's single competencyid column. competency_updater re-reads
     *   the full set via get_linked_competency_ids() rather than relying on
     *   this single value, since an activity can be tagged with more than
     *   one competency.
     */
    protected static function find_linked_competency(int $cmid): ?int {
        $ids = self::get_linked_competency_ids($cmid);

        return $ids ? reset($ids) : null;
    }

    /**
     * All competencies a course module is tagged with, in display order.
     *
     * @return int[]
     */
    public static function get_linked_competency_ids(int $cmid): array {
        if (!class_exists('\core_competency\course_module_competency')) {
            // Competency subsystem not present on this Moodle version.
            return [];
        }

        $links = \core_competency\course_module_competency::list_course_module_competencies($cmid);

        return array_map(static function ($link): int {
            return (int) $link->get('competencyid');
        }, $links);
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
