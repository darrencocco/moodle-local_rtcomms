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
 * Interface definition for listener registrations.
 * @package local_rtcomms
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rtcomms;

/**
 * Interface that listener registrations MUST implement.
 */
interface listener_registration_interface {

    /**
     * Returns an instance of the registration based on the data.
     * @param \stdClass $data
     * @return listener_registration_interface
     */
    public static function instance(\stdClass $data): listener_registration_interface;

    /**
     * Data required to instantiate the registration.
     * @return \stdClass
     */
    public function get_data(): \stdClass;

    /**
     * Given the channel details it indicates interest in the event.
     * @param integer $contextid
     * @param string $component
     * @param string $area
     * @param integer $itemid
     * @return bool
     */
    public function is_interested($contextid, $component, $area, $itemid): bool;

    /**
     * Returns the FQN of the class that will process the event.
     *
     * @return event_handler_interface::class
     */
    public function get_handler(): string;
}
