<?php // $Id: version.php,v 1.5.2.2 2009/03/19 12:23:11 mudrd8mz Exp $

/**
 * Code fragment to define the version of bim
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @author  David Jones <davidthomjones@gmail.com>
 * @package mod/bim
 */

$module->version  = 2011013115;  // The current module version (Date: YYYYMMDDXX)
$module->cron     = 3600;           // Period for cron to check this module (secs)
$module->requires = 2007101592; // require 1.9
?>
