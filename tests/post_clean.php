<?php

print "<h1> hello </h1>";

require_once( dirname(dirname(dirname(dirname(__FILE__)))).'/config.php' );
require_once($CFG->dirroot.'/mod/bim/lib/simplepie/simplepie.inc');

// get all registered blog urls

//$feeds = get_records_select( "bim_student_feeds", "bim=1" );

$feeds = array(
'http://zonazhao.wordpress.com/',
'http://quigleyascqu.wordpress.com/',
'http://neilo2c.wordpress.com/',
'http://snkgoat.wordpress.com/',
'http://unknowla.wordpress.com/',
'http://leggomyeggo.wordpress.com/',
'http://and0smith.wordpress.com/',
'http://sitarih.wordpress.com/',
'http://robkropp.wordpress.com/',
'http://brizziechap.wordpress.com/',
'http://coops73.wordpress.com/',
'http://angad30.wordpress.com/',
'http://fergusom1.wordpress.com/',
'http://jimlin.wordpress.com/',
'http://pathologicalprocrastinator.wordpress.com/',
'http://rudicqu.wordpress.com/',
'http://chiku333.wordpress.com/',
'http://ambuthoughts.wordpress.com/',
'http://habeebkhaan.wordpress.com/',
'http://praj1986.wordpress.com/',
'http://vinu007.wordpress.com/',
'http://nurul83.wordpress.com/',
'http://ani090.wordpress.com/',
'http://jingpingzhang.wordpress.com/',
'http://tabletalk1.wordpress.com/',
'http://sari31.wordpress.com/',
'http://jind86.wordpress.com/',
'http://sazea.wordpress.com/',
'http://rajeeni.wordpress.com/',
'http://balu8787.wordpress.com/',
'http://prabh86.wordpress.com/',
'http://pink.wordpress.com/',
'http://benquine.wordpress.com/',
'http://giri12.wordpress.com/',
'http://lily985.wordpress.com/',
'http://preet285.wordpress.com/',
'http://sruhani.wordpress.com/',
'http://vik86.wordpress.com/',
'http://lucimallick.wordpress.com/',
'http://duochjag.wordpress.com/',
'http://crossaction.wordpress.com/',
'http://raheelskhan.wordpress.com/',
'http://vipulgurjar.wordpress.com/',
'http://patelbh.wordpress.com/',
'http://pratigya999.wordpress.com/',
'http://venatil.wordpress.com/',
'http://erka26.wordpress.com/',
'http://yeterulan.wordpress.com/',
'http://georgenmei.wordpress.com/',
'http://dieldrin.wordpress.com/',
'http://luciabuzek.wordpress.com/',
'http://reet2010blog.wordpress.com/',
'http://nikrich5.wordpress.com/',
'http://mehmul1453.wordpress.com/',
'http://stalinj.wordpress.com/',
'http://cawilso1.wordpress.com/',
'http://suj28.wordpress.com/',
'http://chutimatoon.wordpress.com/',
'http://corollacool.wordpress.com/',
'http://ruchal.wordpress.com/',
'http://npurnama.wordpress.com/',
'http://fatimaeve.wordpress.com/',
'http://s0178225.wordpress.com/',
'http://oldstudent3.wordpress.com/',
'http://zainshaikhh.wordpress.com/',
'http://harmansatija.wordpress.com/',
'http://annabelami.wordpress.com/',
'http://imkhan.wordpress.com/',
'http://waranchalee.wordpress.com/',
'http://dutrutai.wordpress.com/',
'http://s0182995.wordpress.com/',
'http://tekr.wordpress.com/',
'http://alrahahlehblogs.wordpress.com/',
'http://kaisun.wordpress.com/',
'http://yogid.wordpress.com/',
'http://salmamithani.wordpress.com/',
'http://rohansblog.wordpress.com/',
'http://contactkhalid1.wordpress.com/',
'http://kinanti007.wordpress.com/',
'http://rajavijayalakshmi.wordpress.com/',
'http://azadbalochistan.wordpress.com/',
'http://endlesssalman.wordpress.com/',
'http://bingbongbi.wordpress.com/',
'http://shannonnjohnny.wordpress.com/',
'http://cmateuso.wordpress.com/',
'http://jameshsieh.wordpress.com/',
'http://s0131698.wordpress.com/',
'http://angel3x.wordpress.com/',
'http://kirangill.wordpress.com/',
'http://lebbe.wordpress.com/',
'http://sajee2.wordpress.com/',
'http://melissali10.wordpress.com/',
'http://nhosn147.wordpress.com/',
'http://ranakuli.wordpress.com/',
'http://sathish123.wordpress.com/',
'http://wangzhou86.wordpress.com/',
'http://lawsonacqu.wordpress.com/',
'http://lele1027.wordpress.com/',
'http://amrajkaur.wordpress.com/',
'http://angkorwu.wordpress.com/',
'http://ogomes.wordpress.com/',
'http://xup6m6m06.wordpress.com/',
'http://pan1986.wordpress.com/',
'http://mses.wordpress.com/',
'http://poonely1985.wordpress.com/',
'http://vartazar.wordpress.com/',
'http://deepa1811.wordpress.com/',
'http://hari03.wordpress.com/',
'http://geoffwb.wordpress.com/',
'http://ongee.wordpress.com/',
'http://wangmanyu0068.wordpress.com/',
'http://angel3x.wordpress.com/',
'http://yolanda2010.wordpress.com/'
 );

//print_object( $feeds );


foreach ( $feeds as $feed )
{
//  $feed = $feeds[16];
  //$feed = $feeds[63];
//  $feed = $feeds[36];
  $feed = $feeds[53];
  print "<h1> feed is " . $feed . "**********</h1>";
  $pie = get_pie( $feed );

  foreach ( $pie->get_items() as $item ) {
     $link = $item->get_permalink() ;
     $title = $item->get_title();
     $content = $item->get_content() ;
print "<strong>before</strong> $content<br />";
$content = clean_post( $content );
print "<strong>after</strong> $content<br />";

  $c_array = str_split( $content );

     $count = 0;
     foreach ( $c_array as $c ) {
//print "c is $c<br />";
        $ord = ord( $c );
        if ( $ord > 127 ) {
           print "$count) ord is $ord c is $c<br />";
        }
        $count++;
     }
     $entry = new StdClass;
     $entry->id = NULL;
     $entry->bim = 1;
     $entry->userid = 17012;
     $entry->marker = NULL;
     $entry->question = NULL;
     $entry->mark = NULL;
     $entry->status = "Unallocated";
     $entry->timepublished = $item->get_date( "U" );
     $entry->timemarked = NULL;
     $entry->link = $link;
     $entry->title = $title;
     $entry->post = $content;
     $entry->comments = NULL ;

     $safe = addslashes_object( $entry );
     $id = insert_record( "bim_marking", $safe ) ;
print "<h3> id is $id </h3>"; 
     // remove it if ok
     if ( $id > 0 ) {
       delete_records( 'bim_marking', 'id', $id );
     }
  }
die;
}


function get_pie( $feed )
{
    $pie = new SimplePie();
    $pie->set_feed_url( $feed );
    $pie->enable_cache( false );
    $pie->init();

    return $pie;
}

function clean_post( $post )
{
    $badchr        = array(
/*        'â€œ',  // left side double smart quote
        'â€'.chr(157),  // right side double smart quote
        'â€˜',  // left side single smart quote
        'â€™',  // right side single smart quote
        'â€¦',  // elipsis
        'â€”',  // em dash
        'â€“',  // en dash*/
        
        '&#8217;', // single quote
        '&#8211;', // dash

 /*       chr(149),
        chr(150),
        chr(151),
        chr(153),
        chr(169),
        chr(174), */

        chr(194),
        chr(160),
        chr(226),
        chr(156),
        chr(128),
        chr(157),
        chr(147),
        chr(152),
        chr(153)
    );
       
  $goodchr    = array(//'"', '"', "&&'", "**'", "...", "-", "-",
                     '\'', '-',
//                     '****', '&#8226;', '&ndash;', '&mdash;', '&#8482;', '&copy;', '&ref;',
         ' ', ' ', '\'', '', '', '', '', '','' );

  $post = str_replace($badchr, $goodchr, $post);

  return $post;
}

?>
