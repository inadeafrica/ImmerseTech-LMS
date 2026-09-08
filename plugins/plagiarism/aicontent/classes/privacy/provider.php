<?php

namespace plagiarism_aicontent\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\writer;
use context;
use context_system;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider (NDPR — spec 5.13): detection results carry a userid,
 * and submission text is sent to a third-party provider for scoring.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider
{
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'plagiarism_aicontent_result',
            [
                'userid' => 'privacy:metadata:plagiarism_aicontent_result:userid',
                'submissionid' => 'privacy:metadata:plagiarism_aicontent_result:submissionid',
                'likelihood' => 'privacy:metadata:plagiarism_aicontent_result:likelihood',
                'timecreated' => 'privacy:metadata:plagiarism_aicontent_result:timecreated',
            ],
            'privacy:metadata:plagiarism_aicontent_result'
        );

        $collection->add_external_location_link(
            'aicontentprovider',
            [
                'userid' => 'privacy:metadata:aicontentprovider:userid',
                'text' => 'privacy:metadata:aicontentprovider:text',
            ],
            'privacy:metadata:aicontentprovider'
        );

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $contextlist->add_system_context();

        return $contextlist;
    }

    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $user = $contextlist->get_user();
        $records = $DB->get_records('plagiarism_aicontent_result', ['userid' => $user->id], 'timecreated ASC');

        if (empty($records)) {
            return;
        }

        $results = array_values(array_map(static function (\stdClass $record): \stdClass {
            return (object) [
                'submissionid' => $record->submissionid,
                'likelihood' => $record->likelihood,
                'timecreated' => \core_privacy\local\request\transform::datetime($record->timecreated),
            ];
        }, $records));

        writer::with_context(context_system::instance())
            ->export_data(['plagiarism_aicontent'], (object) ['results' => $results]);
    }

    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;

        if ($context->contextlevel === CONTEXT_SYSTEM) {
            $DB->delete_records('plagiarism_aicontent_result');
        }
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $DB->delete_records('plagiarism_aicontent_result', ['userid' => $contextlist->get_user()->id]);
    }
}
