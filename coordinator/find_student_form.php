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
 * The form for coordinator to specify what students they are looking for
 *
 * @package mod_bim
 * @copyright 2010 onwards David Jones {@link http://davidtjones.wordpress.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php" );

class find_student_form extends moodleform {
    public function definition() {

        global $COURSE;
        $mform =& $this->_form;

        $id = $this->_customdata['id'];

        // Add hidden fields to get going the right way
        $mform->addElement( 'hidden', 'id', $id );
        $mform->setType( 'id', PARAM_INT );
        $mform->addElement( 'hidden', 'tab', 'find' );
        $mform->setType( 'tab', PARAM_ALPHA);
        $mform->addElement( 'hidden', 'op', 'details' );
        $mform->setType( 'op', PARAM_ALPHA);

        $mform->addElement( 'text', 'student',
                get_string( 'bim_find_text', 'bim' ) );
        $mform->setType( 'student', PARAM_TEXT );
        $mform->addRule( 'student', null, 'required', null, 'client' );

        $mform->addElement( 'submit', 'submitbutton', 'Submit' );
    }

}

