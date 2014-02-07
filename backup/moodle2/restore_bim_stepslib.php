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

defined('MOODLE_INTERNAL') || die();

/**
 * Structure step to restore one bim activity
 */
class restore_bim_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('bim', '/activity/bim');
        if ( $userinfo ) {
            $paths[] = new restore_path_element( 'bim_allocation',
                               '/activity/bim/allocations/allocation' );
        }
        $paths[] = new restore_path_element('bim_question',
                               '/activity/bim/questions/question');
        if ($userinfo) {
            $paths[] = new restore_path_element('bim_student_feed',
                               '/activity/bim/feeds/feed');
            $paths[] = new restore_path_element('bim_marking',
                               '/activity/bim/markings/marking');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    protected function process_bim($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the bim record
        $newitemid = $DB->insert_record('bim', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_bim_allocation($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->bim = $this->get_new_parentid('bim');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupid);

        $newitemid = $DB->insert_record('bim_group_allocation', $data);
        $this->set_mapping('bim_allocation', $oldid, $newitemid);
    }

    protected function process_bim_question($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->bim = $this->get_new_parentid('bim');

        $newitemid = $DB->insert_record('bim_questions', $data);
        $this->set_mapping('bim_question', $oldid, $newitemid);
    }

    protected function process_bim_student_feed($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->bim = $this->get_new_parentid('bim');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->lastpost = $this->apply_date_offset( $data->lastpost );
        $newitemid = $DB->insert_record('bim_student_feeds', $data);
        $this->set_mapping('bim_student_feed', $oldid, $newitemid);
    }

    protected function process_bim_marking($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->bim = $this->get_new_parentid('bim');
        $data->question = $this->get_mappingid( 'bim_question', $data->question );

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->marker = $this->get_mappingid('user', $data->marker);

        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timemarked = $this->apply_date_offset($data->timemarked);
        $data->timereleased = $this->apply_date_offset($data->timereleased);
        $data->timepublished = $this->apply_date_offset($data->timepublished);

        $newitemid = $DB->insert_record('bim_marking', $data);
        // No need to save this mapping as far as nothing depend on it.
        // (child paths, file areas nor links decoder)
        $this->set_mapping('bim_marking', $oldid, $newitemid);
    }

    protected function after_execute() {
        // Add bim related files, no need to match by itemname (just internally handled context)
        // $this->add_related_files('mod_bim', 'intro', null);
    }
}
