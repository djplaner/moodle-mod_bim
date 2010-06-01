<?php  // $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $

/**
 * This page prints a particular instance of bim
 *
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
 * @author  David Jones <davidthomjones@gmail.com>
 * @version $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $
 * @package mod/bim
 */

/// (Replace bim with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once('lib.php');
require_once($CFG->dirroot.'/mod/bim/lib/locallib.php');
require_once($CFG->dirroot.'/mod/bim/student/view.php');
require_once($CFG->dirroot.'/mod/bim/marker/view.php');
require_once($CFG->dirroot.'/mod/bim/coordinator/view.php');
require_once($CFG->dirroot.'/mod/bim/lib/bim_rss.php');
require_once($CFG->dirroot.'/lib/tablelib.php' );

global $USER;

// course_module id ($cm) entry from course_modules table
// - is the unique combination of course and module/activity
$id = optional_param('id', 0, PARAM_INT); 

// Given the course module id, get the other information
// $cm - row from course_module table
// $course - row from course
// $bim - row from bim

if ($id) {
    if (! $cm = get_coursemodule_from_id('bim', $id)) {
        error('Course Module ID was incorrect');
    }
    if (! $course = get_record('course', 'id', $cm->course)) {
        error('Course is misconfigured');
    }
    if (! $bim = get_record('bim', 'id', $cm->instance)) {
        error('Course module is incorrect');
    }
/*} else if ($a) {
    if (! $bim = get_record('bim', 'id', $a)) {
        error('Course module is incorrect');
    }
    if (! $course = get_record('course', 'id', $bim->course)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('bim', $bim->id, $course->id)) {
        error('Course Module ID was incorrect');
    }
*/
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

// log additions now moved down into specific actions
//add_to_log($course->id, "bim", "view", "view.php?id=$cm->id", "$bim->id");


/// Print the main part of the page

// determine the type of user
$context = get_context_instance( CONTEXT_MODULE, $cm->id );

// Who is the user

if ( empty($USER->id)) {
    $userid = 0;
} else {
    $userid = $USER->id;
}

//*****************************
// Time to handle over to the different functions that
// figure out what to show for each "type of user"

if ( has_capability( 'mod/bim:coordinator', $context)) {
    // administrator can the configure stuff
    show_coordinator( $bim, $userid, $cm, $course );
}else if (has_capability('mod/bim:student', $context)) {
    // student can see details of their registered blog
    show_student($bim, $userid, $cm, $course );
} else if ( has_capability( 'mod/bim:marker', $context )) {
    show_marker( $bim, $userid, $cm, $course );
} else {
  error( "No capability to access this page" );
}


?>

