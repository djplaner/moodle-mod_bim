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
 * Figure out what an individual bim activity will show when viewed
 * What is shown depends on the role the user is performing. The
 * different roles recognised are
 * - mod/bim:administrator (the coordinator usually)
 *   - Can see/mark all students, configure and released
 *   - Implemented in show_coordinator 
 * - mod/bim:reviewstudentdetails (the student)
 *   - Can see their details
 *   - Implemented in show_student
 * - mod/bim:viewstuddetails
 *   - Can see/mark their students posts
 *   - Implemented in show_teacher
 *
 * @package mod_bim
 * @copyright 2010 onwards David Jones {@link http://davidtjones.wordpress.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/mod/bim/student/view.php');
require_once($CFG->dirroot.'/mod/bim/marker/view.php');
require_once($CFG->dirroot.'/mod/bim/coordinator/view.php');

global $USER;
global $DB;

// course_module id ($cm) entry from course_modules table
// - is the unique combination of course and module/activity
$id = optional_param('id', 0, PARAM_INT);
$n = optional_param('n', 0, PARAM_INT);

// Given the course module id, get the other information
// $cm - row from course_module table
// $course - row from course
// $bim - row from bim

if ($id) {
    $cm = get_coursemodule_from_id('bim', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST );
    $bim = $DB->get_record('bim', array('id'=>$cm->instance), '*', MUST_EXIST );
} else if ($n) {
    $bim = $DB->get_record('bim', array('id'=>$n), '*', MUST_EXIST );
    $course = $DB->get_record('course', array('id'=>$bim->course), '*', MUST_EXIST );
    $cm = get_coursemodule_from_instance('bim', $bim->id, $course->id, false, MUST_EXIST);
} else {
    print_error( 'invalidcoursemodule' );
}

require_login($course, true, $cm);
$context = context_module::instance( $cm->id );

add_to_log($course->id, "bim", "view", "view.php?id={$cm->id}", $bim->id, $cm->id);

// Print the page header

$PAGE->set_url('/mod/bim/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($bim->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

if ( empty($USER->id)) {
    $userid = 0;
} else {
    $userid = $USER->id;
}

// Time to handle over to the different functions that
// figure out what to show for each "type of user"

if ( has_capability( 'mod/bim:coordinator', $context)) {
    // administrator can the configure stuff
    show_coordinator( $bim, $userid, $cm, $course );
} else if ( has_capability( 'mod/bim:marker', $context )) {
    show_marker( $bim, $userid, $cm, $course );
} else if (has_capability('mod/bim:student', $context)) {
    // student can see details of their registered blog
    show_student($bim, $userid, $cm, $course );
} else {
    print_error( "No capability to access this page" );
}


