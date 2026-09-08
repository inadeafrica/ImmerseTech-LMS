<?php

namespace local_vrtracking;

defined('MOODLE_INTERNAL') || die();

/**
 * Bridges a VR completion event onto Moodle's competency API (spec 5.4, 5.5).
 */
class competency_updater {

    /**
     * A completed VR session satisfies its linked competency(ies) to the
     * point of being ready for sign-off — but does not auto-approve them.
     * Spec 5.6 requires an Instructor or Qualified Assessor to review the
     * session (replay/performance log) and sign off explicitly, so this logs
     * evidence and flags the competency for review (Moodle's own
     * "waiting for review" status) rather than marking it demonstrated.
     *
     * Uses \core_competency\evidence::ACTION_LOG with $recommend = true
     * instead of ACTION_COMPLETE, which core's own api.php documents as "an
     * action to use with automated systems" — i.e. the auto-approve path
     * this plugin must not take.
     */
    public static function mark_demonstrated(\stdClass $session): void {
        if (!class_exists('\core_competency\api') || !\core_competency\api::is_enabled()) {
            // Competency subsystem disabled or not present on this site.
            return;
        }

        $competencyids = session_repository::get_linked_competency_ids($session->cmid);
        if (!$competencyids) {
            // No competency linked to this module (yet) — nothing to update.
            return;
        }

        $cm = get_coursemodule_from_id(null, $session->cmid, 0, false, IGNORE_MISSING);
        if (!$cm) {
            // Course module has since been deleted; nothing sensible to log.
            return;
        }

        $context = \context_module::instance($session->cmid);
        $note = self::build_note($session);

        foreach ($competencyids as $competencyid) {
            try {
                \core_competency\api::add_evidence(
                    $session->userid,
                    $competencyid,
                    $context,
                    \core_competency\evidence::ACTION_LOG,
                    'evidence_vrcompletion',
                    'local_vrtracking',
                    $cm->name,
                    true,
                    $session->replayurl ?: null,
                    null,
                    $session->userid,
                    $note
                );
            } catch (\Exception $e) {
                // One competency failing to log (e.g. it was deleted after
                // being tagged on the activity) must not stop the others or
                // fail the webhook response.
                debugging(
                    'local_vrtracking: failed to log competency evidence for competency '
                        . $competencyid . ': ' . $e->getMessage(),
                    DEBUG_DEVELOPER
                );
            }
        }
    }

    /**
     * A short human-readable note attached to the evidence, giving the
     * reviewing assessor the session's time-on-task and score without
     * needing to open the replay link.
     */
    protected static function build_note(\stdClass $session): ?string {
        $parts = [];

        if ($session->timeontasksecs !== null) {
            $parts[] = get_string('evidencenote_timeontask', 'local_vrtracking', round($session->timeontasksecs / 60, 1));
        }

        if ($session->score !== null) {
            $parts[] = get_string('evidencenote_score', 'local_vrtracking', $session->score);
        }

        return $parts ? implode(' ', $parts) : null;
    }
}
