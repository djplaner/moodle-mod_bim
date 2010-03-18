<?php //$Id: question_form.php,v 1.2.2.3 2009/03/19 12:23:11 mudrd8mz Exp $

/**
 * question_form.php
 * - define the form used by the coordiantor to modify, add
 *   and delete questions for a BIM activity
 * - Show a "Add a new question" collection of title, min, max and questions
 * - Show a list of similar collections for all existing questions
 */

require_once("$CFG->libdir/formslib.php" );

class question_form extends moodleform {

    function definition() {

        global $COURSE;
        $mform =& $this->_form;

        $questions = $this->_customdata['questions'];
        $id = $this->_customdata['id'];

        // Add hidden fields to get going the right way
        $mform->addElement( 'hidden', 'id', $id );
        $mform->setType( 'id', PARAM_INT );
        $mform->addElement( 'hidden', 'tab', 'questions' );
        $mform->setType( 'tab', PARAM_ALPHA );

        // Add new question
        $mform->addElement('header', "new_question", 
              get_string( 'bim_qform_addnew', 'bim' ) );

        // row with title, min and max mark ?? delete
        $mform->addElement( 'html', 
                    '<table cellpadding="2"><tr><th>' .
                    get_string( 'bim_qform_title', 'bim' ) . '</th><td>' );
        $mform->addElement( 'text', "title_new", '', 'size="20"' );
        $mform->setType( "title_new", PARAM_TEXT );
        $mform->addElement( 'html', '</td><th>' .
                    get_string( 'bim_qform_min', 'bim' ) . '</th><td>' );
        $mform->addElement( 'text', "min_new", '', 'size="5"' );
        $mform->setType( "min_new", PARAM_NUMBER );
        $mform->addRule('min_new', null, 'numeric', null, 'client' );

        $mform->addElement( 'html', '</td><th>' .
                    get_string( 'bim_qform_max', 'bim' ) . '</th><td>' );
        $mform->addElement( 'text', "max_new", '', 'size="5"' );
        $mform->setType( "max_new", PARAM_NUMBER );
        $mform->addRule('max_new', null, 'numeric', null, 'client' );
        $mform->addElement( 'html', '</td></tr></table>' );

        // html editor with body
        $mform->addElement( 'htmleditor', "body_new", '' );
        $mform->setType( "body_new", PARAM_RAW );
 
        $button_array = array();
        $button_array[] = &$mform->createElement( 'submit', 'submitbutton',
                            get_string( 'bim_submit', 'bim' ) );
        $button_array[] = &$mform->createElement( 'cancel', 'cancelbutton',
                                   get_string( 'bim_cancel', 'bim' ) );
        $mform->addGroup( $button_array, 'buttonar', '', array( ''), false );

        if ( empty( $questions ) ) return;
 
        foreach ( $questions as $question )
        {

          $mform->addElement('header', $question->id, 
                   get_string( 'bim_qform_question', 'bim', $question->title ) );
          // add the stats
          $status_array = array( 'Submitted', 'Marked', 'Released' );
          $stats = "";
          foreach ( $status_array as $status )
          {
            if ( isset( $question->status[$status] ) )
            {
              if ( $stats != "" ) $stats .= ", ";
              $stats .= "$status: " . $question->status[$status] ;
            }
          } 
          $mform->addElement( 'html', get_string( 'bim_qform_stats', 'bim', 
                                           $stats ) );

          // row with title, min and max mark ?? delete
          $mform->addElement( 'html', 
                      '<table cellpadding="2"><tr><th>' .
                      get_string('bim_qform_title', 'bim' ) . '</th><td>' );
          $mform->addElement( 'text', "title_$question->id", '', 'size="20"' );
          $mform->addRule( "title_$question->id", null, 'required', null, 
                           'client' );
          $mform->setType( "title_$question->id", PARAM_TEXT );

          $mform->addElement( 'html', '</td><th>' .
                       get_string( 'bim_qform_min', 'bim' ) . '</th><td>' );
          $mform->addElement( 'text', "min_$question->id", '', 'size="5"' );
          $mform->addRule( "min_$question->id", null, 'required', null, 'client' );
          $mform->setType( "min_$question->id", PARAM_NUMBER );

          $mform->addElement( 'html', '</td><th>' . 
                       get_string( 'bim_qform_max', 'bim' ) . 
                           '</th><td valign="top">' );
          $mform->addElement( 'text', "max_$question->id", '', 'size="5"' );
          $mform->addRule( "max_$question->id", null, 'required', null, 'client' );
          $mform->setType( "max_$question->id", PARAM_NUMBER );
          $mform->addElement( 'html', '</td><th>' .
                       get_string( 'bim_qform_delete', 'bim' ) . '</th><td>' );
          $mform->addElement( 'checkbox', "delete_$question->id", '' );
          $mform->addElement( 'html', '</td></tr></table>' );
  
          // html editor with body
          $editor_settings = array( 'canUseHtmlEditor'=>'detect',
                   'rows' => 10, 'cols' => 40, 'width' => 0,
                   'height' => 0, 'course' => 0 );
          $mform->addElement( 'htmleditor', "body_$question->id", '',
                                $editor_settings );
          $mform->setType( "body_$question->id", PARAM_RAW );
 
          $mform->addGroup( $button_array, 'buttonar', '', array( ''), false );
        } 
    } 
}

?>
