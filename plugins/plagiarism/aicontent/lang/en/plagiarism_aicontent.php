<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'AI-generated-content detection';

$string['enabled'] = 'Enable AI-content detection';
$string['enabled_desc'] = 'Distinct from similarity/plagiarism checking. Advisory only — never auto-blocks a submission on its own (spec 5.2).';
$string['enabled_help'] = 'Distinct from similarity/plagiarism checking. Advisory only — never auto-blocks a submission on its own (spec 5.2).';
$string['apiendpoint'] = 'Detection API endpoint';
$string['apiendpoint_desc'] = 'Base URL of the third-party AI-content detection API.';
$string['apiendpoint_help'] = 'Base URL of the third-party AI-content detection API.';
$string['apikey'] = 'Detection API key';
$string['apikey_desc'] = 'API key for the configured detection provider.';
$string['apikey_help'] = 'API key for the configured detection provider.';
$string['flagthreshold'] = 'Flag threshold (%)';
$string['flagthreshold_desc'] = 'Likelihood score, as a percentage, above which a submission is visually flagged for instructor review. This is a display threshold only — configurable auto-block/flag behaviour per assignment (spec 5.2) is set on the assignment itself, not here.';
$string['flagthreshold_help'] = 'Likelihood score, as a percentage, above which a submission is visually flagged for instructor review. This is a display threshold only — configurable auto-block/flag behaviour per assignment (spec 5.2) is set on the assignment itself, not here.';

$string['pending'] = 'AI-content check pending';
$string['likelihood_score'] = 'AI-content likelihood: {$a}%';
$string['studentdisclosure'] = 'This submission will be checked for likely AI-generated content. This is advisory only and does not automatically affect your grade.';

$string['privacy:metadata'] = 'The AI-generated-content detection plugin sends submission text to a third-party detection service and stores the resulting likelihood score.';
$string['privacy:metadata:plagiarism_aicontent_result'] = 'Records of AI-content detection results per submission.';
$string['privacy:metadata:plagiarism_aicontent_result:userid'] = 'The id of the trainee who submitted the work.';
$string['privacy:metadata:plagiarism_aicontent_result:submissionid'] = 'The assignment submission this result belongs to.';
$string['privacy:metadata:plagiarism_aicontent_result:likelihood'] = 'The AI-content likelihood score, 0.0-1.0.';
$string['privacy:metadata:plagiarism_aicontent_result:timecreated'] = 'The time the check was completed.';
$string['privacy:metadata:aicontentprovider'] = 'Submission text is sent to the configured third-party AI-content detection provider to be scored.';
$string['privacy:metadata:aicontentprovider:userid'] = 'An identifier for the submitting trainee may be included depending on provider configuration.';
$string['privacy:metadata:aicontentprovider:text'] = 'The submitted text being scored.';
