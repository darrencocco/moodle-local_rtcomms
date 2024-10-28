<?php

namespace local_rtcomms;

use local_rtcomms\api;
use local_rtcomms\event_handler_interface;

/**
 * Logs and reflects messages
 */
class test_handler implements event_handler_interface {

    public static function instance(): event_handler_interface {
        return new self();
    }

    /**
     * Logs and reflects incoming messages.
     *
     * @param $contextid
     * @param $component
     * @param $area
     * @param $itemid
     * @param $payload
     * @return void
     * @throws \coding_exception
     */
    public function process_event($from, $contextid, $component, $area, $itemid, $payload) {
        api::send_to_clients(\context::instance_by_id($contextid), $component, $area, $itemid,
            fn() => [$from], $payload);
    }
}