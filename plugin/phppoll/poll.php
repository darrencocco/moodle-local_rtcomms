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

// Who is the current user making request.
$userid = required_param('userid', PARAM_INT);
$token = required_param('token', PARAM_RAW);

$lastidseen = optional_param('lastidseen', -1, PARAM_INT);
$since = optional_param('since', -1, PARAM_INT);

if (\tool_realtime\manager::get_enabled_plugin_name() !== 'phppoll') {
    echo json_encode(['error' => 'Plugin is not enabled']);
    exit;
}

$plugin = \tool_realtime\manager::get_plugin();

if ($lastidseen === -1 && $since === -1) {
    // TODO: Throw a required param like exception as one of the two must be defined.
}

$polltype = get_config('rtcomms_phppoll', 'polltype');

if ($polltype === 'short') {
    $plugin->get_poll_handler()->shortpoll($userid, $token, $lastidseen, $since);
} elseif ($polltype === 'long') {
    $plugin->get_poll_handler()->longpoll($userid, $token, $lastidseen, $since);
} else {
    echo json_encode(['error' => 'Unknown poll type']);
    exit;
}