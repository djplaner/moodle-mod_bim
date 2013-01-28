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
 * Define all the backup steps that will be used by the backup_bim_activity_task
 */


/**
 * Define the complete bim structure for backup, with file and id annotations
 */
class backup_bim_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $bim = new backup_nested_element( 'bim', array('id' ),
                      array( 'name', 'intro', 'introformat', 'timecreated',
                             'timemodified', 'register_feed', 'mirror_feed',
                             'change_feed', 'grade' ) );

        $allocations = new backup_nested_element( 'allocations' );
        $allocation = new backup_nested_element( 'allocation', array('id'),
                      array( 'bim', 'groupid', 'userid' ) );

        $questions = new backup_nested_element('questions' );
        $question = new backup_nested_element('question', array('id'),
                      array( 'bim', 'title', 'body', 'min_mark', 'max_mark' ));

        $feeds = new backup_nested_element( 'feeds' );
        $feed = new backup_nested_element( 'feed', array('id'),
                      array( 'bim', 'userid', 'numentries', 'lastpost',
                             'blogurl', 'feedurl' ));

        $markings = new backup_nested_element( 'markings' );
        $marking = new backup_nested_element( 'marking', array('id'),
                      array( 'bim', 'userid', 'marker', 'question', 'mark',
                             'status', 'timemarked', 'timereleased', 'link',
                             'timepublished', 'title', 'post', 'comments' ));

        // Build the tree.
        $bim->add_child( $allocations);
        $allocations->add_child( $allocation);
        $bim->add_child( $questions );
        $questions->add_child( $question );
        $bim->add_child( $feeds );
        $feeds->add_child( $feed );
        $bim->add_child( $markings );
        $markings->add_child( $marking );

        // Define sources.
        $bim->set_source_table( 'bim', array( 'id' => backup::VAR_ACTIVITYID));
        if ( $userinfo ) {
            $allocation->set_source_table( 'bim_group_allocation',
                          array( 'bim' => backup::VAR_ACTIVITYID ));
        }
        $question->set_source_table( 'bim_questions',
                          array( 'bim' => backup::VAR_ACTIVITYID ));
        if ( $userinfo ) {
            $feed->set_source_table( 'bim_student_feeds',
                          array( 'bim' => backup::VAR_ACTIVITYID ));
            $marking->set_source_table( 'bim_marking',
                          array( 'bim' => backup::VAR_ACTIVITYID ));
        }

        // Define id annotations.
        $allocation->annotate_ids( 'user', 'userid' );
        $allocation->annotate_ids( 'group', 'groupid' );
        $feed->annotate_ids( 'user', 'userid' );
        $marking->annotate_ids( 'user', 'userid' );
        $marking->annotate_ids( 'user', 'marker' );

        // Define file annotations.

        // Return the root element (bim), wrapped into standard activity structure.
        return $this->prepare_activity_structure( $bim );
    }
}
