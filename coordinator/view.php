<?php  // $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $

require_once('lib.php');
require_once($CFG->dirroot.'/mod/bim/lib/locallib.php');
require_once($CFG->dirroot.'/mod/bim/coordinator/manage_questions.php');
require_once($CFG->dirroot.'/mod/bim/coordinator/manage_marking.php');
require_once($CFG->dirroot.'/mod/bim/coordinator/allocate_markers.php');
require_once($CFG->dirroot.'/mod/bim/coordinator/find_student.php');

/*************************************
 * show_coordinator( $bim, $userid, $cm )
 * - show coordinator interface
 */

function show_coordinator( $bim, $userid, $cm, $course )
{

    // optional params required to chose screen/tab
    $tab = optional_param('tab', "config", PARAM_ALPHA);
    $screen = optional_param('screen', "", PARAM_ALPHA);

    // Some kludges here, if $screen is set to ShowPostDetails
    // ShowDetails and AllocatePosts then tab is ShowCoordDetails
    if ( $screen == "ShowPostDetails" || $screen == "AllocatePosts" ||
         $screen == "ShowDetails" || $screen == "MarkPost" ||
         $screen == "changeBlogRegistration" ) {
        $tab = "details";
    }

    bim_print_header( $cm, $bim, $course, $screen );

    // ** eventually will need to pass screen
    bim_build_coordinator_tabs( $cm, $tab );
  
  if ( $tab == "config" )
  {
    bim_configuration_screen( $bim, $cm );
  }
  else if ( $tab == "markers" )
  {
    bim_allocate_markers( $bim, $cm, $userid );
  }
  else if ( $tab == "questions" )
  {
    bim_manage_questions( $bim, $cm );
  }
  else if ( $tab == "manage" )
  {
    $op = optional_param('op', NULL, PARAM_ALPHA);
    if ( $op == "" )
      bim_manage_marking( $bim, $userid, $cm );
    else if ( $op == "release" )
      bim_manage_release( $bim, $userid, $cm );
    else if ( $op == "view" )
      bim_manage_view( $bim, $userid, $cm );
  }
  else if ( $tab == "find" )
  {
    bim_find_student( $bim, $cm );
  }
  else if ( $tab == "details" )
  {
    if ( $screen == "ShowDetails" ) {
        show_marker_student_details( $bim, $userid, $cm );
    } else if ( $screen == "ShowPostDetails" ) {
        show_marker_post_details( $bim, $userid, $cm );
    } else if ( $screen == "AllocatePosts" ) {
        $student = optional_param('uid', 0, PARAM_INT);
        bim_marker_allocate_posts( $bim, $userid, $cm, $student );
    } else if ( $screen == "MarkPost" ) {
        $marking = optional_param('markingId', 0, PARAM_INT);
        bim_marker_mark_post( $bim, $userid, $cm, $marking );
    } else if ( $screen == "changeBlogRegistration" ) {
        $student = optional_param( 'student', 0, PARAM_INT );
        bim_change_blog_registration( $bim, $student, $cm );
    }
  }
}

/*******
 * bim_configuration_screen( $bim, $cm )
 * - display the HTML for the coordinators configuration screen
 */

function bim_configuration_screen( $bim, $cm )
{
  global $CFG;

  $a = new StdClass();
  $a->wwwroot = $CFG->wwwroot;
  $a->cmid = $cm->id;

  print_string( 'bim_configuration_screen', 'bim', $a );

  print_heading( get_string( 'bim_configuration_details', 'bim' ), 'left', 2);

  $details = new stdClass;
  $details->class = 'generaltable';
  $details->align = array( 'center', 'left' );
  $details->valign = array( 'top', 'top' );
  $details->size =  array( '20%', '80%' );
  $details->width = "80%";

  if ( $bim->register_feed == 0 )
  {
    print_string( 'bim_configuration_no_register', 'bim' );
  }
  if ( $bim->mirror_feed == 0 )
  {
    print_string( 'bim_configuration_no_mirror', 'bim' );
  }

  $details->head = array( get_string('bim_configuration_settings','bim' ),
                          get_string('bim_configuration_values', 'bim' ));

  $details->data = array();
  $details->data[] = array( get_string('bim_configuration_name','bim'),
                format_string( $bim->name ) );

  // simple array to get yes/no
  $yes = array( 0 => "<strong>No</strong>", 1 => "Yes" );
  $details->data[] = array( get_string('bim_configuration_registration','bim'),
                          $yes[$bim->register_feed] );
  $details->data[] = array( get_string('bim_configuration_mirror','bim'),
                          $yes[$bim->mirror_feed] );
  $details->data[] = array( get_string('bim_configuration_grade','bim'),
                          $yes[$bim->grade_feed] );

  $details->data[] = array( get_string('bim_configuration_intro','bim'),
                format_text( $bim->intro ) );
  print_table( $details );

  // display general advice on steps
  print_heading( get_string('bim_configuration_steps_heading', 'bim' ),
                   'left', 2 );
  echo '<a name="steps"></a>';
  print_string( 'bim_configuration_steps_description', 'bim' );

}


?>

