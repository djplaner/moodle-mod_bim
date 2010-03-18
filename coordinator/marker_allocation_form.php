<?php //$Id: marker_allocation_form.php,v 1.2.2.3 2009/03/19 12:23:11 mudrd8mz Exp $

/**
 * This form defines the interface used to assign users with
 * bim/marker and bim/coordinator capabilities to groups of students
 * for which they will be and probably responsible for marking
 */

require_once("$CFG->libdir/formslib.php" );

class marker_allocation_form extends moodleform {

    function definition() {

        global $COURSE;
        $mform =& $this->_form;

        // additional data for form construction
        $groups = $this->_customdata['groups'];
        $markers = $this->_customdata['markers'];
        $id = $this->_customdata['id'];
 
       

        // Add hidden fields to get going the right way
        $mform->addElement( 'hidden', 'id', $id );
        $mform->setType( 'id', PARAM_INT );
        $mform->addElement( 'hidden', 'tab', 'markers' );
        $mform->setType( 'id', PARAM_ALPHA );

        // create array of groupnames for the multi-select element
        $group_names = array();
 
        foreach ( $groups as $group )
        {
          $group_names[$group->id] = $group->name;
        }
        // Generate essentially the same information per marker
        //    Marker name and role      List of groups
        foreach ( $markers as $marker )
        {
          // the markers id is used to unique identify each
          // form element per marker
          $marker_id = $marker->id;

          $marker_group = array();
          $name = '<strong>' . $marker->firstname . ' ' . 
                  $marker->lastname . '</strong>' ;
          $select =& $mform->addElement( 'select', 'groups_'.$marker_id,
                         "$name", $group_names );
          $select->setMultiple( true );
 
          $mform->addElement( 'submit', 'submitbutton', 'Submit' );
        }
    }

}

?>
