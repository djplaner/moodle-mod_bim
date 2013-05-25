<?php 

/**
 * This page lists all the instances of bim in a particular course
 *
 * @author  David Jones <davidthomjones@gmail.com>
 * @package mod/bim
 */

/// Replace bim with the name of your module and remove this line

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);   // course

if (! $course = get_record('course', 'id', $id)) {
    error('Course ID is incorrect');
}

require_course_login($course);

add_to_log($course->id, 'bim', 'view all', "index.php?id=$course->id", '');


/// Get all required stringsbim

$strbims = get_string('modulenameplural', 'bim');
$strbim  = get_string('modulename', 'bim');


/// Print the header

$navlinks = array();
$navlinks[] = array('name' => $strbims, 'link' => '', 'type' => 'activity');
$navigation = build_navigation($navlinks);

print_header_simple($strbims, '', $navigation, '', '', true, '', navmenu($course));

/// Get all the appropriate data

if (! $bims = get_all_instances_in_course('bim', $course)) {
    notice('There are no instances of bim', "../../course/view.php?id=$course->id");
    die;
}

/// Print the list of instances (your module will probably extend this)

$timenow  = time();
$strname  = get_string('name');
$strweek  = get_string('week');
$strtopic = get_string('topic');

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
        //Show dimmed if the mod is hidden
        $link = "<a class=\"dimmed\" href=\"view.php?id=$bim->coursemodule\">$bim->name</a>";
    } else {
        //Show normal if the mod is visible
        $link = "<a href=\"view.php?id=$bim->coursemodule\">$bim->name</a>";
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array ($bim->section, $link);
    } else {
        $table->data[] = array ($link);
    }
}

print_heading($strbims);
print_table($table);

/// Finish the page

print_footer($course);

?>
