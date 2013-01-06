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

/**
 * This file defines the main bim configuration form
 * It uses the standard core Moodle (>1.8) formslib. For
 * more info about them, please visit:
 *
 * http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * The form must provide support for, at least these fields:
 *   - name: text element of 64cc max
 *
 * Also, it's usual to use these fields:
 *   - intro: one htmlarea element to describe the activity
 *            (will be showed in the list of activities of
 *             bim type (index.php) and in the header
 *             of the bim main page (view.php).
 *   - introformat: The format used to write the contents
 *             of the intro field. It automatically defaults
 *             to HTML when the htmleditor is used and can be
 *             manually selected if the htmleditor is not used
 *             (standard formats are: MOODLE, HTML, PLAIN, MARKDOWN)
 *             See lib/weblib.php Constants and the format_text()
 *             function for more info
 */

defined( 'MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_bim_mod_form extends moodleform_mod {

    public function definition() {

        global $COURSE;
        $mform =& $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('bimname', 'bim'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the required "intro" field to hold the description of the instance
        $editor_settings = array( 'canUseHtmlEditor'=>'detect',
                'rows' => 20, 'cols' => 40, 'width' => 0,
                'height' => 0, 'course' => 0 );

        //        $mform->addElement('htmleditor', 'intro', get_string('bimintro', 'bim'),
        //                         $editor_settings);
        //      $mform->setType('intro', PARAM_RAW);
        //        $mform->addRule('intro', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton( 'intro', 'bimintro', 'bim' );

        $this->add_intro_editor();

        // Adding the rest of bim settings, spreeading all them into this fieldset
        // or adding more fieldsets ('header' elements) if needed for better logic

        $mform->addElement('header', 'bimfieldset', get_string('bimfieldset', 'bim'));
        $mform->addElement('advcheckbox', 'register_feed',
                get_string('bim_register_feed', 'bim'), '' );
        /*    $mform->addElement('advcheckbox', 'change_feed',
              get_string('change_feed', 'bim'), '' ); */
        $mform->addElement('advcheckbox', 'mirror_feed',
                get_string('bim_mirror_feed', 'bim'), '' );
        /*        $mform->addElement('advcheckbox', 'grade_feed',
                  get_string('bim_grade_feed', 'bim'), '' ); */

        $mform->addHelpButton( 'register_feed', 'bim_register_feed', 'bim' );
        $mform->addHelpButton( 'mirror_feed', 'bim_mirror_feed', 'bim' );
        $mform->addHelpButton( 'grade_feed', 'bim_grade_feed', 'bim' );

        $this->standard_grading_coursemodule_elements();

        // add standard elements, common to all modules
        $features = new stdClass;
        $features->groups = false;
        $this->standard_coursemodule_elements( $features);

        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
}

