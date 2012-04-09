<?php

/****
 * allocate_markers.php
 * - alow coordiantor to allocate markers
 */

/***
 * bim_allocate_markers( $bim, $cm )
 * - handle the form for allocating markers
 */

require_once($CFG->dirroot.'/lib/grouplib.php' );
require_once($CFG->dirroot.'/mod/bim/coordinator/marker_allocation_form.php' );

function bim_allocate_markers( $bim, $cm, $userid )
{
  global $CFG, $OUTPUT;

  // **** SET UP THE DATA
  // get all the groups for the course
  $groups = groups_get_all_groups( $cm->course );
  // get all the users who can mark/coordinator this activity
  $context = get_context_instance( CONTEXT_MODULE, $cm->id );
  $markers = get_users_by_capability( $context, 
                array( 'mod/bim:marker', 'mod/bim:coordinator' ),
                'u.id,u.firstname,u.lastname', 'u.lastname',
                '', '', '', '', false, true );

  // error if no markers
  if ( empty( $markers )) {
        echo $OUTPUT->heading( get_string( 'bim_allocate_marker_nomarkers_heading','bim'), 2 );
        print_string( 'bim_allocate_marker_nomarkers_description', 'bim' );
        return;
  }
  $markers_ids = array_keys( $markers );

  $markers_allocations = bim_get_all_markers_groups( $bim, $markers_ids );
  // connect the groups for each marker into $markers->allocations
  // markers_allocations will be empty initially
  if ( $markers_allocations )
  {
    foreach ( $markers_allocations as $allocation )
    {
      $markers[$allocation->userid]->allocations[$allocation->groupid] = 
                   $allocation->groupid;
    }
  }

  // If there are no groups, display error and no form
  if ( empty( $groups ))
  {
    echo $OUTPUT->heading( get_string( 'bim_allocate_marker_nogroups_heading','bim'), 2 );
    print_string( 'bim_allocate_marker_nogroups_description', 'bim' );
    return;
  }

  // *** PROCESS AND DISPLAY
  // create the form
  $allocate_form = new marker_allocation_form( 'view.php', 
                         array( 'groups' => $groups, 'markers' => $markers,
                                'id' => $cm->id ) );

  // process it
  if ( ! $allocate_form->is_submitted() )
  {
    add_to_log( $cm->course, "bim", "markers allocate",
                 "view.php?id=$cm->id&tab=markers",
                "List all", $cm->id );

    $heading = get_string('bim_allocate_marker_heading', 'bim' );
    echo $OUTPUT->heading( $heading, 2 );
    print_string('bim_allocate_marker_description', 'bim' );

    $toform = new StdClass;
    // for each marker set up $toform based on their current allocations
    foreach ( $markers as $marker )
    {
      // only proceed if there are groups currently allocated in the dbase
      if ( isset( $marker->allocations ) )
      {
        $id = "groups_".$marker->id;
        $toform->$id = array_keys( $marker->allocations );
        $g = $toform->$id;
      }
    }
    $allocate_form->set_data( $toform );
    $allocate_form->display();
  }
  else if ( $allocate_form->is_cancelled() )
  {
    // what to do here??
  }
  else if ( $fromform = $allocate_form->get_data() )
  {
    process_markers_form( $markers, $fromform, $groups, $bim, $cm );

    redirect( "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&tab=markers" );
    // do the redirect?
  }
}

/**
 * process_markers_form( $markers, $fromform, $groups, $bim )
 * - process the updated form and delete and add new entries
 *   to bim_group_allocation as required
 */

function process_markers_form( $markers, $fromform, $groups, $bim, $cm )
{
  global $DB, $OUTPUT;

  echo $OUTPUT->box_start( "noticebox boxwidthnormal" );
  $heading = get_string( "bim_group_allocations_heading", "bim" );
  echo $OUTPUT->heading( $heading, 1 );
//  print "<h1>Updating group allocations</h1>";

  $change = false;

  echo '<ul>' ;
  foreach ( $markers as $marker )
  {
    $id = "groups_".$marker->id;
    // Start with anything that might be added, this means there
    // has to be something in the form for this marker
    if ( isset( $fromform->$id ))
    {
      $form_groups = $fromform->$id;
    // loop through each group id and see if it already exists
      foreach ( $form_groups as $group )
      {
        // if not already set, then add it
        if ( ! isset( $marker->allocations[$group] ))
        {
          $insert = new StdClass;
          $insert->bim = $bim->id;
          $insert->userid = $marker->id;
          $insert->groupid = $group;
          if ( $DB->insert_record( "bim_group_allocation", $insert, true ) > 0 )
          {
 //'<li>Added group "'. $groups[$group]->name . '" for ' .
            $a = new StdClass;
            $a->group = $groups[$group]->name;
            $a->marker = "$marker->firstname $marker->lastname";
            print_string( 'bim_group_allocations_added', 'bim', $a );

            $change = true;
          }
        }
      }
    }
    // Now, what about current dbase allocations that aren't
    // in the form for the marker. i.e. we need to delete them

    // loop through each group allocated to marker in database
    // - if there are any allocations at all
    if ( isset( $marker->allocations ))
    {
      $marker_dbase_groups = array_keys( $marker->allocations );
      foreach ( $marker_dbase_groups as $group )
      {
        // if it doesn't exist in the form, delete it
        // nothing at all in the form for this marker 
        if ( ! isset( $fromform->$id ) )
        {
          if ( $DB->delete_records( "bim_group_allocation", 
                    array("bim" => $bim->id, "groupid" => $group,   
                          "userid"=> $marker->id)))
          {
            $a = new StdClass;
            $a->group = $groups[$group]->name;
            $a->marker = "$marker->firstname $marker->lastname";
            print_string( 'bim_group_allocations_removed', 'bim', $a );
            $change = true;
          }
        }
        else // something in form, check for group
        {
          // http://kevingessner.com/nihilartikel/fast-array-membership-in-php/
          $flip = array_flip( $fromform->$id );
          if ( ! isset( $flip[$group] ))
          {
            if ( $DB->delete_records( "bim_group_allocation", 
                    array("bim"=> $bim->id, "groupid"=> $group, 
                          "userid"=>$marker->id)))
            {
              $a = new StdClass;
              $a->group = $groups[$group]->name;
              $a->marker = "$marker->firstname $marker->lastname";
              print_string( 'bim_group_allocations_removed', 'bim', $a );
              $change = true;
            }
          }
        }
      }
    }
  }
  if ( ! $change )
  {
    print_string( 'bim_group_allocations_none', 'bim' );
  } 
  else
  {
    add_to_log( $cm->course, "bim", "markers allocate",
                 "view.php?id=$cm->id&tab=markers",
                "Change in allocation", $cm->id );
  }

  echo $OUTPUT->box_end();
}




?>
