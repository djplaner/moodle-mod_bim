<?php  // $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $

require_once( $CFG->dirroot.'/mod/bim/lib/opml.php' );

/*
 * bim/marker/generateOpml.php
 * - called by marker to generate OPML file for their students
 */

/*
 * bim_generate_opml( $cm, $bim, $userid )
 * - for the current user and BIM, ouput OPML for all their
 *   registered students
 */

function bim_generate_opml( $bim, $cm, $userid ) {
    global $CFG, $COURSE;
    $url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&screen=generateOpml";

    add_to_log( $cm->course, "bim", "generate opml", 
                 "view.php?id=$cm->id&screen=generateOpml",
                "", $cm->id );


    //********* Get data
    // Get the student details
    $student_details = bim_get_markers_students( $bim, $userid );
    // get the student feeds
    $student_ids = array_keys( $student_details );
    $feed_details = bim_get_feed_details( $bim->id, $student_ids );

    // generate the structure for OPML generate
    $struct = Array(
                "head" => Array(
                    "title" => "Your students' feeds for BIM activity " .
                              $bim->name . " from " . $COURSE->fullname
                ),
                "items" => Array()
              );

    // add the items
    foreach ( $feed_details as $feed ) {
        $student = $student_details[$feed->userid];
        $title = "Blog for $student->firstname $student->lastname ($student->username)";
        $item = Array(
                    "text" => $title,
                    "description" => $title,
                    "title" => $title,
                    "htmlUrl" => $feed->blogurl,
                    "xmlUrl" => $feed->feedurl,
                    "type" => "rss",
                );
        $struct["items"][] = $item;
    }

  $string = bim_generate_opml_string( $struct );

  // generate the header necessary to force a download
  header( "Content-Type: application/download\n" );
  $opml_file = clean_filename( "the file.opml" );
  header( "Content-Disposition: attachment; filename=\"$opml_file\"" ); 
  echo $string;
}

?>
