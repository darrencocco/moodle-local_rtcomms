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
 * Testing realtime in behat
 *
 * This is not an example of how to use polling! Polling is designed to send notifications to OTHER
 * sessions and other users. This is just a test that can be executed in single-threaded behat.
 *
 * @package    local_rtcomms
 * @copyright  2024 Marina Glancy, Darren Cocco
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../../../config.php');

// Only continue for behat site.
defined('BEHAT_SITE_RUNNING') ||  die();

require_login(0, false);
$PAGE->set_url('/local/rtcomms/tests/fixtures/receive_only.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');


\local_rtcomms\api::init();
$pluginname = \local_rtcomms\manager::get_enabled_plugin_name();
$usercontext = context_user::instance($USER->id);
$usercontextid = $usercontext->id;
echo $OUTPUT->header();
$PAGE->requires->js_amd_inline(<<<EOL
    M.util.js_pending('initrealtimetest');
    require(['jquery', 'local_rtcomms/api'], function($, RealTimeAPI) {
        RealTimeAPI.subscribe({$usercontextid}, 'local_rtcomms', 'test', 0,
            function(event) {
                $('#realtimeresults').append('Received event for component ' + event.component +
                ', area = ' + event.area + ', itemid = ' + event.itemid +
                ', context id = ' + event.context.id +
                ', contextlevel = ' + event.context.contextlevel +
                ', context instanceid = ' + event.context.instanceid +
                ', payload data = ' + event.payload.data + '<br>');
            });

        $('#realtimeresults').append('Realtime plugin - {$pluginname}<br>');
        return M.util.js_complete('initrealtimetest');
    });
EOL
);

?>
<div id="realtimeresults">
</div>
<?php
echo $OUTPUT->footer();
