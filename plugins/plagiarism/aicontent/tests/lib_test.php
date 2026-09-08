<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/plagiarism/aicontent/lib.php');

/**
 * Unit tests for the plagiarism_plugin_aicontent hooks (spec 5.2).
 *
 * @package plagiarism_aicontent
 */
final class plagiarism_aicontent_lib_test extends advanced_testcase {

    public function test_disabled_plugin_returns_nothing(): void {
        $this->resetAfterTest();
        set_config('enabled', 0, 'plagiarism_aicontent');

        $plugin = new plagiarism_plugin_aicontent();
        $this->assertSame('', $plugin->get_links(['cmid' => 1, 'userid' => 2]));
        $this->assertSame('', $plugin->print_disclosure(1));
    }

    public function test_get_links_without_cmid_or_userid_returns_nothing(): void {
        $this->resetAfterTest();
        set_config('enabled', 1, 'plagiarism_aicontent');

        $plugin = new plagiarism_plugin_aicontent();
        $this->assertSame('', $plugin->get_links(['userid' => 2]));
        $this->assertSame('', $plugin->get_links(['cmid' => 1]));
    }

    public function test_get_links_shows_pending_when_no_result_yet(): void {
        $this->resetAfterTest();
        set_config('enabled', 1, 'plagiarism_aicontent');

        $plugin = new plagiarism_plugin_aicontent();
        $links = $plugin->get_links(['cmid' => 1, 'userid' => 2]);

        $this->assertStringContainsString('plagiarism_aicontent_pending', $links);
    }

    public function test_get_links_shows_score_once_a_result_exists(): void {
        $this->resetAfterTest();
        set_config('enabled', 1, 'plagiarism_aicontent');
        set_config('flagthreshold', 70, 'plagiarism_aicontent');

        \plagiarism_aicontent\result_repository::record(1, 2, 99, 0.42);

        $plugin = new plagiarism_plugin_aicontent();
        $links = $plugin->get_links(['cmid' => 1, 'userid' => 2]);

        $this->assertStringContainsString('plagiarism_aicontent_score', $links);
        $this->assertStringContainsString('42', $links);
        $this->assertStringNotContainsString('plagiarism_aicontent_score_flagged', $links);
    }

    public function test_get_links_flags_score_above_threshold(): void {
        $this->resetAfterTest();
        set_config('enabled', 1, 'plagiarism_aicontent');
        set_config('flagthreshold', 70, 'plagiarism_aicontent');

        \plagiarism_aicontent\result_repository::record(1, 2, 99, 0.9);

        $plugin = new plagiarism_plugin_aicontent();
        $links = $plugin->get_links(['cmid' => 1, 'userid' => 2]);

        $this->assertStringContainsString('plagiarism_aicontent_score_flagged', $links);
    }

    public function test_print_disclosure_when_enabled(): void {
        $this->resetAfterTest();
        set_config('enabled', 1, 'plagiarism_aicontent');

        $plugin = new plagiarism_plugin_aicontent();
        $disclosure = $plugin->print_disclosure(1);

        $this->assertStringContainsString('plagiarism_aicontent_disclosure', $disclosure);
        $this->assertNotSame('', $disclosure);
    }
}
