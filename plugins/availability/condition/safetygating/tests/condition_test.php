<?php

namespace availability_safetygating;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/completionlib.php');

/**
 * Unit tests for the safety-gating condition (spec 5.7).
 *
 * @package availability_safetygating
 */
final class condition_test extends \advanced_testcase {

    public static function setupBeforeClass(): void {
        global $CFG;
        require_once($CFG->dirroot . '/availability/tests/fixtures/mock_info.php');
    }

    /**
     * Tests the constructor, including error conditions, and __toString().
     */
    public function test_constructor(): void {
        // Missing ->cm.
        $structure = new \stdClass();
        try {
            new condition($structure);
            $this->fail('Expected a coding_exception for missing ->cm');
        } catch (\coding_exception $e) {
            $this->assertStringContainsString('Missing or invalid ->cm', $e->getMessage());
        }

        // Invalid ->cm.
        $structure->cm = 'hello';
        try {
            new condition($structure);
            $this->fail('Expected a coding_exception for invalid ->cm');
        } catch (\coding_exception $e) {
            $this->assertStringContainsString('Missing or invalid ->cm', $e->getMessage());
        }

        // Valid ->cm, no ->validitydays: defaults to 0 (no expiry).
        $structure->cm = 42;
        $cond = new condition($structure);
        $this->assertEquals('{safetygating:cm42}', (string) $cond);

        // With a validity period.
        $structure->validitydays = 30;
        $cond = new condition($structure);
        $this->assertEquals('{safetygating:cm42 valid30d}', (string) $cond);

        // Negative validitydays is clamped to 0.
        $structure->validitydays = -5;
        $cond = new condition($structure);
        $this->assertEquals('{safetygating:cm42}', (string) $cond);
    }

    /**
     * Tests the save() function round-trips through get_json().
     */
    public function test_save_and_json(): void {
        $structure = condition::get_json(42, 30);
        $cond = new condition($structure);
        $this->assertEquals($structure, $cond->save());

        // No validity period.
        $structure = condition::get_json(7);
        $cond = new condition($structure);
        $this->assertEquals(0, $structure->validitydays);
        $this->assertEquals($structure, $cond->save());
    }

    /**
     * Tests is_available() and get_description() with no expiry configured.
     */
    public function test_usage_no_expiry(): void {
        global $USER, $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();

        $CFG->enablecompletion = true;
        $CFG->enableavailability = true;
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['enablecompletion' => 1]);
        $induction = $generator->get_plugin_generator('mod_page')->create_instance([
            'course' => $course->id,
            'name' => 'Safety Induction',
            'completion' => COMPLETION_TRACKING_MANUAL,
        ]);

        $modinfo = get_fast_modinfo($course);
        $inductioncm = $modinfo->get_cm($induction->cmid);
        $info = new \core_availability\mock_info($course, $USER->id);

        $cond = new condition(condition::get_json((int) $inductioncm->id));

        // Not yet completed: gated activity unavailable.
        $this->assertFalse($cond->is_available(false, $info, true, $USER->id));
        $this->assertTrue($cond->is_available(true, $info, true, $USER->id));

        $description = $cond->get_description(false, false, $info);
        $description = \core_availability\info::format_info($description, $course);
        $this->assertMatchesRegularExpression('~Safety Induction~', $description);

        // Mark the induction complete.
        $completion = new \completion_info($course);
        $completion->update_state($inductioncm, COMPLETION_COMPLETE);
        (\cache::make('core', 'completion'))->purge();

        $this->assertTrue($cond->is_available(false, $info, true, $USER->id));
        $this->assertFalse($cond->is_available(true, $info, true, $USER->id));
    }

    /**
     * Tests the expiry/re-lock behaviour: a completion older than the
     * configured validity period stops satisfying the condition, without
     * any cron job — is_available() reads the live completion timestamp.
     */
    public function test_expiry(): void {
        global $USER, $CFG, $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $CFG->enablecompletion = true;
        $CFG->enableavailability = true;
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['enablecompletion' => 1]);
        $induction = $generator->get_plugin_generator('mod_page')->create_instance([
            'course' => $course->id,
            'name' => 'Safety Induction',
            'completion' => COMPLETION_TRACKING_MANUAL,
        ]);

        $modinfo = get_fast_modinfo($course);
        $inductioncm = $modinfo->get_cm($induction->cmid);
        $info = new \core_availability\mock_info($course, $USER->id);

        // Valid for 30 days.
        $cond = new condition(condition::get_json((int) $inductioncm->id, 30));

        $completion = new \completion_info($course);
        $completion->update_state($inductioncm, COMPLETION_COMPLETE);
        (\cache::make('core', 'completion'))->purge();

        // Freshly completed: still within the validity window.
        $this->assertTrue($cond->is_available(false, $info, true, $USER->id));

        // Backdate the completion beyond the 30-day validity window.
        $DB->set_field(
            'course_modules_completion',
            'timemodified',
            time() - (31 * DAYSECS),
            ['coursemoduleid' => $inductioncm->id, 'userid' => $USER->id]
        );
        (\cache::make('core', 'completion'))->purge();

        // Lapsed certification: gated activity re-locks automatically.
        $this->assertFalse($cond->is_available(false, $info, true, $USER->id));
        $this->assertTrue($cond->is_available(true, $info, true, $USER->id));
    }

    /**
     * A referenced induction activity that no longer exists fails closed
     * rather than silently unlocking the gated activity.
     */
    public function test_missing_induction_activity_fails_closed(): void {
        global $USER, $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();

        $CFG->enablecompletion = true;
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['enablecompletion' => 1]);
        $info = new \core_availability\mock_info($course, $USER->id);

        // No course-module with this id exists in the course.
        $cond = new condition(condition::get_json(999999));

        $this->assertFalse($cond->is_available(false, $info, true, $USER->id));
        // Even the NOT form should not unlock on a broken reference.
        $this->assertTrue($cond->is_available(true, $info, true, $USER->id));
    }

    /**
     * Tests update_dependency_id() remaps a matching course_modules id.
     */
    public function test_update_dependency_id(): void {
        $cond = new condition(condition::get_json(42, 10));

        $this->assertTrue($cond->update_dependency_id('course_modules', 42, 99));
        $this->assertEquals((object) ['type' => 'safetygating', 'cm' => 99, 'validitydays' => 10], $cond->save());

        // Non-matching table/id: no change.
        $this->assertFalse($cond->update_dependency_id('course_modules', 42, 5));
        $this->assertFalse($cond->update_dependency_id('other_table', 99, 5));
    }
}
