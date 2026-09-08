<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Restriction by safety induction';
$string['description'] = 'Gate an activity on completion of a safety induction activity, with an optional validity period after which it re-locks (spec 5.7).';

$string['title'] = 'Safety induction';
$string['missing'] = '(missing safety induction activity)';

$string['requires_induction'] = 'You have completed <strong>{$a}</strong>';
$string['requires_induction_not'] = 'You have not completed <strong>{$a}</strong>';
$string['requires_induction_expiring'] = 'You have completed <strong>{$a->induction}</strong> within the last {$a->days} day(s)';
$string['requires_induction_not_expiring'] = 'You have not completed <strong>{$a->induction}</strong> within the last {$a->days} day(s)';

$string['label_induction'] = 'Safety induction activity';
$string['label_validitydays'] = 'Certification valid for (days, 0 = no expiry)';
$string['option_novalidity'] = 'No expiry';

$string['privacy:metadata'] = 'The Restriction by safety induction plugin does not store any personal data itself; it reads existing activity completion data belonging to core_completion.';
