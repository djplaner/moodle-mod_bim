<?php
  /*** mange_marking.php
   * - provide the functions that implement the ManageMarking
   *   view functions
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

function bim_manage_marking( $bim, $userid, $cm )
{
  global $CFG;

  $base_url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&tab=manage";

  add_to_log( $cm->course, "bim", "manage marking",
                 "view.php?id=$cm->id&tab=manage",
                "Show status", $cm->id );

  // Calculations to find out how many unregistered students there are
  $allStudents = bim_get_all_students( $cm );
  $student_ids = array_keys( $allStudents );
  $feed_details = bim_get_feed_details( $bim->id, $student_ids );
  $unregistered = array_diff_key( $allStudents, $feed_details);
  $count_unreg = count( $unregistered); 
  // Get the question data required
  // all questions for this bim
  $questions = bim_get_question_hash( $bim->id );

  // add  the stats for the questions
  $questions = bim_get_question_response_stats( $questions);

  $help = helpbutton( 'manageMarking', 'manageMarking', 'bim',
                          true, false, '', true );
  print_heading( get_string('bim_marking_heading', 'bim' ).$help,
                     'left', 1 );

  if ( empty( $questions ) ) {
      print_string( 'bim_marking_no_questions', 'bim' );
  }

  if ( $count_unreg > 0 ) {
      print_string( 'bim_marking_unregistered', 'bim', $count_unreg );
  }

  // Get question titles
  $question_titles = array();
  foreach ( (array)$questions as $question )
  {
    $question_titles[] = $question->title;
  }

  // markers details and the makers student information
  // - Get all the students so we can add the stats
  $markers_students = bim_get_all_markers_students( $bim );
  if ( empty( $markers_students ) )
  {
    print_heading( get_string( 'bim_marking_no_markers_heading', 'bim' ), 'left', 2 );
    print_string( 'bim_marking_no_markers_description', 'bim' );
  }
  else
  {
    $markers_students = bim_get_all_marker_stats( $markers_students, $questions,
                                                $bim );

    // get the ids of all ther markers
    $markers = array_keys( $markers_students );

    //***********************
    // Start setting up the table

    $table = new flexible_table( 'bim-manage-marking-'.$cm->course.'-'.
                                   $cm->id.'-'.$userid );
// get a page parameter??
    $table->course = $cm->course;
    $table->define_baseurl( $base_url );

    $table->set_attribute('cellpadding','5');
    #$table->set_attribute('class', 'generaltable generalbox reporttable');
    $table->set_attribute('class', 'generalbox reporttable');

    $columns = array( 'marker', 'studs' );
    $columns = array_merge( $columns, $question_titles );
    $table->define_columns( $columns );
    $headers = array( 'Marker', 'Studs' );

    // set the column titles for the questions, including link to
    // release posts if there are any Marked posts for the question
    foreach ( $question_titles as $title )
    {
      $qid = bim_get_question_id( $title, $questions );
      if ( $qid != -1 )
      {
        $newTitle = $title;
        if ( $questions[$qid]->status["Marked"] != 0 )
        {
          $newTitle .= '<br /><small><a href="'.$base_url.
                   '&op=release&question='.$qid.'">release</a></small>';
        }
        $headers[] = $newTitle;
      }
    }
    $table->define_headers( $headers );
    $table->setup();

    //****
    // Start creating the data for the table, each row matches a marker
    foreach ( $markers_students as $marker )
    {
      // data
      // - students - is name, mailto, username, blog of student
      // - stats - string summary of posts in bim_marking
      // - one column per question to give overview of what's going on 
    
      $entry["marker"] = '<a href="mailto:'.$marker->details->email.'">'.
               $marker->details->firstname.' '.$marker->details->lastname.'</a>' ;
      // if the marker has some 'Marked' osts add a release option
      if ( isset( $marker->statistics["Total"]->Marked ) )
      {
        $entry["marker"] .= 
               '<br /><small><a href="'.$base_url.'&op=release&marker='.
               $marker->marker.'">release</a>' ; 
      }
    
      $num_students = count( $marker->students );
      $entry["studs"] = $num_students;

      foreach ( $question_titles as $title )
      {
        $qid = bim_get_question_id( $title, $questions );
        if ( $qid != -1 )
        {
          $question_stats = bim_get_marker_question_stats( $marker, 
                                       $qid, $questions );
       
          $mark = "Marked:";
          if ( $question_stats["Marked"] != 0 )
          {
            $mark = '<a href="'.$base_url.'&op=release&marker='.$marker->marker.
                  '&question='.$qid.'">Marked:</a>';
          }
          $entry[$title] = '<table border="0">';
     
          foreach ( array( "Submitted", "Marked", "Suspended", "Released", "Missing" ) as $status)
          {
            $label = "$status:";
  
            if ( $question_stats[$status] > 0 )
            {
              $label = '<a href="'.$base_url.'&op=view&marker='.$marker->marker.
                       '&question='.$qid.'&status='.$status.'">'.$label.'</a>';
            }

            $entry[$title] .= '<tr><th align="right"><small>'.$label.
                 '</small></th><td align="right"><small>'.
                 $question_stats[$status].'</small></td></tr>';
          }

           // add the release for this question/marker if any in marked state
           if ( $question_stats["Marked"] != 0 )
           {
             $entry[$title] .= 
                 '<tr><td colspan="2" align="center"><small><a href="' .
                 $base_url.'&op=release&marker='.$marker->marker.
                   '&question='.$qid.'">release</a><small></td></tr></table>';
           }
           else
           {
             $entry[$title] .= '</table>';
           }
         }
      }
      $table->add_data_keyed( $entry );
    }
    format_text( '<div align="center">' );

    $num_marked = bim_get_marked( $bim );
    if ( $num_marked > 0 )
    {
      echo '<p>[<small><a href="'.$base_url.'&op=release">' ;
      print_string( 'bim_marking_release', 'bim', $num_marked );
      echo '</a></small>]</p>'; 
    }
    $table->print_html();
    echo "</div>" ;
  }

  // Show unregstered students

  $unreg_data = bim_create_details_display( $unregistered, NULL, $cm );

  $table = bim_setup_details_table( $cm, $bim->id, $userid, 'unregistered' );
  foreach ( $unreg_data as $row )
  {
    $table->add_data_keyed( $row );
  }
  echo '<a name="unreg"></a>';
  print_heading( "Unregistered students", "left", 2 );
  print_container( "<p>The following " . count($unregistered) . 
                   " student(s) have not yet registered their feeds</p>" );
  // show the email textbox
  bim_show_unregistered_students_email( $unregistered );
  $table->print_html();
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

function bim_manage_release( $bim, $userid, $cm )
{
  global $CFG;
  $base_url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&tab=manage";

  add_to_log( $cm->course, "bim", "manage marking",
                 "view.php?id=$cm->id&tab=manage",
                "Releasing results", $cm->id );

  // Check parameters
  $marker = optional_param( "marker", NULL, PARAM_INT);
  $question = optional_param( "question", NULL, PARAM_INT);

  $sql = "bim=$bim->id and status='Marked' " ;

  if ( $marker != 0 )
  {
    $marker_students = bim_get_markers_students( $bim, $marker );
    $marker_ids = implode( ",", array_keys( $marker_students ) );
    $sql .= " and userid in ( $marker_ids ) ";
  }
  if ( $question != 0 )
  {
    $sql .= " and question=$question ";
  }

  // get the id,userid,mark fields from the rows that will be changed
  // Has to be done here before thte values are changed below
  $students_changing = get_fieldset_select( "bim_marking", "userid", $sql );

  // set status=Released
  $released = set_field_select( "bim_marking", "status", "Released", $sql );
  // set timereleased=now
  $time = time();
  $timeReleased = set_field_select( "bim_marking", "timereleased", $time, $sql );

  if ( ! $timeReleased ) {
      print "ERROR with time relase<br />";
  }
  if ( ! $released ) {
      print "ERROR with relased<br />";
  }
  
  // update the gradebook entry if it makes sense
  if ( $bim->grade_feed == 1 )
  {
    $raw_sql = "bim=$bim->id and status='Released' group by userid";
    $grades = get_records_select( 'bim_marking', $raw_sql, '',
                                'userid,sum(mark) as rawgrade' );
    bim_grade_item_update( $bim, $grades );
    // get results for all students_changing
    // use $marking_details to create an array of entries for 
    // each student released Form is
    //     userid = $userid   rawgrade = ??
  }

  print_heading( get_string( 'bim_release_heading', 'bim' ), "left", 2);

  if ( $released && $timeReleased  )
  {
    print_string( 'bim_release_success', 'bim' );
  }
  else
  {
    print_string( 'bim_release_errors', 'bim' );
    print '<p>Errors encountered while releasing results.</p>';
  }
  print_string( 'bim_release_return', 'bim', $base_url );
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

function bim_manage_view( $bim, $userid, $cm )
{
  global $CFG;
  $base_url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&tab=manage";

  add_to_log( $cm->course, "bim", "manage marking",
                 "view.php?id=$cm->id&tab=manage",
                "Show state", $cm->id );
  // Check parameters
  $status = optional_param( "status", NULL, PARAM_ALPHA);
  $marker = optional_param( "marker", NULL, PARAM_INT);
  $question = optional_param( "question", NULL, PARAM_INT);
  // - detailed checks not needed, should come out in SQL
  //   results 

  // get list of all student ids that have posts matching
  // details from parameters i.e. 
  //    select distinct userid from bim_marking where
  //        status='' and marker='' and question=''

  $sql = "";
  $students = array();

  // get the students that match the critiera

  $sql = "select distinct userid as userid from {$CFG->prefix}bim_marking " .
         "where bim=$bim->id";

  if ( $marker != "" )
  {
    // the marker field in bim_marking can be unreliable.
    // convert marker into an id of their students
    $marker_students = bim_get_markers_students( $bim, $marker );
    $marker_ids = implode( ",", array_keys( $marker_students ) );
    $sql .= " and userid in ( $marker_ids ) ";
  }
  if ( $question != "" )
    $sql .=" and question=$question";
  if ( $status != "" && $status != "Missing" )
    $sql .=" and status='".$status."'";

  $matching_students = Array();
  if ( $status == "Missing" ) {
      // if there's a marker specified , just get all markers students
      if ( $marker == "" ) {
          $all_students = bim_get_all_students( $cm );
      }
      else
      {
          $all_students = $marker_students;
      }
      // remove from this list the students that match the criteria
      $all = array_keys( $all_students );
      $matching = array_keys( $matching_students );
      $ids = array_diff( $all, $matching );
  } else {
      $matching_students = get_records_sql( $sql );
      $ids = array_keys( $matching_students );
  }

  // get list of student_details, $marking_details and feed_details
  // for all students in STATUS MARKER and QUESTION 
  $feed_details = bim_get_feed_details( $bim->id, $ids );
  $marking_details = bim_get_marking_details( $bim->id, $ids );
  $questions = bim_get_question_hash( $bim->id );
  $student_details = bim_get_student_details( $ids );

  $unregistered = array_diff_key( $student_details, $feed_details);
  $registered = array_diff_key( $student_details, $unregistered );

  // Show the data??
  print_heading( get_string( 'bim_release_manage_header', 'bim' ), "left", 2 );
  $a = count( $student_details );
  print_string( 'bim_release_manage_view', 'bim', $a );
  if ( $marker == 0 && $status == "" && $question == 0  )
  {
    print_string( 'bim_release_manage_any', 'bim' );
  }
  else
  {
    print_string( 'bim_release_manage_criteria', 'bim' );
    if ( $status != "" )
      print_string( 'bim_release_manage_status', 'bim', $status );
    if ( $marker != "" )
    {
      // get marker user details
      $marker_details = get_records_select( "user", "id=$marker" );
      $a = $marker_details[$marker]->firstname . ' ' .
           $marker_details[$marker]->lastname;
      print_string( 'bim_release_manage_marker', 'bim', $a );
    }
    if ( $question != "" )
      print_string( 'bim_release_manage_response', 'bim',
                      $questions[$question]->title );
    echo '</ul>';
  }
  print_string( 'bim_release_return', 'bim', $base_url );

  if ( $registered )
  {
    print_heading( get_string( 'bim_release_manage_registered_heading', 'bim' ),
                        "left", 2 );
    $a = count($registered);
    print_string( 'bim_release_manage_registered_description', 'bim', $a );
    $table = bim_setup_posts_table( $cm, $bim->id, $userid, $questions  );
    $reg_data = bim_create_posts_display( $cm, $registered, $feed_details,
                                $marking_details, $questions );
    foreach ( $reg_data as $row )
    {
      $table->add_data_keyed( $row );    
    }
    $table->print_html();
  }

  if ( $unregistered )
  {
    print_heading(get_string('bim_release_manage_unregistered_heading', 'bim' ),
                            "left", 2 );
    $a = count($unregistered);
    print_string( 'bim_release_manage_unregistered_description', 'bim', $a );
    $unreg_data = bim_create_details_display( $unregistered, $feed_details, $cm );
    $table = bim_setup_details_table( $cm, $bim->id, $userid, 'unregistered' );
    foreach ( $unreg_data as $row )
    {
      $table->add_data_keyed( $row );
    }
    $table->print_html();
  }
}

?>
