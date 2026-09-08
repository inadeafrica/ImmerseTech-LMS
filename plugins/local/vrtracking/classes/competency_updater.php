<?php

namespace local_vrtracking;

defined('MOODLE_INTERNAL') || die();

/**
 * Bridges a VR completion event onto Moodle's competency API (spec 5.4, 5.5).
 */
class competency_updater {

    /**
     * A completed VR session satisfies its linked competency to the point of
     * being ready for sign-off — but does not auto-approve it. Spec 5.6
     * requires an Instructor or Qualified Assessor to review the session
     * (replay/performance log) and sign off explicitly, so this marks the
     * competency evidence as available for review rather than "demonstrated".
     */
    public static function mark_demonstrated(\stdClass $session): void {
        if (empty($session->competencyid)) {
            // No competency linked to this module (yet) — nothing to update.
            return;
        }

        if (!class_exists('\core_competency\api')) {
            // Competency subsystem disabled on this site.
            return;
        }

        // TODO: once find_linked_competency() resolves real competency ids,
        // wire this to \core_competency\api::add_evidence() against the
        // trainee's active learning plan for $session->competencyid, using
        // this VR session (replay URL, score, time-on-task) as the evidence
        // record, and notify the course's Qualified Assessor(s) that a
        // sign-off is pending (spec 5.6).
    }
}
