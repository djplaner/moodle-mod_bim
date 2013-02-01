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

require_once($CFG->dirroot.'/mod/bim/lib/bim_rss.php');
/**
 * Library of local functions for BIM
 *
 * bim_feed_exists
 * bim_get_mirrored
 * bim_get_student_feeds
 * bim_get_feed_details
 * bim_generate_marking_stats
 * bim_generate_student_results
 */

// SQL functions
// - misc tests/inserts of databsae

/**
 * Returns TRUE iff there already exists a registered feed
 * for $userid in the bim activity $bim
 */

function bim_feed_exists( $bim, $userid ) {
    global $DB;
    return $DB->record_exists( 'bim_student_feeds', Array( 'bim' =>$bim,
                'userid' => $userid) );
}

/**
 * bim_get_mirrored()
 * Return an array of bim ids for all those that are currently
 * being mirrored
 */

function bim_get_mirrored() {
    global $DB;
    $mirrored = $DB->get_records_select( "bim", "mirror_feed = ?", array( 1 ) );

    $bims = array();

    if ( empty( $mirrored )) {
        return $bims;
    }

    foreach ($mirrored as $row) {
        $bims[] = $row;
    }
    return $bims;
}

/******
 * $students = bim_get_student_feeds( $bim )
 * - return array of entries from bim_student_feeds which
 *   match the bim activity id passed in
 */

function bim_get_student_feeds( $bim ) {
    global $DB;
    $students = $DB->get_records_select( "bim_student_feeds",
            "bim = ?", array( $bim ) );
    return $students;
}

/******
 * $questionHash = bim_get_question_hash( $bim )
 * - given a bim id, return a hash (keyed on question id)
 *   with information about questions associated with
 *   the bim activity
 **/

function bim_get_question_hash( $bim ) {
    global $DB;
    $questions = $DB->get_records_select( "bim_questions", "bim=?", array($bim) );

    return $questions;
}

/*****
 * $array = bim_get_feed_details( $bim, $user_ids)
 * - return an array of data from bim_student_feeds
 *   based on the list of $user_ids and $bim
 * - array is keyed on user id
 */

function bim_get_feed_details( $bim, $user_ids ) {
    global $DB;
    // create the array where key is userid, not feed id
    $student_feeds = array();

    if ( empty( $user_ids )) {
        return $student_feeds;
    }

    list( $usql, $params ) = $DB->get_in_or_equal( $user_ids );
    array_unshift( $params, $bim );
    $usql = "bim=? and userid $usql";

    $feed_details = $DB->get_records_select( "bim_student_feeds",
            $usql, $params );

    if ( $feed_details ) {
        foreach ($feed_details as $row) {
            $student_feeds[$row->userid] = $row;
        }
    }
    return $student_feeds;
}

/******
 * $array = bim_get_marking_details( $bim, $user_ids )
 * - Return array of data from bim_marking for the students
 *   specified in the list of $user_ids and for $bim
 */

function bim_get_marking_details( $bim, $user_ids ) {
    global $DB;
    // make sure it was valid
    if ( empty( $user_ids )) {
        return Array();
    }

    list( $usql, $params ) = $DB->get_in_or_equal( $user_ids );
    array_unshift( $params, $bim );
    $usql = "bim=? and userid $usql";
    $marking_details = $DB->get_records_select( "bim_marking",
            $usql, $params);

    return $marking_details;
}

/******
 * $stats = bim_generate_marking_stats( $marking_details )
 * - given an array of marking details return an array of
 *   integers which represent the number of posts in various
 *   states
 */

function bim_generate_marking_stats( $marking_details ) {
    $post_stats = array( "Released" => 0, "Marked" => 0, "Submitted" => 0,
            "Unallocated" => 0, "Suspended" => 0 );

    if ( empty( $marking_details ) ) {
        return $post_stats;
    }

    foreach ($marking_details as $post) {
        $post_stats[$post->status]++;
    }

    return $post_stats;
}

/*****
 * $string = bim_generate_student_results( $marking_details, $questions )
 * - given array of marking_details - bim_marking content for a student
 *   and $questions - array of bim_questions content for a bim
 * - calculate a string based student result e.g. 5 of 10
 */

function bim_generate_student_results( $marking_details, $questions, $cm ) {
    $total = 0;
    $score = 0;

    if ( ! empty( $questions )) {
        foreach ($questions as $question) {
            $total += $question->max_mark;
        }
    } else {
        return "No questions";
    }

    $context = get_context_instance( CONTEXT_MODULE, $cm->id );

    foreach ($marking_details as $details) {
        // if user is a student, then only include released posts

        if ( has_capability('mod/bim:student', $context) ) {
            if ( $details->status == "Released" ) {
                $score += $details->mark;
            }
        } else if ( has_capability('mod/bim:coordinator', $context) ||
                has_capability('mod/bim:marker', $context ) ) {
            $score += $details->mark;
        }
    }
    return "$score of $total";
}

/******
 * bim_print_header( $cm, $bim, $course, $screen )
 * - print the header for the bim view.php
 * - $screen specifies which page/screen is to be displayed
 *   which is the major way in which breadcrumbs will change
 * - Values for $screen include
 *   coordinator
 *   - what the coordinator/chief academic sees
 *                 THIS MAY CHANGE AS IT IS IMPLEMENTED
 *   ShowDetails
 *   - initial details for staff - first thing they see
 *   ShowPostDetails
 *   - Other teaching staff,
 *   AllocatePosts
 *   - Get here from ShowPostDetails, so add breadcrumbs
 *     to get back to ShowPostDetails
 */

function bim_print_header($cm, $bim, $course, $screen) {
    global $CFG;
    global $PAGE, $OUTPUT;

    $context = get_context_instance( CONTEXT_MODULE, $cm->id );

    $PAGE->set_url( '/mod/bim/view.php', array( 'id'=> $cm->id ));
    $PAGE->set_title(format_string($bim->name));
    $PAGE->set_heading(format_string($course->fullname));
    $PAGE->set_context($context);
    //  $base_url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id";

    // $strbims = get_string('modulenameplural', 'bim');
    // $strbim  = get_string('modulename', 'bim');

    $navlinks = array();

    if ( $screen == "student" || $screen == "") {
        $navlinks[] = array('name' => $strbims,
                'link' => "index.php?id=$course->id",
                'type' => 'activity');
        $navlinks[] = array('name' => get_string( 'bim_header_details', 'bim' ) .
                format_string($bim->name),
                'link' => $base_url,
                'type' => 'activityinstance');
    } else {  // either a marker or coordinator

        // start with link to all bims
        $navlinks[] = array( 'name' => $strbims,
                'link' => "index.php?id=$course->id",
                'type' => 'activity');

        if ( $screen == "MarkPost" ) {
            // show post details - and link
            $navlinks[] = array( 'name' =>
                    get_string('bim_header_post_details', 'bim'),
                    'link' => $base_url."&screen=ShowPostDetails",
                    'type' => 'activityinstance');
            // mark post title
            $navlinks[] = array( 'name' =>
                    get_string('bim_header_mark', 'bim' ),
                    'link' => '',
                    'type' => 'title' );
        } else if ( $screen == "changeBlogRegistration" ) {
            $navlinks[] = array( 'name' =>
                    get_string( 'bim_header_student_details',
                        'bim' ),
                    'link' => $base_url."&screen=ShowDetails",
                    'type' => 'activityinstance');
            // add change blog
            $navlinks[] = array('name' => get_string('bim_header_changeblog',
                        'bim' ),
                    'link' => $base_url."&screen=changeBlogDetails&".
                    "student=",
                    'type' => 'title' );
        } else if ( $screen == "ShowDetails" ) {
            $navlinks[] = array('name' => get_string( 'bim_header_student_details',
                        'bim' ),
                    'link' => $base_url."&screen=ShowDetails",
                    'type' => 'activityinstance');
        } else if ( $screen == "ShowPostDetails" ) {
            $navlinks[] = array('name' => get_string( 'bim_header_student_details',
                        'bim'),
                    'link' => $base_url."&screen=ShowDetails",
                    'type' => 'activityinstance');
            $navlinks[] = array('name' => get_string('bim_header_post_details', 'bim'),
                    'link' => '',
                    'type' => 'title');
        } else if ( $screen == "AllocatePosts" ) {
            $navlinks[] = array('name' => get_string('bim_header_student_details', 'bim'),
                    'link' => $base_url."&screen=ShowDetails",
                    'type' => 'activityinstance');
            $navlinks[] = array('name' => get_string('bim_header_post_details', 'bim'),
                    'link' => $base_url."&screen=ShowPostDetails",
                    'type' => 'activityinstance');
            $navlinks[] = array( 'name' => get_string('bim_header_allocate', 'bim' ),
                    'link' => '',
                    'type' => 'title' );
        }
    }

    // *** BRING IT BACK
    //  $navigation = build_navigation($navlinks);

    //  print_header_simple(format_string($bim->name), '', $navigation, '', '', true,
    //             update_module_button($cm->id, $course->id, $strbim),
    //            navmenu($course, $cm));

    echo $OUTPUT->header();
}

/**********
 * bim_build_coordinator_tabs( $cm, $tab )
 * - display the tabs for the coordinator screens for view.php
 * - $tab is id for tab currently being displayed
 */

function bim_build_coordinator_tabs( $cm, $tab ) {
    global $CFG;

    $tabs = array();
    $rows = array();
    $inactive = array();  $activated = array();

    $rows[] = new tabobject( 'config',
            "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&tab=config",
            get_string('bim_tabs_config', 'bim' ) );
    $rows[] = new tabobject( 'questions',
            "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&tab=questions",
            get_string('bim_tabs_questions', 'bim' ) );
    $rows[] = new tabobject( 'markers',
            "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&tab=markers",
            get_string('bim_tabs_markers', 'bim' ) );
    $rows[] = new tabobject( 'manage',
            "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&tab=manage",
            get_string('bim_tabs_manage', 'bim' ) );
    $rows[] = new tabobject( 'find',
            "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&tab=find",
            get_string('bim_tabs_find', 'bim' ) );

    $rows[] = new tabobject( 'details',
            "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&tab=details&screen=ShowDetails",
            get_string('bim_tabs_details', 'bim' ) );

    $tabs[] = $rows;
    print_tabs( $tabs, $tab );
}

/*****
 * $questions = bim_get_question_response_stats( $questions )
 * - given a array of records from bim_questions generate
 *   a new entry response_stats indicating the number of
 *   responses for each student in each status in bim_marking
 */

function bim_get_question_response_stats( $questions ) {
    global $DB;
    if ( empty($questions)) {
        return null;
    }

    foreach ($questions as $question) {
        // get count of student posts in each status for this question
        $sql = "select status,count(id) as x from {bim_marking} where " .
            "question=$question->id and status!='Unallocated' " .
            "group by status";
        $marking_details = $DB->get_records_sql( $sql );

        // get ready to update $question->status with information
        $question->status = array();
        $status = array( "Unallocated", "Submitted", "Released", "Marked",
                "Suspended" );

        foreach ($status as $field) {
            // if there was a post in that status, update $question->status
            if ( isset( $marking_details[$field] ) ) {
                $question->status[$field] = $marking_details[$field]->x;
            } else {
                $question->status[$field] = 0;
            }
        }
    }
    return $questions;
}

/*******
 * $markers_students_stats = bim_get_all_marker_stats( $markers_students,
 $questions, $bim )
 * - return an array - keyed on marker user id - containing value
 *   statistics, for each question, i.e. # posts in each status
 * - taka original markers_students, list of questions and bim id
 */

function bim_get_all_marker_stats( $markers_students, $questions, $bim ) {
    global $DB;

    // don't do anything if array is empty
    if ( empty( $questions ) ) {
        return $markers_students;
    }

    //  $question_ids = implode( ",", array_keys( $questions ));

    $num_questions = count( $questions );
    foreach ($markers_students as $marker) {
        // calculate the markers students (only want them)
        if ( ! empty( $marker->students )) {
            $num_students = count( $marker->students );

            // prepare list of student ids for SQL
            list( $ssql, $s_params ) = $DB->get_in_or_equal( array_keys($marker->students) );
            // get the list of ids for each question for SQL query
            list( $qsql, $q_params ) = $DB->get_in_or_equal( array_keys($questions) );
            $params = array_merge( $s_params, $q_params );
            $sql = "select question,status,count(id) as x from " .
                "{bim_marking} where " .
                "userid $ssql and " .
                "question $qsql and bim=$bim->id " .
                "group by question,status";

            // get the set of records matching the query
            $rs = $DB->get_recordset_sql( $sql, $params );

            // populate the data structure with the number of questions for the marker
            // in each status
            $stats = array();
            $total = new StdClass;
            foreach ($rs as $rec) {
                $status = $rec->status;
                $stats[$rec->question]->$status = $rec->x;
                $total->$status += $rec->x;
            }
            $rs->close( $rs );
            $stats["Total"] = $total;
            $marker->statistics = $stats;
            $marker->total = $total;
        }
    }
    return $markers_students;
}

/*******
 * $q_stats = bim_get_marker_question_stats( $marker, $qid, $questions )
 * - given a array of marker stats (how many posts for each question in
 *   each state), the title of a question and a list of questions
 * - return an array keyed on states with the count
 */

function bim_get_marker_question_stats( $marker, $qid, $questions ) {
    $stats = array( "Submitted" => 0, "Marked" => 0, "Released" => 0,
            "Missing" => 0, "Suspended" => 0);

    // if no statistics, then no posts for students of this marker
    if ( ! isset( $marker->statistics )) {
        return $stats;
    }

    // get prepared
    $statistics = $marker->statistics;
    $num_students = count( $marker->students );

    // if there's stats for this $qid (i.e. student has actually posted)
    if ( isset( $statistics[$qid] ) ) {
        $q_stats = $statistics[$qid];
        $num_found = 0;
        foreach (array_keys( $stats ) as $status) {
            if ( isset( $q_stats->$status )) {
                $stats[$status] = $q_stats->$status;
                $num_found+=$stats[$status];
            }
        }
        //    $stats["Missing"] = $q_stats["Total"] - $num_found;
        $stats["Missing"] = $num_students - $num_found;
    } else {
        // which means no posts for that qid yet
        $stats = array( "Submitted" => 0, "Marked" => 0, "Released" => 0,
                "Missing" => $num_students, "Suspended" => 0 );
    }

    return $stats;
}

/***
 * return the difference between two unix timestampes
 * - taken from http://www.charles-reace.com/PHP_and_MySQL/Time_Difference/
 */

function bim_time_diff($t1, $t2) {
    if ($t1 > $t2) {
        $time1 = $t2;
        $time2 = $t1;
    } else {
        $time1 = $t1;
        $time2 = $t2;
    }
    $diff = array(
            'years' => 0, 'months' => 0, 'weeks' => 0, 'days' => 0,
            'hours' => 0, 'minutes' => 0, 'seconds' =>0
            );

    foreach (array('years', 'months', 'weeks', 'days', 'hours', 'minutes', 'seconds') as $unit) {
        while (true) {
            $next = strtotime("+1 $unit", $time1);
            if ($next < $time2) {
                $time1 = $next;
                $diff[$unit]++;
            } else {
                break;
            }
        }
    }
    // convert to string
    $output="";
    foreach ($diff as $unit => $value) {
        if ( $value != 0 ) {
            $output .= " $value $unit,";
        }
    }
    $output = trim($output, ',');

    return($output);
}

/*
 * bim_get_unanswered( $marking_details, $questions )
 * - given a list of stored posts from students ($marking_details)
 *   and list of questions for bim ($questions)
 *   return an array (key = question id) for questions
 *   that do not appear in $marking_details
 * - i.e. return list of questions the student has not
 *   answered yet, at least according to the allocation in bim_marking
 */

function bim_get_unanswered( $marking_details, $questions ) {
    $unanswered = Array();

    if ( empty( $questions ) ) {
        return $unanswered;
    }

    // loop through each question, if ! found in marking_details
    // it's unanswered
    foreach ($questions as $question) {
        $found = false;
        if ( ! empty( $marking_details ) ) {
            foreach ($marking_details as $detail) {
                if ( $detail->question == $question->id ) {
                    $found = true;
                    break;
                }
            }
        }
        if ( ! $found ) {
            $unanswered[$question->id] = $question->id;
        }
    }

    return $unanswered;
}

/*
 * bim_show_student_details
 * - given various info about student, marking etc. show
 *   a table of information about the student and marking progress
 */

function bim_show_student_details( $student, $marking_details,
        $questions, $feed_details, $cm ) {
    global $DB, $OUTPUT;
    // calculate stats for student posts
    $post_stats = bim_generate_marking_stats( $marking_details );

    // Get progress result for the student
    $progress = bim_generate_student_results( $marking_details, $questions, $cm );
    $num_questions = count( $questions );
    if ( empty( $questions ) ) {
        $num_questions = 0;
    }
    $student_details = $DB->get_record( "user", Array("id" => $student) );

    print_heading( get_string('bim_find_student_details_heading', 'bim'), 'left', 2 );

    $details_table = new html_table;

    $details_table->class = 'generaltable';
    //    $details_table->head = array( 'Label', 'Value' );
    $details_table->align = array( 'center', 'left' );
    $details_table->size =  array( '30%', '60%' );
    $details_table->width = "70%";

    $details_table->data = array();
    $details_table->data[] = array( "Student",
            '<a href="mailto:'.$student_details->email.'">'.
            $student_details->lastname.', '.
            $student_details->firstname.' ('.
                $student_details->username.')</a>' );

    $answers = $post_stats["Released"]+$post_stats["Marked"]+
        $post_stats["Submitted"];
    $marked = $post_stats["Released"]+$post_stats["Marked"];
    $total_posts = count( $marking_details );
    // DETAILS TABLE
    $details_table->data[] = array(
            get_string('bim_marker_blog', 'bim'), '<a href="'.
            $feed_details[$student]->blogurl. '">'.
            $feed_details[$student]->blogurl. '</a>' );
    // # posts mirrored
    $help = $OUTPUT->help_icon( 'numMirrored', 'bim' );
    $details_table->data[] = array(
            get_string('bim_marker_posts', 'bim') . $help, $total_posts );

    // num actual and required answers
    $help = $OUTPUT->help_icon( 'numAnswers', 'bim');
    $details_table->data[] = array(
            get_string('bim_marker_answers', 'bim') . $help,
            "$answers / $num_questions" );

    // released and marked
    $help = $OUTPUT->help_icon( 'numReleased', 'bim' );
    $details_table->data[] = array(
            get_string('bim_marker_m_r', 'bim') . $help,
            $post_stats["Released"]." / $marked" );

    // progress result
    $help = $OUTPUT->help_icon( 'progressResult', 'bim' );
    $details_table->data[] = array(
            get_string('bim_marker_progress', 'bim').$help, $progress );

    echo html_writer::table( $details_table );
    echo '<p>&nbsp;</p>';
}

/*
 * $num = bim_get_marked( $bim )
 * - return the number of answers that have been marked and ready
 *   for release for a given BIM
 */

function bim_get_marked( $bim ) { global $DB;

    // define the SQL
    // get count of student posts in each status for this question
    $sql = "select count(id) as x from {bim_marking} where " .
        "bim=$bim->id and status='Marked' ";

    // get the value
    $details = $DB->get_records_sql( $sql );
    // return it
    if (empty( $details ) ) {
        return 0;
    } else {
        $detailx = array_values( $details );
        return $detailx[0]->x;
    }
}

/*
 * bim_show_student_posts( $mark_details, $questions )
 * - given array of records from bim_marking for a single
 *   student and a list of all the questions
 * - generate some HTML to show an overview of the questions
 */

function bim_show_student_posts( $mark_details, $questions ) {

    echo '<a name="allposts"></a>';
    print_heading( get_string('student_details_allposts_heading', 'bim'),
            'left', 2 );

    $total_posts = count( $mark_details );
    print_string( 'student_details_allposts_description', 'bim', $total_posts );

    $posts = new html_table;
    $posts->head = array( get_string('bim_mark_post', 'bim'),
            get_string('student_details_status_heading', 'bim'));
    $posts->tablealign = "center";
    $posts->size = (array( "60%", "40%" ));
    $posts->width="70%";

    // loop through each element
    foreach ($mark_details as $detail) {
        $url = '<a href="' . $detail->link . '">' . $detail->title . '</a>';
        $description = bim_is_item_allocated( $detail, $questions );
        $posts->data[] = array( $url, $description );
    }
    echo html_writer::table( $posts );
}

/*
 * $description = bim_is_item_allocated( $item, $mark_details, $questions )
 * - given a RSS item and array of objects
 *   return string description to use in display
 * - if Allocated/Marked give status
 * - else Not allocated
 */

function bim_is_item_allocated( $detail, $questions ) {
    global $OUTPUT;

    if ( $detail->status == "Unallocated" ) {
        /*        $help = helpbutton( 'unallocatedPostStudent',
                  'What does not allocated mean',
                  'bim', true, false, '', true ); */
        $help = $OUTPUT->help_icon( 'unallocatedPostStudent', 'bim' );

print "<xmp>" . $help . "</xmp>";
        return get_string( 'bim_item_allocated_not', 'bim' ) . $help;
    } else if ( $detail->status == "Released" ) {
        $qid = $detail->question;
        $a = new StdClass();
        $a->title = $questions["$qid"]->title;
        $a->mark = $detail->mark;
        $a->max = $questions["$qid"]->max_mark;

        return get_string( 'bim_item_allocated_released', 'bim', $a );
    } else if ( $detail->status == "Submitted" ) {
        $qid = $detail->question;
        if ( isset( $questions[$qid] ) ) {
            $question_title = $questions["$qid"]->title;
            /*            $help = helpbutton( 'allocatedPostStudent',
                          'What does not allocated mean',
                          'bim', true, false, '', true ); */
            $help = $OUTPUT->help_icon( 'allocatedPostStudent', 'bim' );
print "<xmp>" . $help . "</xmp>";
            return get_string( 'bim_item_allocated_allocated', 'bim',
                    $question_title ) . $help;
        }
    } else if ( $detail->status == 'Marked' ||
            $detail->status == 'Suspended' ) {
        $qid = $detail->question;
        if ( isset( $questions[$qid] ) ) {
            $question_title = $questions["$qid"]->title;
            /*            $help = helpbutton( 'markedPostStudent',
                          'What does marked mean',
                          'bim', true, false, '', true );*/
            $help = $OUTPUT->help_icon( 'markedPostStudent', 'bim' );
            return( get_string('bim_item_allocated_marked', 'bim',
                        $question_title) . $help);
        }
    }
}

/***********
 * bim_show_questions( $cm, $bim )
 * - show the list of questions for the given bim
 */

function bim_show_questions( $cm, $bim ) {

    // get the questions
    $questions = bim_get_question_hash( $bim->id );

    // should never be empty as the link shouldn't appear
    // but just in case
    if ( empty( $questions )) {
        return;
    }

    // show the heading/description
    print_heading( get_string('show_qs_heading', 'bim'), 'left', 2 );
    print_string( 'show_qs_description', 'bim', count( $questions ) );

    // create the table of questions

    $question_table = new html_table;
    $question_table->head = array(
            get_string( 'show_qs_title', 'bim' ),
            get_string( 'show_qs_body', 'bim' ) );
    $question_table->tablealign = 'left';
    $question_table->width = "700";
    $question_table->class = 'generaltable';

    foreach ($questions as $question) {
        $question_table->data[] = array( $question->title, $question->body );
    }

    echo html_writer::table( $question_table );
}

/**********
 * bim_truncate( $string, $limit=252, $break=" ", $pad="..." )
 * - shorten a string to a specified length
 * - Adapted from here
 *    http://www.the-art-of-web.com/php/truncate/
 */

function bim_truncate( $string, $limit = 250, $break = " ", $pad = "..." ) {
    // return with no change if string is shorter than $limit
    if (strlen($string) <= $limit) {
        return $string;
    }

    $string = substr($string, 0, $limit);

    if (false !== ($breakpoint = strrpos($string, $break))) {
        $string = substr($string, 0, $breakpoint);
    }
    return $string . $pad;
}

/*******
 * bim_email_merge( $ids, $course, $returnto, $button_msg )
 * - display/generate a form with submit button to generate
 *   an email merge with selected users ($ids) for $course
 *   from $returnto
 * - $button_msg text on the button
 */

function bim_email_merge( $ids, $course, $returnto, $button_msg ) {

    global $CFG;

    print <<<EOF
        <form method="post" action="$CFG->wwwroot/user/messageselect.php" />
        <input type="hidden" name="id" value="$course" />
        <input type="hidden" name="returnto" value="$returnto" />
        <input type="hidden" name="formaction" value="messageselect.php" />
        <input type="submit" name="submit" value="$button_msg" />
EOF;

    foreach ($ids as $id) {
        print "<input type=\"hidden\" name=\"user{$id}\" value=\"on\" />";
    }
    print "</form>";
}

