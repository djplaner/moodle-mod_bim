<?php //$Id: marking_form.php,v 1.2.2.3 2009/03/19 12:23:11 mudrd8mz Exp $

/**
 *
 */

require_once("$CFG->libdir/formslib.php" );

class marking_form extends moodleform {

    function definition() {

        global $COURSE, $OUTPUT;
        $mform =& $this->_form;

        $marking_details = $this->_customdata['marking_details'];
        $questions = $this->_customdata['questions'];
        $uid = $this->_customdata['uid'];
        $id = $this->_customdata['id'];
        $marking = $this->_customdata['marking']; 

//print_object( $this->_customdata );
        $attributes = 'onchange="this.form.submit()"';

        // Add hidden fields to get going the right way
        $mform->addElement( 'hidden', 'id', $id );
        $mform->setType( 'id', PARAM_INT );
        $mform->addElement( 'hidden', 'screen', 'MarkPost' );
        $mform->setType( 'id', PARAM_ALPHA );
        $mform->addElement( 'hidden', 'markingId', $marking );
        $mform->setType( 'id', PARAM_INT );

        // calculate the list of options for the reallocate drop box
        $allocate_array = $this->calculateAllocate( $marking_details, 
                                                    $questions);

        // Put in the form
        $row = $marking_details[$marking];

        // the heading
        $heading = get_string( 'marking_form_status','bim', $row->status ); 
        if ( $row->status != "Unallocated" && 
             isset( $questions[$row->question]) )
        {
          $heading .= ' (' . $questions[$row->question]->title . ')';
        }
        $mform->addElement('header', 'Post '.$row->id, $heading );

        // get help buttons for headings
        $markHelp = $OUTPUT->help_icon( 'mark', 'bim' );
        $suspendHelp = $OUTPUT->help_icon( 'suspend', 'bim' );
        $allocationHelp = $OUTPUT->help_icon( 'markAllocation', 'bim' );
        // add error box if suspended
        // posted and allocation
        $mform->addElement( 'html',
              '<table border="0" cellpadding="2" width="100%"><tr>' .
              '<th valign="top" align="left">'.
              get_string('allocation_form_posted','bim').'</th>' .
              '<th valign="top" align="left">' .
              get_string('allocation_form_mark','bim').$markHelp.
              '</th><th valign="top" align="left">' .
              get_string('allocation_form_suspend','bim').$suspendHelp.
              '</th><th valign="top" align="left">' . 
                     get_string('allocation_form_change','bim').$allocationHelp.
              '</th>' .
              '</tr><tr><td valign="top">' . 
                   date('H:i:s D, d/M/Y', $row->timepublished ).
             '</td><td valign="top">' );
         // text box for mark
         $mform->addElement( 'text', 'mark', '', 'size="10"' ); 
//                             get_string('marking_form_mark','bim'),'size="10"' );
         $mform->addRule('mark', null, 'numeric', null, 'client' );
         $mform->setType( 'mark', PARAM_NUMBER );


         
         $mform->addElement( 'html', 
                             '<br /><small>' .
                             get_string('marking_form_min', 'bim') . 
                             $questions[$row->question]->min_mark . ' ' .
                             get_string('marking_form_max', 'bim') .
                             $questions[$row->question]->max_mark .
                             '<small></td><td valign="top" align="left">'  );

         // add suspend checkbox
         $mform->addElement( 'checkbox', 'suspend', '' );
         
         $mform->addElement( 'html', '</td><td valign="top" align="left">' );
         // and reallocate
         $mform->addElement( 'select', 'Reallocate'.$row->id, '',
                             $allocate_array, $attributes );

        $markerCommentsHelp = $OUTPUT->help_icon( 'markerComments', 'bim' );
        $mform->addElement( 'html', '</td></tr>'.
               '<tr><td valign="top" colspan="2" width="50%">'.
               get_string('marking_form_student_post','bim', $row->link ).
               '</td><td valign="top" colspan="2" width="50%"> ' .
               get_string('marking_form_marker_comments','bim',
                           $markerCommentsHelp)  .  '</td></tr>' );

         $mform->addElement( 'html', '<tr><td valign="top" colspan="2">'.
                 $row->post.'</td><td valign="top" colspan="2">' );

         // the HTML editor
         $editor_settings = array( 'canUseHtmlEditor'=>'detect',
                   'rows' => 20, 'cols' => 40, 'width' => 0,
                   'height' => 0, 'course' => 0 );
         $mform->addElement( 'editor', 'comments', '', $editor_settings );
         $mform->setType( 'comments', PARAM_RAW );
         //$mform->addRule( 'comments', null, 'required', null, 'client' );

//         $mform->addElement( 'html', '<br /><p>' );
         $button_array = array();
         $button_array[] = &$mform->createElement( 'submit', 'submitbutton', 
                                get_string('bim_submit','bim') ); 
         $button_array[] = &$mform->createElement( 'cancel', 'cancelbutton', 
                                get_string('bim_cancel','bim') ); 
         $mform->addGroup( $button_array, 'buttonar', '', array( ''), false );
         
         
 //        $mform->addElement( 'html', '</p>' );

         $mform->addElement( 'html', '</td></tr></table>' );
       
/*         $toform->comments = $row->comments;
         $this->set_data( $toform);*/
/*        $mform->addElement( 'html', 
              '<div align="center"><table width="80%">' .
              '<tr bgcolor="#dddddd"><td>' . $row->post . '</td></tr></table></div>' );

        } */
//        $this->add_action_buttons();
    }

    /* $array = calculateAllocate( $marking_details, $questions )
     * - return an array of values for a select box based on
     *   the $questions that haven't yet been allocated in
     *   $marking_details
     * - Array will have some defaults
     *        default => ..Choose one.. 
     *        Unallocate => Unallocate
     */

    function calculateAllocate( $marking_details, $questions )
    {
      // start with the defaults that are always there
      $array = array( 'default' => '..Choose one..',
                          'Unallocate' => 'Unallocated' );

      // only add the questions that haven't been allocated
      foreach ( $questions as $question )
      {
        $found = false;
        foreach ( $marking_details as $detail )
        {
          if ( $question->id == $detail->question ) 
          { 
            $found = true; 
            break;
          }
        }
        if ( ! $found )
        {
          $array[$question->id] = $question->title ;
        }
      }
      return $array;
    }
}

?>
