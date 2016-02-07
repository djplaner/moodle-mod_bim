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
 * Code fragment to define the version of bim
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @author  David Jones <davidthomjones@gmail.com>
 * @package mod_bim
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version  = 2016020708;  // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires = 2013051400;
$plugin->cron     = 3600;           // Period for cron to check this module (secs)
$plugin->component = 'mod_bim';
$plugin->release = "2.1 (Build: 2016020402)";
$plugin->component = "mod_bim";
$plugin->maturity = MATURITY_STABLE;

