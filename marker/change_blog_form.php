<?php 

/**
 *      Define the register form for students
 */

#require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->libdir.'/formslib.php');

class change_blog_form extends moodleform {

    function definition() {
        global $COURSE;
        $mform =& $this->_form;

        // get parameters passed
        $student = $this->_customdata['student'];
        $id = $this->_customdata['id'];

        // set hidden parameters
        $mform->addElement( 'hidden', 'id', $id );
        $mform->setType( 'id', PARAM_INT );
        $mform->addElement( 'hidden', 'student', $student );
        $mform->setType( 'student', PARAM_INT );
        $mform->addElement( 'hidden', 'screen', 'changeBlogRegistration' );
        $mform->setType( 'screen', PARAM_ALPHA );
 
        
        // rest of the form
        $mform->addElement( 'header', 'general', 
                   get_string('bim_change_form_heading', 'bim'));

        $mform->addElement( 'html', get_string( 'bim_change_form_description',
                                                'bim' ));
        $mform->addElement('text', 'blogurl', 
                   get_string( 'bim_change_form_url','bim' ), 
                array('size'=>'50','maxlength'=>'255' ));
        $mform->setType( 'blogurl', PARAM_TEXT );
        $mform->addRule( 'blogurl', null, 'required', null, 'client' );

        // add standard buttons, common to all modules
        $this->add_action_buttons();

    }
}

?>
