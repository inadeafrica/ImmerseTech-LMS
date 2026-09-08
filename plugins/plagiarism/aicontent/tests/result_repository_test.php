<?php

namespace plagiarism_aicontent;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for AI-content detection result persistence (spec 5.2).
 *
 * @package plagiarism_aicontent
 */
final class result_repository_test extends \advanced_testcase {

    public function test_record_and_get_latest(): void {
        $this->resetAfterTest();

        $this->assertNull(result_repository::get_latest(1, 2));

        $result = result_repository::record(1, 2, 99, 0.73, '{"raw":true}');
        $this->assertNotEmpty($result->id);

        $latest = result_repository::get_latest(1, 2);
        $this->assertEqualsWithDelta(0.73, $latest->likelihood, 0.0001);
        $this->assertEquals(99, $latest->submissionid);
    }

    public function test_get_latest_returns_most_recent(): void {
        $this->resetAfterTest();

        result_repository::record(1, 2, 98, 0.1);
        $this->waitForSecond();
        $newest = result_repository::record(1, 2, 99, 0.9);

        $latest = result_repository::get_latest(1, 2);
        $this->assertEquals($newest->id, $latest->id);
    }

    public function test_get_latest_is_scoped_to_cmid_and_userid(): void {
        $this->resetAfterTest();

        result_repository::record(1, 2, 99, 0.5);

        $this->assertNull(result_repository::get_latest(1, 3), 'Different user must not match.');
        $this->assertNull(result_repository::get_latest(2, 2), 'Different cmid must not match.');
    }
}
