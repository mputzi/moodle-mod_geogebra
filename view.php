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
 * Prints a particular instance of geogebra
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage geogebra
 * @copyright  2011 Departament d'Ensenyament de la Generalitat de Catalunya
 * @author     Sara Arjona Téllez <sarjona@xtec.cat>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->libdir.'/completionlib.php');


$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // geogebra instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('geogebra', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $geogebra  = $DB->get_record('geogebra', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $geogebra  = $DB->get_record('geogebra', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $geogebra->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('geogebra', $geogebra->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/geogebra:view', $context);

add_to_log($course->id, 'geogebra', 'view', "view.php?id={$cm->id}", $geogebra->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/geogebra/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($geogebra->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);


// Mark viewed if required
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('geogebra-'.$somevar);

geogebra_view_header($geogebra, $cm, $course);
geogebra_view_intro($geogebra, $cm);

$action = optional_param('action', '', PARAM_TEXT);
if (has_capability('mod/geogebra:grade', $context, $USER->id, false)){    
    if ($action == 'preview'){
        geogebra_view_applet($geogebra, $context, true);
    } else{
        geogebra_view_dates($geogebra, $cm);
        geogebra_print_results_table($geogebra, $context, $cm, $course, $action);
    }
    
} else{
    geogebra_view_applet($geogebra, $context);    
}

geogebra_view_footer();
