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
 * Class rtcomms_phppoll\plugin
 *
 * @package     rtcomms_phppoll
 * @copyright   2020 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace rtcomms_phppoll;

defined('MOODLE_INTERNAL') || die();

use Closure;
use tool_realtime\plugin_base;
use get_config;

/**
 * Class rtcomms_phppoll\plugin
 *
 * @package     rtcomms_phppoll
 * @copyright   2020 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin extends plugin_base {

    /** @var bool */
    static protected $initialised = false;
    /** @var string */
    const TABLENAME = 'rtcomms_phppoll';
    static protected $pluginname;
    protected $poll;
    protected $token;

    public function __construct() {
        self::$pluginname = 'phppoll';
        $this->token = new token();
        $this->poll = new poll($this->token, self::TABLENAME);
    }

    /**
     * Is the plugin setup completed
     *
     * @return bool
     */
    public function is_set_up(): bool {
        return true;
    }

    /**
     * Subscribe the current page to receive notifications about events
     *
     * @param \context $context
     * @param string $component
     * @param string $area
     * @param int $itemid
     */
    public function subscribe(\context $context, string $component, string $area, int $itemid): void {
        // TODO check that area is defined only as letters and numbers.
        global $PAGE;
        if (!$this->is_set_up() || !isloggedin() || isguestuser()) {
            return;
        }
        $this->init();
        $fromtimestamp = microtime(true);
        $PAGE->requires->js_call_amd('tool_realtime/api', 'subscribe',
            [ $context->id, $component, $area, $itemid, -1, -1]);
    }

    /**
     * Intitialises realtime tool for Javascript subscriptions
     *
     */
    public function init(): void {
        // TODO check that area is defined only as letters and numbers.;
        if (\tool_realtime\manager::get_enabled_plugin_name() !== self::$pluginname) {
            throw new \coding_exception("Attempted to initialise a realtime plugin that is not enabled.");
        }
        if (!$this->is_set_up() || !isloggedin() || isguestuser() || self::$initialised) {
            return;
        }
        self::$initialised = true;

        $this->init_js();
    }

    protected function init_js(): void {
        global $PAGE, $USER;
        $earliestmessagecreationtime = $_SERVER['REQUEST_TIME'];
        $maxfailures = get_config('rtcomms_phppoll', 'maxfailures');
        $polltype = get_config('rtcomms_phppoll', 'polltype');
        $url = new \moodle_url('/admin/tool/realtime/plugin/phppoll/poll.php');
        $PAGE->requires->js_call_amd('rtcomms_phppoll/realtime',  'init', [[
                "userId" => $USER->id,
                "token" => $this->token::get_token(),
                "pollURLParam" => $url->out(false),
                "maxDelay" => $this->poll->get_delay_between_checks(),
                "maxFailures" => $maxfailures,
                "earliestMessageCreationTime" => $earliestmessagecreationtime,
                "pollType" => $polltype,
            ]]);
    }

    /**
     * Notifies all subscribers about an event
     *
     * @param \context $context
     * @param string $component
     * @param string $area
     * @param int $itemid
     * @param string $userselector
     * @param array|null $payload
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function notify(\context $context, string $component, string $area, int $itemid, Closure $userselector, ?array $payload = null): void {
        global $DB;
        $time = time();
        $targetuserids = $userselector($context, $component, $area, $itemid, $payload);
        if (count($targetuserids) < 1) {
            return;
        }

        $encodedpayload = json_encode($payload ?? []);

        $notifications = array_map(
            function ($userid) use ($time, $encodedpayload, $itemid, $area, $component, $context) {
                return [
                    'contextid' => $context->id,
                    'targetuser' => $userid,
                    'component' => $component,
                    'area' => $area,
                    'itemid' => $itemid,
                    'payload' => $encodedpayload,
                    'timecreated' => $time,
                    'timemodified' => $time,
                ];
            }, $targetuserids);

        $DB->insert_records(self::TABLENAME, $notifications);
    }

    function get_poll_handler()  {
        return $this->poll;
    }
}
