<?php
namespace local_rtcomms;

interface event_handler_interface {
    public static function instance(): event_handler_interface;
    public function process_event($from, $contextid, $component, $area, $itemid, $payload);
}