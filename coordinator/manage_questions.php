<?php
/*
 * manage_questions.php
 * - initial test to see if separate files for functions
 *   for specific screens make better sense going into
 *   small files
 * - coordinator/manage_questions provides support functions
 *   for the manage questions screen/page for coordinators
 *
 * bim_get_question_form( $questions, $cm )
 * - given list of questions for a bim and the $cm
 *   return the moodleform to display and manage questions
 */

require_once($CFG->dirroot.'/mod/bim/coordinator/question_form.php');

/*******
 * bim_manage_questions( $bim, $cm )
 * - set up and process the form to manage/configure questions
 */

function bim_manage_questions( $bim, $cm )
{
    global $CFG;
    global $DB;

    $questions = bim_get_question_hash( $bim->id );
    $num_questions = count( $questions );
    if ( empty($questions)) $num_questions=0;

    $questions = bim_get_question_response_stats( $questions );

    if ( $num_questions > 0 ) {
        print_string( 'bim_questions_current', 'bim', $num_questions );
    } else {
        print_heading( get_string( 'bim_questions_none_heading', 'bim' ),
                         'left', 2 );
        print_string( 'bim_questions_none_description', 'bim' );
    }

    $question_form = bim_get_question_form( $questions, $cm );

    if ( ! $question_form->is_submitted() )
    {
      add_to_log( $cm->course, "bim", "Questions manage",
                 "view.php?id=$cm->id&tab=questions",
                "Display", $cm->id );
      $question_form->display();
    }
    else if ( $question_form->is_cancelled() )
    {
      $question_form->display();
    }
    else if ( $fromform = $question_form->get_data() )
    {
      $additions = false;
      $deletions = false;

      print_box_start( 'noticebox boxwidthnormal' );
      print_heading( get_string( 'bim_questions_changes_heading', 'bim' ),
                      "left", 2 );
      // check the new/add question
      if ( $fromform->title_new != "" || $fromform->max_new != 0 ||
           $fromform->min_new != 0 || $fromform->body_new != "" )
      {
        // create new record
        $new_question->title = $fromform->title_new;
        $new_question->min_mark = $fromform->min_new;
        $new_question->max_mark = $fromform->max_new;
//        $new_question->body = addslashes(
//               preg_replace( '/^ /', '', $fromform->body_new ) );
        $new_question->bim = $bim->id;
        $new_question->id = '';

        print_string( 'bim_questions_adding', 'bim', $fromform->title_new );

        if ( ! $DB->insert_record( "bim_questions", $new_question ) )
        {
          error( get_string( 'bim_questions_error_insert', 'bim' ) );
        }
        $additions = true;
        add_to_log( $cm->course, "bim", "Questions manage",
                 "view.php?id=$cm->id&tab=questions",
                "Adding question", $cm->id );
      }

      // loop through each existing question
      // if any change in the form content, update the database
      $changed = array();

      if ( ! empty( $questions ) )
      {
        foreach ( $questions as $question )
        {
          $qid = $question->id;
          $title = "title_".$qid;
          $min = "min_".$qid;
          $max = "max_".$qid;
          $body = "body_".$qid;
          $delete = "delete_".$qid;

          if ( isset( $fromform->$delete ) )
          {

            if ( ! $DB->delete_records( "bim_questions", array("id"=>$qid)) )
            {
              print_string( 'bim_questions_error_delete', 'bim',
                               $question->title );
            }
            else
            {
              print_string( 'bim_questions_deleting', 'bim',
                               $question->title );
              $deletions = true;
            }
          }
          // KLUDGE: for some reason body has a space at the start
          // after being passed back from the form.  Don't want that.
          $fromform->$body = preg_replace( '/^ /', '', $fromform->$body );

          if ( $fromform->$title != $question->title  ||
               $fromform->$min != $question->min_mark ||
               $fromform->$max != $question->max_mark ||
               $fromform->$body != $question->body )
          {
            $question->title = $fromform->$title;
            $question->min_mark = $fromform->$min;
            $question->max_mark = $fromform->$max;
            $question->body = $fromform->$body;

            // get a copy so the unsert won't cause problems
            $changed[$qid] = clone $question;
            unset( $changed[$qid]->status );
          }
        }
      }
      $changes = count( $changed );
      if ( $changes )
      {
        print_string( 'bim_questions_changing', 'bim', $changes );
        // loop through each change and update the database
      foreach ( $changed as $change )
        {
          echo "<li> $change->title </li> ";
          if ( ! $DB->update_record( 'bim_questions', $change ) )
          {
            error( get_string( 'bim_questions_error_changing_title','bim' ) );
          }
        }
        echo "</ul>";
      }
      if ( $changes || $deletions )
      {
        add_to_log( $cm->course, "bim", "Questions manage",
                 "view.php?id=$cm->id&tab=questions",
                "Modified question(s)", $cm->id );
      }
      if ( ! $additions && ! $deletions && $changes == 0 )
      {
        print_string( 'bim_questions_nochanges', 'bim' );
      }
      print_box_end();
      redirect( "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&" .
                "tab=questions" );
    }
    else
    {
      error( get_string( 'bim_questions_error_processing', 'bim' ) );
    }
}

/*
 * bim_get_question_form( $questions, $cm )
 * - given list of questions for a bim and the $cm
 *   return the moodleform to display and manage questions
 */

function bim_get_question_form( $questions, $cm )
{
    $question_form = new question_form( 'view.php',
                           array( 'questions' => $questions,
                                  'id' => $cm->id ) );

    // set the form values to existing questions
    $toform = array();
    if ( empty( $questions ) ) return $question_form;

    foreach ( $questions as $question )
    {
      $toform["title_$question->id"] = $question->title ;
      $toform["min_$question->id"] = $question->min_mark;
      $toform["max_$question->id"] = $question->max_mark;
      $toform["body_$question->id"] = $question->body;
    }

    $question_form->set_data( $toform );

    return $question_form;
}

?>
