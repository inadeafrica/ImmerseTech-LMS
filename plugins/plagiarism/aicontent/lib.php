<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

// Get global class.
require_once($CFG->dirroot . '/plagiarism/lib.php');

/**
 * AI-generated-content detection (spec 5.2).
 *
 * Distinct from similarity/plagiarism checking (Turnitin/Copyleaks, which
 * Moodle already integrates well) — this flags likely AI-written text with
 * an advisory score, since detection tools carry a false-positive risk and
 * this must never auto-block a submission on its own (spec 5.2).
 */
class plagiarism_plugin_aicontent extends plagiarism_plugin {

    /**
     * Displays the AI-likelihood score beside a submission, if one has been
     * computed yet.
     *
     * @param array $linkarray
     * @return string
     */
    public function get_links($linkarray) {
        if (empty(get_config('plagiarism_aicontent', 'enabled'))) {
            return '';
        }

        if (empty($linkarray['cmid']) || empty($linkarray['userid'])) {
            return '';
        }

        $result = \plagiarism_aicontent\result_repository::get_latest(
            (int) $linkarray['cmid'],
            (int) $linkarray['userid']
        );

        if (!$result) {
            // Detection runs asynchronously (see classes/observer.php and
            // classes/task/check_submission.php) so there is often nothing
            // to show yet immediately after submission.
            return html_writer::span(
                get_string('pending', 'plagiarism_aicontent'),
                'plagiarism_aicontent_pending'
            );
        }

        $percent = round($result->likelihood * 100);
        $class = 'plagiarism_aicontent_score';
        if ($percent >= get_config('plagiarism_aicontent', 'flagthreshold')) {
            $class .= ' plagiarism_aicontent_score_flagged';
        }

        return html_writer::span(
            get_string('likelihood_score', 'plagiarism_aicontent', $percent),
            $class
        );
    }

    /**
     * Student-facing disclosure: advisory, not a hard block (spec 5.2).
     *
     * @param int $cmid
     * @return string
     */
    public function print_disclosure($cmid) {
        if (empty(get_config('plagiarism_aicontent', 'enabled'))) {
            return '';
        }

        return html_writer::tag(
            'div',
            get_string('studentdisclosure', 'plagiarism_aicontent'),
            ['class' => 'plagiarism_aicontent_disclosure']
        );
    }
}
