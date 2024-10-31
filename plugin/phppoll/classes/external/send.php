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
 * Send to server external service.
 *
 * @package rtcomms_phppoll
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace rtcomms_phppoll\external;

use external_value;
use local_rtcomms\api;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * API definition for send to server functionality.
 */
class send extends \external_api {
    /**
     * Parameter validation rules.
     *
     * @return \external_function_parameters
     */
    public static function execute_parameters(): \external_function_parameters {
        return new \external_function_parameters([
            "contextid" => new \external_value(PARAM_INT, "context for message"),
            "component" => new \external_value(PARAM_TEXT, "component/plugin name"),
            "area" => new external_value(PARAM_TEXT, "plugin area"),
            "itemid" => new external_value(PARAM_INT, "item id for area"),
            "payload" => new external_value(PARAM_RAW, "message payload"),
        ]);
    }

    /**
     * Return validation rules.
     *
     * @return void
     */
    public static function execute_returns() {
    }

    /**
     * Does the work of sending data to the server.
     * @param $contextid
     * @param $component
     * @param $area
     * @param $itemid
     * @param $payload
     * @return void
     * @throws \invalid_parameter_exception
     */
    public static function execute($contextid, $component, $area, $itemid, $payload) {
        global $USER;
        $params = self::validate_parameters(self::execute_parameters(), [
            "contextid" => $contextid,
            "component" => $component,
            "area" => $area,
            "itemid" => $itemid,
            "payload" => $payload,
        ]);
        api::send_to_server($USER->id, $params['contextid'], $params['component'],
            $params['area'], $params['itemid'],
            json_decode($params['payload'], true));
    }
}
