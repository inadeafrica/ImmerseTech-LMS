<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'VR Simulation Tracking';

$string['enabled'] = 'Enable VR tracking';
$string['enabled_desc'] = 'When disabled, the partner webhook still authenticates but rejects completion events rather than recording them.';
$string['webservice_heading'] = 'Partner web service';
$string['webservice_heading_desc'] = 'Completion events are received via the "ImmerseTech VR Partner Integration" external service and the local_vrtracking_record_completion function. Issue the VR partner a dedicated token restricted to that service — see Site administration > Server > Web services.';

$string['vrtracking:receivewebhook'] = 'Receive VR completion events';
$string['vrtracking:viewsessions'] = 'View VR session records';

$string['evidence_vrcompletion'] = 'Completed the VR simulation "{$a}" — pending assessor sign-off';
$string['evidencenote_timeontask'] = 'Time on task: {$a} min.';
$string['evidencenote_score'] = 'Score: {$a}.';

$string['privacy:metadata:local_vrtracking_session'] = 'Records of VR practical completion/progress events reported by the VR partner platform.';
$string['privacy:metadata:local_vrtracking_session:userid'] = 'The id of the trainee who took the VR session.';
$string['privacy:metadata:local_vrtracking_session:cmid'] = 'The course module (VR practical/lesson) the session belongs to.';
$string['privacy:metadata:local_vrtracking_session:status'] = 'Completion status of the session.';
$string['privacy:metadata:local_vrtracking_session:timeontasksecs'] = 'Time on task, in seconds.';
$string['privacy:metadata:local_vrtracking_session:score'] = 'Performance score reported by the simulation, where supported.';
$string['privacy:metadata:local_vrtracking_session:timecreated'] = 'The time the event was recorded.';
$string['privacy:metadata:vrpartner'] = 'To track VR practicals, session progress and performance data is exchanged with the VR partner platform hosting the simulation.';
$string['privacy:metadata:vrpartner:userid'] = 'The trainee identifier used to match the VR session back to a Moodle user.';
$string['privacy:metadata:vrpartner:status'] = 'Completion status reported back by the VR platform.';
$string['privacy:metadata:vrpartner:score'] = 'Performance score reported back by the VR platform.';
