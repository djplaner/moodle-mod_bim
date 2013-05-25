<?php  

//require_once($CFG->libdir.'/filelib.php');
/**
 * Library of functions associated with groups and students/users
 * for bim
 * 
 * bim_get_markers_students
 * bim_get_all_markers_students
 * bim_get_markers_groups
 * bim_get_all_students
 * bim_get_student_details
 * bim_get_all_markers_groups( $bim, $markers_ids )
 * - return array of arrays with ids of markers groups
 */

/*****
 * $array = bim_get_markers_students( $bim, $marker )
 * - return an array of objects containing user information
 *   for all the students associated with $marker (user id)
 */

function bim_get_markers_students( $bim, $marker )
{
    $student_details = array();
    // get list of students
//    $students = get_records_select( "bim_markers_students",
 //                  "course=$course and marker=$marker" );

    // get the groups belonging to the marker
    $groups = get_records_select( "bim_group_allocation",
                     "bim=$bim->id and userid=$marker" );

    // if no groups allocated, no students
    if ( empty( $groups ) ) return $student_details;
    
    $group_ids = array();
    foreach ( $groups as $group )
    {
      $group_ids[] = $group->groupid;
    }
    $group_ids_string = implode( ",", $group_ids );
 
    // now get the list of students from group_members
    $students = get_records_select( "groups_members",
                     "groupid in ( $group_ids_string )" );

    if ( empty( $students )) return $student_details;

    $student_ids = array();
    foreach ( $students as $student )
    {
      $student_ids[] = $student->userid;
    }
    $student_ids_string = implode( ",", $student_ids );
    // get the user details of all the students
    $student_details = get_records_select( "user", 
                        "id in ( $student_ids_string ) " );

    return $student_details;
}

/*****
 * $array = bim_get_all_students( $cm )
 * - return user details for all students in the course
 */

function bim_get_all_students( $cm )
{
  // get list of students in the course
  $context = get_context_instance( CONTEXT_COURSE, $cm->course );
  $students = get_users_by_capability( $context, 'mod/bim:student', 
                      'u.id,u.username,u.firstname,u.lastname,u.email',
                      'u.lastname', '', '', '', '', false, true );
            
  // generate string of ids ready for select
  $ids = array_keys( $students );

  $student_details = Array();
  if ( ! empty ( $ids ) ) {
      $ids_string = implode( ",", $ids );

      // get the user details of all the students
      $student_details = get_records_select( "user", "id in ( $ids_string ) " );
  }

  return $student_details;
}

/****
 * $details = bim_get_student_details( $ids )
 * - given a list of student ids return array of data structures
 *   with information about the students
 */

function bim_get_student_details( $ids )
{
  $ids_string = implode( ",", $ids );

  // get the user details of all the students
  $student_details = get_records_select( "user",
                  "id in ( $ids_string ) " );

  return $student_details;
}

/*****
 * $array = bim_get_all_markers_students( $bim );
 * - for a given course return an array keyed out marker userid
 *   that contains all student details for all markers
 * - also includes user details for the markers
 */

function bim_get_all_markers_students( $bim )
{
    global $CFG;

    // get the ids for the markers
    $groups = get_records_select( "bim_group_allocation",
                     "bim=$bim->id" );
    $sql = "select distinct userid as marker from " .
           "{$CFG->prefix}bim_group_allocation where " .
           "bim=$bim->id";
    $markers = get_records_sql( $sql );
 
    if ( empty( $markers ))
    {
         return Array();
    }
    // get the students for each marker
    foreach ( $markers as $marker )
    {
      $marker->students = bim_get_markers_students( $bim, $marker->marker );
    }

    // add in the marker user details
    $marker_ids = array_keys( $markers );
    $ids_string = implode( ",", $marker_ids );
    $marker_details = get_records_select( "user",
                    "id in ( $ids_string ) " );

    // link the marker_details into the structure being passed back
    foreach ( $marker_ids as $id )
    {
      $markers[$id]->details = $marker_details[$id];
    }
    
    return $markers;
}

/*****
 * $groups = bim_get_all_markers_groups( $bim, $markers_ids )
 * - given a BIM and list of markers ids, get all the
 *   records from bim_group_allocation
 *  - i.e. which groups which markers have been allocated to
 */

function bim_get_all_markers_groups( $bim, $markers_ids )
{
  $ids = implode( ', ', $markers_ids );

  $groups = get_records_select( "bim_group_allocation",
                   "bim=$bim->id and userid in ( $ids )" );

  if ( $groups )
  {
    // add in the empty array for allocations
    foreach ( $groups as $group )
    {
      $group->allocations = array();
    }
  }
  return $groups;
}

?>
