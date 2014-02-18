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
 * Provide the form used to allocate markers to groups of students
 *
 * @package mod_bim
 * @copyright 2010 onwards David Jones {@link http://davidtjones.wordpress.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php" );

class marker_allocation_form extends moodleform {

    public function definition() {

        global $COURSE;
        $mform =& $this->_form;

        // additional data for form construction
        $groups = $this->_customdata['groups'];
        $markers = $this->_customdata['markers'];
        $id = $this->_customdata['id'];

        // Add hidden fields to get going the right way
        $mform->addElement( 'hidden', 'id', $id );
        $mform->setType( 'id', PARAM_INT );
        $mform->setType( 'tab', PARAM_ALPHA );
        $mform->addElement( 'hidden', 'tab', 'markers' );
        $mform->setType( 'id', PARAM_ALPHA );

        // create array of groupnames for the multi-select element
        $group_names = array();

        foreach ($groups as $group) {
            $group_names[$group->id] = $group->name;
        }
        // Generate essentially the same information per marker
        //  Marker name and role      List of groups
        foreach ($markers as $marker) {
            // the markers id is used to unique identify each
            // form element per marker
            $marker_id = $marker->id;

            $marker_group = array();
            $name = '<strong>' . $marker->firstname . ' ' .
                $marker->lastname . '</strong>';
            $select =& $mform->addElement( 'select', 'groups_'.$marker_id,
                    "$name", $group_names );
            $select->setMultiple( true );

            $mform->addElement( 'submit', 'submitbutton', 'Submit' );
        }
    }

}

