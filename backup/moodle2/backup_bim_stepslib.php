<?php
 
/**
 * Define all the backup steps that will be used by the backup_bim_activity_task
 */


/**
 * Define the complete bim structure for backup, with file and id annotations
 */     
class backup_bim_activity_structure_step extends backup_activity_structure_step {
 
    protected function define_structure() {
 
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
 
        // Define each element separated
        $bim = new backup_nested_element( 'bim', array('id' ),
                      array( 'name', 'intro', 'introformat', 'timecreated',
                             'timemodified', 'register_feed', 'mirror_feed',
                             'change_feed', 'grade_feed' ) );

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
 
        // Build the tree
        $bim->add_child( $allocations);
        $bim->add_child( $allocation);
        $bim->add_child( $questions );
        $bim->add_child( $question );
        $bim->add_child( $feeds );
        $bim->add_child( $feed );
        $bim->add_child( $markings );
        $bim->add_child( $marking );
 
        // Define sources
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
 
        // Define id annotations
        $allocation->annotate_ids( 'user', 'userid' );
        $allocation->annotate_ids( 'group', 'groupid' );
        $feed->annotate_ids( 'user', 'userid' );
        $marking->annotate_ids( 'user', 'userid' );
        $marking->annotate_ids( 'user', 'marker' );
 
        // Define file annotations
 
        // Return the root element (bim), wrapped into standard activity structure
        return $this->prepare_activity_structure( $bim ); 
    }
}