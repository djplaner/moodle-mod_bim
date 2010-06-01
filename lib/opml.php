<?php    // $Id

/****************
 * Library of functions for generating OPML files
 */


/*
 * $opml = bim_generate_opml( $structure )
 * - given $structure (array of hashes) convert it into
 *   a string in OPML 2.0 format
 */

function bim_generate_opml( $structure ) {
    $opml = ""; 
    $header_fields = Array( "title", "dateCreated", "dataModified",
                            "ownerName", "ownerEmail" );
    $item_fields = Array( "text", "description", "htmlUrl", "language",
                          "title", "type", "version", "xmlUrl" );
// testing

    $structure =
        Array(
            head =>
                Array(
                    title => "The title of the OPML file",
                ),
            items =>
                Array(
                    0 => Array(
                        text => "David's website",
                        description => "A personal blog",
                        htmlUrl => "http://davidtjones.wordpress.com/",
                        xmlUrl => "http://davidtjones.wordpress.com/feed/",
                        type => "rss",
                        title => "Weblog of (a) David Jones"
                    ),
                    1 => Array(
                        text => "Indicators",
                        description => "Indicators site",
                        htmlUrl => "http://indicatorsproject.wordpress.com/",
                        xmlUrl => "http://indicatorsproject.wordpress.com/feed/",
                        type => "rss",
                        title => "Indicators project"
                    )
            )
        );

//    print_r( $structure );

    // generate the intro
    $opml =<<<EOF
<?xml version="1.0" encoding="ISO-8859-1"?>
<opml version="2.0">
EOF;

    // add the header
    $opml .= "<head>";
    foreach ( $header_fields as $field ) {
        if ( array_key_exists( $field, $structure["head"] )) {
            $value = htmlspecialchars( $structure["head"][$field] );
            $opml .= "<$field>$value</$field>";
        }
    }
    $opml .= "</head><body>";

    // add the items (no sub-item suport at this stage)
    foreach( $structure["items"] as $item ) {
        $opml .= "<outline ";
        foreach ( $item_fields as $field ) {
            if ( array_key_exists( $field, $item )) {
                $value = htmlspecialchars( $item[$field] );
                $opml .= "$field=\"$value\" ";
            }
        }
        $opml .= "/>";
    }
   
    $opml .= "</body></opml>";              
    return $opml;
}

?>
