<?php

/****

  ************************************************
  ******* DEVELOPMENT CODE
  ******* The following is still not complete
  ******* Currently, no user data being backed up
  ************************************************

Attempt to get backup going for BIM.

Database structure 
- bim
  CL, pk->id
  - bim_questions
    CL, pk->id, fk->bim
  - bim_group_allocation
    UL, pk->id, fk->bim,  -- fk->groupid, fk->userid
  - bim_student_feeds
    UL, pk->id, fk->bim
  - bim_marking
    UL, pk->id, fk->bim
 
**/

// Backup the chosen bims
function bim_backup_mods( $bf, $preferences ) {
    global $CFG;
    global $DB;

    $status = true;

    // get the matching bims
    $bims = $DB->get_records( 'bim', 'course', $preferences->backup_course, 'id' );
    
    // if we got something, loop through and backup each one
    if ( $bims ) {
        foreach ( $bims as $bim ) {
            if ( backup_mod_selected( $preferences, 'bim', $bim->id )) {
                $status = bim_backup_one_mod( $bg, $preferences, $bim );
            }
        }
    } 
}

// do one

function bim_backup_one_mod( $bf, $preferences, $bim ) {

    global $CFG;
    global $DB;

    // if given an id, get the record
    if ( is_numeric( $bim ) ) {
        $bim = $DB->get_record( 'bim', 'id', $bim );
    }

    $status = true;

    // Start MOD
    fwrite( $bf, start_tag( "MOD", 3, true ));

    // BIM data
    fwrite( $bf, full_tag( "ID", 4, false, $bim->id ));
    fwrite( $bf, full_tag( "MODTYPE", 4, false, 'bim' ));
    fwrite( $bf, full_tag( "NAME", 4, false, $bim->name ));
    fwrite( $bf, full_tag( "INTRO", 4, false, $bim->intro ));
    fwrite( $bf, full_tag( "INTROFORMAT", 4, false, $bim->introformat ));
    fwrite( $bf, full_tag( "TIMECREATED", 4, false, $bim->timecreated ));
    fwrite( $bf, full_tag( "TIMEMODIFIED", 4, false, $bim->timemodified ));
    fwrite( $bf, full_tag( "REGISTER_FEED", 4, false, $bim->register_feed ));
    fwrite( $bf, full_tag( "MIRROR_FEED", 4, false, $bim->mirror_feed ));
    fwrite( $bf, full_tag( "CHANGE_FEED", 4, false, $bim->change_feed ));
    fwrite( $bf, full_tag( "GRADE_FEED", 4, false, $bim->grade_feed ));

    $status = backup_bim_questions( $bf, $preferences, $bim->id );

    // do the user level data if chosen
    if ( backup_userdata_selected( $preferences, 'bim', $bim->id ) ) {
        $status = backup_bim_group_allocation( $bf, $preferences, $bim->id );
        $status = backup_bim_student_feeds( $bf, $preferences, $bim->id );
        $status = backup_bim_marking( $bf, $preferences, $bim->id );
    }

    //end MOD
    $status = fwrite( $bf, end_tag( "MOD", 3, true ));

    return $status;
}

//************************
// back up bim_questions

function backup_bim_questions( $bf, $preferences, $bim ) {

    global $CFG;
    global $DB;

    $status = true;

    $bim_questions = $DB->get_records( 'bim_questions', 'bim', $bim, 'id' );

    // Are there questions?
    if ( $bim_questions ) {
        $status = fwrite( $bf, start_tag( "QUESTIONS", 4, true ));
 
        // go through each of the questions
        foreach ( $bim_questions as $question ) {
            // start question
            $status = fwrite( $bf, start_tag( "QUESTION", 5, true ));
            // guts
            fwrite( $bf, full_tag( "ID", 6, false, $question->id ));
            fwrite( $bf, full_tag( "TITLE", 6, false, $question->title ));
            fwrite( $bf, full_tag( "BODY", 6, false, $question->body ));
            fwrite( $bf, full_tag( "MIN_MARK", 6, false, $question->min_mark ));
            fwrite( $bf, full_tag( "MAX_MARK", 6, false, $question->max_mark ));

            // end question
            $status = fwrite( $bf, end_tag( "QUESTION", 5, true ));
        }
        
        $status = fwrite( $bf, end_tag( "QUESTIONS", 4, true ));
    }

    return $status;
}

//**********************
// Back up marking/group allocations
function backup_bim_group_allocation( $bf, $preferences, $bim ) {

    global $CFG;
    global $DB;

    $status = true;

    $bim_allocations = $DB->get_records( 'bim_group_allocation', 'bim', $bim, 'id' );

    // Are there questions?
    if ( $bim_allocations) {
        $status = fwrite( $bf, start_tag( "GROUP_ALLOCATION", 4, true ));
 
        // go through each of the questions
        foreach ( $bim_allocations as $allocation ) {
            // start question
            $status = fwrite( $bf, start_tag( "ALLOCATION", 5, true ));
            // guts
            fwrite( $bf, full_tag( "ID", 6, false, $allocation->id ));
            fwrite( $bf, full_tag( "GROUPID", 6, false, $allocation->groupid ));
            fwrite( $bf, full_tag( "USERID", 6, false, $allocation->userid ));

            // end question
            $status = fwrite( $bf, end_tag( "ALLOCATION", 5, true ));
        }
        
        $status = fwrite( $bf, end_tag( "GROUP_ALLOCATION", 4, true ));
    }

    return $status;
}

//****************************
// STUDENT FEEdS

function backup_bim_student_feeds( $bf, $preferences, $bim ) {

    global $CFG;
    global $DB;

    $status = true;

    $bim_student_feeds = $DB->get_records( 'bim_student_feeds', 'bim', $bim, 'id' );

    // Are there questions?
    if ( $bim_student_feeds) {
        $status = fwrite( $bf, start_tag( "STUDENT_FEEDS", 4, true ));
 
        // go through each of the questions
        foreach ( $bim_student_feeds as $feed ) {
            // start question
            $status = fwrite( $bf, start_tag( "STUDENT_FEED", 5, true ));
            // guts
            fwrite( $bf, full_tag( "ID", 6, false, $feed->id ));
            fwrite( $bf, full_tag( "USERID", 6, false, $feed->userid ));
            fwrite( $bf, full_tag( "NUMENTRIES", 6, false, $feed->numentries ));
            fwrite( $bf, full_tag( "LASTPOST", 6, false, $feed->lastpost ));
            fwrite( $bf, full_tag( "BLOGURL", 6, false, $feed->blogurl ));
            fwrite( $bf, full_tag( "FEEDURL", 6, false, $feed->feedurl ));

            // end question
            $status = fwrite( $bf, end_tag( "STUDENT_FEED", 5, true ));
        }
        
        $status = fwrite( $bf, end_tag( "STUDENT_FEEDS", 4, true ));
    }

    return $status;
}

// Save the marking information
function backup_bim_marking( $bf, $preferences, $bim ) {

    global $CFG;
    global $DB;
    $status = true;

    $bim_marking = $DB->get_records( 'bim_marking', 'bim', $bim, 'id' );

    // Are there questions?
    if ( $bim_marking) {
        $status = fwrite( $bf, start_tag( "MARKING", 4, true ));
 
        // go through each of the questions
        foreach ( $bim_marking as $entry ) {
            // start question
            $status = fwrite( $bf, start_tag( "ENTRY", 5, true ));
            // guts
            fwrite( $bf, full_tag( "ID", 6, false, $entry->id ));
            fwrite( $bf, full_tag( "USERID", 6, false, $entry->userid ));
            fwrite( $bf, full_tag( "MARKER", 6, false, $entry->marker ));
            fwrite( $bf, full_tag( "QUESTION", 6, false, $entry->question ));
            fwrite( $bf, full_tag( "MARK", 6, false, $entry->mark ));
            fwrite( $bf, full_tag( "STATUS", 6, false, $entry->status ));
            fwrite( $bf, full_tag( "TIMEMARKED", 6, false, $entry->timemarked ));
            fwrite( $bf, full_tag( "TIMERELEASED", 6, false, $entry->timereleased ));
            fwrite( $bf, full_tag( "LINK", 6, false, $entry->link ));
            fwrite( $bf, full_tag( "TIMEPUBLISHED", 6, false, $entry->timepublished ));
            fwrite( $bf, full_tag( "TITLE", 6, false, $entry->title ));
            fwrite( $bf, full_tag( "POST", 6, false, $entry->post ));
            fwrite( $bf, full_tag( "COMMENTS", 6, false, $entry->comments ));

            // end question
            $status = fwrite( $bf, end_tag( "ENTRY", 5, true ));
        }
        
        $status = fwrite( $bf, end_tag( "MARKING", 4, true ));
    }

    return $status;
}
// Is this where we return the information to backup.php so it
// it knows what options to display?

function bim_check_backup_mods( $course, $user_data=true, 
                                $backup_unique_code, $instances=null ) {

    if ( !empty($instances) && is_array($instances) && count($instances)) {
        $info = array();
        foreach ( $instances as $id => $instance ) {
            $info += bim_check_backup_mods_instances( $instance,
                                                      $backup_unique_code );
        }
        return $info;
    }

    // Get the course data
    //    BIMS => # bim activities
    $info[0][0] = get_string( 'modulepluralname', 'bim' );
    if ( $ids = bim_ids( $course ) ) {
        $info[0][1] = count( $ids );
    } else {
        $info[0][1] = 0;
    } 

    // Now the user data, if requested
    if ( $user_data ) {
        // go through each of the user level tables
        // group_allocation
        $info[1][0] = get_string("group_allocation","forum");
        if ($ids = bim_allocations_ids_by_course( $course )) {
            $info[1][1] = count($ids);
        } else {
            $info[1][1] = 0;
        }

        // student_feeds
        $info[2][0] = get_string( 'student_feeds', 'bim' );
        if ( $ids = bim_feed_ids_by_course( $course ) ) {
            $info[2][1] = count($ids);
        } else {
            $info[2][1] = 0;
        }

        // marking
        $info[3][0] = get_string( 'marking', 'bim' );
        if ( $ids = bim_marking_ids_by_course( $course ) ) {
            $info[3][1] = count($ids);
        } else {
            $info[3][1] = 0;
        }
    } 

    return $info;
}

// Return an array of info (name,value) ?? for an instance??

function bim_check_backup_mods_instances( $instance, $backup_unique_code ) {

    // course data
    $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
    $info[$instance->id.'0'][1] = '';

    // user data if requested
    if ( ! empty( $instances->userdata) ) {
        // one entry for each type of user data

        // group_allocation
        $info[$instance->id.'1'][0] = get_string( 'group_allocation', 'bim' );
        if ( $ids = bim_allocation_ids_by_instance( $instance->id ) ) {
            $info[$instance->id.'1'][1] = count( $ids );
        } else {
            $info[$instance->id.'1'][1]  = 0;
        }
        // student_feeds
        $info[$instance->id.'2'][0] = get_string( 'student_feeds', 'bim' );
        if ( $ids = bim_feed_ids_by_instance( $instance->id ) ) {
            $info[$instance->id.'2'][1] = count( $ids );
        } else {
            $info[$instance->id.'2'][1]  = 0;
        }
        // marking
        $info[$instance->id.'3'][0] = get_string( 'marking', 'bim' );
        if ( $ids = bim_marking_ids_by_instance( $instance->id ) ) {
            $info[$instance->id.'3'][1] = count( $ids );
        } else {
            $info[$instance->id.'3'][1]  = 0;
        }

    }

    return $info;
}

//************** internal functions

// return an array of bim ids
function bim_ids( $course ) {
    global $CFG;
    global $DB;

    return $DB->get_records_sql( 
              "SELECT bim.id,bim.course FROM {$CFG->prefix}bim bim" .
              "WHERE bim.course='$course'" );
}

// return an array of allocations ids
function bim_allocations_ids_by_course( $course ) {
    global $CFG;
    global $DB;

    return $DB->get_records_sql( 
              "SELECT a.id,a.bim " .
                  "FROM {$CFG->prefix}group_allocation a, {$CFG->prefix}bim bim" .
              "WHERE bim.course='$course' AND a.bim=bim.id" );
}

// return an array of allocations ids
function bim_allocations_ids_by_instance( $instanceid ) {
    global $CFG;
    global $DB;

    return $DB->get_records_sql( 
              "SELECT a.id,a.bim " .
                  "FROM {$CFG->prefix}group_allocation a " .
              "WHERE a.bim=$instanceid" );
}

// return an array of feeds ids
function bim_feed_ids_by_course( $course ) {
    global $CFG;
    global $DB;

    return $DB->get_records_sql( 
              "SELECT f.id,f.bim " .
                  "FROM {$CFG->prefix}student_feeds f, {$CFG->prefix}bim bim" .
              "WHERE bim.course='$course' AND f.bim=bim.id" );
}

// return an array of feeds ids
function bim_feed_ids_by_instance( $instanceid ) {
    global $CFG;
    global $DB;

    return $DB->get_records_sql( 
              "SELECT f.id,f.bim " .
                  "FROM {$CFG->prefix}student_feeds f " .
              "WHERE f.bim=$instanceid" );
}


// return an array of markings ids
function bim_marking_ids_by_course( $course ) {
    global $CFG;
    global $DB;

    return $DB->get_records_sql( 
              "SELECT m.id,m.bim " .
                  "FROM {$CFG->prefix}marking m, {$CFG->prefix}bim bim " .
              "WHERE bim.course='$course' AND m.bim=bim.id" );
}

// return an array of markings ids
function bim_marking_ids_by_instance( $instanceid ) {
    global $CFG;
    global $DB;

    return $DB->get_records_sql( 
              "SELECT m.id,m.bim " .
                  "FROM {$CFG->prefix}marking m " .
              "WHERE m.bim=$instanceid" );
}

?>
