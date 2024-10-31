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
 * Callbacks etc.
 *
 * @package local_rtcomms
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Provides test listener registration.
 *
 * @return \local_rtcomms\listener_registration_interface[]
 */
function local_rtcomms_rtcomms_listener_registration() {
    return [
        \local_rtcomms\default_listener_registration::instance((object)[
            "contextid" => "*",
            "component" => "local_rtcomms",
            "area" => "test",
            "itemid" => "*",
            "handler" => "\\local_rtcomms\\test_handler",
        ]),
    ];
}
