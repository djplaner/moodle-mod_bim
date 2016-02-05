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

/****************
 * Library of functions for manipulating the student feeds
 *
 * $feed = get_local_bim_feed( $course, $bim, $username )
 * - given course, bim and username IDs parse
 *   the feed located locally at
 *       $CFG->dataroo/$courseid/$bim/$username.xml
 * - return false otherwise
 */

// $origin = $CFG->dataroot.'/'.$courseid.'/'.$file;

/*require_once($CFG->dirrott.'/mod/bim/lib.php');
  require_once('lib.php');*/
// require_once($CFG->dirroot.'/mod/bim/lib/simplepie/simplepie.inc');
require_once( $CFG->libdir.'/simplepie/moodle_simplepie.php' );

// define some error ids

define( "BIM_FEED_INVALID_URL", 1);
define( "BIM_FEED_NO_RETRIEVE_URL", 2 );
define( "BIM_FEED_NO_FEEDS", 3 );
define( "BIM_FEED_NO_LINKS", 4 );
define( "BIM_FEED_WRONG_URL", 5 );
define( "BIM_FEED_TIMEOUT", 6 );

/*
 * bim_process_feed( $cm, $bim, $feedurl )
 * - given the details of cm, bim and student
 *   get a copy of their RSS file and then seek to process it
 * - i.e. check for updates and try to add any new posts
 *   into the bim_marking table
 */

function bim_process_feed( $bim, $student_feed, $questions ) {
    global $CFG;
    global $DB;
    if ( $student_feed->userid == "" ) {
        return;
    }

    // check cache directory exists
    $dir = $CFG->dataroot . "/" . $bim->course ."/moddata/$bim->id";
    if ( ! check_dir_exists( $dir, true, true ) ) {
        mtrace( "Unable to create directory $dir" );
        return false;
    }

    // get the RSS file
    //    $feed = new SimplePie();
    $feed = new moodle_simplepie();
    $feed->set_feed_url( $student_feed->feedurl );
    $feed->set_timeout( 18000 );
    $feed->set_autodiscovery_level(SIMPLEPIE_LOCATOR_ALL);
    $feed->init();

    if ( $feed->error() ) {
        mtrace( "Error getting $student_feed->feedurl" );
        return false;
    }

    // get the users marking details
    // - create an array keyed on link element of marking details
    $marking_details = bim_get_marking_details( $bim->id,
            Array( $student_feed->userid ) );
    $details_link = Array();

    if ( ! empty( $marking_details )) {
        foreach ($marking_details as $detail) {
            $details_link[$detail->link] = $detail;
        }
    }
    $unanswered_qs = bim_get_unanswered( $marking_details, $questions );
    foreach ($feed->get_items() as $item) {
        // Only process this item, if it isn't already in bim_marking
        $link = $item->get_permalink();

        if ( ! isset( $details_link[$link]) ) {
            $title = bim_truncate( $item->get_title() );

            $raw_content = $item->get_content();
            $content = iconv( "ISO-8859-1", "UTF-8//IGNORE", $raw_content );

            // create most of a new entry
            $entry = new StdClass;
            $entry->id = null;
            $entry->bim = $bim->id;
            $entry->userid = $student_feed->userid;
            $entry->marker = null;
            $entry->question = null;
            $entry->mark = null;
            $entry->status = "Unallocated";
            $entry->timepublished = $item->get_date( "U" );
            $entry->timemarked = null;
            $entry->timereleased = null;
            $entry->link = $link;
            $entry->title = $title;
            $entry->post = $content;
            $entry->comments = null;

            if ( ! empty( $questions ) ) {
                // loop through each of the unallocated questions
                foreach ($unanswered_qs as $unanswered_q) {
                    if ( bim_check_post( $title, $content,
                                $questions[$unanswered_q] )) {
                        // the question now answered, remove from unanswered
                        $entry->question = $unanswered_q;
                        $entry->status = "Submitted";

                        // the question isn't unanswered now
                        unset( $unanswered_qs[$unanswered_q] );

                        break;
                    } // bim_check_post
                } // loop through unallocated questions
            } // empty questions

            // insert the new entry
            if ( ! $DB->insert_record( "bim_marking", $entry ) ) {
                mtrace( get_string( 'bim_process_feed_error', 'bim',
                            $entry->link ) );
            } else {
                // time to update the lastpost field in bim_student_feeds
                if ( $student_feed->lastpost < $entry->timepublished ) {
                    $student_feed->lastpost = $entry->timepublished;
                }
                $student_feed->numentries++;
                //              $safe = addslashes_object( $student_feed );
                if ( ! $DB->update_record( 'bim_student_feeds', $student_feed ) ) {
                    mtrace( "unable to update record for feed" );
                }
            } // couldn't insert into bim_marking
        }
    } // looping through all items
}

/*
 * bim_check_post( $item, $question )
 * - given a SimplePie item
 * - return TRUE if the item seems to match in some
 *   way the question
 * - Current search replaces any whitespace in the question title
 *   with .*
 */

function bim_check_post( $title, $content, $question ) {
    // replace white space with any non a-z
    $q_title = $question->title;
    $q_title = preg_replace( "/ +/", "[^a-z0-9]*", $q_title );

    return preg_match( "!$q_title!i", $title ) ||
        preg_match( "!$q_title!i", $content );;
}

/*
 * bim_process_unallocated( $bim, $student_feed, $questions )
 * - look through all of the student's unallocated items in the
 *   bim_marking table and process them again
 * - just in case some new questions have been added, existing
 *   ones deleted etc.
 */

function bim_process_unallocated( $bim, $student_feed, $questions ) {
    global $DB;

    // get the marking_details for the student
    $marking_details = bim_get_marking_details( $bim->id,
            Array( $student_feed->userid => $student_feed->userid ) );
    // get the unanswered questions
    $unanswered_qs = bim_get_unanswered( $marking_details, $questions );

    // go through each unallocated question
    foreach ($marking_details as $detail) {
        if ( $detail->status == "Unallocated" ) {
            // go through the unanswered questions, does it match now?
            foreach ($unanswered_qs as $unanswered_q) {
                if ( bim_check_post( $detail->title, $detail->post,
                            $questions[$unanswered_q] )) {
                    $detail->question = $unanswered_q;
                    $detail->status = "Submitted";
                    $detail->timereleased = 0;

                    //          $safe = addslashes_object( $detail );
                    $DB->update_record( "bim_marking", $detail );
                    unset( $unanswered_qs[$unanswered_q] );

                    // update the database with the new entry now
                    break;
                } // bim_check_post
            }
        }
    }
}

/****
 * $feed_urlbim_get_feed_url( $fromform, $cm, $bim )
 * - given the form elements submitted by the student and the
 * - perform various checks and see if we can get a feedurl
 * - return $fromform
 * - if no errors, then feedurl will have url for feed
 * - if errors, then feedurl will be an INT error number and
 *  YEA, I know this is ugly.
 */

function bim_get_feed_url( $fromform, $cm, $bim ) {
    global $CFG;

    // Remove white space from the URL
    $fromform->blogurl = trim($fromform->blogurl);

    // do some pre-checks on the URL
    if ( ! bim_is_valid_url( $fromform->blogurl )) {
        $fromform->feedurl = BIM_FEED_INVALID_URL;
        $fromform->error = get_string( 'register_error_invalid_url', 'bim');
        return $fromform;
    }

    $dir = $CFG->dataroot . "/".$cm->course."/moddata/" .$bim->id;

    if ( ! check_dir_exists( $dir, true, true ) ) {
        mtrace( "Unable to create directory $dir" );
        return false;
    }

    $feed = new moodle_simplepie();
    $feed->set_feed_url( $fromform->blogurl );
    $feed->set_timeout( 18000 );
    $feed->enable_cache( true );
    $feed->set_cache_location( $dir );
    $feed->init();

    // check if any errors getting the file
    if ( $feed->error() ) {
        $fromform->error = $feed->error();
        if ( preg_match( "!^A feed could not be found at !", $fromform->error )) {
            $fromform->feedurl = BIM_FEED_NO_LINKS;
        } else if ( preg_match( "!time out after!", $fromform->error )) {
            $fromform->feedurl = BIM_FEED_TIMEOUT;
        } else {
            $fromform->feedurl = BIM_FEED_NO_RETRIEVE_URL;
        }
        return $fromform;
    }

    $fromform->blogurl = $feed->get_permalink();
    $fromform->feedurl = $feed->subscribe_url();

    // do additional checks on common mistake URLs
    $error = bim_check_wrong_urls( $fromform->blogurl, $fromform->feedurl );
    if ( $error != "" ) {
        $fromform->feedurl = BIM_FEED_WRONG_URL;
        $fromform->error = $error;
        return $fromform;
    }

    // getting here means success, get the date published for lastpost
    $item = $feed->get_item();
    // situations where there is a feed, but no items
    if ( $item ) {
        $fromform->lastpost = $item->get_date( "U" );
    } else {
        $fromform->lastpost = "";
    }

    return $fromform;
}

/*
 * bim_is_valid_url( $url )
 * - return true if valie
 *   Taken from http://www.phpcentral.com/208-url-validation-php.html
 */

function bim_is_valid_url($url) {
    return preg_match('|^http(s)?://[a-z0-9-]+(.[\[\]a-z0-9-]+)*(:[0-9\[\]]+)?(/.*\[*\]*)?$|i',
            $url);
}

/*
 * bim_display_error( $error, $fromform )
 * - given a particular error ID, display appropriate error message
 * - $fromform contains information about what was submitted
 * - log the error
 */

function bim_display_error( $error, $fromform, $cm ) {
    global $OUTPUT;

    if ( $error == BIM_FEED_INVALID_URL ) {
        // Appears this type of error logging doesn't fit with the 
        // conception of Logging2. https://docs.moodle.org/dev/Logging_2#Related
        // Removing for now.
        //add_to_log( $cm->course, "bim", "registration error",
        //        "view.php?id=$cm->id",
        //        "$fromform->blogurl Invalid URL", $cm->id );

        echo $OUTPUT->heading( 
            get_string( 'bim_register_invalid_url_heading', 'bim' ), 2, left );
        print_string( 'bim_register_invalid_url_description', 'bim',
                $fromform->blogurl );
        return 1;
    }
    if ( $error == BIM_FEED_NO_RETRIEVE_URL ) {
        // add_to_log( $cm->course, "bim", "registration error",
        //         "view.php?id=$cm->id",
        //         "$fromform->blogurl no retrieve", $cm->id );
        echo $OUTPUT->heading( 
            get_string( 'bim_register_no_retrieve_heading', 'bim' ), 2, "left" );
        $a = new StdClass();
        $a->url = $fromform->blogurl;
        $a->error = $fromform->error;
        print_string( 'bim_register_no_retrieve_description', 'bim', $a );

        return 1;
    }
    if ( $error == BIM_FEED_NO_LINKS ) {
        // add_to_log( $cm->course, "bim", "registration error",
        //         "view.php?id=$cm->id",
        //         "$fromform->blogurl no feed links", $cm->id );
        echo $OUTPUT->heading( 
                get_string( 'bim_register_nolinks_heading', 'bim' ), 2, "left" );
        print_string( 'bim_register_nolinks_description', 'bim',
                $fromform->blogurl );
        return 1;
    }
    if ( $error == BIM_FEED_WRONG_URL ) {
        // add_to_log( $cm->course, "bim", "registration error",
        //         "view.php?id=$cm->id",
        //         "$fromform->blogurl wrong url", $cm->id );
        echo $OUTPUT->heading( 
            get_string( 'bim_register_wrong_url_heading', 'bim' ), 2, "left" );
        echo $fromform->error;
        return 1;
    }
    if ( $error == BIM_FEED_TIMEOUT ) {
        // add_to_log( $cm->course, "bim", "registration error",
        //         "view.php?id=$cm->id",
        //         "$fromform->blogurl timeout", $cm->id );
        echo $OUTPUT->heading( 
                get_string( 'bim_register_timeout_heading', 'bim' ), 2, "left" );
        $a = new StdClass();
        $a->url = $fromform->blogurl;
        $a->error = $fromform->error;
        print_string( 'bim_register_timeout_description', 'bim', $a );
        return 1;
    }
}

/*
 * $error = bim_check_wrong_URLs( blogurl, feedurl )
 * - given the blog and feed url submitted by the students
 *   perform various checks to exclude some known common mistakes
 * - Return a string containing a description of the error if any found
 * - Return "" if none
 * - e.g.
 *   Tried to registere the home page for Wordpress
 *   Tried to register a URL partway through the registeration process
 *
 */

function bim_check_wrong_urls( $blog_url, $feed_url ) {
    if ( preg_match( '!http://en.blog.wordpress.com!i', $blog_url )) {
        return get_string( 'bim_wrong_url_wordpress', 'bim', $blog_url );
    }

    // check either blog or feed urls to see if the user has copied
    // the URL before the registration process is complete
    //   i.e.  http://en.wordpress.com.*
    if ( preg_match( '!http://en.wordpress.com!i', $blog_url ) ) {
        return get_string( 'bim_wrong_url_notfinished', 'bim', $blog_url );
    }
    if ( preg_match( '!http://en.wordpress.com!i', $feed_url )) {
        return get_string( 'bim_wrong_feed_notfinished', 'bim', $feed_url );
    }
    return "";
}

/*
 * $content = bim_clean_content( $content )
 * - do some additional cleaning of the content of a blog post.
 *   Mostly removing "special characters" from Word copy and paste
 * - Yep, it's a kludge
 */

function bim_clean_content( $content ) {
    $post = clean_text( $content );
    return $post;
}


