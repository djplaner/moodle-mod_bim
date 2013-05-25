<?php 

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

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_bim_mod_form extends moodleform_mod {

    function definition() {

        global $COURSE;
        $mform =& $this->_form;

//-------------------------------------------------------------------------------
    /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

    /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('bimname', 'bim'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

    /// Adding the required "intro" field to hold the description of the instance
        $editor_settings = array( 'canUseHtmlEditor'=>'detect',
                   'rows' => 20, 'cols' => 40, 'width' => 0,
                   'height' => 0, 'course' => 0 );

        $mform->addElement('htmleditor', 'intro', get_string('bimintro', 'bim'),
                          $editor_settings);
        $mform->setType('intro', PARAM_RAW);
        $mform->addRule('intro', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton( 'intro', 
                               array( 'intro', 
                               get_string( 'bim_register_feed', 'bim' ), 
                               'bim' ));

    /// Adding "introformat" field
        $mform->addElement('format', 'introformat', get_string('format'));

//-------------------------------------------------------------------------------
    /// Adding the rest of bim settings, spreeading all them into this fieldset
    /// or adding more fieldsets ('header' elements) if needed for better logic


        $mform->addElement('header', 'bimfieldset', get_string('bimfieldset', 'bim'));
        $mform->addElement('advcheckbox', 'register_feed', 
                    get_string('bim_register_feed', 'bim'), '' );
    /*    $mform->addElement('advcheckbox', 'change_feed', 
                    get_string('change_feed', 'bim'), '' ); */
        $mform->addElement('advcheckbox', 'mirror_feed', 
                    get_string('bim_mirror_feed', 'bim'), '' );
        $mform->addElement('advcheckbox', 'grade_feed', 
                    get_string('bim_grade_feed', 'bim'), '' );

        $mform->setHelpButton( 'register_feed', 
                               array( 'register_feed', 
                               get_string( 'bim_register_feed', 'bim' ), 
                               'bim' ));
        $mform->setHelpButton( 'mirror_feed', array( 'mirror_feed', get_string( 'bim_mirror_feed', 'bim' ), 'bim' ));
        $mform->setHelpButton( 'grade_feed', array( 'grade_feed', get_string( 'bim_grade_feed', 'bim' ), 'bim' ));
//        $mform->setHelpButton( 'change_feed', array( 'change_feed', get_string( 'change_feed', 'bim' ), 'bim' ));

//-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $features = new stdClass;
        $features->groups = false;
        $this->standard_coursemodule_elements( $features);
//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();

    }
}

?>
