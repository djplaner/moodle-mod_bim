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

    $event = \mod_bim\event\opml_created::create(array(
                     'context' => context_module::instance($cm->id),
                     'objectid' => $cm->id,
    ));
    $event->trigger();

    // Get data
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
    foreach ($feed_details as $feed) {
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
    echo "\n";
    exit;
}

