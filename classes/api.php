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
 * Class api
 *
 * @package     local_rtcomms
 * @copyright   2020 Moodle Pty Ltd <support@moodle.com>
 * @author      2020 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license     Moodle Workplace License, distribution is restricted, contact support@moodle.com
 */

namespace local_rtcomms;

use Closure;

/**
 * Class api
 *
 * @package     local_rtcomms
 * @copyright   2020 Moodle Pty Ltd <support@moodle.com>
 * @author      2020 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license     Moodle Workplace License, distribution is restricted, contact support@moodle.com
 */
class api {

    /**
     * Subscribe the current page to receive notifications about events
     *
     * @param \context $context
     * @param string $component
     * @param string $area
     * @param int $itemid
     */
    public static function subscribe(\context $context, string $component, string $area, int $itemid) {
        manager::get_plugin()->subscribe($context, $component, $area, $itemid);
    }

    /**
     * SEt up realtime tool
     */
    public static function init() {
        manager::get_plugin()->init();
    }

    /**
     * Notifies all subscribers about an event
     *
     * @deprecated
     * @param \context $context
     * @param string $component
     * @param string $area
     * @param int $itemid
     * @param array|null $payload
     */
    public static function notify(\context $context, string $component, string $area, int $itemid,
                                  Closure $userselector, ?array $payload = null) {
        debugging("Notify is deprecated", DEBUG_DEVELOPER);
        self::send_to_clients($context, $component, $area, $itemid, $userselector, $payload);
    }

    /**
     * Notifies all subscribers about an event
     *
     * @param \context $context
     * @param string $component
     * @param string $area
     * @param int $itemid
     * @param array|null $payload
     */
    public static function send_to_clients(\context $context, string $component, string $area, int $itemid,
                                           Closure $userselector, ?array $payload = null) {
        manager::get_plugin()->send_to_clients($context, $component, $area, $itemid, $userselector, $payload);
    }

    /**
     * Send a message to server listeners interested in it.
     *
     * @param integer $from
     * @param integer $context
     * @param string $component
     * @param string $area
     * @param integer $itemid
     * @param array $payload
     * @return void
     */
    public static function send_to_server($from, $context, $component, $area, $itemid, $payload) {
        manager::get_plugin()->process_event($from, $context, $component, $area, $itemid, $payload);
    }
}
