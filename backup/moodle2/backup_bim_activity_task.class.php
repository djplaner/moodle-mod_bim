<?php
 
require_once($CFG->dirroot . '/mod/bim/backup/moodle2/backup_bim_stepslib.php'); // Because it exists (must)
require_once($CFG->dirroot . '/mod/bim/backup/moodle2/backup_bim_settingslib.php'); // Because it exists (optional)
 
/**
 * bim backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_bim_activity_task extends backup_activity_task {
 
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }
 
    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Choice only has one structure step
        $this->add_step(new backup_bim_activity_structure_step('bim_structure', 'bim.xml'));
    }
 
    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote( $CFG->wwwroot, "/" );

        // Link to the list of bims
        $search = "/(".$base."\/mod\/bim\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace( $search, '$@CHOICEINDEX*$2@$', $content );

        // link to bim view by module id
        $search = "/(".$base."\/mod\/bim\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@BIMVIEWBYID*$2@$', $content);
 
        return $content;
    }
}
