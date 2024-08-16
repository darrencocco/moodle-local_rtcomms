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
 * Plugin strings are defined here.
 *
 * @package     rtcomms_phppollmuc
 * @category    string
 * @copyright   2020 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['checkinterval'] = 'Check interval, ms';
$string['checkintervaldesc'] = 'Interval between the polling requests to the server and also sleep interval between checks for new events on the server during long polling, in milliseconds, can not be less than {$a} ms';
$string['pluginname'] = 'PHP polling (MUC)';
$string['privacy:metadata'] = 'The PHP polling plugin only stores user information for a short period of time';
$string['requesttimeout'] = 'Maximum duration for polling requests';
$string['requesttimeoutdesc'] = 'Maximum duration of a polling request if there are no new events, set to 0 to use short polling instead of long polling';
$string['component'] = 'Component';
$string['cachedef_events'] = 'User message store';
$string['cachedef_tracker'] = 'User message tracker';
$string['polltype'] = 'Poll type';
$string['polltypedesc'] = 'Which kind of polling strategy to use for requests.';
$string['shortpoll'] = 'Standard polling';
$string['longpoll'] = 'Long polling';
$string['maxfailures'] = 'Max retries';
$string['maxfailuresdesc'] = 'The maximum number of consecutive times a request can fail and be retried before the client assumes something is very wrong in the backend and gives up.';