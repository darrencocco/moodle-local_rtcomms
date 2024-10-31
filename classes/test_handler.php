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
 * Handler used for testing.
 * @package local_rtcomms
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rtcomms;

use local_rtcomms\api;
use local_rtcomms\event_handler_interface;

/**
 * Logs and reflects messages
 */
class test_handler implements event_handler_interface {

    /**
     * Does what it says on the tin.
     * @return \local_rtcomms\test_handler
     */
    public static function instance(): event_handler_interface {
        return new self();
    }

    /**
     * Logs and reflects incoming messages.
     *
     * @param $contextid
     * @param $component
     * @param $area
     * @param $itemid
     * @param $payload
     * @return void
     * @throws \coding_exception
     */
    public function process_event($from, $contextid, $component, $area, $itemid, $payload) {
        api::send_to_clients(\context::instance_by_id($contextid), $component, $area, $itemid,
            fn() => [$from], $payload);
    }
}
