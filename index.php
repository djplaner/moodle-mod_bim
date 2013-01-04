<?php // $Id: index.php,v 1.7.2.2 2009/03/31 13:07:21 mudrd8mz Exp $

/**
 * This page lists all the instances of bim in a particular course
 *
 * @author  David Jones <davidthomjones@gmail.com>
 * @version $Id$ 
 * @package mod/bim
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);   // course

// **** ADDD THIS BACK
if (! $course = $DB->get_record('course', array('id'=> $id))) {
    error('Course ID is incorrect');
}

require_course_login($course);
$context = get_context_instance( CONTEXT_COURSE, $course->id );

add_to_log($course->id, 'bim', 'view all', "index.php?id=$course->id", '');

/// Get all required stringsbim

//$strbims = get_string('modulenameplural', 'bim');
//$strbim  = get_string('modulename', 'bim');

/// Print the header
$PAGE->set_url( '/mod/bim/index.php', array( 'id' => $id) );
$PAGE->set_title( format_string($course->fullname));
$PAGE->set_heading( format_string($course->fullname));
$PAGE->set_context( $context);

// ?? not sure whether this is needed
//$navlinks = array();
//$navlinks[] = array('name' => $strbims, 'link' => '', 'type' => 'activity');
//$navigation = build_navigation($navlinks);

echo $OUTPUT->header();

/// Get all the appropriate data

if (! $bims = get_all_instances_in_course('bim', $course)) {
    echo $OUTPUT->heading(get_string('nonewbims', 'bim'), 2);
    echo $OUTPUT->continue_button("view.php?id=$course->id");
    echo $OUTPUT->footer();
    die();
}

/// Print the list of instances (your module will probably extend this)

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

echo $OUTPUT->heading( get_string('modulenameplural', 'bim'), 2);
echo html_writer::table( $table );

/// Finish the page

echo $OUTPUT->footer();
?>
