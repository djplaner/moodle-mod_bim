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

require_once($CFG->libdir.'/formslib.php');

class mod_bim_register_form extends moodleform {

    public function definition() {
        global $COURSE;
        $mform =& $this->_form;

        $mform->addElement( 'header', 'general',
                get_string('bim_please_register_heading', 'bim'));

        $unregistered = get_string( 'bim_please_register_description', 'bim' );

        $mform->addElement('html', $unregistered );

        $mform->addElement('text', 'blogurl', 'Blog URL', array('size'=>'50',
                'maxlength'=>'255' ));
        $mform->setType( 'blogurl', PARAM_TEXT );
        $mform->addRule( 'blogurl', null, 'required', null, 'client' );

        // add standard buttons, common to all modules
        $this->add_action_buttons( true, get_string( 'bim_register', 'bim'));

    }
}

