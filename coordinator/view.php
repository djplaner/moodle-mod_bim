<?php  // $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/bim/lib/locallib.php');
require_once($CFG->dirroot.'/mod/bim/coordinator/manage_questions.php');
require_once($CFG->dirroot.'/mod/bim/coordinator/manage_marking.php');
require_once($CFG->dirroot.'/mod/bim/coordinator/allocate_markers.php');
require_once($CFG->dirroot.'/mod/bim/coordinator/find_student.php');

/*************************************
 * show_coordinator( $bim, $userid, $cm )
 * - show coordinator interface
 */

function show_coordinator( $bim, $userid, $cm, $course ) {
    global $OUTPUT;
//echo $OUTPUT->heading( "Hello there coordinator" );
    // optional params required to chose screen/tab
    $tab = optional_param('tab', "config", PARAM_ALPHA);
    $screen = optional_param('screen', "", PARAM_ALPHA);

    // Some kludges here, if $screen is set to ShowPostDetails
    // ShowDetails and AllocatePosts then tab is ShowCoordDetails
    if ( $screen == "ShowPostDetails" || $screen == "AllocatePosts" ||
         $screen == "ShowDetails" || $screen == "MarkPost" ||
         $screen == "changeBlogRegistration" || $screen == "generateOpml" ) {
        $tab = "details";
    }

    // only generate header of web page for normal HTML pages
    if ( $screen != "generateOpml" ) {
        bim_print_header( $cm, $bim, $course, $screen );

        // ** eventually will need to pass screen
        bim_build_coordinator_tabs( $cm, $tab );
    }
  
    if ( $tab == "config" ) {
        bim_configuration_screen( $bim, $cm );
    } else if ( $tab == "markers" ) {
        bim_allocate_markers( $bim, $cm, $userid );
    } else if ( $tab == "questions" ) {
        bim_manage_questions( $bim, $cm );
    } else if ( $tab == "manage" ) {
        $op = optional_param('op', NULL, PARAM_ALPHA);
        if ( $op == "" )
            bim_manage_marking( $bim, $userid, $cm );
        else if ( $op == "release" )
            bim_manage_release( $bim, $userid, $cm );
        else if ( $op == "view" )
            bim_manage_view( $bim, $userid, $cm );
    } else if ( $tab == "find" ) {
        bim_find_student( $bim, $cm );
    } else if ( $tab == "details" ) {
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
        } else if ( $screen == "generateOpml" ) {
            bim_generate_opml( $bim, $cm, $userid );
        }
    }
    if ( $screen != "generateOpml" ) {
        echo $OUTPUT->footer();
    } 
}

/*******
 * bim_configuration_screen( $bim, $cm )
 * - display the HTML for the coordinators configuration screen
 */

function bim_configuration_screen( $bim, $cm )
{
    global $CFG, $OUTPUT;

    $a = new StdClass();
    $a->wwwroot = $CFG->wwwroot;
    $a->cmid = $cm->id;

    print_string( 'bim_configuration_screen', 'bim', $a );

    echo $OUTPUT->heading( get_string( 'bim_configuration_details', 'bim' ), 2);

    // Construct a table containing a summary of the current configuration elements
    // of the BIM activity
    $table = new html_table;

//  $details = new stdClass;
    $table->class = 'generaltable';
    $table->align = array( 'left', 'left' );
    $table->valign = array( 'top', 'top' );
    $table->size =  array( '20%', '80%' );
    $table->width = "80%";

    if ( $bim->register_feed == 0 ) {
        print_string( 'bim_configuration_no_register', 'bim' );
    }
    if ( $bim->mirror_feed == 0 ) {
        print_string( 'bim_configuration_no_mirror', 'bim' );
    }

    $table->head = array( get_string('bim_configuration_settings','bim' ),
                          get_string('bim_configuration_values', 'bim' ));

    $table->data = array();

    // name of the activity
    $title_cell = $OUTPUT->help_icon( 'config_bim_name', 'bim' ) . '&nbsp;' .
                      get_string( 'config_bim_name', 'bim' ) ;

    $table->data[] = array( $title_cell, $bim->name );

    // Can students register their feed?
    $yes = array( 0 => "<strong>No</strong>", 1 => "Yes" );
    $title_cell = $OUTPUT->help_icon( 'config_student_reg', 'bim' ) . '&nbsp;' .
                  get_string( 'config_student_reg', 'bim' );
    $table->data[] = array( $title_cell, $yes[$bim->register_feed] );

    // Are posts being mirrored
    $title_cell = $OUTPUT->help_icon( 'config_mirror', 'bim' ) . '&nbsp;' .
                           get_string( 'config_mirror', 'bim' );
    $table->data[] = array( $title_cell, $yes[$bim->register_feed] );

    // Gradebook integration?
    $title_cell = $OUTPUT->help_icon( 'config_grade', 'bim' ) . '&nbsp;' .
                           get_string( 'config_grade', 'bim' );
    if ( $bim->grade == 0 ) {
        $table->data[] = array($title_cell, get_string('config_no_grade', 'bim'));
    } else if ( $bim->grade > 0 ) {
        $table->data[] = array($title_cell, 
                                 get_string('config_grade_max','bim',$bim->grade));
    }
 
    // the introduction/about information for the activity
    $title_cell = $OUTPUT->help_icon('config_about', 'bim') . '&nbsp;' .
                           get_string( 'config_about', 'bim' );
    $table->data[] = array( $title_cell, $bim->intro );

    echo html_writer::table( $table );

    // display general advice on steps
    echo $OUTPUT->heading( get_string('bim_configuration_steps_heading', 'bim' ), 2 );
    echo '<a name="steps"></a>';
    print_string( 'bim_configuration_steps_description', 'bim' );

}


?>
