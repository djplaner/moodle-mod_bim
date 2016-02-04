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
 * This page lists all the instances of bim in a particular course
 *
 * @package mod_bim
 * @copyright 2010 onwards David Jones {@link http://davidtjones.wordpress.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);   // course

// **** ADDD THIS BACK
if (! $course = $DB->get_record('course', array('id'=> $id))) {
    error('Course ID is incorrect');
}

require_course_login($course);
$context = context_course::instance( $course->id );

$event = \mod_bim\event\course_module_instance_list_viewed::create(array(
    'context' => context_course::instance($course->id)
));
$event->trigger();

// Get all required stringsbim

// $strbims = get_string('modulenameplural', 'bim');
// $strbim  = get_string('modulename', 'bim');

// Print the header
$PAGE->set_url( '/mod/bim/index.php', array( 'id' => $id) );
$PAGE->set_title( format_string($course->fullname));
$PAGE->set_heading( format_string($course->fullname));
$PAGE->set_context( $context);

// ?? not sure whether this is needed
// $navlinks = array();
// $navlinks[] = array('name' => $strbims, 'link' => '', 'type' => 'activity');
// $navigation = build_navigation($navlinks);

echo $OUTPUT->header();

// Get all the appropriate data

if (! $bims = get_all_instances_in_course('bim', $course)) {
    echo $OUTPUT->heading(get_string('nonewbims', 'bim'), 2);
    echo $OUTPUT->continue_button("view.php?id=$course->id");
    echo $OUTPUT->footer();
    die();
}

// Print the list of instances (your module will probably extend this)

$timenow  = time();
$strname  = get_string('name');
$strweek  = get_string('week');
$strtopic = get_string('topic');

$table = new html_table;

if ($course->format == 'weeks') {
    $table->head  = array ($strweek, $strname);
    $table->align = array ('center', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array ($strtopic, $strname);
    $table->align = array ('center', 'left', 'left', 'left');
} else {
    $table->head  = array ($strname);
    $table->align = array ('left', 'left', 'left');
}

foreach ($bims as $bim) {
    if (!$bim->visible) {
        // Show dimmed if the mod is hidden
        $link = "<a class=\"dimmed\" href=\"view.php?id=$bim->coursemodule\">$bim->name</a>";
    } else {
        // Show normal if the mod is visible
        $link = "<a href=\"view.php?id=$bim->coursemodule\">$bim->name</a>";
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array ($bim->section, $link);
    } else {
        $table->data[] = array ($link);
    }
}

echo $OUTPUT->heading( get_string('modulenameplural', 'bim'), 2);
echo html_writer::table( $table );

// Finish the page

echo $OUTPUT->footer();

