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

require_once("$CFG->libdir/formslib.php" );

class allocation_form extends moodleform {

    public function definition() {

        global $COURSE;
        $mform =& $this->_form;

        $marking_details = $this->_customdata['marking_details'];
        $questions = $this->_customdata['questions'];
        $uid = $this->_customdata['uid'];
        $id = $this->_customdata['id'];

        // turn off the checking -- but only for Moodle versions that can
        if ( method_exists( $mform, "disable_form_change_checker" ) ) {
            $mform->disable_form_change_checker();
        }

        // Add hidden fields to get going the right way
        $mform->addElement( 'hidden', 'id', $id );
        $mform->setType( 'id', PARAM_INT );
        $mform->addElement( 'hidden', 'screen', 'AllocatePosts' );
        $mform->setType( 'screen', PARAM_ALPHA );
        $mform->addElement( 'hidden', 'uid', $uid );
        $mform->setType( 'uid', PARAM_INT );

        // add tiny explanation
        $mform->addElement( 'html', get_string('allocation_form_description',
                    'bim' ) );

        // calculate the list of options for the reallocate drop box
        $allocate_array = $this->calculate_allocate( $marking_details,
                $questions);
        $attributes='onchange="this.form.submit()"';

        // Generate a field set per post
        foreach ($marking_details as $row) {
            // Use the post title and status/allocation as header
            // - released/allocated include question allocation

            // show some colour depending on state
            $a = new stdClass();
            $a->status = $row->status;
            $a->class = "notifysuccess";
            if ( $row->status == "Unallocated" ) {
                $a->class = "notifyproblem";
            } else if ( $row->status == "Suspended" ) {
                $a->class = "errorboxcontent";
            }

            $heading = get_string('allocation_form_status', 'bim', $a );

            if ($row->status!="Unallocated" && isset($questions[$row->question])) {
                $heading .= ' (' . $questions[$row->question]->title . ')';
            }

            $heading .= '.&nbsp;&nbsp;' .
            get_string( 'allocation_form_post_title', 'bim') .
                        '<a href="'.$row->link.'">' .  $row->title . '</a>';
            //                     get_string('allocation_form_original', 'bim' ) .'</a>';

            $mform->addElement('header', 'Post '.$row->id, $heading );

            // add the Posted and allocate choice in a two cell table
            $mform->addElement( 'html',
                '<table border="0" cellpadding="2" width="100%"><tr>' .
                '<tr><th valign="top">' .
                get_string( 'allocation_form_posted', 'bim') .
                '</th><td valign="top">' .
                date('H:i:s D, d/M/Y', $row->timepublished ) .
                '</td><th valign="top">' .
                get_string( 'allocation_form_change', 'bim' ) .
                '</th><td valign="top" align="left">'  );

            // DROP BOX HERE

            $mform->addElement( 'select', 'Reallocate_'.$row->id,
                    '', $allocate_array, $attributes );
            $mform->addElement( 'html', '</td></tr></table>' );

            $mform->addElement( 'html',
                                '<div align="center"><table width="80%">' .
                                '<tr class="highlight"><td>' . $row->post . '</td></tr></table></div>' );

        }
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

