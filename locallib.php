<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Description
 *
 * @package    local
 * @subpackage validatelang
 * @copyright  &copy; 2013 Nine Lanterns Pty Ltd  {@link http://www.ninelanterns.com.au}
 * @author     james.ballard
 * @version    1.0
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Description
 */
class local_validatelang_settings extends moodleform {

    const CASE_RULE_NONE = 0;
    const CASE_RULE_SENTENCE = 1;
    const CASE_RULE_TITLE = 2;

    // Define the form.
    public function definition() {
        global $CFG, $DB;
        $mform =& $this->_form;

        $options = $DB->get_records_menu('tool_customlang_components', null, '', 'id, name');
        $mform->addElement('select', 'componentid', get_string('componentid', 'local_validatelang'), $options,
            array('class' => 'chosen-select'));

        $options = array(
            self::CASE_RULE_NONE => get_string('caserulenone', 'local_validatelang'),
            self::CASE_RULE_SENTENCE => get_string('caserulesentence', 'local_validatelang')
        );
        $mform->addElement('select', 'caserule', get_string('caserule', 'local_validatelang'), $options,
            array('class' => 'chosen-select'));

        $this->add_action_buttons();
    }

    // Perform some extra moodle validation.
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}

function local_validatelang_sentence_case($str, $lang) {
    $cap = true;
    $ret='';
    $capstr = preg_replace_callback(
        '/(\b[A-Z][A-Z]+\b)/',
        create_function(
            '$matches',
            'return "#$matches[0]#";'
        ),
        $str
    );
    $capstr = strtolower($capstr);
    if ($lang == 'en') {
        $capstr = preg_replace("/\bi\b/", "I", $capstr);
    }
    for ($x = 0; $x < strlen($capstr); $x++) {
        $letter = substr($capstr, $x, 1);
        if ($letter == "." || $letter == "!" || $letter == "?") {
            $cap = true;
        } else if ($letter != " " && $cap == true) {
            $letter = strtoupper($letter);
            $cap = false;
        }
        $ret .= $letter;
    }
    $ret = preg_replace_callback(
        '/#(\b[a-z][a-z]+\b)#/',
        create_function(
            '$matches',
            'return strtoupper(str_replace("#", "", $matches[0]));'
        ),
        $ret
    );
    return $ret;
}