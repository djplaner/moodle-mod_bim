<?php //$Id: register_form.php,v 1.2.2.3 2009/03/19 12:23:11 mudrd8mz Exp $

/**
 *      Define the register form for students
 */

#require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->libdir.'/formslib.php');

class mod_bim_register_form extends moodleform {

    function definition() {
        global $COURSE;
        $mform =& $this->_form;

        $mform->addElement( 'header', 'general', 
                   get_string('bim_please_register_heading', 'bim'));

        $unregistered = get_string( 'bim_please_register_description', 'bim' );

        $mform->addElement('html', $unregistered );

#        $mform->addElement('text' 'blogurl', get_string('fooname', 'foo'),
        $mform->addElement('text', 'blogurl', 'Blog URL', 
                array('size'=>'50','maxlength'=>'255' ));
        $mform->setType( 'blogurl', PARAM_URL );
        $mform->addRule( 'blogurl', null, 'required', null, 'client' );

//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons( true, get_string( 'bim_register','bim'));

    }
}

?>
