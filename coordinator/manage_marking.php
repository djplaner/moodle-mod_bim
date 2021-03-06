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
 * Allow the coordinator to get an overview of how the marking for all students is going
 *
 * @package mod_bim
 * @copyright 2010 onwards David Jones {@link http://davidtjones.wordpress.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/mod/bim/lib.php');
require_once($CFG->dirroot.'/mod/bim/lib/groups.php');

/*
 * bim_manage_marking( $bim, $userid, $cm )
 * - generate and process the form for the coordinators
 *   Manage Marking tab
 * - Give an overview of the marking progress by marker
 *   and provide a number of related services
 * - Also show a list of the students who haven't registered their blos
 */

function bim_manage_marking( $bim, $userid, $cm ) {
    global $CFG, $OUTPUT;
    $base_url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&tab=manage";

    $event = \mod_bim\event\marking_viewed::create(array(
                     'context' => context_module::instance($cm->id),
                     'objectid' => $cm->id,
                     'other' => array(
                            'viewed' => 'show overall status'
                     )
     ));
     $event->trigger();

    // Calculations to find out how many unregistered students there are
    $all_students = bim_get_all_students( $cm );
    $student_ids = array_keys( $all_students );
    $feed_details = bim_get_feed_details( $bim->id, $student_ids );
    $unregistered = array_diff_key( $all_students, $feed_details);
    $count_unreg = count( $unregistered);
    // Get the question data required
    // all questions for this bim
    $questions = bim_get_question_hash( $bim->id );

    // add  the stats for the questions
    $questions = bim_get_question_response_stats( $questions);

    $title_help = $OUTPUT->help_icon( 'manageMarking', 'bim' );
    echo $OUTPUT->heading( get_string('bim_marking_heading', 'bim' ).'&nbsp;'.$title_help, 1 );

    if ( empty( $questions ) ) {
        print_string( 'bim_marking_no_questions', 'bim' );
    }

    if ( $count_unreg > 0 ) {
        print_string( 'bim_marking_unregistered', 'bim', $count_unreg );
    }

    // Get question titles
    $question_titles = array();
    foreach ((array)$questions as $question) {
        $question_titles[$question->id] = $question->title;
    }
    // markers details and the makers student information
    // - Get all the students so we can add the stats
    $markers_students = bim_get_all_markers_students( $bim );
    if ( empty( $markers_students ) ) {
        echo $OUTPUT->heading( get_string( 'bim_marking_no_markers_heading', 'bim' ), 2, 'left' );
        print_string( 'bim_marking_no_markers_description', 'bim' );
    } else {
        $markers_students = bim_get_all_marker_stats( $markers_students, $questions, $bim );
        // get the ids of all ther markers
        $markers = array_keys( $markers_students );

        // Start setting up the table

        $table = new html_table();

        //    $table->set_attribute('cellpadding','5');
        // $table->set_attribute('class', 'generaltable generalbox reporttable');
        //   $table->set_attribute('class', 'generalbox reporttable');

        /*    $columns = array( 'marker', 'studs' );
              $columns = array_merge( $columns, array_keys( $question_titles ) );
              $table->define_columns( $columns );
              $headers = array( 'Marker', 'Studs' ); */

        // **** TODO CHANGING OVER TO HTML_TABLE
        // *** add in change to attributes, padding etc?
        // *** replace this with internationalisation

        // set the column titles for the questions, including link to
        // release posts if there are any Marked posts for the question
        $headers = array( 'Marker', 'Studs' );
        foreach ($question_titles as $qid => $title) {
            $new_title = $title;
            if ( $questions[$qid]->status["Marked"] != 0 ) {
                $new_title .= '<br /><small><a href="'.$base_url.
                    '&op=release&question='.$qid.'">release</a></small>';
            }
            $headers[] = $new_title;
        }
        $table->head = $headers;

        // Start creating the data for the table, each row matches a marker
        $table_data = array();

        foreach ($markers_students as $marker) {
            // data
            // - students - is name, mailto, username, blog of student
            // - stats - string summary of posts in bim_marking
            // - one column per question to give overview of what's going on

            $entry["marker"] = '<a href="mailto:'.$marker->details->email.'">'.
                $marker->details->firstname.' '.$marker->details->lastname.'</a>';
            // if the marker has some 'Marked' osts add a release option
            if ( isset( $marker->statistics["Total"]->Marked ) ) {
                $entry["marker"] .=
                    '<br /><small><a href="'.$base_url.'&op=release&marker='.
                    $marker->marker.'">release</a>';
            }

            $num_students = count( $marker->students );
            $entry["studs"] = $num_students;

            foreach ($question_titles as $qid => $title) {
                $question_stats = bim_get_marker_question_stats( $marker,
                        $qid, $questions );

                $mark = "Marked:";
                if ( $question_stats["Marked"] != 0 ) {
                    $mark = '<a href="'.$base_url.'&op=release&marker='.$marker->marker.
                        '&question='.$qid.'">Marked:</a>';
                }

                $status_table = new html_table;
                $status_table->attributes['class'] = 'status_stats';

                $status_data = array();

                foreach (array( "Submitted", "Marked", "Suspended", "Released", "Missing" ) as $status) {
                    $label = "$status:";

                    if ( $question_stats[$status] > 0 ) {
                        $label = '<a href="'.$base_url.'&op=view&marker='.$marker->marker.
                            '&question='.$qid.'&status='.$status.'">'.$label.'</a>';
                    }

                    // ** TODO internationalisation ???
                    $status_data[] = array( "<small>".$label."</small>",
                            "<small>".$question_stats[$status]."</small>" );
                }

                // add the release for this question/marker if any in marked state
                if ( $question_stats["Marked"] != 0 ) {
                    // *** TODO need to make this a COLSPAN=2
                    $status_data[] = array( '<small><a href="' .
                            $base_url.'&op=release&marker='.$marker->marker.
                            '&question='.$qid.'">release</a></small>', '' );
                }
                $status_table->data = $status_data;
                // **** TODO need to remove the border from this table
                $entry[$qid] = html_writer::table( $status_table );
            }
            $table_data[] = $entry;
        }

        $num_marked = bim_get_marked( $bim );
        if ( $num_marked > 0 ) {
            echo '<p>[<small><a href="'.$base_url.'&op=release">';
            print_string( 'bim_marking_release', 'bim', $num_marked );
            echo '</a></small>]</p>';
        }
        $table->data = $table_data;
        echo html_writer::table( $table );
    }

    // Show unregstered students
    $unreg_data = bim_create_details_display( $unregistered, null, $cm );
    // need to remove id field in each entry in the array
    $unreg_data_purge = array();
    foreach ($unreg_data as $unreg) {
        unset( $unreg['id'] );
        $unreg_data_purge[] = $unreg;
    }

    $table = bim_setup_details_table( $cm, $bim->id, $userid, 'unregistered' );
    $table->data = $unreg_data_purge;

    echo '<a name="unreg"></a>';
    echo $OUTPUT->heading( "Unregistered students", 2, "left" );
    echo $OUTPUT->container( "<p>The following " . count($unregistered) .
            " student(s) have not yet registered their feeds</p>" );
    // show the email textbox
    // bim_show_unregistered_students_email( $unregistered );
    $userids = array_keys( $unregistered );
    bim_email_merge( $userids, $cm->course, $base_url,
            "Email unregistered students" );
    echo '<br />';
    // $table->print_html();
    echo html_writer::table( $table );
}

/******************
 * bim_manage_release( $bim, $userid, $cm )
 * - Manage the release of posts i.e. change status in bim_marking to Released
 * - which ones to release are based on optional parameters
 *      question and marker
 * - question/marker both empty - release all
 * - question set - release all for that question
 * - marker set - release all for that marker
 * - marker/question set - release all questions for that marker
 */

function bim_manage_release( $bim, $userid, $cm ) {
    global $CFG, $DB, $OUTPUT;

    $base_url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&tab=manage";

    $event = \mod_bim\event\marking_updated::create(array(
                     'context' => context_module::instance($cm->id),
                     'objectid' => $cm->id,
         ));
    $event->trigger();

    // Check parameters
    $marker = optional_param( "marker", null, PARAM_INT);
    $question = optional_param( "question", null, PARAM_INT);

    $sql = "bim=$bim->id and status='Marked' ";

    if ( $marker != 0 ) {
        $marker_students = bim_get_markers_students( $bim, $marker );
        $marker_ids = implode( ",", array_keys( $marker_students ) );
        $sql .= " and userid in ( $marker_ids ) ";
    }
    if ( $question != 0 ) {
        $sql .= " and question=$question ";
    }

    // get the id,userid,mark fields from the rows that will be changed
    // Has to be done here before thte values are changed below
    $students_changing = $DB->get_fieldset_select( "bim_marking", "userid", $sql );

    // set status=Released
    $released = $DB->set_field_select( "bim_marking", "status", "Released", $sql );
    // set timereleased=now
    $time = time();
    $time_released = $DB->set_field_select( "bim_marking", "timereleased", $time, $sql );

    if ( ! $time_released ) {
        print "ERROR with time relase<br />";
    }
    if ( ! $released ) {
        print "ERROR with relased<br />";
    }

    bim_update_gradebook( $bim );

    echo $OUTPUT->heading( get_string( 'bim_release_heading', 'bim' ), 2, 'left');

    if ( $released && $time_released  ) {
        print_string( 'bim_release_success', 'bim' );
    } else {
        print_string( 'bim_release_errors', 'bim' );
        print '<p>Errors encountered while releasing results.</p>';
    }
    print_string( 'bim_release_return', 'bim', $base_url );
}

/******************
 * bim_update_gradebook( $bim )
 * - update the gradebook entries (if appropriate) for all students
 * - Currently implements a single, simple gradebook calculation
 *   - sum the marks on all student released posts 
 *   - calculate what percentage that is of the total possible marks
 *   - calculate the equivalent percentage of the maximum mark to go in gradebook
 * - e.g.
 *   - the maximum mark for the gradebook is 10 
 *   - there are 5 posts in the activity, each worth a maximum of 10 marks
 *   - that's a total possible mark of 50 marks
 *   - a student has only one post released, that post was good, worth 10 marks
 *   - So the student's total so far, is 10 marks. 
 *   - 10 marks is 20% of 50 marks (the total possible mark)
 *   - 20% of 10 (maximum gradebook mark) is 2
 *   - So the student should get 2 marks in their gradebook entry
 */

function bim_update_gradebook( $bim ) {
    global $DB;

    // need to make sure we have cmidnumber set
    if ( ! isset( $bim->cmidnumber ) ) {
        if ( ! $cm = get_coursemodule_from_instance( 'bim', $bim->id )) {
            print_error( 'bim_cmiderror', 'bim'  );
        } else {
            $bim->cmidnumber = $cm->id;
        }
    }
    // update the gradebook entry if configured to
    if ( $bim->grade > 0 ) {
        // get sum max marks for all this bim's questions
        $max_sql = "SELECT bim,sum(max_mark) as max from {bim_questions} where bim= ? group by bim";
        $max_results = $DB->get_records_sql($max_sql, array($bim->id));
        $max_total = $max_results[$bim->id]->max;

        // only do the update if max_total is greater than 0
        if ( $max_total > 0 ) {
            // get the list of students with released results and the sum of 
            // their marks for released posts
            $raw_sql = "SELECT userid,sum(mark) as mark from {bim_marking} where bim= ? and status='Released' group by userid";
            $grades = $DB->get_records_sql( $raw_sql, array( $bim->id ) );

            // calculate the grade/mark to stick in the gradebook
            foreach ( $grades as $userid => $grade ) {
                $grade->posts_percentage =  $grade->mark * (100/$max_total);

                $grade->rawgrade = ($grade->posts_percentage/100) * $bim->grade;
            } 
            // update the gradebook for all the students
            bim_grade_item_update( $bim, $grades );
        } // max_total > 0 
    } // bim->grade > 0
}

/******************
 * bim_manage_view( $bim, $userid, $cm )
 * - show details of students in a given state
 * - which ones to release are based on optional parameters
 *      question marker status
 * - question/marker both empty - view all
 * - question set - view all for that question
 * - marker set - view all for that marker
 * - marker/question set - view all questions for that marker
 * - .. add status into that mix
 */

function bim_manage_view( $bim, $userid, $cm ) {
    global $CFG, $DB, $OUTPUT;

    $base_url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&tab=manage";

    $event = \mod_bim\event\marking_viewed::create(array(
                     'context' => context_module::instance($cm->id),
                     'objectid' => $cm->id,
                     'other' => array(
                            'viewed' => 'students in state'
                     )
         ));
    $event->trigger();
    // Check parameters
    $status = optional_param( "status", null, PARAM_ALPHA);
    $marker = optional_param( "marker", null, PARAM_INT);
    $question = optional_param( "question", null, PARAM_INT);

    // get list of all student ids that have posts matching
    // details from parameters i.e.
    //    select distinct userid from bim_marking where
    //        status='' and marker='' and question=''
    $sql = "";
    $students = array();

    // get the students that match the critiera
    $sql = "select distinct userid as userid from {bim_marking} " .
        "where bim=$bim->id";

    if ( $marker != "" ) {
        // the marker field in bim_marking can be unreliable.
        // convert marker into an id of their students
        $marker_students = bim_get_markers_students( $bim, $marker );
        $marker_ids = implode( ",", array_keys( $marker_students ) );
        $sql .= " and userid in ( $marker_ids ) ";
    }
    if ( $question != "" ) {
        $sql .=" and question=$question";
    }
    if ( $status != "" && $status != "Missing" ) {
        $sql .=" and status='".$status."'";
    }
    $matching_students = Array();
    if ( $status == "Missing" ) {
        // if there's a marker specified , just get all markers students
        if ( $marker == "" ) {
            $all_students = bim_get_all_students( $cm );
        } else {
            $all_students = $marker_students;
        }
        // remove from this list the students that match the criteria
        $all = array_keys( $all_students );
        $matching = array_keys( $matching_students );
        $ids = array_diff( $all, $matching );
    } else {
        $matching_students = $DB->get_records_sql( $sql );
        $ids = array_keys( $matching_students );
    }

    // give the list of students matching the critera
    // get their details, marking and feed details
    // for all students in STATUS MARKER and QUESTION
    $feed_details = bim_get_feed_details( $bim->id, $ids );
    $marking_details = bim_get_marking_details( $bim->id, $ids );
    $questions = bim_get_question_hash( $bim->id );
    $student_details = bim_get_student_details( $ids );

    $unregistered = array_diff_key( $student_details, $feed_details);
    $registered = array_diff_key( $student_details, $unregistered );

    // Show the what we found
    echo $OUTPUT->heading( get_string( 'bim_release_manage_header', 'bim' ), 2, "left" );
    $a = new StdClass;
    $a->match = count( $student_details );
    $a->registered = count( $registered );
    $a->unregistered = count($unregistered);
    // how many students matched
    print_string( 'bim_release_manage_view', 'bim', $a );

    if ( $marker == 0 && $status == "" && $question == 0  ) {
        print_string( 'bim_release_manage_any', 'bim' );
    } else {
        print_string( 'bim_release_manage_criteria', 'bim' );
        if ( $status != "" ) {
            print_string( 'bim_release_manage_status', 'bim', $status );
        }
        if ( $marker != "" ) {
            // get marker user details
            $marker_details = $DB->get_records_select( "user", "id=$marker" );
            $a = $marker_details[$marker]->firstname . ' ' .
                $marker_details[$marker]->lastname;
            print_string( 'bim_release_manage_marker', 'bim', $a );
        }
        if ( $question != "" ) {
            print_string( 'bim_release_manage_response', 'bim',
                    $questions[$question]->title );
        }
        echo '</ul>';
    }
    // show email merge
    bim_email_merge( array_keys( $student_details), $cm->course, $base_url,
            "Email all matching students" );
    print_string( 'bim_release_return', 'bim', $base_url );
    if ( $registered ) {
        echo '<a name="registered"></a>';
        echo $OUTPUT->heading( get_string( 'bim_release_manage_registered_heading', 'bim' ), 2, "left" );
        $a = count($registered);
        print_string( 'bim_release_manage_registered_description', 'bim', $a );
        bim_email_merge( array_keys( $registered), $cm->course, $base_url,
                "Email registered students" );
        echo '<br />';
        $table = bim_setup_posts_table( $cm, $bim->id, $userid, $questions  );

        $reg_data = bim_create_posts_display( $cm, $registered, $feed_details,
                $marking_details, $questions );
        foreach ($reg_data as $row) {
            $table->add_data_keyed( $row );
        }
        $table->print_html();
    }

    if ( $unregistered ) {
        echo '<a name="unregistered"></a>';
        echo $OUTPUT->heading(get_string('bim_release_manage_unregistered_heading', 'bim' ), 2, "left" );
        $a = count($unregistered);
        print_string( 'bim_release_manage_unregistered_description', 'bim', $a );
        bim_email_merge( array_keys( $unregistered), $cm->course, $base_url,
                "Email unregistered students" );
        echo '<br />';
        $unreg_data = bim_create_details_display( $unregistered, $feed_details, $cm );
        $table = bim_setup_details_table( $cm, $bim->id, $userid, 'unregistered' );
        foreach ($unreg_data as $row) {
            $table->data[] = array( $row['username'], $row['name'], $row['email'],
                    $row['register'] );
        }
        echo html_writer::table( $table );
        //    $table->print_html();
    }
}

