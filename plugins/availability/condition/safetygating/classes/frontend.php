<?php

namespace availability_safetygating;

defined('MOODLE_INTERNAL') || die();

/**
 * Front-end class: populates the "Add restriction" dialog for safety gating.
 */
class frontend extends \core_availability\frontend {

    /** @var array Cached init parameters. */
    protected $cacheinitparams = [];

    /** @var string Cache key (course/cm/section ids) for the cached params. */
    protected $cachekey = '';

    protected function get_javascript_strings() {
        return ['label_induction', 'label_validitydays', 'option_novalidity'];
    }

    protected function get_javascript_init_params($course, ?\cm_info $cm = null, ?\section_info $section = null) {
        $cachekey = $course->id . ',' . ($cm ? $cm->id : '') . ',' . ($section ? $section->id : '');
        if ($cachekey !== $this->cachekey) {
            $context = \context_course::instance($course->id);
            $modinfo = get_fast_modinfo($course);
            $cms = [];
            foreach ($modinfo->cms as $id => $othercm) {
                // Any activity can act as the induction gate, whether or not
                // it tracks completion itself is not required here since the
                // condition reads the induction cm's own completion state.
                if ((empty($cm) || $cm->id != $id) && !$othercm->deletioninprogress) {
                    $cms[] = (object) [
                        'id' => $id,
                        'name' => format_string($othercm->name, true, ['context' => $context]),
                    ];
                }
            }
            $this->cachekey = $cachekey;
            $this->cacheinitparams = [$cms];
        }

        return $this->cacheinitparams;
    }

    protected function allow_add($course, ?\cm_info $cm = null, ?\section_info $section = null) {
        // Meaningful only once there's at least one other activity that
        // could serve as the safety induction gate.
        $params = $this->get_javascript_init_params($course, $cm, $section);

        return !empty($params[0]);
    }
}
