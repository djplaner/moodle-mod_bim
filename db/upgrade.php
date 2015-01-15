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

    // the wrong change to mark type
/*    if ($oldversion < 2011013119) {


        // Changing type of field mark on table bim_marking to int
        $table = new xmldb_table('bim_marking');
        $field = new xmldb_field('mark', XMLDB_TYPE_INTEGER, '6', null, null, null, null, 'question');

        // Launch change of type for field mark
        $dbman->change_field_type($table, $field);

        // bim savepoint reached
        upgrade_mod_savepoint(true, 2011013119, 'bim');
    } */

    // the right? change to mark type
    //if ($oldversion < 2011013120) {
    if ($oldversion < 2015011501) {

        // Changing type of field mark on table bim_marking to number
        $table = new xmldb_table('bim_marking');
        $field = new xmldb_field('mark', XMLDB_TYPE_NUMBER, '6, 2', null, null, null, null, 'question');
        // Launch change of type for field mark
        $dbman->change_field_type($table, $field);

        //** also allow bim_questions min_mark and max_mark be number as well
        $table = new xmldb_table('bim_questions');
        $field = new xmldb_field('min_mark', XMLDB_TYPE_NUMBER, '6, 2', null, null, null, null, 'question');
        $dbman->change_field_type($table, $field);
        $field = new xmldb_field('max_mark', XMLDB_TYPE_NUMBER, '6, 2', null, null, null, null, 'question');
        $dbman->change_field_type($table, $field);

        // bim savepoint reached
        upgrade_mod_savepoint(true, 2015011501, 'bim');
    }
    return true;
}

