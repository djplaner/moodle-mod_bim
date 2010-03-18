<?php //$Id: find_student_form.php,v 1.2.2.3 2009/03/19 12:23:11 mudrd8mz Exp $

/**
 * Allow coordinator to search for a specific student
 */

require_once("$CFG->libdir/formslib.php" );

class find_student_form extends moodleform {

    function definition() {

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

?>
