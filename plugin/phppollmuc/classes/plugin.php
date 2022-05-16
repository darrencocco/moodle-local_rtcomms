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
 * Class realtimeplugin_phppollmuc\plugin
 *
 * @package     realtimeplugin_phppollmuc
 * @copyright   2022 Darren Cocco
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace realtimeplugin_phppollmuc;

defined('MOODLE_INTERNAL') || die();

use tool_realtime\plugin_base;

/**
 * Class realtimeplugin_phppollmuc\plugin
 *
 * @package     realtimeplugin_phppollmuc
 * @copyright   2020 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin extends plugin_base {

    /** @var bool */
    static protected $initialised = false;
    /** @var string */
    const TABLENAME = 'realtimeplugin_phppollmuc';

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
        // TODO: this should be JS with a web-service providing assistance.
        global $PAGE, $USER, $DB;
        if (!$this->is_set_up() || !isloggedin() || isguestuser()) {
            return;
        }
        self::init();

        $eventtracker = \cache::make('realtimeplugin_phppollmuc', 'tracker');
        $fromid = $eventtracker->get($this->generate_cache_item_tracker($context->id, $component, $area, $itemid));
        if ($fromid === false) {
            $fromid = 0;
        }
        $fromtimestamp = microtime(true);

        // TODO: WTF is this  $url definition for?
        $url = new \moodle_url('/admin/tool/realtime/plugin/phppollmuc/poll.php');
        $PAGE->requires->js_call_amd('realtimeplugin_phppollmuc/realtime', 'subscribe',
            [ $context->id, $component, $area, $itemid, $fromid, $fromtimestamp]);
    }

    /**
     * Intitialises realtime tool for Javascript subscriptions
     *
     */
    public function init(): void {
        // TODO check that area is defined only as letters and numbers.
        // TODO: This should probably be pure JS with some backend web-services to handle things.
        global $PAGE, $USER;
        if (!$this->is_set_up() || !isloggedin() || isguestuser() || self::$initialised) {
            return;
        }
        self::$initialised = true;
        $url = new \moodle_url('/admin/tool/realtime/plugin/phppollmuc/poll.php');
        $PAGE->requires->js_call_amd('realtimeplugin_phppollmuc/realtime',  'init',
            [$USER->id, self::get_token(), $url->out(false),
                $this->get_delay_between_checks()]);
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
    public function notify(\context $context, string $component, string $area, int $itemid, ?array $payload = null): void {
        $time = time();
        $data = [
            'contextid' => $context->id,
            'component' => $component,
            'area' => $area,
            'itemid' => $itemid,
            'payload' => $payload,
            'timecreated' => $time
        ];

        $lastwrittentracker = $this->generate_cache_item_tracker($context->id, $component, $area, $itemid);

        // TODO: Cache definition.
        $eventcache = \cache::make('realtimeplugin_phppollmuc', 'events');
        $eventtracker = \cache::make('realtimeplugin_phppollmuc', 'tracker');

        // Only one notification can be written at a time, gated by this cache element.
        $eventtracker->acquire_lock($lastwrittentracker);

        // Work out what the key will be for this event.
        $lastwrittenid = $eventtracker->get($lastwrittentracker);
        $nextkey = $this->generate_cache_item_id($lastwrittenid + 1, $context->id, $component, $area, $itemid);
        $data['index'] = $lastwrittenid + 1;

        // Write the data.
        $eventcache->set($nextkey, $data);
        $eventtracker->set($lastwrittentracker, $lastwrittenid + 1);

        // Release the write lock on this notifications store.
        $eventtracker->release_lock($lastwrittentracker);
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
    public static function validate_token(int $userid, string $token) {
        global $DB;
        $sessions = $DB->get_records('sessions', ['userid' => $userid]);
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
     * @param int $contextidentifier
     * @param int $fromid
     * @param string $component
     * @param string $area
     * @param int $itemid
     * @param float $fromtimestamp
     * @return array
     */
    public function get_all(int $contextidentifier,
                            int $fromindex, string $component,
                            string $area, int $itemid,
                            float $fromtimestamp): array {
        $eventcache = \cache::make('realtimeplugin_phppollmuc', 'events');

        $ids = $this->ids_for_retrieval($fromindex, $contextidentifier, $component, $area, $itemid);

        $events = [];
        $fromtimestampseconds = floor($fromtimestamp / 1000);

        $events = $eventcache->get_many($ids);

        $events = array_filter($events, function($event)  use ($fromtimestampseconds) {
            return $event !== false &&
                $event["timecreated"] > $fromtimestampseconds;
        });
        array_walk($events, function(&$item) {
            $context = \context::instance_by_id($item["contextid"]);
            $item["context"] = ['id' => $context->id, 'contextlevel' => $context->contextlevel,
                'instanceid' => $context->instanceid];
            unset($item["contextid"]);
        });
        return $events;
    }

    protected function generate_cache_item_id($index, $contextid, $component, $area, $itemid) {
        return "$contextid-$component-$area-$itemid-$index";
    }

    protected function generate_cache_item_tracker($contextid, $component, $area, $itemid) {
        return "$contextid-$component-$area-$itemid-lastwrittenindex";
    }

    protected function index_range_from_last_written($lastwrittenindex) {
        $before = 200;
        $after = 10;
        $min = $lastwrittenindex > $before ? $lastwrittenindex - $before : 0;
        $max = $lastwrittenindex + $after;
        return range($min, $max);
    }

    protected function index_range_from_last_seen($lastseenindex) {
        $after = 100;
        $min = $lastseenindex + 1;
        $max = $lastseenindex + $after;
        return range($min, $max);
    }

    protected function ids_for_retrieval($index, $contextid, $component, $area, $itemid) {
        if ($index > 0) {
            $range = $this->index_range_from_last_seen($index);
        } else {
            // TODO: cache definition
            $eventtracker = \cache::make('realtimeplugin_phppollmuc', 'tracker');
            $lastwritten = $eventtracker->get($this->generate_cache_item_tracker($contextid, $component, $area, $itemid));
            $range = $this->index_range_from_last_written($lastwritten);
        }
        return array_map(function ($index) use ($contextid, $component, $area, $itemid) {
            return $this->generate_cache_item_id($index, $contextid, $component, $area, $itemid);
        }, $range);
    }

    /**
     * Delay between checks (or between short poll requests), ms
     *
     * @return int sleep time between checks, in milliseconds
     */
    public function get_delay_between_checks(): int {
        $period = get_config('realtimeplugin_phppollmuc', 'checkinterval');
        return max($period, 200);
    }

    /**
     * Maximum duration for poll requests
     *
     * @return int time in seconds
     */
    public function get_request_timeout(): float {
        $duration = get_config('realtimeplugin_phppollmuc', 'requesttimeout');
        return (isset($duration) && $duration !== false) ? (float)$duration : 30;
    }
}
