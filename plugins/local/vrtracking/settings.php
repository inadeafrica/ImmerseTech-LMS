<?php

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_vrtracking', get_string('pluginname', 'local_vrtracking'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configcheckbox(
        'local_vrtracking/enabled',
        get_string('enabled', 'local_vrtracking'),
        get_string('enabled_desc', 'local_vrtracking'),
        1
    ));

    $settings->add(new admin_setting_heading(
        'local_vrtracking/webservice_heading',
        get_string('webservice_heading', 'local_vrtracking'),
        get_string('webservice_heading_desc', 'local_vrtracking')
    ));
}
