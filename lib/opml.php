<?php    // $Id

/****************
 * Library of functions for generating OPML files
 */


/*
 * $opml = bim_generate_opml_string( $structure )
 * - given $structure (array of hashes) convert it into
 *   a string in OPML 2.0 format
 */

function bim_generate_opml_string( $structure ) {
    $header_fields = Array( "title", "dateCreated", "dataModified",
                            "ownerName", "ownerEmail" );
    $item_fields = Array( "text", "description", "htmlUrl", "language",
                          "title", "type", "version", "xmlUrl" );

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
