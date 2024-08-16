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
namespace rtcomms_phppollmuc;

defined('MOODLE_INTERNAL') || die();

use Closure;
use tool_realtime\plugin_base;

/**
 * Class rtcomms_phppollmuc\plugin
 *
 * @package     rtcomms_phppollmuc
 * @copyright   2024 Darren Cocco
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin extends plugin_base {

    /** @var bool */
    static protected $initialised = false;
    /** @var string */
    const TABLENAME = 'rtcomms_phppollmuc';


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
        global $PAGE, $USER;
        if (!$this->is_set_up() || !isloggedin() || isguestuser()) {
            return;
        }
        $this->init();

        $eventtracker = \cache::make('rtcomms_phppollmuc', 'tracker');
        $fromid = $eventtracker->get($USER->id) ?: 0;
        $fromtimestamp = microtime(true);

        $PAGE->requires->js_call_amd('rtcomms_phppoll/realtime', 'subscribe',
            [ $context->id, $component, $area, $itemid, $fromid, $fromtimestamp]);
    }

    /**
     * Intitialises realtime tool for Javascript subscriptions
     *
     */
    public function init(): void {
        global $PAGE, $USER;
        if (!$this->is_set_up() || !isloggedin() || isguestuser() || self::$initialised) {
            return;
        }
        self::$initialised = true;
        $earliestmessagecreationtime = $_SERVER['REQUEST_TIME'];
        $maxfailures = get_config('rtcomms_phppollmuc', 'maxfailures');
        $polltype = get_config('rtcomms_phppollmuc', 'polltype');
        $url = new \moodle_url('/admin/tool/realtime/plugin/phppollmuc/poll.php');
        $PAGE->requires->js_call_amd('rtcomms_phppoll/realtime',  'init',
            [$USER->id, self::get_token(), $url->out(false), $this->get_delay_between_checks(),
                $maxfailures, $earliestmessagecreationtime, $polltype]);
    }

    /**
     * Notifies all subscribers about an event
     *
     * @param \context $context
     * @param string $component
     * @param string $area
     * @param int $itemid
     * @param Closure $userselector
     * @param array|null $payload
     */
    public function notify(\context $context, string $component, string $area, int $itemid, Closure $userselector, ?array $payload = null): void {
        $time = time();
        $data = [
            'contextid' => $context->id,
            'component' => $component,
            'area' => $area,
            'itemid' => $itemid,
            'payload' => $payload,
            'timecreated' => $time
        ];

        $targetuserids = $userselector($context, $component, $area, $itemid, $payload);

        if (count($targetuserids) < 1) {
            return;
        }

        $mucinterface = muc::get_instance();

        $failedtowrite = [];

        foreach ($targetuserids as $targetuserid) {
            if (!$mucinterface->write_event($data, $targetuserid)) {
                $failedtowrite[] = $targetuserid;
            }
        }

        if (count($failedtowrite) > 0) {
            $doublefailed = [];
            usleep(100);
            foreach($failedtowrite as $targetuserid) {
                if (!$mucinterface->write_event($data, $targetuserid)) {
                    $doublefailed[] = $targetuserid;
                }
            }
            // TODO: write out all double failed as error messages.
        }
    }

    /**
     * Get token for current user and current session
     *
     * @return string
     */
    public static function get_token() {
        global $USER;
        $sid = session_id();
        return self::get_token_for_user($USER->id, $sid);
    }

    /**
     * Get token for a given user and given session
     *
     * @param int $userid
     * @param string $sid
     * @return false|string
     */
    protected static function get_token_for_user(int $userid, string $sid) {
        return substr(md5($sid . '/' . $userid . '/' . get_site_identifier()), 0, 10);
    }

    /**
     * Validate that a token corresponds to one of the users open sessions
     *
     * @param int $userid
     * @param string $token
     * @return bool
     */
    public function validate_token(int $userid, string $token) {
        global $DB;
        $sessions = \core\session\manager::get_sessions_by_userid($userid);
        foreach ($sessions as $session) {
            if (self::get_token_for_user($userid, $session->sid) === $token) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all notifications for a given user
     *
     * @param int $userid
     * @param int $fromindex
     * @param int $fromtimestamp
     * @return array
     */
    public function get_all(int $userid, int $fromindex, int $fromtimestamp): array {
        return muc::class->get_all($userid, $fromindex, $fromtimestamp);
    }

    /**
     * Delay between checks (or between short poll requests), ms
     *
     * @return int sleep time between checks, in milliseconds
     */
    public function get_delay_between_checks(): int {
        $period = get_config('rtcomms_phppollmuc', 'checkinterval');
        return max($period, 200);
    }

    /**
     * Maximum duration for poll requests
     *
     * @return int time in seconds
     */
    public function get_request_timeout(): float {
        $duration = get_config('rtcomms_phppollmuc', 'requesttimeout');
        return (isset($duration) && $duration !== false) ? (float)$duration : 30;
    }
}
