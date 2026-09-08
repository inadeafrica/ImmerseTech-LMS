<?php

namespace plagiarism_aicontent\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for the AI-content detection adhoc task (spec 5.2).
 *
 * @package plagiarism_aicontent
 */
final class check_submission_test extends \advanced_testcase {

    protected function make_task(array $customdata): check_submission {
        $task = new check_submission();
        $task->set_custom_data($customdata);

        return $task;
    }

    public function test_does_nothing_when_plugin_disabled(): void {
        global $DB;
        $this->resetAfterTest();
        set_config('enabled', 0, 'plagiarism_aicontent');

        $task = $this->make_task(['cmid' => 1, 'userid' => 2, 'submissionid' => 3]);
        $task->execute();

        $this->assertEquals(0, $DB->count_records('plagiarism_aicontent_result'));
    }

    public function test_does_nothing_when_provider_not_configured(): void {
        global $DB;
        $this->resetAfterTest();
        set_config('enabled', 1, 'plagiarism_aicontent');
        // apikey/apiendpoint deliberately left unset.
        $this->expectOutputString("plagiarism_aicontent: skipping, provider not configured\n");

        $task = $this->make_task(['cmid' => 1, 'userid' => 2, 'submissionid' => 3]);
        $task->execute();

        $this->assertEquals(0, $DB->count_records('plagiarism_aicontent_result'));
    }

    public function test_does_nothing_when_no_text_extracted(): void {
        // Regression guard for the current state of the implementation:
        // text extraction is not built yet (see the TODO in
        // check_submission::execute()), so even with the plugin fully
        // configured, the task must exit quietly rather than call the
        // detector with empty text or throw.
        global $DB;
        $this->resetAfterTest();
        set_config('enabled', 1, 'plagiarism_aicontent');
        set_config('apikey', 'test-key', 'plagiarism_aicontent');
        set_config('apiendpoint', 'https://example.test/detect', 'plagiarism_aicontent');

        $task = $this->make_task(['cmid' => 1, 'userid' => 2, 'submissionid' => 3]);
        $task->execute();

        $this->assertEquals(0, $DB->count_records('plagiarism_aicontent_result'));
    }
}
