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
 *
 */

require_once("$CFG->libdir/formslib.php" );

class marking_form extends moodleform {

    public function definition() {

        global $COURSE, $OUTPUT;
        $mform =& $this->_form;

        $marking_details = $this->_customdata['marking_details'];
        $questions = $this->_customdata['questions'];
        $uid = $this->_customdata['uid'];
        $id = $this->_customdata['id'];
        $marking = $this->_customdata['marking'];

        // print_object( $this->_customdata );
        $attributes = 'onchange="this.form.submit()"';

        // Add hidden fields to get going the right way
        $mform->addElement( 'hidden', 'id', $id );
        $mform->setType( 'id', PARAM_INT );
        $mform->addElement( 'hidden', 'screen', 'MarkPost' );
        $mform->setType( 'id', PARAM_ALPHA );
        $mform->addElement( 'hidden', 'markingId', $marking );
        $mform->setType( 'id', PARAM_INT );

        // calculate the list of options for the reallocate drop box
        $allocate_array = $this->calculate_allocate( $marking_details,
                $questions);

        // Put in the form
        $row = $marking_details[$marking];

        // the heading
        $heading = get_string( 'marking_form_status', 'bim', $row->status );
        if ( $row->status != "Unallocated" && isset( $questions[$row->question]) ) {
            $heading .= ' (' . $questions[$row->question]->title . ')';
        }
        $mform->addElement('header', 'Post '.$row->id, $heading );

        // get help buttons for headings
        $mark_help = $OUTPUT->help_icon( 'mark', 'bim' );
        $suspend_help = $OUTPUT->help_icon( 'suspend', 'bim' );
        $allocation_help = $OUTPUT->help_icon( 'markAllocation', 'bim' );
        // add error box if suspended
        // posted and allocation
        $mform->addElement( 'html',
                        '<table border="0" cellpadding="2" width="100%"><tr>' .
                        '<th valign="top" align="left">'.
                        get_string('allocation_form_posted', 'bim').'</th>' .
                        '<th valign="top" align="left">' .
                        get_string('allocation_form_mark', 'bim').$mark_help.
                        '</th><th valign="top" align="left">' .
                        get_string('allocation_form_suspend', 'bim').$suspend_help.
                        '</th><th valign="top" align="left">' .
                        get_string('allocation_form_change', 'bim').$allocation_help.
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

        $marker_comments_help = $OUTPUT->help_icon( 'markerComments', 'bim' );
        $mform->addElement( 'html', '</td></tr>'.
                            '<tr><td valign="top" colspan="2" width="50%">'.
                            get_string('marking_form_student_post', 'bim', $row->link ).
                            '</td><td valign="top" colspan="2" width="50%"> ' .
                            get_string('marking_form_marker_comments', 'bim',
                                $marker_comments_help)  .  '</td></tr>' );

        $mform->addElement( 'html', '<tr><td valign="top" colspan="2">'.
                            $row->post.'</td><td valign="top" colspan="2">' );

        // the HTML editor
        $editor_settings = array( 'canUseHtmlEditor'=>'detect',
                            'rows' => 20, 'cols' => 40, 'width' => 0,
                            'height' => 0, 'course' => 0 );
        $mform->addElement( 'editor', 'comments', '', $editor_settings );
        $mform->setType( 'comments', PARAM_RAW );
        // $mform->addRule( 'comments', null, 'required', null, 'client' );
        //         $mform->addElement( 'html', '<br /><p>' );
        $button_array = array();
        $button_array[] = &$mform->createElement( 'submit', 'submitbutton',
                            get_string('bim_submit', 'bim') );
        $button_array[] = &$mform->createElement( 'cancel', 'cancelbutton',
                            get_string('bim_cancel', 'bim') );
        $mform->addGroup( $button_array, 'buttonar', '', array( ''), false );

        //        $mform->addElement( 'html', '</p>' );

        $mform->addElement( 'html', '</td></tr></table>' );

    }

    /* $array = calculate_allocate( $marking_details, $questions )
     * - return an array of values for a select box based on
     *   the $questions that haven't yet been allocated in
     *   $marking_details
     * - Array will have some defaults
     *        default => ..Choose one..
     *        Unallocate => Unallocate
     */

    protected function calculate_allocate( $marking_details, $questions ) {
        // start with the defaults that are always there
        $array = array( 'default' => '..Choose one..',
                'Unallocate' => 'Unallocated' );

        // only add the questions that haven't been allocated
        foreach ($questions as $question) {
            $found = false;
            foreach ($marking_details as $detail) {
                if ( $question->id == $detail->question ) {
                    $found = true;
                    break;
                }
            }
            if ( ! $found ) {
                $array[$question->id] = $question->title;
            }
        }
        return $array;
    }

}
