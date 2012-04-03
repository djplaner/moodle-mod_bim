<?php // $Id: version.php,v 1.5.2.2 2009/03/19 12:23:11 mudrd8mz Exp $

/**
 * Code fragment to define the version of bim
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @author  Your Name <your@email.address>
 * @version $Id: version.php,v 1.5.2.2 2009/03/19 12:23:11 mudrd8mz Exp $
 * @package mod/bim
 */

defined('MOODLE_INTERNAL') || die;

$module->version  = 2011013116;  // The current module version (Date: YYYYMMDDXX)
$module->requires = 2010031900;
$module->cron     = 3600;           // Period for cron to check this module (secs)
$module->component = 'mod_bim';

?>
