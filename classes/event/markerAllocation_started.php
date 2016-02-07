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

/*
 * The markerAllocation_started event.
 *
 * @package mod_bim
 * @copyright 2010 onwards David Jones {@link http://davidtjones.wordpress.com} 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_bim\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The markerAllocation_started event class.
 *
 * @property-read array $other {
 *      Extra information about the event
 *
 **/
class markerAllocation_started extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'r'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'bim_marking';
    }
 
    public static function get_name() {
        return get_string('eventmarkerallocationstarted', 'mod_bim');
    }
 
    public function get_description() {
        return "The user with id {$this->userid} viewed marker allocation for BIM with id {$this->objectid}.";
    }
 
    public function get_url() {
        return new \moodle_url('/mod/bim/view.php', 
                array('id' => $this->contextinstanceid,
                      'tab' => 'markers' ));
    }
 
    public function get_legacy_logdata() {
        // Override if you are migrating an add_to_log() call.
        return array($this->courseid, 'bim', 'markers allocation viewed',
            'view.php?tab=markers&id=' . $this->contextinstanceid,
            'List all', $this->contextinstanceid);
    }
}



