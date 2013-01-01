<?php  // $Id: lib.php,v 1.7.2.5 2009/04/22 21:30:57 skodak Exp $

defined('MOODLE_INTERNAL') || die();

// NOT SURE IF THIS SHOULD BE commented out DJ
/*require_once($CFG->libdir.'/filelib.php');
  ** the following (locallib.php) should never be here **
require_once($CFG->dirroot.'/mod/bim/lib/locallib.php'); */

/**
 * Library of functions and constants for module bim
 * 
 *    **** CORE FUNCTIONS ****
 * bim_add_instance
 * bim_update_instance
 * bim_delete_instance
 * bim_cron
 *
 *    **** BIM FUNCTIONS ****
 * bim_feed_exists
 * bim_get_mirrored
 * bim_get_student_feeds
 * bim_get_feed_details
 * bim_generate_marking_stats
 * bim_generate_student_results
 */

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function bim_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:         return true;
        case FEATURE_GRADE_HAS_GRADE:   return true;
        case FEATURE_BACKUP_MOODLE2:    return true;
        default:                        return null;
    }
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $bim An object from the form in mod_form.php
 * @return int The id of the newly inserted bim record
 */
//function bim_add_instance($bim) {
function bim_add_instance(stdClass $bim, mod_bim_mod_form $mform = null) {
    global $DB;

    $bim->timecreated = time();

    # You may have to add extra stuff in here #

    if ( ! $bim->id = $DB->insert_record('bim', $bim) ) {
        return false;
    }

    if ( $bim->grade_feed == 1 ) {
        bim_grade_item_update( $bim );
    }

    return $bim->id;
}


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $bim An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function bim_update_instance(stdClass $bim, mod_bim_mod_form $mform = null) {
    global $DB;

    $bim->timemodified = time();
    $bim->id = $bim->instance;

    // - what about removing grades?
    if ( ! $DB->update_record('bim', $bim) ) {
        error( 'Cannot update bim' );
    }

    if ( $bim->grade_feed == 1 ) {
        bim_grade_item_update( $bim );
    } 
    // What if grading is turned off and grade was set,
    // should we delete the item?  Or leave that to the
    // user, using the gradebook?

    return true;
}


/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function bim_delete_instance($id) {
    global $DB;

    if (! $bim = $DB->get_record('bim', array('id' => $id))) {
        return false;
    }

    $result = true;

    // bim_group_allocation
    if ( ! $DB->delete_records( 'bim_group_allocation', array('bim'=>$bim->id))) {
       $result = false;
    }
    // bim_questions
    if ( ! $DB->delete_records( 'bim_questions', array('bim'=>$bim->id))) {
       $result = false;
    }
    // bim_marking
    if ( ! $DB->delete_records( 'bim_marking',array('bim'=>$bim->id))) {
       $result = false;
    }
    // bim_student_feeds
    if ( ! $DB->delete_records( 'bim_student_feeds',array('bim'=>$bim->id))) {
       $result = false;
    }
    // bim
    if ( !$DB->delete_records('bim',array('id'=>$bim->id))) {
       $result = false;
    }

    // gradebook
    bim_grade_item_delete( $bim );

    return $result;
}


/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 */
function bim_user_outline($course, $user, $mod, $bim) {
    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}


/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function bim_user_complete($course, $user, $mod, $bim) {
    return '';
}


/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in bim activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function bim_print_recent_activity($course, $isteacher, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

function bim_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {}



/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function bim_cron () {
 /*   global $CFG;

    // get list of bims currently being mirrored
    $mirrored = bim_get_mirrored();

    // loop through each one
    foreach ( $mirrored as $bim )
    {
      // make sure directory exists for caching of file
      $dir = $CFG->dataroot . "/" . $bim->course . "/moddata/" . $bim->id;
      if ( ! check_dir_exists( $dir, true, true ) ) {
          mtrace( "Unable to create directory $dir" );
          return false;
      }

      // get list of student feeds for the bim
      $students_feeds = bim_get_student_feeds( $bim->id );
      $questions = bim_get_question_hash( $bim->id );

      if ( ! empty( $students_feeds ) )  {
          foreach ( $students_feeds as $student_feed )
          {
              bim_process_feed( $bim, $student_feed, $questions );
              // *** do a check on unallocated questions to see if
              // new questions or other changes can allocate them
              bim_process_unallocated( $bim, $student_feed, $questions );
          }
      } // empty $student_feeds
  //    }
    }*/
    return true;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of bim. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $bimid ID of an instance of this module
 * @return mixed boolean/array of students
 */
function bim_get_participants($bimid) {
    return false;
}

function bim_get_extra_capabilities() {
    return array();
}



/**
 * This function returns if a scale is being used by one bim
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $bimid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 */
function bim_scale_used($bimid, $scaleid) {
    $return = false;

    //$rec = get_record("bim","id","$bimid","scale","-$scaleid");
    //
    //if (!empty($rec) && !empty($scaleid)) {
    //    $return = true;
    //}

    return $return;
}


/**
 * Checks if scale is being used by any instance of bim.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any bim
 */
function bim_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('bim', 'grade', -$scaleid)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give newmodule instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $newmodule instance object with extra cmidnumber and modname property
 * @return void
 */
function bim_grade_item_update(stdClass $bim, $grades=NULL) {
    global $CFG;

    if ( !function_exists('grade_update')) {
        require_once($CFG->libdir.'/gradelib.php');
    }

#print "<h1> bim </h1>";
#print_r( $bim );
    /** @example */
    $item = array();
    $item['itemname'] = clean_param($bim->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    #$item['grademax']  = $bim->grade;
    $item['grademax']  = 10;
    $item['grademin']  = 0;

    grade_update('mod/bim', $bim->course, 'mod', 'bim', $bim->id, 0, $grades, $item);
}

/**
 * Update newmodule grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $newmodule instance object with extra cmidnumber and modname p
roperty
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function bim_update_grades(stdClass $bim, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $grades = array(); // populate array of grade objects indexed by userid

    grade_update('mod/bim', $bim->course, 'mod', 'bim', $bim->id, 0, $grades);
}


/**
 * Execute post-install custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function bim_install() {
    return true;
}


/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function bim_uninstall() {
    return true;
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function bim_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * Serves the files from the newmodule file areas
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return void this should never return to the caller
 */
function bim_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding newmodule nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the newmodule module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function bim_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the newmodule settings
 *
 * This function is called when the context for the page is a newmodule module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $newmodulenode {@link navigation_node}
 */
function bim_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $newmodulenode=null) {
}

/**
 * Delete the grade item for a given BIM
 */

function bim_grade_item_delete( $bim ) {
    global $CFG;
    require_once( $CFG->libdir.'/gradelib.php' );

    return grade_update('mod/bim', $bim->course, 'mod', 'bim', $bim->id,
                         0, NULL, array('deleted'=>1) );
}

?>
