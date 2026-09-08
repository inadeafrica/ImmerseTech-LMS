<?php

namespace local_vrtracking\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\writer;
use context;
use context_system;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider (NDPR — spec 5.13): VR session records carry a userid,
 * so this plugin must support the export/delete-my-data workflows like any
 * other Moodle data store.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider
{
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_vrtracking_session',
            [
                'userid' => 'privacy:metadata:local_vrtracking_session:userid',
                'cmid' => 'privacy:metadata:local_vrtracking_session:cmid',
                'status' => 'privacy:metadata:local_vrtracking_session:status',
                'timeontasksecs' => 'privacy:metadata:local_vrtracking_session:timeontasksecs',
                'score' => 'privacy:metadata:local_vrtracking_session:score',
                'timecreated' => 'privacy:metadata:local_vrtracking_session:timecreated',
            ],
            'privacy:metadata:local_vrtracking_session'
        );

        $collection->add_external_location_link(
            'vrpartner',
            [
                'userid' => 'privacy:metadata:vrpartner:userid',
                'status' => 'privacy:metadata:vrpartner:status',
                'score' => 'privacy:metadata:vrpartner:score',
            ],
            'privacy:metadata:vrpartner'
        );

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        // VR session records aren't scoped to a per-course context in this
        // plugin's data model; they live at system context.
        $contextlist = new contextlist();
        $contextlist->add_system_context();

        return $contextlist;
    }

    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $user = $contextlist->get_user();
        $records = $DB->get_records('local_vrtracking_session', ['userid' => $user->id], 'timecreated ASC');

        if (empty($records)) {
            return;
        }

        $sessions = array_values(array_map(static function (\stdClass $record): \stdClass {
            return (object) [
                'cmid' => $record->cmid,
                'status' => $record->status,
                'timeontasksecs' => $record->timeontasksecs,
                'score' => $record->score,
                'timecreated' => \core_privacy\local\request\transform::datetime($record->timecreated),
            ];
        }, $records));

        writer::with_context(context_system::instance())
            ->export_data(['local_vrtracking'], (object) ['sessions' => $sessions]);
    }

    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;

        if ($context->contextlevel === CONTEXT_SYSTEM) {
            $DB->delete_records('local_vrtracking_session');
        }
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $DB->delete_records('local_vrtracking_session', ['userid' => $contextlist->get_user()->id]);
    }
}
