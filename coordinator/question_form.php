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
 * Provide the form for managing and displaying the questions
 *
 * @package mod_bim
 * @copyright 2010 onwards David Jones {@link http://davidtjones.wordpress.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php" );

class question_form extends moodleform {

    public function definition() {

        global $COURSE, $OUTPUT;
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

        $title_help = $OUTPUT->help_icon( 'qform_title', 'bim' );
        $min_help = $OUTPUT->help_icon( 'qform_min', 'bim' );
        $max_help = $OUTPUT->help_icon( 'qform_max', 'bim' );
        $stats_help = $OUTPUT->help_icon( 'qform_stats', 'bim' );

        // row with title, min and max mark ?? delete
        $mform->addElement( 'html',
                '<table cellpadding="2"><tr><th>' .
                get_string( 'bim_qform_title_help', 'bim', $title_help ) . '</th><td>' );
        $mform->addElement( 'text', "title_new", '', 'size="20"' );
        $mform->setType( "title_new", PARAM_TEXT );
        $mform->addElement( 'html', '</td><th>' .
                get_string( 'bim_qform_min_help', 'bim', $min_help ) . '</th><td>' );
        $mform->addElement( 'text', "min_new", '', 'size="5"' );
        $mform->setType( "min_new", PARAM_NUMBER );
        $mform->addRule('min_new', null, 'numeric', null, 'client' );

        $mform->addElement( 'html', '</td><th>' .
                get_string( 'bim_qform_max_help', 'bim', $max_help ) . '</th><td>' );
        $mform->addElement( 'text', "max_new", '', 'size="5"' );
        $mform->setType( "max_new", PARAM_NUMBER );
        $mform->addRule('max_new', null, 'numeric', null, 'client' );
        $mform->addElement( 'html', '</td></tr></table>' );

        // html editor with body
        $editor_settings = array( 'canUseHtmlEditor'=>'detect',
                'rows' => 10, 'cols' => 40, 'width' => 0,
                'height' => 0, 'course' => 0 );
        $mform->addElement( 'editor', "body_new", '', $editor_settings );
        $mform->setType( "body_new", PARAM_RAW );

        $button_array = array();
        $button_array[] = &$mform->createElement( 'submit', 'submitbutton',
                get_string( 'bim_submit', 'bim' ) );
        $button_array[] = &$mform->createElement( 'cancel', 'cancelbutton',
                get_string( 'bim_cancel', 'bim' ) );
        $mform->addGroup( $button_array, 'buttonar', '', array( ''), false );

        if ( empty( $questions ) ) {
            return;
        }

        foreach ($questions as $question) {
            $mform->addElement('header', $question->id,
                    get_string( 'bim_qform_question', 'bim', $question->title ) );
            // add the stats
            $status_array = array( 'Submitted', 'Marked', 'Released' );
            $stats = "";
            foreach ($status_array as $status) {
                if ( isset( $question->status[$status] ) ) {
                    if ( $stats != "" ) {
                        $stats .= ", ";
                    }
                    $stats .= "$status: " . $question->status[$status];
                }
            }
            $mform->addElement( 'html', get_string( 'bim_qform_stats', 'bim',
                        Array( 'stats' =>$stats, 'help' => $stats_help )) );

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
            $mform->addElement( 'editor', "body_$question->id", null,
                    $editor_settings );
            $mform->setType( "body_$question->id", PARAM_RAW );

            $mform->addGroup( $button_array, 'buttonar', '', array( ''), false );
        }
    }
}

