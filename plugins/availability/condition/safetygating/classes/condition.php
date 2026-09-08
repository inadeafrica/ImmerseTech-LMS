<?php

namespace availability_safetygating;

use core_availability\info;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/completionlib.php');

/**
 * Safety & compliance gating condition (spec 5.7).
 *
 * Gates an activity (typically a hazardous physical or VR practical) on
 * completion of a referenced safety induction activity, with an optional
 * validity period after which the certification is treated as lapsed and
 * the gated activity re-locks automatically — because is_available() is
 * evaluated live against the induction activity's completion timestamp
 * rather than a one-off "has ever completed" flag, no separate expiry
 * job is needed for the re-lock itself to take effect.
 */
class condition extends \core_availability\condition {

    /** @var int Course-module id of the safety induction activity this depends on. */
    protected $inductioncmid;

    /** @var int Days the induction stays valid for after completion, or 0 for no expiry. */
    protected $validitydays;

    /**
     * Constructor.
     *
     * @param stdClass $structure Data structure from JSON decode.
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {
        if (isset($structure->cm) && is_number($structure->cm)) {
            $this->inductioncmid = (int) $structure->cm;
        } else {
            throw new \coding_exception('Missing or invalid ->cm for safetygating condition');
        }

        if (isset($structure->validitydays) && is_number($structure->validitydays)) {
            $this->validitydays = max(0, (int) $structure->validitydays);
        } else {
            $this->validitydays = 0;
        }
    }

    public function save(): stdClass {
        return (object) [
            'type' => 'safetygating',
            'cm' => $this->inductioncmid,
            'validitydays' => $this->validitydays,
        ];
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param int $inductioncmid Course-module id of the safety induction activity.
     * @param int $validitydays Validity period in days, or 0 for no expiry.
     */
    public static function get_json(int $inductioncmid, int $validitydays = 0): stdClass {
        return (object) [
            'type' => 'safetygating',
            'cm' => $inductioncmid,
            'validitydays' => $validitydays,
        ];
    }

    public function is_available($not, info $info, $grabthelot, $userid): bool {
        $modinfo = $info->get_modinfo();
        $cmid = $this->inductioncmid;

        if (!array_key_exists($cmid, $modinfo->cms) || $modinfo->cms[$cmid]->deletioninprogress) {
            // Referenced induction activity no longer exists — fail closed
            // rather than silently unlocking a safety-gated practical.
            $allow = false;
        } else {
            $completion = new \completion_info($modinfo->get_course());
            $data = $completion->get_data((object) ['id' => $cmid], $grabthelot, $userid);
            $completed = in_array($data->completionstate, [COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS], true);

            if (!$completed) {
                $allow = false;
            } else if ($this->validitydays > 0) {
                $expiresat = $data->timemodified + ($this->validitydays * DAYSECS);
                $allow = time() < $expiresat;
            } else {
                $allow = true;
            }
        }

        if ($not) {
            $allow = !$allow;
        }

        return $allow;
    }

    public function get_description($full, $not, info $info): string {
        $modinfo = $info->get_modinfo();
        if (!array_key_exists($this->inductioncmid, $modinfo->cms)
                || $modinfo->cms[$this->inductioncmid]->deletioninprogress) {
            $modname = get_string('missing', 'availability_safetygating');
        } else {
            $modname = self::description_cm_name($this->inductioncmid);
        }

        if ($this->validitydays > 0) {
            $str = $not ? 'requires_induction_not_expiring' : 'requires_induction_expiring';
            return get_string($str, 'availability_safetygating', (object) [
                'induction' => $modname,
                'days' => $this->validitydays,
            ]);
        }

        $str = $not ? 'requires_induction_not' : 'requires_induction';
        return get_string($str, 'availability_safetygating', $modname);
    }

    protected function get_debug_string(): string {
        $debug = 'cm' . $this->inductioncmid;
        if ($this->validitydays > 0) {
            $debug .= ' valid' . $this->validitydays . 'd';
        }
        return $debug;
    }

    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name): bool {
        global $DB;

        $rec = \restore_dbops::get_backup_ids_record($restoreid, 'course_module', $this->inductioncmid);
        if (!$rec || !$rec->newitemid) {
            if ($DB->record_exists('course_modules', ['id' => $this->inductioncmid, 'course' => $courseid])) {
                return false;
            }
            $this->inductioncmid = 0;
            $logger->process(
                'Restored item (' . $name . ') has a safety-gating condition on a module that was not restored',
                \backup::LOG_WARNING
            );
            return true;
        }

        $this->inductioncmid = (int) $rec->newitemid;
        return true;
    }

    public function update_dependency_id($table, $oldid, $newid) {
        if ($table === 'course_modules' && (int) $this->inductioncmid === (int) $oldid) {
            $this->inductioncmid = $newid;
            return true;
        }

        return false;
    }
}
