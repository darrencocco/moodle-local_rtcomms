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
 * Class plugin_base
 *
 * @package     local_rtcomms
 * @copyright   2020 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rtcomms;

use Closure;

defined('MOODLE_INTERNAL') || die();

/**
 * Class plugin_base
 *
 * @package     local_rtcomms
 * @copyright   2020 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class plugin_base {

    /**
     * Name of this plugin
     *
     * @return false|string
     */
    public function get_name() {
        $parts = preg_split("|\\\\|", get_class($this), -1, PREG_SPLIT_NO_EMPTY);
        return substr($parts[0], strlen(manager::PLUGINTYPE) + 1);
    }

    /**
     * Is current plugin enabled
     *
     * @return bool
     */
    public function is_enabled(): bool {
        return $this->get_name() === manager::get_enabled_plugin_name();
    }

    /**
     * Instance of this plugin
     *
     * Can be overridden to return singleton
     *
     * @return static
     */
    public static function get_instance() {
        return new static();
    }

    /**
     * Is the plugin setup completed
     *
     * @return bool
     */
    abstract public function is_set_up(): bool;

    /**
     * Subscribe the current page to receive notifications about events
     *
     * @param \context $context
     * @param string $component
     * @param string $area
     * @param int $itemid
     */
    abstract public function subscribe(\context $context, string $component, string $area, int $itemid): void;


    /**
     * Set up realtime tool on page
     */
    abstract public function init(): void;

    /**
     * Notifies all subscribers about an event
     *
     * @deprecated
     * @param \context $context
     * @param string $component
     * @param string $area
     * @param int $itemid
     * @param Closure $userselector
     * @param array|null $payload
     */
    public function notify(\context $context, string $component, string $area, int $itemid, Closure $userselector, ?array $payload = null): void {
        debugging('Use of notify is deprecated', DEBUG_DEVELOPER);
        $this->send_to_clients($context, $component, $area, $itemid, $userselector, $payload);
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
    abstract public function send_to_clients(\context $context, string $component, string $area, int $itemid, Closure $userselector, ?array $payload = null): void;

    public function process_event($from, $contextid, $component, $area, $itemid, $payload): void {
        dispatcher::instance()->process_event($from, $contextid, $component, $area, $itemid, $payload);
    }
}
