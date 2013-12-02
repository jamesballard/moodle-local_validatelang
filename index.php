<?php

require_once('../../config.php');
global $DB, $OUTPUT, $PAGE;

require_once('locallib.php');

$PAGE->set_context(null);
require_login();

$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/validatelang/index.php');
$PAGE->set_title($SITE->shortname);
$PAGE->navbar->add(get_string('admin'));

echo $OUTPUT->header();

// Initiate the form.
$mform = new local_validatelang_settings();

// Display the form.
$mform->display();

if ($mform->is_cancelled()) {
    // Tell php what to do if your user presses cancel - probably a redirect is called for!
    redirect($PAGE->url);
} else if ($fromform = $mform->get_data()) {
    $component = $DB->get_record('tool_customlang_components', array('id' => $fromform->componentid));
    $strings = $DB->get_records('tool_customlang', array('componentid' => $component->id));
    foreach ($strings as $string) {
        echo $OUTPUT->box_start();
        $langstring = get_string($string->stringid, $component->name);
        echo $OUTPUT->heading($string->stringid, 4);
        echo $langstring;
        switch ($fromform->caserule) {
            case $mform::CASE_RULE_SENTENCE:
                $casedstring = local_validatelang_sentence_case($langstring, $string->lang);
                $casecompare = strcmp($casedstring, $langstring);
                if (!empty($casecompare)) {
                    echo html_writer::tag('p', get_string('notsentencecase', 'local_validatelang'),
                        array('class' => 'alert alert-error'));
                }
                break;
            case 'title':
                // TODO - test title case.
                break;
            default:
                break;
        }
        echo $OUTPUT->box_end();
    }
}

echo $OUTPUT->footer();

?>