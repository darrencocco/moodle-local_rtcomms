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
 * Event dispatcher
 * @package local_rtcomms
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rtcomms;
/**
 * Default event dispatcher class.
 */
class dispatcher {
    /**
     * @var self
     */
    protected static $instance;
    /**
     * @var listener_registration_interface[]
     */
    protected $listeners = [];

    /**
     * Registers listeners
     */
    protected function __construct() {
        $callbacks = get_plugins_with_function("rtcomms_listener_registration", "rtcomms.php");
        $temp = [];
        foreach ($callbacks as $plugins) {
            foreach ($plugins as $callback) {
                $temp[] = $callback();
            }
        }
        $this->listeners = array_merge(...$temp);
    }

    /**
     * It's a singleton... sort of.
     * @return dispatcher
     */
    public static function instance(): dispatcher {
        self::$instance = new dispatcher();
        return self::$instance;
    }

    /**
     * Dispatches an event to interested listeners.
     *
     * @param integer $from
     * @param integer $contextid
     * @param string $component
     * @param string $area
     * @param integer $itemid
     * @param array $payload
     * @return void
     */
    public function process_event($from, $contextid, $component, $area, $itemid, $payload): void {
        $interestedlisteners = $this->find_interested_listeners($contextid, $component, $area, $itemid);
        foreach ($interestedlisteners as $listener) {
            $listener->get_handler()::instance()->process_event($from, $contextid, $component, $area, $itemid, $payload);
        }
    }

    /**
     * Filters for listeners interested in a channel.
     *
     * @param $contextid
     * @param $component
     * @param $area
     * @param $itemid
     * @return listener_registration_interface[]
     */
    protected function find_interested_listeners($contextid, $component, $area, $itemid): array {
        return array_filter($this->listeners,
            fn($listener) => $listener->is_interested($contextid, $component, $area, $itemid));
    }


}
