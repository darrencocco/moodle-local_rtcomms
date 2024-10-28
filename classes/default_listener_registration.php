<?php

namespace local_rtcomms;

class default_listener_registration implements listener_registration_interface {

    protected $contextid;
    protected $component;
    protected $area;
    protected $itemid;
    protected $handler;
    protected function __construct($contextid, $component, $area, $itemid, $handler) {
        $this->contextid = $contextid;
        $this->component = $component;
        $this->area = $area;
        $this->itemid = $itemid;
        $this->handler = $handler;
    }

    public static function instance(\stdClass $data): listener_registration_interface {
        return new self($data->contextid, $data->component, $data->area, $data->itemid, $data->handler);
    }

    public function get_data(): \stdClass {
        return (object)[
            "contextid" => $this->contextid,
            "component" => $this->component,
            "area" => $this->area,
            "itemid" => $this->itemid,
            "handler" => $this->handler,
        ];
    }

    public function is_interested($contextid, $component, $area, $itemid): bool {
        return $this->match_context_id($contextid) && $this->match_component($component)
            && $this->match_area($area) && $this->match_item_id($itemid);
    }

    protected function match_context_id($contextid) {
        return $this->match($contextid, $this->contextid);
    }
    protected function match_component($component) {
        return $this->match($component, $this->component);
    }

    protected function match_area($area) {
        return $this->match($area, $this->area);
    }

    protected function match_item_id($itemid) {
        return $this->match($itemid, $this->itemid);
    }

    protected function match($candidate, $target) {
        if ($target === "*" || $candidate === $target) {
            return true;
        }
        return false;
    }

    public function get_handler(): string {
        return $this->handler;
    }
}