<?php

/**
 * Structure step to restore one bim activity
 */
class restore_bim_activity_structure_step extends restore_activity_structure_step {
 
    protected function define_structure() {
 
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');
 
        $paths[] = new restore_path_element('bim', '/activity/bim');
        if ( $userinfo ) {
            $paths[] = new restore_path_elements( 'bim_allocation',
                               '/activity/bim/allocations/allocation'
        }
        $paths[] = new restore_path_element('bim_question', 
                               '/activity/bim/questions/question');
        if ($userinfo) {
            $paths[] = new restore_path_element('bim_student_feed', 
                               '/activity/bim/student_feeds/student_feed');
            $paths[] = new restore_path_element('bim_marking', 
                               '/activity/bim/markings/marking');
        }
 
        // Return the paths wrapped into standard activity structure
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
 
        $data->bimid = $this->get_new_parentid('bim');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
 
        $newitemid = $DB->insert_record('bim_group_allocation', $data);
        $this->set_mapping('bim_allocation', $oldid, $newitemid);
    }
 
    protected function process_bim_question($data) {
        global $DB;
 
        $data = (object)$data;
 
        $data->bimid = $this->get_new_parentid('bim');
        $data->timemodified = $this->apply_date_offset($data->timemodified);
 
        $newitemid = $DB->insert_record('bim_questions', $data);
        $this->set_mapping('bim_question', $oldid, $newitemid);
    }

    protected function process_bim_student_feed($data) {
        global $DB;
 
        $data = (object)$data;
 
        $data->bimid = $this->get_new_parentid('bim');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
 
        $newitemid = $DB->insert_record('bim_student_feeds', $data);
        $this->set_mapping('bim_student_feed', $oldid, $newitemid);
    }

    protected function process_bim_marking($data) {
        global $DB;
 
        $data = (object)$data;
 
        $data->bimid = $this->get_new_parentid('bim');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
 
        $newitemid = $DB->insert_record('bim_marking', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
        $this->set_mapping('bim_marking', $oldid, $newitemid);
    }
 
    protected function after_execute() {
        // Add bim related files, no need to match by itemname (just internally handled context)
        //$this->add_related_files('mod_bim', 'intro', null);
    }
}