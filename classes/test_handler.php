<?php

namespace tool_rtcomms;

use local_rtcomms\event_handler_interface;

class test_handler implements event_handler_interface {

    public static function instance(): event_handler_interface {
        return new self();
    }

    public function process_event($contextid, $component, $area, $itemid, $payload) {
        // TODO: Implement process_event() method.
    }
}