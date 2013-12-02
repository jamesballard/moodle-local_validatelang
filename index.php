<?php

require_once('../../config.php');
global $DB, $OUTPUT, $PAGE;

$PAGE->set_context(null);
require_login();

$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/validatelang/index.php');
$PAGE->set_title($SITE->shortname);

$PAGE->navbar->add(get_string('admin'));

// This is hard-coded to a particular component for now - use DB to find it.
$strings = $DB->get_records('tool_customlang', array('componentid' => 1));

echo $OUTPUT->header();

echo $OUTPUT->heading('strings');

foreach ($strings as $string) {
    echo $OUTPUT->box_start();
    $langstring = get_string($string->stringid, 'enrol_avetmiss');
    $casedstring = local_validatelang_sentence_case($langstring);
    echo $langstring;
    $casecompare = strcmp($casedstring, $langstring);
    if (!empty($casecompare)) {
        echo html_writer::tag('p', 'Wrong case', array('class' => 'alert alert-error'));
    }
    echo $OUTPUT->box_end();
}

echo $OUTPUT->footer();

function local_validatelang_sentence_case($str) {
    $cap = true;
    $ret='';
    $str = strtolower($str);
    for ($x = 0; $x < strlen($str); $x++) {
        $letter = substr($str, $x, 1);
        if ($letter == "." || $letter == "!" || $letter == "?") {
            $cap = true;
        } else if ($letter != " " && $cap == true) {
            $letter = strtoupper($letter);
            $cap = false;
        }
        $ret .= $letter;
    }
    return $ret;
}

?>