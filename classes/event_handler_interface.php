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
 * Event handler definition.
 * @package local_rtcomms
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rtcomms;

/**
 * Interface definition that event handlers MUST implement.
 */
interface event_handler_interface {

    /**
     * Instantiator.
     *
     * @return event_handler_interface
     */
    public static function instance(): event_handler_interface;

    /**
     * Use the event.
     *
     * @param integer $from
     * @param integer $contextid
     * @param string $component
     * @param string $area
     * @param integer $itemid
     * @param array $payload
     * @return void
     */
    public function process_event($from, $contextid, $component, $area, $itemid, $payload);
}
