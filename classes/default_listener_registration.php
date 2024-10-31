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
 * Basic listener registration implementation.
 * @package local_rtcomms
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rtcomms;

/**
 * Reference implementation of listener registration.
 *
 * Allows for wildcards in the interested channels using "*"
 */
class default_listener_registration implements listener_registration_interface {

    /**
     * @var string match for contextid
     */
    protected $contextid;
    /**
     * @var string match for component
     */
    protected $component;
    /**
     * @var string match for area
     */
    protected $area;
    /**
     * @var string match for itemid
     */
    protected $itemid;
    /**
     * @var string FQN of handler
     */
    protected $handler;

    /**
     * Protected constructor, please use instance static method.
     * @param string $contextid
     * @param string $component
     * @param string $area
     * @param string $itemid
     * @param string $handler
     */
    protected function __construct($contextid, $component, $area, $itemid, $handler) {
        $this->contextid = $contextid;
        $this->component = $component;
        $this->area = $area;
        $this->itemid = $itemid;
        $this->handler = $handler;
    }

    /**
     * See \rtcomms_phppoll\listener_registration_interface::instance.
     * @param \stdClass $data
     * @return listener_registration_interface
     */
    public static function instance(\stdClass $data): listener_registration_interface {
        return new self($data->contextid, $data->component, $data->area, $data->itemid, $data->handler);
    }

    /**
     * See \rtcomms_phppoll\listener_registration_interface::get_data.
     * @return \stdClass
     */
    public function get_data(): \stdClass {
        return (object)[
            "contextid" => $this->contextid,
            "component" => $this->component,
            "area" => $this->area,
            "itemid" => $this->itemid,
            "handler" => $this->handler,
        ];
    }

    /**
     * See \rtcomms_phppoll\listener_registration_interface::is_interested.
     * @param $contextid
     * @param $component
     * @param $area
     * @param $itemid
     * @return bool
     */
    public function is_interested($contextid, $component, $area, $itemid): bool {
        return $this->match_context_id($contextid) && $this->match_component($component)
            && $this->match_area($area) && $this->match_item_id($itemid);
    }

    /**
     * Compares against the matching rule for contextid.
     * @param $contextid
     * @return bool
     */
    protected function match_context_id($contextid) {
        return $this->match($contextid, $this->contextid);
    }

    /**
     * Compares against the matching rule for component.
     * @param $component
     * @return bool
     */
    protected function match_component($component) {
        return $this->match($component, $this->component);
    }

    /**
     * Compares against the matching rule for area.
     *
     * @param $area
     * @return bool
     */
    protected function match_area($area) {
        return $this->match($area, $this->area);
    }

    /**
     * Compares against the matching rule for itemid.
     *
     * @param $itemid
     * @return bool
     */
    protected function match_item_id($itemid) {
        return $this->match($itemid, $this->itemid);
    }

    /**
     * Basic string comparison.
     *
     * Allows for wildcard comparison.
     *
     * @param string $candidate
     * @param string $target
     * @return bool
     */
    protected function match($candidate, $target) {
        if ($target === "*" || $candidate === $target) {
            return true;
        }
        return false;
    }

    /**
     * See \rtcomms_phppoll\listener_registration_interface::get_handler.
     * @return string
     */
    public function get_handler(): string {
        return $this->handler;
    }
}
