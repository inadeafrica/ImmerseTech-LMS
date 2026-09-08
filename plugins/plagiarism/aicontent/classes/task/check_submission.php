<?php

namespace plagiarism_aicontent\task;

defined('MOODLE_INTERNAL') || die();

use plagiarism_aicontent\detector;
use plagiarism_aicontent\result_repository;

/**
 * Adhoc task: scores one submission for likely AI-generated content and
 * stores the result for display via plagiarism_plugin_aicontent::get_links().
 */
class check_submission extends \core\task\adhoc_task {

    public function execute(): void {
        global $DB;

        $data = $this->get_custom_data();

        if (empty(get_config('plagiarism_aicontent', 'enabled'))) {
            return;
        }

        $apikey = get_config('plagiarism_aicontent', 'apikey');
        $endpoint = get_config('plagiarism_aicontent', 'apiendpoint');
        if (empty($apikey) || empty($endpoint)) {
            mtrace('plagiarism_aicontent: skipping, provider not configured');
            return;
        }

        // TODO: extract the plain-text submission content for
        // $data->submissionid (mod_assign text-entry/online text field, or
        // an extracted-text version of an uploaded file) rather than a
        // placeholder — this task only wires the plumbing so far.
        $text = '';
        if ($text === '') {
            return;
        }

        try {
            $detector = new detector($apikey, $endpoint);
            $likelihood = $detector->score($text);
        } catch (\Exception $e) {
            mtrace('plagiarism_aicontent: detection failed - ' . $e->getMessage());
            return;
        }

        if ($likelihood === null) {
            return;
        }

        result_repository::record(
            (int) $data->cmid,
            (int) $data->userid,
            (int) $data->submissionid,
            $likelihood
        );
    }
}
