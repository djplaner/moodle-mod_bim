<?php
/**
 * Capability definitions for the BIM module.
 *
 */

$mod_bim_capabilities = array(
  // administrator can do lots of things
  'mod/bim:coordinator' => array(
     'captype' => 'manage',
     'contextlevel' => CONTEXT_MODULE,
     'legacy' => array(
         'editingteacher' => CAP_ALLOW,
         'coursecreator' => CAP_ALLOW,
//         'admin' => CAP_ALLOW,
     )
  ), 

    // teacher can view student details
    'mod/bim:marker' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
           'teacher' => CAP_ALLOW,
        ),
    ),

  // student can view their details
  'mod/bim:student' => array(
      'captype' => 'read',
      'contextlevel' => CONTEXT_MODULE,
      'legacy' => array(
          'guest' => CAP_PROHIBIT,
          'student' => CAP_ALLOW,
/*          'teacher' => CAP_PROHIBIT,
          'editingteacher' => CAP_PROHIBIT,
          'admin' => CAP_PROHIBIT, */
      ),
  )
);

?>
