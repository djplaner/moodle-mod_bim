<?php




// run all the restore procedures

function bim_restore_mods( $mod, $restore ) {

    global $CFG;

  $CFG->debugdisplay = true;

    $status = true;

    // get record from backup ids
    $data = backup_getid( $restore->backup_unique_code, $mod->modtype, $mod->id);

    if ( $data ) {
        // get the xmlized object
        $info = $data->info;
        //Now, build the BIM record structure
        $bim->course = $restore->course_id;
        $bim->name = backup_todb( $info['MOD']['#']['NAME']['0']['#'] );
        $bim->intro = backup_todb( $info['MOD']['#']['INTRO']['0']['#'] );
        $bim->introformat = backup_todb( $info['MOD']['#']['INTROFORMAT']['0']['#'] );
        $bim->timecreated = backup_todb( $info['MOD']['#']['TIMECREATED']['0']['#'] );
        $bim->timemodified = backup_todb( $info['MOD']['#']['TIMEMODIFIED']['0']['#'] );
        $bim->register_feed = backup_todb( $info['MOD']['#']['REGISTER_FEED']['0']['#'] );
        $bim->mirror_feed = backup_todb( $info['MOD']['#']['MIRROR_FEED']['0']['#'] );
        $bim->change_feed = backup_todb( $info['MOD']['#']['CHANGE_FEED']['0']['#'] );
        $bim->grade_feed = backup_todb( $info['MOD']['#']['GRADE_FEED']['0']['#'] );

        // stick in the dBase
        $newid = insert_record( 'bim', $bim );
        // Doing some output?
        if ( ! defined( 'RESTORE_SILENTLY' ) ) {
            echo "<li>".get_string("modulename",'bim')." \"".
                format_string( stripslashes( $bim->name), true). "\"</li>";
        }
        backup_flush( 300 );

        if ( $newid ) {
print '<h1> Starting object </h1>';
print_object( $bim );
            backup_putid( $restore->backup_unique_code, $mod->modtype,
                          $mod->id, $newid );
print "<h2> starting questions </h2>";
print "..got new id $newid**<br />";
            $status = bim_questions_restore_mods( $newid, $info, $restore );

            if ( restore_userdata_selected( $restore, 'bim', $mod->id ) ){
                // group allocation
                if ( $status ) {
print "<h2> starting allocations</h2>";
print "..got questions and status is $status**<br />";
                    $status = bim_allocations_restore_mods( 
                                $newid, $info, $restore );
                } else { echo 'No restore questions mod<br />'; }
                // student feed
                if ( $status ) {
print "<h2>start feeds</h2>";
print "..got allocations and status is $status**<br />";
                    $status = bim_feeds_restore_mods( $newid, $info, $restore );
                }
                // marking
                if ( $status ) {
print "<h2>start marking</h2>";
print "..got feeds and status is $status**<br />";
                    $status = bim_marking_restore_mods( $newid, $info, $restore );
print "..got marking and status is $status**<br />";
                }
            } 
        } else {
echo 'No insert bim<br />';
           $status = false;
        }
    }
    return $status;
}


// Restore the questions data

function bim_questions_restore_mods( $bimid, $info, $restore ) {

    global $CFG;

    $status = true;

    $questions = $info['MOD']['#']['QUESTIONS']['0']['#']['QUESTION'];

    // loop over the questions
    for ( $i=0; $i<sizeof($questions); $i++ ) {
        $q_info = $questions[$i];

        // get the old ID
        $oldid = backup_todb( $q_info['#']['ID']['0']['#'] );

        // create the object ready to insert
        $question->bim = $bimid;
        $question->title = backup_todb( $q_info['#']['TITLE']['0']['#'] );
        $question->body =  backup_todb( $q_info['#']['BODY']['0']['#'] );
        $question->min_mark =  backup_todb( $q_info['#']['MIN_MARK']['0']['#'] );
        $question->max_mark =  backup_todb( $q_info['#']['MAX_MARK']['0']['#'] );

        // insert it
        $newid = insert_record( 'bim_questions', $question );

        // do some output essentially a slowly growing list of full stops
        if (($i+1) % 50 == 0) {
            if (!defined('RESTORE_SILENTLY')) {
                echo ".";
                if (($i+1) % 1000 == 0) {
                    echo "<br />";
                }
            }
            backup_flush(300);
        }

        if ( $newid ) {
print "<strong>Saving</strong>: bim_questions *$oldid* bcoming *$newid*<br />";
            backup_putid( $restore->backup_unique_code, "bim_questions", 
                          $oldid, $newid );
        } else {
            $status = false;
echo 'No insert question<br />';
        }
    }
    return $status;
}


// Restore the allocations data

function bim_allocations_restore_mods( $bimid, $info, $restore ) {

    global $CFG;

    $status = true;

    $allocations = $info['MOD']['#']['GROUP_ALLOCATION']['0']['#']['ALLOCATION'];

    // loop over the allocations
    for ( $i=0; $i<sizeof($allocations); $i++ ) {
        $a_info = $allocations[$i];
            traverse_xmlize($a_info);                                        
                         //Debug
            print_object ($GLOBALS['traverse_array']);                         
                         //Debug
            $GLOBALS['traverse_array']="";                                     
                         //Debug

        // get the old ID
        $oldid = backup_todb( $a_info['#']['ID']['0']['#'] );

        // create the object ready to insert
        $allocation->bim = $bimid;
        $allocation->groupid = backup_todb( $a_info['#']['GROUPID']['0']['#'] );
        $allocation->userid =  backup_todb( $a_info['#']['USERID']['0']['#'] );

        // time to do some re-coding and checks
        $toinsert = true;

        // recode the userid 
        $user = backup_getid( $restore->backup_unique_code, 'user', 
                                $allocation->userid );
        if ( $user ) {
            $allocation->userid = $user->new_id;
        } else {
echo 'Error with getting user id<br />';
            $toinsert = false;
        }

        $group = restore_group_getid( $restore, $allocation->groupid );
        if ( $group ) {
            $allocation->groupid = $group->new_id;
        } else {
echo 'Error with getting group id<br />';
            $toinsert = false;
        }

        // insert it
        $newid = 0;
        if ( $toinsert ) {
            $newid = insert_record( 'bim_group_allocation', $allocation );
        }

        // do some output essentially a slowly growing list of full stops
        if (($i+1) % 50 == 0) {
            if (!defined('RESTORE_SILENTLY')) {
                echo ".";
                if (($i+1) % 1000 == 0) {
                    echo "<br />";
                }
            }
            backup_flush(300);
        }

        if ( $newid ) {
            backup_putid( $restore->backup_unique_code, "bim_group_allocation", 
                          $oldid, $newid );
        } else {
            $status = false;
echo 'Error with inserting<br />';
        }
    }
    return $status;
}

// Restore the feeds data

function bim_feeds_restore_mods( $bimid, $info, $restore ) {

    global $CFG;

    $status = true;

    $feeds = $info['MOD']['#']['STUDENT_FEEDS']['0']['#']['STUDENT_FEED'];

    // loop over the feeds
    for ( $i=0; $i<sizeof($feeds); $i++ ) {
        $a_info = $feeds[$i];

        // get the old ID
        $oldid = backup_todb( $a_info['#']['ID']['0']['#'] );

        // create the object ready to insert
        $feed->bim = $bimid;
        $feed->userid =  backup_todb( $a_info['#']['USERID']['0']['#'] );
        $feed->numentries =  backup_todb( $a_info['#']['NUMENTRIES']['0']['#'] );
        $feed->lastpost =  backup_todb( $a_info['#']['LASTPOST']['0']['#'] );
        $feed->blogurl =  backup_todb( $a_info['#']['BLOGURL']['0']['#'] );
        $feed->feedurl =  backup_todb( $a_info['#']['FEEDURL']['0']['#'] );

        // time to do some re-coding and checks
        $toinsert = true;

        // recode the userid 
        $user = backup_getid( $restore->backup_unique_code, 'user', 
                                $feed->userid );
        if ( $user ) {
            $feed->userid = $user->new_id;
        } else {
            $toinsert = false;
        }

        // insert it
        $newid = 0;
        if ( $toinsert ) {
            $newid = insert_record( 'bim_student_feeds', $feed );
        }

        // do some output essentially a slowly growing list of full stops
        if (($i+1) % 50 == 0) {
            if (!defined('RESTORE_SILENTLY')) {
                echo ".";
                if (($i+1) % 1000 == 0) {
                    echo "<br />";
                }
            }
            backup_flush(300);
        }

        if ( $newid ) {
            backup_putid( $restore->backup_unique_code, "bim_student_feed", 
                          $oldid, $newid );
        } else {
            $status = false;
        }
    }
    return $status;
}

// Restore the marking data

function bim_marking_restore_mods( $bimid, $info, $restore ) {

    global $CFG;

    $status = true;

    $marking = $info['MOD']['#']['MARKING']['0']['#']['ENTRY'];

    // loop over the marking
    for ( $i=0; $i<sizeof($marking); $i++ ) {
        $a_info = $marking[$i];

        // get the old ID
        $oldid = backup_todb( $a_info['#']['ID']['0']['#'] );

        // create the object ready to insert
        $mark->bim = $bimid;
        $mark->userid =  backup_todb( $a_info['#']['USERID']['0']['#'] );
        $mark->marker =  backup_todb( $a_info['#']['MARKER']['0']['#'] );
        $mark->question =  backup_todb( $a_info['#']['QUESTION']['0']['#'] );
        $mark->mark =  backup_todb( $a_info['#']['MARK']['0']['#'] );
        $mark->status =  backup_todb( $a_info['#']['STATUS']['0']['#'] );
        $mark->timemarked =  backup_todb( $a_info['#']['TIMEMARKED']['0']['#'] );
        $mark->timereleased =  backup_todb( $a_info['#']['TIMERELEASED']['0']['#'] );
        $mark->link =  backup_todb( $a_info['#']['LINK']['0']['#'] );
        $mark->timepublished =  backup_todb( $a_info['#']['TIMEPUBLISHED']['0']['#'] );
        $mark->title =  backup_todb( $a_info['#']['TITLE']['0']['#'] );
        $mark->post =  backup_todb( $a_info['#']['POST']['0']['#'] );
        $mark->comments =  backup_todb( $a_info['#']['COMMENTS']['0']['#'] );

        // time to do some re-coding and checks
        $toinsert = true;

        //  RE-CODING
        // user 
        $user = backup_getid( $restore->backup_unique_code, 'user', 
                                $mark->userid );
print "    got user id for $mark->userid of $user->new_id**<br />";
        if ( $user ) {
            $mark->userid = $user->new_id;
        } else {
            $toinsert = false;
        }
        // marker
        // if marker is 0, don't try to get id
        if ( $mark->marker != 0 ) {
            $marker = backup_getid( $restore->backup_unique_code, 'user', 
                                    $mark->marker );
print "    got marker id for " .$mark->marker." of ". $marker->new_id ."**<br />";
            if ( $marker ) {
                $mark->marker = $marker->new_id;
            } else {
                $toinsert = false;
            }
        }
        // question
        // if question is 0, we want to stay as 0 (i.e. unallocated)
        if ( $mark->question != 0 ) {
print "    ... question is not 0<br />";
            $question = backup_getid($restore->backup_unique_code,'bim_questions', 
                                      $mark->question );
print "    got qustion id for " . $mark->question . " of " . $question->new_id . "**<br />";
            if ( $question ) {
                $mark->question = $question->new_id;
            } else {
print_object( $question );
                 $toinsert = false;
            }
        }

        // insert it
        $newid = 0;
        if ( $toinsert ) {
            $newid = insert_record( 'bim_marking', $mark );
        }
print "    ... INSERTED OBJECT new id is $newid**<br />";
if ( $newid == 0 ) {
    print_object( $mark );
}

        // do some output essentially a slowly growing list of full stops
        if (($i+1) % 50 == 0) {
            if (!defined('RESTORE_SILENTLY')) {
                echo ".";
                if (($i+1) % 1000 == 0) {
                    echo "<br />";
                }
            }
            backup_flush(300);
        }
        if ( $newid ) {
            backup_putid( $restore->backup_unique_code, "bim_marking", 
                          $oldid, $newid );
        } else {
            $status = false;
        }
    }
    return $status;
}

?>
