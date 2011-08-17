<?php    // $Id

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
require_once($CFG->dirroot.'/mod/bim/lib/simplepie/simplepie.inc');

/** define some error ids **/

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

function bim_process_feed( $bim, $student_feed, $questions )
{
    global $CFG;

    if ( $student_feed->userid == "" )
       return;

    // check cache directory exists
    $dir = $CFG->dataroot . "/" . $bim->course ."/moddata/$bim->id";
    if ( ! check_dir_exists( $dir, true, true ) ) {
          mtrace( "Unable to create directory $dir" );
          return false;
    }

    // get the RSS file 
    $feed = new SimplePie();
    $feed->set_feed_url( $student_feed->feedurl );
    $feed->enable_cache( true );
    $feed->set_cache_location( $dir );
    $feed->init();

    if ( $feed->error() ) {
        mtrace( "Error getting $student_feed->feedurl" );
        return false;
    }
  
    // get the users marking details
    // - create an array keyed on link element of marking details
    $marking_details = bim_get_marking_details( $bim->id, 
                   Array( $student_feed->userid => $student_feed->userid ) );
    $details_link = Array();
 
    if ( ! empty( $marking_details )) {
        foreach ( $marking_details as $detail ) {
            $details_link[$detail->link] = $detail;
        }
    }

  $unanswered_qs = bim_get_unanswered( $marking_details, $questions );

  foreach ( $feed->get_items() as $item ) {
      // Only process this item, if it isn't already in bim_marking
      $link = $item->get_permalink();

      if ( ! isset( $details_link[$link]) ) {
          $title = bim_truncate( $item->get_title() );

          $raw_content = $item->get_content();
          $content = iconv( "ISO-8859-1", "UTF-8//IGNORE", $raw_content );

# FOLLOWING is old KLUDGE, will need to be removed if the above works
#          $content = normalize_special_characters( $item->get_content() );
#          $content = bim_clean_content( $content );
# KLUDGE: simple test to find out which special characters are
#  causing problems
#      $contenta = getCharArray2( $content );
#
#      foreach ( $contenta as $char )
#      {
#        if ( ord( $char ) > 128 ) echo "<h4>";
#        echo "$char .. " . ord( $char ) . "<br />";
#        if ( ord( $char ) > 128 ) echo "</h4>";
#      }
  
          // create most of a new entry
          $entry = new StdClass;
          $entry->id = NULL; 
          $entry->bim = $bim->id;
          $entry->userid = $student_feed->userid;
          $entry->marker = NULL; 
          $entry->question = NULL; 
          $entry->mark = NULL; 
          $entry->status = "Unallocated";
          $entry->timepublished = $item->get_date( "U" );
          $entry->timemarked = NULL; 
          $entry->timereleased = NULL; 
          $entry->link = $link;
          $entry->title = $title;
          $entry->post = $content;
          $entry->comments = NULL ;

          if ( ! empty( $questions ) ) {
              // loop through each of the unallocated questions
              foreach ( $unanswered_qs as $unanswered_q )
              {
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
          $safe = addslashes_object( $entry );
          if ( ! insert_record( "bim_marking", $safe ) ) {
              mtrace( get_string( 'bim_process_feed_error', 'bim', 
                            $entry->link ) );
          } else { 
              // time to update the lastpost field in bim_student_feeds
              if ( $student_feed->lastpost < $entry->timepublished ) {
                 $student_feed->lastpost = $entry->timepublished;
              }
              $student_feed->numentries++;
              $safe = addslashes_object( $student_feed );
              if ( ! update_record( 'bim_student_feeds', $student_feed ) ) {
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

function bim_check_post( $title, $content, $question )
{
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

function bim_process_unallocated( $bim, $student_feed, $questions )
{
  // get the marking_details for the student
  $marking_details = bim_get_marking_details( $bim->id, 
                   Array( $student_feed->userid => $student_feed->userid ) );
  // get the unanswered questions
  $unanswered_qs = bim_get_unanswered( $marking_details, $questions );

  // go through each unallocated question
  foreach ( $marking_details as $detail )
  {
    if ( $detail->status == "Unallocated" )
    {
      // go through the unanswered questions, does it match now?
      foreach ( $unanswered_qs as $unanswered_q )
      {
        if ( bim_check_post( $detail->title, $detail->post, 
                                 $questions[$unanswered_q] ))
        {
          $detail->question = $unanswered_q; 
          $detail->status = "Submitted";
          $detail->timereleased = 0; 
          
          $safe = addslashes_object( $detail );
          update_record( "bim_marking", $safe );
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
 #   $cm and $bim
 * - perform various checks and see if we can get a feedurl
 * - return $fromform
 * - if no errors, then feedurl will have url for feed
 * - if errors, then feedurl will be an INT error number and
 #     fromform->error will be the error string given by simplepie
 *  YEA, I know this is ugly. 
 */

function bim_get_feed_url( $fromform, $cm, $bim )
{
  global $CFG;

  //** do some pre-checks on the URL
  if ( ! bim_is_valid_url( $fromform->blogurl )) {
      $fromform->feedurl = BIM_FEED_INVALID_URL;
      $fromform->error = get_string( 'register_error_invalid_url','bim');
      return $fromform;
  }

  $dir = $CFG->dataroot . "/".$cm->course."/moddata/" .$bim->id;

    if ( ! check_dir_exists( $dir, true, true ) ) {
          mtrace( "Unable to create directory $dir" );
          return false;
    }

  $feed = new SimplePie();
  $feed->set_feed_url( $fromform->blogurl );
  $feed->enable_cache( true );
  $feed->set_cache_location( $dir );
  $feed->init();

  // check if any errors getting the file
  if ( $feed->error() )
  {
    $fromform->error = $feed->error();
    if ( preg_match( "!^A feed could not be found at !", $fromform->error )) {
        $fromform->feedurl = BIM_FEED_NO_LINKS ;
    }
    else if ( preg_match( "!time out after!", $fromform->error )) {
        $fromform->feedurl = BIM_FEED_TIMEOUT;
    }
    else {
        $fromform->feedurl = BIM_FEED_NO_RETRIEVE_URL;
    }
    return $fromform;
  }
 
  $fromform->blogurl = $feed->get_permalink();
  $fromform->feedurl = $feed->subscribe_url() ;

  // do additional checks on common mistake URLs
  $error = bim_check_wrong_urls( $fromform->blogurl, $fromform->feedurl );
  if ( $error != "" ) { 
      $fromform->feedurl = BIM_FEED_WRONG_URL;
      $fromform->error = $error;
      return $fromform;
  }

  // getting here means success, get the date published for lastpost
  $item = $feed->get_item();
  $fromform->lastpost = $item->get_date( "U" );

  return $fromform;
}

/*
 * bim_is_valid_url( $url )
 * - return true if valie  
 *   Taken from http://www.phpcentral.com/208-url-validation-php.html
 */

function bim_is_valid_url($url)
{
 return preg_match('|^http(s)?://[a-z0-9-]+(.[\[\]a-z0-9-]+)*(:[0-9\[\]]+)?(/.*\[*\]*)?$|i', 
                     $url);
} 

/*
 * bim_display_error( $error, $fromform )
 * - given a particular error ID, display appropriate error message
 * - $fromform contains information about what was submitted
 * - log the error
 */

function bim_display_error( $error, $fromform, $cm )
{
    if ( $error == BIM_FEED_INVALID_URL ) {
        add_to_log( $cm->course, "bim", "registration error", 
                    "view.php?id=$cm->id",
                    "$fromform->blogurl Invalid URL", $cm->id );

        print_heading( get_string( 'bim_register_invalid_url_heading', 'bim' ),
                       "left", 2 );
        print_string( 'bim_register_invalid_url_description', 'bim',
                       $fromform->blogurl );
        return 1;
    }
    if ( $error == BIM_FEED_NO_RETRIEVE_URL ) {
       add_to_log( $cm->course, "bim", "registration error", 
                   "view.php?id=$cm->id", 
                   "$fromform->blogurl no retrieve", $cm->id );
        print_heading( get_string( 'bim_register_no_retrieve_heading', 'bim' ),
                         "left", 2 );
        $a = new StdClass();
        $a->url = $fromform->blogurl;
        $a->error = $fromform->error;
        print_string( 'bim_register_no_retrieve_description', 'bim', $a );

        return 1;
    }
    if ( $error == BIM_FEED_NO_LINKS ) {
        add_to_log( $cm->course, "bim", "registration error", 
                    "view.php?id=$cm->id",
                    "$fromform->blogurl no feed links", $cm->id );
        print_heading( get_string( 'bim_register_nolinks_heading', 'bim' ),
                       "left", 2 );
        print_string( 'bim_register_nolinks_description', 'bim',
                      $fromform->blogurl );
        return 1;
     }
     if ( $error == BIM_FEED_WRONG_URL ) {
        add_to_log( $cm->course, "bim", "registration error", 
                    "view.php?id=$cm->id",
                    "$fromform->blogurl wrong url", $cm->id );
        print_heading( get_string( 'bim_register_wrong_url_heading', 'bim' ),
                       "left", 2 );
        echo $fromform->error;
        return 1;
     }
     if ( $error == BIM_FEED_TIMEOUT ) {
        add_to_log( $cm->course, "bim", "registration error", 
                    "view.php?id=$cm->id",
                    "$fromform->blogurl timeout", $cm->id );
        print_heading( get_string( 'bim_register_timeout_heading', 'bim' ),
                       "left", 2 );
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
        return get_string( 'bim_wrong_feed_notfinished','bim', $feed_url );
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

//  $content = htmlentities( $content, ENT_COMPAT, "UTF-8" );
  // thanks http://www.toao.net/48-replacing-smart-quotes-and-em-dashes-in-mysql
  // First, replace UTF-8 characters
  $content = str_replace( array( "\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", 
                                "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", 
                                "\xe2\x80\xa6"),
                          array("'", "'", '"', '"', '-', '--', '...'), $content);

  // Next, replace their Windows-1252 equivalents.
  $content = str_replace( array(chr(145), chr(146), chr(147), chr(148), chr(150),
                                chr(151), chr(133)),
                          array("'", "'", '"', '"', '-', '--', '...'), $content);


  $badchr = array (
         chr(153),
         chr(0xe2) . chr(0x80) . chr(0x98),
         chr(0xe2) . chr(0x80) . chr(0xa6),
         chr(187),
         chr(239),
         chr(191),
         chr(132), chr(162), chr(196), chr(129), chr(148), chr(195),
        'â€œ',  // left side double smart quote
        'â€'.chr(157),  // right side double smart quote
  //      'â€˜',  // left side single smart quote
   //     'â€™',  // right side single smart quote
        'â€¦',  // elipsis
        'â€”',  // em dash
        'â€“',  // en dash

        '&#8217;', // single quote
        '&#8211;', // dash

        chr(189),
        chr(194),
        chr(160),
        chr(226),
        chr(156),
        chr(128),
        chr(157),
        chr(147),
        chr(153),
        chr(152)
    );

    $goodchr    = array(
        "**'++",
        '&lsquo;',
        '...',
        '',
        '', '', '', '', '', '', '', '',
        '"', '"', 
//        "'", 
 //       "'", 
        "...", "-", "-",
        '\'', '-',
        ' ',
        ' ', ' ', "'", '', '', '', '', '', '', '', 
        '', '', '', ''  );

    $post = str_replace($badchr, $goodchr, $content);

    return $post;
}

function normalize_special_characters( $str )
{
    # Quotes cleanup
    # - we're talking multi-byte here, so use mb_ereg..
    $str = mb_ereg_replace( chr(ord("`")), "'", $str );        # `
    $str = mb_ereg_replace( chr(ord("´")), "'", $str );        # ´
    $str = mb_ereg_replace( chr(ord("„")), ",", $str );        # „
    $str = mb_ereg_replace( chr(ord("`")), "'", $str );        # `
    $str = mb_ereg_replace( chr(ord("´")), "'", $str );        # ´
    $str = mb_ereg_replace( chr(ord("“")), "\"", $str );        # “
    $str = mb_ereg_replace( chr(ord("”")), "\"", $str );        # ”
    $str = mb_ereg_replace( chr(ord("´")), "'", $str );        # ´

    $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                                'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                                'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                                'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                                'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
    $str = strtr( $str, $unwanted_array );

    # Bullets, dashes, and trademarks
    # - this ain't multi-byte, standard ascii, so no need for mb_...
    $str = ereg_replace( chr(149), "&#8226;", $str );    # bullet •
    $str = ereg_replace( chr(183), "&#8226;", $str );    # middot but treat as bullet •
    $str = ereg_replace( chr(150), "&ndash;", $str );    # en dash
    $str = ereg_replace( chr(151), "&mdash;", $str );    # em dash
//    $str = mb_ereg_replace( chr(153), "&#8482;", $str );    # trademark
    $str = ereg_replace( chr(169), "&copy;", $str );    # copyright mark
    $str = ereg_replace( chr(174), "&reg;", $str );        # registration mark

    return $str;
}

# support function for the kludge to diagnose problems
# with special characters

function getCharArray2 ($jstring)
{
  $len = mb_strlen ($jstring, 'UTF-8');
  if (mb_strlen ($jstring, 'UTF-8') == 0)
    return array();

  while ($len) {
    $ret[]  = mb_substr($jstring,0,1,"UTF-8");
    $jstring = mb_substr($jstring,1,$len,"UTF-8");
    $len = mb_strlen($jstring);
  }
  return $ret;
}

?>
