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
        global $DB;
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

/*
 * Changes a string to an approximate of sentence case.
 *
 * This will handle English personal pronoun 'I' and words all in capitals.
 * However will not deal with proper nouns, which editor can decide how to handle in lnaguage file.
 *
 * @var string $str
 * @var string $lang
 * return string
 */
function local_validatelang_sentence_case($str, $lang) {
    $capstr = local_validatelang_enclose_allcaps($str, $lang);
    $capstr = local_validatelang_enclose_breaks($capstr, $lang);
    $capstr = strtolower($capstr);
    $capstr = local_validatelang_fix_standard_rules($capstr, $lang);

    $cap = true;
    $ret='';
    for ($x = 0; $x < strlen($capstr); $x++) {
        $letter = substr($capstr, $x, 1);
        if (in_array($letter, array(".", "!", "?", "~"))) {
            $cap = true;
        } else if ($letter != " " && $cap == true) {
            $letter = strtoupper($letter);
            $cap = false;
        }
        $ret .= $letter;
    }
    $ret = local_validatelang_restore_breaks($ret, $lang);
    $ret = local_validatelang_restore_allcaps($ret, $lang);
    return $ret;
}

/*
 * Processes known and well-defined rules for a language.
 *
 * For example the first person pronoun in English must always be capitalised.
 *
 * @var string $str
 * @var string $lang
 * return string
 */
function local_validatelang_fix_standard_rules($str, $lang) {
    if ($lang == 'en') {
        $str = preg_replace("/\bi\b/", "I", $str);
    }
    return $str;
}

/*
 * Returns a string as an array of sentences.
 *
 * @var string $str
 * @var string $lang
 * return string
 */
function local_validatelang_split_sentences($str, $lang) {
    if ($lang == 'en') {
        $sentences = preg_split('/([.?!]+)/', $str, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
    }
    return $sentences;
}

/*
 * Encloses line breaks as #BREAK-EOL# sign to identify them.
 *
 * @var string $str
 * @var string $lang
 * return string
 */
function local_validatelang_enclose_breaks($str, $lang) {
    return preg_replace_callback(
        "/(\n+\r?)/",
        create_function(
            '$matches',
            'return "#~";'
        ),
        $str
    );
}

/*
 * Restores any line breaks to end of line.
 *
 * @var string $str
 * @var string $lang
 * return string
 */
function local_validatelang_restore_breaks($str, $lang) {
    return preg_replace("/#~/", PHP_EOL, $str);
}

/*
 * Standardises line breaks so that strings can compare.
 *
 * This does not alter the original string so is safe - we only want to apply a case rule to paragraphs.
 *
 * @var string $str
 * @var string $lang
 * return string
 */
function local_validatelang_standardise_breaks($str, $lang) {
    return preg_replace("/(\n+\r?)/", PHP_EOL, $str);
}

/*
 * Encloses any words of lenght 2 or more characters within # sign to identify them.
 *
 * @var string $str
 * @var string $lang
 * return string
 */
function local_validatelang_enclose_allcaps($str, $lang) {
    return preg_replace_callback(
        '/(\b[A-Z][A-Z]+\b)/',
        create_function(
            '$matches',
            'return "#$matches[0]#";'
        ),
        $str
    );
}

/*
 * Restores any words enclosed within # signs to all capitals.
 *
 * @var string $str
 * @var string $lang
 * return string
 */
function local_validatelang_restore_allcaps($str, $lang) {
    return preg_replace_callback(
        '/#(\b[a-z][a-z]+\b)#/',
        create_function(
            '$matches',
            'return strtoupper(str_replace("#", "", $matches[0]));'
        ),
        $str
    );
}

/**
 * Remove HTML tags, including invisible text such as style and
 * script code, and embedded objects.  Add line breaks around
 * block-level tags to prevent word joining after tag removal.
 */
function local_validatelang_strip_html_tags($text) {
    $text = preg_replace(
        array(
            // Remove invisible content.
            '@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<object[^>]*?.*?</object>@siu',
            '@<embed[^>]*?.*?</embed>@siu',
            '@<applet[^>]*?.*?</applet>@siu',
            '@<noframes[^>]*?.*?</noframes>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
            '@<noembed[^>]*?.*?</noembed>@siu',
            // Add line breaks before and after blocks.
            '@</?((address)|(blockquote)|(center)|(del))@iu',
            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
            '@</?((table)|(th)|(td)|(caption))@iu',
            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
            '@</?((frameset)|(frame)|(iframe))@iu',
        ),
        array(
            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
            "\n\$0", "\n\$0",
        ),
        $text );
    return strip_tags($text);
}