<?php

// Plagiarism plugins are administered as a standalone external page (see
// \core\plugininfo\plagiarism::load_settings()), not the `$settings`
// admin_settingpage fragment other plugin types use — this file is loaded
// directly as /plagiarism/aicontent/settings.php.

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

admin_externalpage_setup('plagiarismaicontent');

/**
 * Settings form for the AI-generated-content detection plugin.
 */
class plagiarism_aicontent_settings_form extends moodleform {

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement(
            'advcheckbox',
            'enabled',
            get_string('enabled', 'plagiarism_aicontent'),
            '',
            [],
            [0, 1]
        );
        $mform->addHelpButton('enabled', 'enabled', 'plagiarism_aicontent');

        $mform->addElement('text', 'apiendpoint', get_string('apiendpoint', 'plagiarism_aicontent'));
        $mform->setType('apiendpoint', PARAM_URL);
        $mform->addHelpButton('apiendpoint', 'apiendpoint', 'plagiarism_aicontent');

        $mform->addElement('passwordunmask', 'apikey', get_string('apikey', 'plagiarism_aicontent'));
        $mform->setType('apikey', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('apikey', 'apikey', 'plagiarism_aicontent');

        $mform->addElement('text', 'flagthreshold', get_string('flagthreshold', 'plagiarism_aicontent'));
        $mform->setType('flagthreshold', PARAM_INT);
        $mform->addHelpButton('flagthreshold', 'flagthreshold', 'plagiarism_aicontent');

        $this->add_action_buttons();
    }
}

$returnurl = new moodle_url('/admin/settings.php', ['section' => 'plagiarismsettings']);

$mform = new plagiarism_aicontent_settings_form();
$mform->set_data((object) [
    'enabled' => (int) get_config('plagiarism_aicontent', 'enabled'),
    'apiendpoint' => get_config('plagiarism_aicontent', 'apiendpoint'),
    'apikey' => get_config('plagiarism_aicontent', 'apikey'),
    'flagthreshold' => get_config('plagiarism_aicontent', 'flagthreshold') ?: 70,
]);

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    set_config('enabled', (int) !empty($data->enabled), 'plagiarism_aicontent');
    set_config('apiendpoint', $data->apiendpoint, 'plagiarism_aicontent');
    set_config('apikey', $data->apikey, 'plagiarism_aicontent');
    set_config('flagthreshold', $data->flagthreshold, 'plagiarism_aicontent');

    redirect(
        new moodle_url('/plagiarism/aicontent/settings.php'),
        get_string('changessaved'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'plagiarism_aicontent'));
$mform->display();
echo $OUTPUT->footer();
