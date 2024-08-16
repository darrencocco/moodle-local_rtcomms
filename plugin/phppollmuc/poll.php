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
 * Poll for updates.
 *
 * @package     rtcomms_phppollmuc
 * @copyright   2024 Darren Cocco
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
define('NO_MOODLE_COOKIES', true);
// @codingStandardsIgnoreLine This script does not require login.
require_once(__DIR__ . '/../../../../../config.php');

// We do not want to call require_login() here because we don't want to update 'lastaccess' and keep session alive.
// Last event id seen.
$fromid = optional_param('fromid', -1, PARAM_INT); // FIXME: Might deprecate this.
// Last event id seen.

// Who is the current user making request.
$userid = required_param('userid', PARAM_INT);
$token = required_param('token', PARAM_RAW);

if (\tool_realtime\manager::get_enabled_plugin_name() !== 'phppollmuc') {
    echo json_encode(['error' => 'Plugin is not enabled']);
    exit;
}

core_php_time_limit::raise();
// TODO: might be able to reduce overhead by use the HR Time functionality as we don't need absolute time.
$starttime = microtime(true);
/** @var rtcomms_phppollmuc\plugin $plugin */
$plugin = \tool_realtime\manager::get_plugin();
$maxduration = $plugin->get_request_timeout(); // In seconds as float.
$sleepinterval = $plugin->get_delay_between_checks() * 1000; // In microseconds.

$counter = 0;

while (true) {
    if (!$plugin->validate_token($userid, $token)) {
        // User is no longer logged in or token is wrong. Do not poll any more.
        // We check this in a loop because user session may end while we are still waiting.
        echo json_encode(['error' => 'Can not find an active user session']);
        exit;
    }

    $events = $plugin->get_all($userid);

    if (count($events) > 0) {
        echo json_encode(['success' => 1, 'events' => array_values($events)]);
        exit;
    }

    // Nothing new for this user. Sleep and check again.
    if (microtime(true) - $starttime > $maxduration) {
        echo json_encode(['success' => 1, 'events' => []]);
        exit;
    }
    $counter++;
    usleep($sleepinterval);
}
