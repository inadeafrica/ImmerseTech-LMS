<?php

namespace local_vrtracking\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use context_system;

defined('MOODLE_INTERNAL') || die();

/**
 * Webhook entry point for the VR partner platform (spec 5.4, 9.2).
 *
 * Exposed as a Moodle external function rather than a bespoke unauthenticated
 * endpoint so it gets Moodle's existing webservice token auth, parameter
 * validation, and capability checks for free.
 */
class record_completion extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module id of the VR practical/lesson'),
            'userid' => new external_value(PARAM_INT, 'Moodle user id of the trainee'),
            'externalsessionid' => new external_value(
                PARAM_RAW,
                'Partner platform session/attempt id, used to de-duplicate repeated deliveries',
                VALUE_DEFAULT,
                ''
            ),
            'status' => new external_value(PARAM_ALPHANUMEXT, 'not_started | in_progress | completed'),
            'timeontasksecs' => new external_value(
                PARAM_INT,
                'Time on task, in seconds',
                VALUE_DEFAULT,
                null,
                NULL_ALLOWED
            ),
            'score' => new external_value(
                PARAM_FLOAT,
                'Performance score, where the simulation reports one',
                VALUE_DEFAULT,
                null,
                NULL_ALLOWED
            ),
            'replayurl' => new external_value(
                PARAM_RAW,
                'Session replay / performance log URL for assessor review (spec 5.6)',
                VALUE_DEFAULT,
                ''
            ),
        ]);
    }

    public static function execute(
        int $cmid,
        int $userid,
        string $externalsessionid,
        string $status,
        ?int $timeontasksecs,
        ?float $score,
        string $replayurl
    ): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'userid' => $userid,
            'externalsessionid' => $externalsessionid,
            'status' => $status,
            'timeontasksecs' => $timeontasksecs,
            'score' => $score,
            'replayurl' => $replayurl,
        ]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/vrtracking:receivewebhook', $context);

        if (!in_array($params['status'], ['not_started', 'in_progress', 'completed'], true)) {
            throw new \invalid_parameter_exception('Unknown status: ' . $params['status']);
        }

        // get_coursemodule_from_id() throws dml_missing_record_exception on
        // a bad cmid, which is exactly the "reject this delivery" behaviour
        // we want for a malformed or stale partner payload.
        get_coursemodule_from_id(null, $params['cmid'], 0, false, MUST_EXIST);

        $session = \local_vrtracking\session_repository::record($params);

        if ($params['status'] === 'completed') {
            \local_vrtracking\competency_updater::mark_demonstrated($session);
        }

        return ['sessionid' => (int) $session->id, 'status' => 'ok'];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'sessionid' => new external_value(PARAM_INT, 'Id of the recorded session record'),
            'status' => new external_value(PARAM_ALPHA, 'ok'),
        ]);
    }
}
