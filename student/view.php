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
 * @package mod_bim
 * @copyright 2010 onwards David Jones {@link http://davidtjones.wordpress.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * bim/student/view.php
 * - provide the main functions for processing/generating
 *   pages for users with mod/bim:student capability
 *
 * show_student( $bim, $userid, $cm, $course )
 */

require_once($CFG->dirroot.'/mod/bim/student/register_form.php');

/*
 * show_student( $bim, $user, $cm )
 * - Show student interface
 */

function show_student( $bim, $userid, $cm, $course) {
    global $OUTPUT;
    $bimid = $bim->id;

    bim_print_header( $cm, $bim, $course, "student");
    // if there isn't a feed registered, show the register form
    if ( ! bim_feed_exists( $bimid, $userid ) ) {
        // need to check for passing in of parameters
        show_register_feed( $bim, $userid, $cm );
    } else {
        $screen = optional_param( 'screen', '', PARAM_ALPHA );
        if ( $screen == "showQuestions" ) {
            bim_show_questions( $cm, $bim );
        } else {
            $event = \mod_bim\event\details_viewed::create(array(
                'context' => context_module::instance($cm->id),
                'objectid' => $cm->id
            ));
            $event->trigger();

            show_student_details( $bim, $userid, $cm );
        }
    }

    echo $OUTPUT->footer();
}

/*
 * display the student's blog details, including marking
 */

function show_student_details( $bim, $userid, $cm ) {
    global $CFG, $DB, $OUTPUT;

    $base_url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&screen=showQuestions";

    $bimid = $bim->id;

    // * get user details??
    if ( ! $user = $DB->get_record( "user", array("id"=>$userid)) ) {
        print_string( 'student_details_user_error', 'bim', $userid );
        return;
    }


    // Get the question hash
    $question_hash = bim_get_question_hash( $bimid );
    $total_questions = count($question_hash);


    // get the feed details
    $student_ids = array( $userid );
    $feed_details = bim_get_feed_details( $bim->id, $student_ids );

    // report error if no feed found
    if ( empty( $feed_details )) {
        $a = $user->firstname . ' ' . $user->lastname;
        echo $OUTPUT->heading(
                get_string( 'student_details_nofeed_heading', 'bim' ), 1, 'left' );
        print_string( 'student_details_nofeed_description', 'bim', $a );
        return;
    }

    // If questions, then process the feed
    if ( ! empty( $question_hash ) ) {
        bim_process_feed( $bim, $feed_details[$userid], $question_hash );
        bim_process_unallocated( $bim, $feed_details[$userid], $question_hash );
    }
   // Get data from bim_marking
    $mark_select = "( bim= " . $bimid . " AND userid=" . $userid . ")";
    $mark_details = $DB->get_records_select( "bim_marking", $mark_select );

    // Start calculating some data

    // Total number of questions for bim
    // Number allocated - total # from bim_marking
    // Number marked - those in marked status from bim_marking
    $post_stats = bim_generate_marking_stats( $mark_details );
    $total_posts = count( $mark_details );
    if ( empty($mark_details)) {
        $total_posts = 0;
    }

    $num_answered = $post_stats["Released"]+$post_stats["Marked"]+
        $post_stats["Submitted"];
    $num_marked = $post_stats["Released"]+$post_stats["Marked"];

    // Start display

    // Details
    echo $OUTPUT->heading( get_string('student_details_header', 'bim'), 1, 'left' );
    if ( ! empty( $question_hash )) {
        print_string( 'student_details_questions_description', 'bim',
                $base_url);
    }
    print_string( 'student_details_description', 'bim' );

    bim_show_student_details( $userid, $mark_details,
            $question_hash, $feed_details, $cm );

   // show extra message if no posts recorded
    if ( $total_posts == 0 ) {
        echo $OUTPUT->heading( get_string('student_details_noposts_heading', 'bim'), 2, 'left');
        print_string('student_details_noposts_description', 'bim' );
        if ( $bim->mirror_feed == 0 ) {
            print_string('student_details_not_mirrored', 'bim' );
        } else {
            print_string('student_details_reasons', 'bim' );
        }
        return;
    }

    // Show information about any Marked posts
    if ( $num_marked > 0 ) {
        //    print_string('student_details_num_marked','bim',$num_marked);
        if ( $post_stats["Released"] != 0 ) {
            echo $OUTPUT->heading( get_string('student_details_released_heading', 'bim'), 2, "left" );
            //        print_string( 'student_details_released_description','bim',
            //                         $post_stats["Released"] );

            $answers = new html_table;
            $answers->head = array(
                    get_string('student_details_question_heading', 'bim' ),
                    get_string('student_details_mark_heading', 'bim' ),
                    get_string('student_details_markers_comment_heading', 'bim'));
            $answers->tablealign = "center";
            $answers->size = array( "30%", "20%", "50%" );
            $answers->width="90%";

            foreach ($mark_details as $post) {
                if ( $post->status == "Released" ) {
                    $question_id = $post->question;
                    $answers->data[] = array(
                            $question_hash["$question_id"]->title  .'<br />' .
                            '<small>(<a href="' . $post->link . '">'.
                                get_string('student_details_your_answer', 'bim') .
                                '</a>)</small>',
                            sprintf("%3.2f out of %3.2f", $post->mark, 
                                     $question_hash[$question_id]->max_mark),
                            $post->comments );
                }
            }
            echo html_writer::table( $answers );
        }
    } // $num_marked - show marked answers

    // show links to all of the posts

    bim_show_student_posts( $mark_details, $question_hash );

    // show information about the activity
    echo '<a name="about"></a>';
    echo $OUTPUT->heading( get_string( 'student_details_about_heading', 'bim'), 2, 'left');
    print_string( 'student_details_about_description', 'bim', $bim->intro );
}

/*
 * Show and process the student register blog form
 */

function show_register_feed( $bim, $userid, $cm) {
    global $CFG, $DB, $OUTPUT;
    $base_url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id";

    // don't let registration proceed if register_feed off
    if ( $bim->register_feed == 0 ) {
        echo $OUTPUT->heading( get_string('register_cannot_heading', 'bim' ), 1 );
        print_string( 'register_cannot_description', 'bim' );
        return;
    }
    $mform = new mod_bim_register_form( "view.php?id=$cm->id" );

    if ( $mform->is_cancelled() ) {
        print( "cancelled" );
    } else if ( $fromform = $mform->get_data() ) {
        $response = new stdClass();
        $response->blogurl = $fromform->blogurl;

        $response->bim = $bim->id;
        $response->userid = $userid;
        $response->numentries = 0;
        $response->lastpost = "NULL";

        // Do some checks on the feed url and try to retrieve it
        // Will set some errors if found, to be handled further below
        $fromform = bim_get_feed_url( $fromform, $cm, $bim );

        // if no errors process it
        if ( ! isset( $fromform->error ) ) {
            $response->feedurl = $fromform->feedurl;
            $response->blogurl = $fromform->blogurl;
            $response->lastpost = $fromform->lastpost;

            $feed_id = 0;
            if ( ! $feed_id = $DB->insert_record( 'bim_student_feeds', $response ) ) {
                print_string('bim_error_updating', 'bim');
            } else {
                $event = \mod_bim\event\registration_created::create(array(
                     'context' => context_module::instance($cm->id),
                     'objectid' => $cm->id,
                     'other' => array(
                            'blogurl' => $fromform->blogurl
                     )
                ));
                $event->trigger();

                echo $OUTPUT->heading( get_string('register_success_heading', 'bim'), 2, "left" );
                print_string( 'register_success_description', 'bim' );

                // get the questions for this bim
                $questions = bim_get_question_hash( $bim->id );
                // always want to process feed so that items are put into
                // database
                // make sure we have the id for the new feed passed in
                $response->id = $feed_id;
                bim_process_feed( $bim, $response, $questions );

                show_student_details( $bim, $userid, $cm);
                return 1;
            }
        } else {
            // else got error from bim_get_feed_url
            // logging is done in bim_display_error
            bim_display_error( $fromform->feedurl, $fromform, $cm );
            print_string( 'register_again', 'bim', $base_url );
            return 0;
        }
    } else {
        $event = \mod_bim\event\registration_started::create(array(
                     'context' => context_module::instance($cm->id),
                     'objectid' => $cm->id
                ));
        $event->trigger();
        print format_text( $bim->intro );
        echo "<p><br /></p>";
        $mform->display();
    }
}

