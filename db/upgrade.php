<?php  //$Id: upgrade.php,v 1.2 2007/08/08 22:36:54 stronk7 Exp $

// This file keeps track of upgrades to
// the bim module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_bim_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;

    $result = true;

 /// *********** NEED TO START USING THIS PROPERLY
/*    if ($result && $oldversion < 2011013113) {
        /// Changing list of values (enum) of field status on table bim_marking to 'Submitted', 'Marked', 'Released', 'Unallocated', 'Suspended', 'Deleted', 'Testing'
        $table = new XMLDBTable('bim_marking');
        $field = new XMLDBField('status');
        $field->setAttributes(XMLDB_TYPE_CHAR, '12', null, null, null, XMLDB_ENUM, array('Submitted', 'Marked', 'Released', 'Unallocated', 'Suspended', 'Deleted'), 'Unallocated', 'mark');

        /// Launch change of list of values for field status
        $result = $result && change_field_enum($table, $field);
     }
*/
    return $result;
}

?>
