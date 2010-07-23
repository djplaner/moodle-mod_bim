<?php  // $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $

/*
 * bim/marker/view.php
 * - implement functions to produce view for users with
 *   the mdl/bim:marker capability
 */

require_once('lib.php');
require_once($CFG->dirroot.'/mod/bim/marker/allocation_form.php');
require_once($CFG->dirroot.'/mod/bim/marker/marking_form.php');
require_once($CFG->dirroot.'/mod/bim/marker/lib.php');
require_once($CFG->dirroot.'/mod/bim/marker/change_blog_form.php');
require_once($CFG->dirroot.'/mod/bim/marker/generateOpml.php');

/*************************************
 * show_marker( $bim, $userid, $cm )
 * - show teacher interface
 */

function show_marker( $bim, $userid, $cm, $course )
{
    global $CFG;
    // find out which screen is required
    $screen = optional_param('screen', 0, PARAM_ALPHA);
    if ( $screen == "" ) $screen = "ShowDetails";

    if ( $screen != "generateOpml" ) {
        bim_print_header( $cm, $bim, $course, $screen);
    }

    //** create the tabs
    $base_url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id";

    // switch to run the various screens
    if ( $screen == "ShowDetails" ) {
        $inactive = array( );
        $inactive[] = "posts";
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
        $student = optional_param( "student", 0, PARAM_INT );
        bim_change_blog_registration( $bim, $student, $cm );
    } else if ( $screen == "showQuestions" ) {
        bim_show_questions( $cm, $bim );
    } else if ( $screen == "generateOpml" ) {
        bim_generate_opml( $cm, $bim );
    }

    if ( $screen != "generateOpml" ) {
        print_footer( $course );
    }
}

/****
 * bim_marker_allocate_posts
 * - provide and handle form interface for teacher to 
 *   change the allocation of posts
 */

function bim_marker_allocate_posts( $bim, $userid, $cm, $student )
{
    global $CFG;

    print_box( '<a href="'.
        "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&screen=ShowDetails".
        '">' .
        get_string( 'bim_marker_student_details', 'bim' ) .
        '</a> | <a href="'.
        "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&screen=ShowPostDetails".
        '">' .  get_string( 'bim_marker_post_details', 'bim' ) .  '</a>',
           'noticebox boxaligncenter boxwidthnarrow centerpara highlight' );

    $help = helpbutton( 'changeAllocations', 'changeAllocations', 'bim',
                          true, false, '', true );
    print_heading( get_string('bim_marker_allocate_heading', 'bim' ).$help, 
                     'left', 1 );

    //************ Get the necessary data

    // the list of students the marker is supposed to mark
    $markers_students = bim_get_markers_students( $bim, $userid );

    // make sure the student is one of the markers
    if ( ! isset( $markers_students[$student] ))
    {
      print_heading( get_string( 'bim_marker_notstudent_heading', 'bim' ),
                         'left', 2 );
      print_string( 'bim_marker_notstudent_description', 'bim', $student );
      return;
    }

    // get student user details
    $student_details = get_records_select( "user", "id=$student" );

    // get student details from bim_student_feeds
    // *** should be just for this student, not all students for the marker
    $student_ids = array( $student );
    $feed_details = bim_get_feed_details( $bim->id, $student_ids );
    $marking_details = bim_get_marking_details( $bim->id, $student_ids);

    // all of the questions for this bim
    $questions = bim_get_question_hash( $bim->id );
    $num_questions = count( $questions );

    // calculate stats for student posts
    $post_stats = bim_generate_marking_stats( $marking_details );

    // Get progress result for the student
    $progress = bim_generate_student_results( $marking_details, $questions, $cm );

    //*********** Show the data

    //*** the student details table first
    
    print_heading( get_string( 'bim_find_student_details_heading', 'bim' ),
                        'left', 2 );

    $details_table = new stdClass;
    $details_table->class = 'generaltable';
    $details_table->align = array( 'center', 'left' );
    $details_table->size =  array( '30%', '60%' );
    $details_table->width = "70%";
   
    $details_table->data = array();
    $details_table->data[] = array( get_string('bim_marker_student','bim'), 
           '<a href="mailto:'.$student_details[$student]->email.'">'.
           $student_details[$student]->lastname.', '.
           $student_details[$student]->firstname.' ('.
           $student_details[$student]->username.')</a>' );

      $answers = $post_stats["Released"]+$post_stats["Marked"]+
                 $post_stats["Submitted"];
      $marked = $post_stats["Released"]+$post_stats["Marked"];
      $total_posts = count( $marking_details );

      #-- hand the case when the form is being processed
/*      $allocation_form = new allocation_form( 'view.php', array( 
                                 'marking_details' => $marking_details,
                                 'questions' => $questions ,
                                 'id' => $cm->id,
                                 'uid' => $student 
                                 )
                                ); */

    // DETAILS TABLE
    $details_table->data[] = array( get_string('bim_marker_blog','bim'),
           '<a href="'.$feed_details[$student]->blogurl. '">'.
           $feed_details[$student]->blogurl. '</a>' );
    $details_table->data[] = array( 
           get_string('bim_marker_posts','bim'), $total_posts );
    $details_table->data[] = array( 
           get_string('bim_marker_answers','bim'),"$answers of $num_questions" );
    $details_table->data[] = array( 
           get_string('bim_marker_m_r','bim'), 
            $post_stats["Released"]." / $marked" );
    $details_table->data[] = array( 
           get_string('bim_marker_progress','bim'), $progress );

    print_table( $details_table );


    // Start the form
    // at this stage the database has not been updated
    $allocation_form = new allocation_form( 'view.php', array( 
                                 'marking_details' => $marking_details,
                                 'questions' => $questions ,
                                 'id' => $cm->id,
                                 'uid' => $student 
                                 )
                                );

    if ( ! $allocation_form->is_submitted() || $allocation_form->is_cancelled() )
    {
      add_to_log( $cm->course, "bim", "posts allocate", 
                 "view.php?id=$cm->id&screen=AllocatePosts&uid=$student",
                "List all $student", $cm->id );
      print_heading( get_string('marker_allocation_heading','bim'), 'left', 2 );
      $allocation_form->display();
/*      print_heading( "Student posts", 'left', 2 );
      $allocation_form->display(); */
    }
    else if ( $fromform = $allocation_form->get_data() )
    {
      add_to_log( $cm->course, "bim", "posts allocate", 
                 "view.php?id=$cm->id&screen=AllocatePosts&uid=$student",
                "Processing allocation", $cm->id );
      bim_process_allocate_form( $marking_details, $fromform, $questions );

      print_heading( get_string('bim_marker_posts','bim'), 'left', 2 );
      $allocation_form = new allocation_form( 'view.php', array( 
                                 'marking_details' => $marking_details,
                                 'questions' => $questions ,
                                 'id' => $cm->id,
                                 'uid' => $student 
                                 )
                                );
      $allocation_form->set_data( $fromform );
      $allocation_form->display();
    }
    else 
    {
      add_to_log( $cm->course, "bim", "posts allocate", 
                 "view.php?id=$cm->id&screen=AllocatePosts&uid=$student",
                "Error processing form - $student", $cm->id );
      print_heading( get_string('bim_marker_error_heading', 'bim'), 'left', 2 );
      print_string( 'bim_marker_error_description', 'bim' );
      $allocation_form->display();
    }
}
    
/**
 * bim_process_allocate_form
 * - take the form to allocate posts and process it (duh)
 */

function bim_process_allocate_form( $marking_details, $fromform, $questions )/*
                $questions, $cm, $student ) */
{
    foreach ( $marking_details as $detail )
    {
      // get the key and value for the select menu matching
      // the current entry in bim_marking
//print "Doing $detail->id .... ";
      $param = "Reallocate_".$detail->id;
      if ( isset( $fromform->$param ) )
      {
        $allocation = $fromform->$param;
//print "allocation is $allocation...";
        // if there is a change modify the local settings
        // and those in the database
        if ( $allocation != "default" )
        {
          // set the select menu back to default for re-display
          $fromform->$param = "default";
          if ( $allocation == "Unallocate" )
          {
//print "...unallocate..";
            // Change status to unallocated and question to NULL
            $detail->status = "Unallocated";
            $detail->question = 0;
            $post = $detail->post;
            $detail->post = addslashes( $detail->post );
            if ( !isset( $detail->timereleased ) || $safe->timereleased == '') {
                $detail->timereleased = 0;
            }
            if ( ! update_record( 'bim_marking', $detail ) )
            {
              error( get_string('bim_error_updating','bim') );
            }
            else
            {
              $detail->post = $post;
              print_heading( get_string('marker_unallocating_heading','bim'),
                             "left", 2 );
              print_string( 'marker_unallocating_description', 'bim',
                               $detail->link );
            }
          } 
          else /* set to a question */
          {
//print "..change to $detail->question...";
            // Change question allocation
            $old_allocation = $detail->question;
            $old_status = $detail->status;
            $detail->question = $allocation;
            if ( $detail->status == "Unallocated" ) 
                   $detail->status = "Submitted";
            // update database
            $post = $detail->post;
            $detail->post = addslashes( $detail->post );
            if ( !isset( $detail->timereleased ) || $safe->timereleased == '') {
                $detail->timereleased = 0;
            }
            if ( ! update_record( 'bim_marking', $detail ) )
            {
              error( get_string('bim_error_updating','bim') );
            }
            else
            {
              // return the post to pre-addslashes
              $detail->post = $post;
              print_heading( get_string('marker_change_alloc_heading','bim'),
                              'left', 2 );
//print "...old status is $old_status...";
              if ( $old_status != "Unallocated" )
              {
                $a = new StdClass();
                $a->link = $detail->link;
                $a->old = $questions[$old_allocation]->title;
                $a->new = $questions[$allocation]->title;
                print_string( 'marker_change_alloc_descriptor', 'bim', $a );
              }
              else
              {
                $a = new StdClass();
                $a->link = $detail->link;
                $a->title = $questions[$allocation]->title;
                print_string( 'marker_allocate', 'bim', $a );
              }
            }
          } /*******/
        } /* no allocation */
      }
    }  
}

/****
 * show_marker_post_details
 * - display information about all of the answers a student
 *   has made, include pointers to overall information
 */

function show_marker_post_details( $bim, $userid, $cm )
{
    global $CFG;

    $url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&screen=ShowDetails";
    $show_qs_url = $CFG->wwwroot.'/mod/bim/view.php?id='.$cm->id.
                     '&screen=showQuestions'; 

    print_box( '<a href="'.
        "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&screen=ShowDetails".
        '">'  .
        get_string( 'bim_marker_student_details', 'bim' ) .
        '</a> | <strong>' .
        get_string( 'bim_marker_post_details', 'bim' ) .
        '</strong>',
               'noticebox boxaligncenter boxwidthnarrow centerpara highlight ' );


    //********* Get data
    // Array of all student information
    $student_details = bim_get_markers_students( $bim, $userid );

    // get marker user details
    $marker_details = get_records_select( "user", "id=$userid" );

    // get student details from bim_student_feeds
    $student_ids = array_keys( $student_details );
    $feed_details = bim_get_feed_details( $bim->id, $student_ids );
 
    $marking_details = bim_get_marking_details( $bim->id, $student_ids);

    // get student ids where they haven't registered their feed
    $unregistered = array_diff_key( $student_details, $feed_details);
    $registered = array_diff_key( $student_details, $unregistered );

    // the questions for this bim
    $questions = bim_get_question_hash( $bim->id );

    // Show registered information
    $help = helpbutton( 'markPostsAll', 'markposts', 'bim',
                          true, false, '', true );
    print_heading( get_string('bim_post_heading','bim').$help, "left", 2 );

    if ( empty( $questions ) ) {
        print_string( 'bim_post_no_questions', 'bim' );
    } else {
        $link = link_to_popup_window( $show_qs_url, 'showquestions',
                         get_string( 'bim_marker_show_qs_link','bim'),
                          700, 600, 'Show questions', null, true );
        print_string( 'bim_marker_show_qs', 'bim', $link );
    } 

    $table = bim_setup_posts_table( $cm, $bim->id, $userid, $questions  );
    $reg_data = bim_create_posts_display( $cm, $registered, $feed_details,
                                $marking_details, $questions );
    foreach ( $reg_data as $row )
    {
      $table->add_data_keyed( $row );
    }
    $table->print_html();
}

/*****
 * show_marker_student_details
 * - show the teacher summary of details for their students
 */

function show_marker_student_details( $bim, $userid, $cm )
{
    global $CFG;
    $url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&screen=ShowPostDetails";

     add_to_log( $cm->course, "bim", "students details", 
                 "view.php?id=$cm->id&screen=ShowDetails",
                "", $cm->id );


    //********* Get data
    // Array of all student information
    $student_details = bim_get_markers_students( $bim, $userid );

    // get marker user details
    $marker_details = get_records_select( "user", "id=$userid" );

    // get student details from bim_student_feeds
    $student_ids = array_keys( $student_details );
    $feed_details = bim_get_feed_details( $bim->id, $student_ids );

    // get student ids where they haven't registered their feed
    $unregistered = array_diff_key( $student_details, $feed_details);
    $registered = array_diff_key( $student_details, $unregistered );

    $a = new stdClass;
    $a->registered = count( $registered );
    $a->unregistered = count( $unregistered );
    $a->mark = 
       "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&screen=ShowPostDetails";

    // show some information about config if registration/mirroring not turned on
    if ( $bim->register_feed == 0 || $bim->mirror_feed == 0 ) {
        print_heading( get_string( 'marker_student_config_heading', 'bim' ),
                        'left', 2 );

        print_string( 'marker_student_config_description', 'bim' );
        if ( $bim->register_feed == 0 ) {
            print_string( 'marker_student_no_register', 'bim' );
        } 
        if ( $bim->mirror_feed == 0 ) {
            print_string( 'marker_student_no_mirror', 'bim' );
        } 
        print_string( 'marker_student_config_end', 'bim' );
    }

    // display reason for no markers and advise what to do
    if ( $a->registered == 0 && $a->unregistered == 0 ) {
        print_heading( get_string('bim_release_no_students_heading','bim' ),
                       'left', 2);
        print_string('bim_release_no_students_description', 'bim' );
        return;
    }
    // Start displaying details about students
     print_box( '<strong>' .
           get_string( 'bim_marker_student_details', 'bim' ) . 
          '</strong> | <a href="'.
        "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&screen=ShowPostDetails".'">'.
           get_string( 'bim_marker_post_details', 'bim' ) .  '</a>',
           'noticebox boxaligncenter boxwidthnarrow centerpara highlight ' );

    $help = helpbutton( 'yourStudents', 'yourStudents', 'bim',
                          true, false, '', true );
    print_heading( get_string( 'bim_student_details_heading','bim').$help,
                   'left',2);
    print_string( 'bim_details_count', 'bim', $a );
    $help = helpbutton( 'opml', 'opml', 'bim', true, false, '', true );
    $opml->url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&screen=generateOpml";
    $opml->help = $help;

    print_string( 'bim_details_opml', 'bim', $opml );

    //********* View data
    // uncregistred students
    if ( isset( $unregistered ))
    {
      $help = helpbutton( 'unregisteredDetails', 'unregdetails', 'bim',
                          true, false, '', true );
      echo '<a name="unreg"></a>';
      print_heading( get_string('bim_release_manage_unregistered_heading','bim').
                     $help, "left", 2 );
      print_string( 'bim_details_unregistered_description', 'bim' );

      $unreg_data = bim_create_details_display( $unregistered, $feed_details, 
                                $cm );

      // show the "email merge" form for 
      $returnto = $url;
      $userids = array_keys( $unregistered );
      bim_email_merge( $userids, $cm->course, $returnto, 
                        "Email unregistered students" );
      print "<p>&nbsp;</p>";

      $table = bim_setup_details_table( $cm, $bim->id, $userid, 'unregistered' );
      foreach ( $unreg_data as $row )
      {
        $table->add_data_keyed( $row );
      }
#       $unreg_table->add_data_keyed( $unregistered );
      $table->print_html();
    }

    // Show registered information
    $help = helpbutton( 'registeredDetails', 'regdetails', 'bim',
                          true, false, '', true );
    echo '<a name="reg"></a>';
    print_heading( get_string('bim_details_registered_heading', 'bim' ).$help, 
                               "left", 2 );
    $table = bim_setup_details_table( $cm, $bim->id, $userid, 'registered' );
    $reg_data = bim_create_details_display( $registered, $feed_details,
                    $cm );
    foreach ( $reg_data as $row )
    {
      $table->add_data_keyed( $row );
    }
    $table->print_html();
}

/*
 * $table = bim_setup_posts_table( $cm, $bim, $userid, $questions );
 * - return flexible_table object for showing posts details
 */

function bim_setup_posts_table( $cm, $bim, $userid, $questions )
{
      global $CFG;
      $baseurl = $CFG->wwwroot.'/mod/bim/view.php?id='.$cm->id .
                        '&screen=ShowPostDetails';

      $table = new flexible_table( "BimShowPostDetails-".$cm->id.
                                            '-'.$bim.'-'.$userid );
      $table->course = $cm->course;
  
      $num_questions = count( $questions );

      $columns = array( "username", "name", "questions",
                "marked" ) ;
      $headers = array( get_string('bim_table_username','bim'),
                        get_string('bim_table_name_blog', 'bim' ),
                        get_string('bim_table_questions', 'bim' ),
                        get_string('bim_table_marked', 'bim' ) );

      $no_sorting = array( 'questions', 'marked', 'username', 'name' );
      // add columns/headers for each question
   
      if ( ! empty( $questions ) )
      {
        foreach ( $questions as $id => $question )
        {
          $columns[] = $id;
          $headers[] = $question->title;
          $no_sorting[] = $question->title;
        }
      }

      $table->define_columns( $columns );
      $table->define_headers( $headers );

      $table->define_baseurl( $baseurl );

      $table->sortable( true, 'name', 'ASC' );
      $table->sortable( true, 'username', 'ASC' );
      foreach ( $no_sorting as $field )
      {
        $table->no_sorting( $field );
      }

      $table->set_attribute('cellpadding','5');
      $table->set_attribute('class', 'generaltable generalbox reporttable');
      $table->set_control_variables(array(
                                  TABLE_VAR_SORT    => 'ssortShowPosts',
                                  TABLE_VAR_HIDE    => 'shideShowPosts',
                                  TABLE_VAR_SHOW    => 'sshowShowPosts',
                                  TABLE_VAR_IFIRST  => 'sifirstShowPosts',
                                  TABLE_VAR_ILAST   => 'silastShowPosts',
                                  TABLE_VAR_PAGE    => 'spageShowPosts'
                                            ));
      $table->setup();
      return $table;
}

/*
 * bim_create_posts_display( $cm, $user_details, $feed_details, 
 *                          $marking_details, $questions )
 * - given user, feed and marking details return an array that combines
 *   the three bits of data to give what is needed for the posts
 *   display
 * - Need username name email from user_details
 * - Need num_entries last_post blog_url from feed_details
 */

function bim_create_posts_display( $cm, $user_details, $feed_details, 
                                     $marking_details, $questions )
{
      global $CFG;
      $baseurl = $CFG->wwwroot.'/mod/bim/view.php?id='.$cm->id; 

  $display = array();
  $num_questions = count( $questions );

  // TO DO -- need to get num answered and num marked for user
  //          and total number of posts
  foreach ( $user_details as $user )
  {
    if ( isset( $feed_details[$user->id] ))
    {
      // username includes a mailto link
      $display[$user->id]["username"] = 
            '<a href="mailto:'.$user->email.'">'.$user->username.'</a>';

      // the name is also a link to the student's live blog
      $display[$user->id]["name"] =
            '<a href="'.$feed_details[$user->id]->blogurl.'">'.
               $user->lastname.", ".$user->firstname.'</a>';

      $stud_marking_details = array();
      foreach ( $marking_details as $row )
      {
        if ( $row->userid == $user->id )
          $stud_marking_details[] = $row;
      }

      // get the details for this students marked posts and
      // generate some stats based on various states
      $post_stats = bim_generate_marking_stats( $stud_marking_details );
      $answers = $post_stats["Released"]+$post_stats["Marked"]+
                 $post_stats["Submitted"];
      $marked = $post_stats["Released"]+$post_stats["Marked"];
      $total_posts = count( $stud_marking_details );

      // questions - summarises how many of all questions answered
      // and the total number of posts
      $display[$user->id]["questions"] = 
            '<small>'.$answers.' of '.$num_questions.'<br />' .
            '<a href="'.$baseurl.'&screen=AllocatePosts&uid='.$user->id.'">'.
            $total_posts.get_string('bim_posts','bim').'</a></small>';
      $display[$user->id]["marked"] =
            "<small>$marked of $answers</small>";

      // need to add details about each students question
      // go through each question configured for the BIM activity
      foreach ( $questions as $bim_qid => $question )
      {
        // for a given question, loop through the rows from
        // mdl_bim_marking for the student and generate what 
        // needs to go into the cell
        foreach ( $stud_marking_details as $row ) {
          $qid = $row->question;

          // does the student have an entry for the current question
          if ( ! isset( $questions[$row->question] ) ) continue;
          if ( $qid == $bim_qid )
          {
            if ( $row->status == "Submitted" )
            {
              $display[$user->id][$qid] =
                '<small><a href="'.$row->link.'">'.
                get_string('bim_answer','bim').'</a><br />'.
                '<a href="'.$baseurl.'&screen=MarkPost&markingId='.$row->id.
                '">'. get_string('bim_not_marked','bim').'</a>';
            }
            else if ( $row->status == "Suspended" ) {
                $display[$user->id][$qid] = 
                    '<small><a href="'.$row->link.'">'.
                    get_string('bim_answer','bim').'</a><br />'.
                    '<a href="'.$baseurl.'&screen=MarkPost&markingId='.$row->id.
                    '">'. get_string('bim_suspended','bim').'</a>';
            } else {
                $display[$user->id][$qid] = 
                 '<small><a href="'.$row->link.'">'.
                        get_string('bim_answer','bim') . '</a><br />'.
                  '(<a href="'.$baseurl.'&screen=MarkPost&markingId='.$row->id.
                  '">'.$row->mark.'</a> - ' . $row->status .')';
            }
            break;
          }
        }
        // nothing was put in place when looping through the students
        // details.  Which implies, student hasn't answered yet
        if ( ! isset( $display[$user->id][$bim_qid] ) )
        {
          $display[$user->id][$bim_qid] = "<small>No answer</small>";
        }
      }
    } 
  }
  return $display;
}

/*
 * $table = bim_setup_details_table( $cm, $bim, $userid );
 * - return flexible_table object 
 */

function bim_setup_details_table( $cm, $bim, $userid, $table_id )
{
//      global $CFG;
 //     $baseurl = $CFG->wwwroot.'/mod/bim/view.php?id='.$cm->id.'&tab=manage';

      $table = new flexible_table( $table_id.'-'.$cm->id.
                                            '-'.$bim.'-'.$userid );
      $table->course = $cm->course;
  //    $table->define_baseurl( $baseurl );
  
      if ( $table_id == "unregistered" )
      {
        $table->define_columns( array( "username", "name", "email", "register" ) );
        $table->define_headers( array( 
                    get_string('bim_table_username','bim' ),
                    get_string('bim_table_name','bim'),
                    get_string('bim_table_email','bim'),
                    get_string('bim_table_register','bim') ));
        $table->no_sorting( 'email', 'name' );
      }
      else if ( $table_id == "registered" )
      {
        $table->define_columns( array( "username", "name", "email",
                                       "num_entries", "last_post", 
                                       "blog_url" ) );
        $table->define_headers( array(
                    get_string('bim_table_username','bim' ),
                    get_string('bim_table_name','bim'),
                    get_string('bim_table_email','bim'),
                    get_string('bim_table_entries','bim'),
                    get_string('bim_table_last_post','bim'),
                    get_string('bim_table_live_blog','bim') ));
      }
      else
      {
        $table->define_columns( array( "username", "name", "details" ) );
        $table->define_headers( array( 
                    get_string('bim_table_username','bim' ),
                    get_string('bim_table_name','bim'),
                    get_string('bim_table_details','bim') ));
      }


//    $table->sortable( true, 'name' );
//      $table->sortable( true, 'username', 'ASC' );

      $table->set_attribute('cellpadding','5');
      $table->set_attribute('class', 'generaltable generalbox reporttable');

/*      $table->set_control_variables(array(
                                            TABLE_VAR_SORT    => 'ssort'.$table_id,
                                            TABLE_VAR_HIDE    => 'shide'.$table_id,
                                            TABLE_VAR_SHOW    => 'sshow'.$table_id,
                                            TABLE_VAR_IFIRST  => 'sifirst'.$table_id,
                                            TABLE_VAR_ILAST   => 'silast'.$table_id,
                                            TABLE_VAR_PAGE    => 'spage'.$table_id
                                            )); */
      $table->setup();
      return $table;
}

/*
 * bim_create_details_display( $user_details, $feed_details )
 * - given user and feed details return an array that combines
 *   the two bits of data to give what is needed for the details
 *   display
 * - Need username name email from user_details
 * - Need num_entries last_post blog_url from feed_details
 */

function bim_create_details_display( $user_details, $feed_details=NULL, 
                                     $cm = NULL ) {
    global $CFG;

    $display = array();

    foreach ( $user_details as $user ) {
        $display[$user->id] = array(
          "username" => $user->username,
          "name" => $user->lastname . ", " . $user->firstname,
          "email" => '<a href="mailto:' .$user->email .
                     '">Send email</a>',
          "id" => $user->id
            );
        // if ther's some feed stuff
        if ( isset( $feed_details[$user->id] )) {
            $display[$user->id]["num_entries"] = 
                 $feed_details[$user->id]->numentries;

            $lastpost = $feed_details[$user->id]->lastpost;
            if ( $lastpost > 0 ) {
                $diff = timeDiff( time(), $feed_details[$user->id]->lastpost );
               
                if ( preg_match( '/^.[0-9]+ hours/', $diff)==1 ||
                     preg_match( '/^.[0-9]+ days/', $diff)==1) {
                    $display[$user->id]["last_post"] =
                        '<span class="notifysuccess">'.$diff.'</span>';
                } else { //if ( preg_match( '!^[0-9]+ days!', $diff)) {
                    $display[$user->id]["last_post"] =
                        '<span class="notifyproblem">'.$diff.'</span>';
                } 
            }
            else {
                $display[$user->id]["last_post"] = 
                   get_string('bim_details_unavailable','bim' );
            }
  
            $base_url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id" .
                        "&screen=changeBlogRegistration" .
                        "&student=$user->id";

            // entry for Student Blog should show
            //   "View current" for all student, but
            //   "CHange it" for coordinator

            $blog = '<a href="'. $feed_details[$user->id]->blogurl .
                     '">'.get_string('bim_table_current_blog','bim').'</a>';

            $context = get_context_instance( CONTEXT_MODULE, $cm->id );
            if ( has_capability('mod/bim:coordinator',$context) ) {
                $blog .= ' | <a href="' . $base_url . '">' . 
                         get_string('bim_table_change_blog','bim').'</a>';
            }
            $display[$user->id]["blog_url"] = $blog;

        } else { // no feed == unregistered
            $base_url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id" .
                        "&screen=changeBlogRegistration" .
                        "&student=$user->id";
            
            $display[$user->id]["register"] =
                  '<a href="'. $base_url . '">' .
                   get_string('bim_table_register_blog','bim').'</a>';
        }
          
    }
    return $display;
}

/****
 * bim_marker_mark_post
 * - provide and handle form interface for teacher to 
 *   mark a post
 */

function bim_marker_mark_post( $bim, $userid, $cm, $marking )
{
    global $CFG;

    // show navigation to view all student options
    print_box( '<a href="'.
        "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&screen=ShowDetails".
        '">'.get_string('bim_marker_student_details','bim').'</a> | <a href="'.
        "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&screen=ShowPostDetails".
        '">'.get_string('bim_marker_post_details','bim').'</a>',
           'noticebox boxaligncenter boxwidthnarrow centerpara highlight ' );

    print_heading( get_string('bim_mark_post_heading','bim'), 'left', 1 );

    //************ Get the necessary data

    // get the student for the entry
    $student = get_field( 'bim_marking', 'userid', 'id', $marking );

    if ( ! $student )
    {
      error( get_string( 'bim_mark_details_error','bim') );
      return;
    } 
 
    // the list of students the marker is supposed to mark
    $markers_students = bim_get_markers_students( $bim, $userid );

    // make sure the student is one of the markers
    if ( ! isset( $markers_students[$student] ))
    {
      print_heading( get_string('bim_marker_notstudent_heading', 'bim'), 
                        'left', 2 );
      print_string( 'bim_marker_notstudent_description', 'bim', $student );
      return;
    }

    // get student user details
//    $student_details = get_records_select( "user", "id=$student" );

    // get student details from bim_student_feeds
    // *** should be just for this student, not all students for the marker
    $student_ids = array( $student );
    $feed_details = bim_get_feed_details( $bim->id, $student_ids );
    $marking_details = bim_get_marking_details( $bim->id, $student_ids);

    // all of the questions for this bim
    $questions = bim_get_question_hash( $bim->id );

    // get next and prev question id based on marking_details (what the
    // student has answered and $marking, the current student post
    $nextPrevQ = bim_get_next_prev_question( $marking, $marking_details );
    $nextPrevS = bim_get_next_prev_student( 
                       $marking_details[$marking]->question, $student, 
                       $markers_students, $bim );
 
    //*********** Show the data
    $marking_form = new marking_form( 'view.php', array( 
                                 'marking_details' => $marking_details,
                                 'questions' => $questions ,
                                 'id' => $cm->id,
                                 'uid' => $student,
                                 'marking' => $marking 
                                 )
                                );

    // **** display form for first time
    if ( ! $marking_form->is_submitted() )
    {
      add_to_log( $cm->course, "bim", "post mark", 
                 "view.php?id=$cm->id&screen=ShowPostDetails",
                "Starting marking", $cm->id );
      // make sure the comments from the dbase appear in the editor
      if ( $marking_details[$marking]->comments == "NULL" )
        $marking_details[$marking]->comments = "";
      $toform->comments = $marking_details[$marking]->comments;
      $toform->mark = $marking_details[$marking]->mark;
      if ( $marking_details[$marking]->status == "Suspended" ) {
          $toform->suspend = 1;
      }
      $marking_form->set_data( $toform );

      //*** the student details table first
      bim_show_student_details( $student, $marking_details,
                                   $questions, $feed_details, $cm );

//      ivew.php cm->id, screen=MarkPost and markingId
      $url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&screen=MarkPost&" .
               "markingId=";
      print_box_start( "box generalbox boxaligncenter boxwidthnormal centerpara" );
      bim_show_next_prev_question( $cm, $nextPrevQ, $url, "q"  );
      bim_show_next_prev_question( $cm, $nextPrevS, $url, "s"  );
      print_box_end();

      print_heading( get_string('bim_mark_post','bim'), "left", 2 );
       
      $marking_form->display();
    }
    else if  ( $marking_form->is_cancelled() )
    {
      // send user back to post details page
      print_heading( get_string('bim_mark_cancel_heading','bim'), 'left', 1);
      print_string( 'bim_mark_cancel_description','bim');
      redirect( "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&" .
                "screen=ShowPostDetails" );
    }
    else if ( $fromform = $marking_form->get_data() )
    {
      // Process the form content
      $comments_change = $fromform->comments != 
                         $marking_details[$marking]->comments;
      // Assume we are changing mark, and subsequent do stuff below
      $mark_change = true; 
      // unless the current status is Released
      // then base change on whether the value is different
      if ( $marking_details[$marking]->status == "Released" ) {
          $mark_change = $fromform->mark != $marking_details[$marking]->mark;
      }

      // check and show error if mark is above max mark for question
      if ( $fromform->mark > 
               $questions[$marking_details[$marking]->question]->max_mark ) {
          print_box_start( 'noticebox boxwidthnormal' );
          print_heading( get_string('bim_mark_max_exceeded_heading','bim'),
                              'left', 2 );
          $a = new stdClass;
          $a->mark = $fromform->mark;
          $a->max = $questions[$marking_details[$marking]->question]->max_mark; 
          print_string( 'bim_mark_max_exceeded_description', 'bim', $a );
          print_box_end();
      }
      // check and show error if mark is below min mark for question
      if ( $fromform->mark < 
               $questions[$marking_details[$marking]->question]->min_mark ) {
          print_box_start( 'noticebox boxwidthnormal' );
          print_heading( get_string('bim_mark_min_exceeded_heading','bim'),
                              'left', 2 );
          $a = new stdClass;
          $a->mark = $fromform->mark;
          $a->max = $questions[$marking_details[$marking]->question]->min_mark; 
          print_string( 'bim_mark_min_exceeded_description', 'bim', $a );
          print_box_end();
      }

      print_box_start( 'noticebox boxwidthnormal' );
      print_heading( get_string('bim_mark_changes_heading','bim'), 'left',2);
      $menu_name = "Reallocate$marking";

      // ensure that suspend is set in the form - makes it easier below
      if ( ! isset( $fromform->suspend ) ) {
          $fromform->suspend = 0;
      }
   
      // check whether or not the suspension has changed from dbase value
                          // dbase not suspended, being suspended
      $suspend_change = ( $marking_details[$marking]->status != "Suspended" && 
                             $fromform->suspend == 1 ) ||
                          // dbase suspended, being unsuspended
                        ( $marking_details[$marking]->status == "Suspended" &&
                             $fromform->suspend == 0 );


      $allocation_change = false;
      if ( isset( $fromform->$menu_name) )
            $allocation_change = $fromform->$menu_name != "default";

      $change = $comments_change || $mark_change || $allocation_change ||
                $suspend_change;;

      if ( $change ) echo '<ul>';

      // there's been some movement in suspendsion, handle it
      if ( $suspend_change ) {
          if ( $marking_details[$marking]->status != "Suspended" &&  
                             $fromform->suspend == 1 ) {
              $marking_details[$marking]->status = "Suspended";
              print_string( 'bim_mark_suspended', 'bim' );
          } else if ( $marking_details[$marking]->status == "Suspended" &&
                             $fromform->suspend == 0 ) {
              $marking_details[$marking]->status = "Marked";
              print_string( 'bim_mark_unsuspended', 'bim' );
          }
      }

      if ( $comments_change )
      { 
        $marking_details[$marking]->comments = $fromform->comments;
        print_string('bim_mark_comments_updated','bim' );
      }
      if ( $mark_change )
      { 
        $marking_details[$marking]->mark = $fromform->mark;
        print_string('bim_mark_mark_updated','bim' );
      }

      // chage the status to marked, unless we're suspended
      if ( $marking_details[$marking]->status != 'Suspended' ) {
        // chnage status if there is any change in mark ||
        // there is a comment change in a Released post
        if ( $mark_change ||
             ($marking_details[$marking]->status = 'Released' &&
                $comments_change == true )) {
            $marking_details[$marking]->status = 'Marked';
            print_string('bim_mark_marked','bim' );
        }
      }

      if ( $allocation_change )
      {
        // if making unallocated remove question and change status
        if ( $fromform->$menu_name == "Unallocate" )
        {
          $marking_details[$marking]->status = "Unallocated";
          $marking_details[$marking]->question = 0;
          print_string('bim_mark_unallocated','bim' );
        }
        else // re-allocating to a different question
        {
          $marking_details[$marking]->question = $fromform->$menu_name;
          print_string('bim_mark_unallocated','bim',
                $questions[$marking_details[$marking]->question]->title );
        }
      }

      if ( $change )
      {
        add_to_log( $cm->course, "bim", "post mark", 
                 "view.php?id=$cm->id&screen=ShowPostDetails",
                "Marking change", $cm->id );
        $marking_details[$marking]->marker = $userid;
        print_string('bim_mark_marker','bim' );
        echo '</ul>';

        $safe = addslashes_object( $marking_details[$marking] );
        if ( !isset( $safe->timereleased ) || $safe->timereleased == '') {
            $safe->timereleased = 0;
        }
        if ( ! update_record( 'bim_marking', $safe ))
        {
              error( get_string('bim_error_updating', 'bim') );
        }
      }
      else
      {
        print_string( 'bim_mark_nochanges', 'bim' );
      }
      print_box_end();
      
      $marking_details = bim_get_marking_details( $bim->id, $student_ids);

      //*** the student details table first
      bim_show_student_details( $student, $marking_details,
                                   $questions, $feed_details, $cm );
      
      $url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&screen=MarkPost&" .
               "markingId=";
      print_box_start( "box generalbox boxaligncenter boxwidthnormal centerpara" );
      bim_show_next_prev_question( $cm, $nextPrevQ, $url, "q"  );
      bim_show_next_prev_question( $cm, $nextPrevS, $url, "s"  );
      print_box_end();

      print_heading( get_string('bim_mark_continue','bim'), 'left', 2 );

      $marking_form = new marking_form( 'view.php', array( 
                                 'marking_details' => $marking_details,
                                 'questions' => $questions ,
                                 'id' => $cm->id,
                                 'uid' => $student,
                                 'marking' => $marking 
                                 )
                                );
      $marking_form->display();
    }
    else 
    {
      print_heading( get_string('bim_marker_error_heading','bim'));
      print_string( 'bim_mark_nochanges', 'bim' );
      $marking_form->display();
    }
}

/*
 * bim_change_blog_registration( $bim, $student, $cm )
 * - display and process the form that allows a marker/coordinator
 *   to change the blog for a registered student.
 */

function bim_change_blog_registration( $bim, $student, $cm ) {

    global $CFG;
    $base_url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&screen=".
                "changeBlogRegistration&student=$student";


    // show details about the student
    $student_details = bim_get_student_details( Array( $student ) );
    $marking_details = bim_get_marking_details( $bim->id, Array( $student ));
    $feed_details = bim_get_feed_details( $bim->id, Array( $student ));
    $questions = bim_get_question_hash( $bim->id );


    // let's show and process the form
    $form = new change_blog_form( 'view.php', array( 'id' => $cm->id,
                                    'student' => $student ));

    // if there are feed etails, then we're changing and have
    // studnet details to show, otherwise we're registtering
    $heading = 'bim_change_heading';
    $description = 'bim_change_description';
    if ( empty( $feed_details )) {
        $heading = 'bim_change_register_heading';
        $description = 'bim_change_register_description'; 
    }
    if ( ! $form->is_submitted() ) {
        // add to log
        add_to_log( $cm->course, "bim", "Change blog",
                      "view.php?cm=id&screen=changeBlogRegistration",
                      "Start/display", $cm->id );

        // heading and description
        print_heading( get_string($heading,'bim'),'left', 2);
        print_string( $description, 'bim' );

        if ( ! empty( $feed_details ) ) {
            bim_show_student_details( $student, $marking_details, 
                               $questions, $feed_details, $cm );
        }
        $form->display();
    } else if ( $form->is_cancelled() ) {
        // ?????
        add_to_log( $cm->course, "bim", "Change blog",
                      "view.php?cm=id&screen=changeBlogRegistration",
                      "Cancel", $cm->id );
    } else if ( $fromform = $form->get_data() ) {

        // do some checks and try to retrieve
        // is it "fromform" that is being passed in here, should 
        // if be feed_deails?
        $fromform = bim_get_feed_url( $fromform, $cm, $bim );

        // if no error then update
        if ( ! isset( $fromform->error) ) {
           // update bim_student_feeds with new url
           // need bim and student, also feed
           $feed = new stdClass;
           if ( ! empty( $feed_details ) ) {
               // modify existing feed details
               $feed = $feed_details[$student];
           } else {  // probably need to set up $feed elmements
               $feed->userid = $student;
               $feed->bim = $bim->id;
           }

           $feed->numentries = 0;
           $feed->feedurl = $fromform->feedurl;
           $feed->blogurl = $fromform->blogurl;
           $feed->lastpost = $fromform->lastpost;

           // ***** update/insert the feeds here
           // This is called by both register blog and change
           // blog - so needs to be able to insert or update
           $feed_id = 0;
           if ( isset( $feed->id ) ) {
               if ( ! $feed_id = update_record( 'bim_student_feeds', $feed ) ) {
                   mtrace( 'Error unable to update existing feed for student ' .
                           $student . ' for bim activity ' . $bim->id );
                   print_string( 'bim_error_updating', 'bim' );
                   return false;
               } 
           } else if ( ! $feed_id = insert_record( 'bim_student_feeds', $feed )){
               mtrace( 'Error unable to insert feed for student ' .
                           $student . ' for bim activity ' . $bim->id );
               print_string( 'bim_error_updating', 'bim' );
               return false;
           }    

           // ***** delete the existing entries in bim_marking
           if ( ! delete_records( 'bim_marking', 'bim', $bim->id,
                           'userid', $student ) ) {
               mtrace( 'Error deleting marking records for bim ' . $bim->id .
                       ' and student ' . $student );
               return false; 
           }

           // add to log and show record of success
           add_to_log( $cm->course, 'bim', 'Change blog',
                        "view.php?cm=id&screen=changeBlogRegistration",
                        "user: $student $fromform->blogurl ", $cm->id );

           // prepare to process new feeds
           $questions = bim_get_question_hash( $bim->id );
           $feed->id = $feed_id;
           bim_process_feed( $bim, $feed, $questions ); 

           // show results
           // Does this show all of the details and nicely on page
           // with existing content? 
         
           // get marking details again as they may/should have changed
           $marking_details = bim_get_marking_details( $bim->id, 
                                                       Array( $student ));
           $feed_details = bim_get_feed_details( $bim->id, Array( $student ));

            // heading and description
            print_heading( get_string('bim_change_success_heading','bim'),'left', 2);
            print_string( 'bim_change_success_description', 'bim' );
            bim_show_student_details( $student, $marking_details, 
                               $questions, $feed_details, $cm );
            bim_show_student_posts( $marking_details, $questions );

        } else {  // error with retrieving URL
            bim_display_error( $fromform->feedurl, $fromform, $cm );
            print_string( 'bim_change_again','bim', $base_url );
            return 0;
        }
    }
}

?>
