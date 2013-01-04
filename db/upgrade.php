<?php


function xmldb_bim_upgrade($oldversion = 0) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2011013117) {
        // Define field id to be added to bim
        $table = new xmldb_table('bim');
        $field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', null);

        // Conditionally launch add field id
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);

            // ** drop grade_feed
            $old_field = new xmldb_field('grade_feed');

            // Conditionally launch drop field grade
            if ($dbman->field_exists($table, $old_field)) {
                $dbman->drop_field($table, $old_field);
            }
            upgrade_mod_savepoint(true, 2011013117, 'bim');
        } 
    }

    return true;
}
