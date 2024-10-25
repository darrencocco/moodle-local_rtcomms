<?php

namespace tool_rtcomms;

interface listener_registration_interface {

    public static function instance(\stdClass $data): listener_registration_interface;
    public function get_data(): \stdClass;
    public function is_interested($contextid, $component, $area, $itemid): bool;
    /**
     * @return event_handler_interface::class
     */
    public function get_handler(): string;
}