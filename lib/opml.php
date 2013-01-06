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
    foreach ($header_fields as $field) {
        if ( array_key_exists( $field, $structure["head"] )) {
            $value = htmlspecialchars( $structure["head"][$field] );
            $opml .= "<$field>$value</$field>";
        }
    }
    $opml .= "</head><body>";

    // add the items (no sub-item suport at this stage)
    foreach ($structure["items"] as $item) {
        $opml .= "<outline ";
        foreach ($item_fields as $field) {
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

