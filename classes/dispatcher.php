<?php

namespace tool_rtcomms;

class dispatcher {
    /**
     * Implementation notes.
     * - Needs to have setup cached
     * - Needs to be a standard way for a plugin to tell it about having a function listening.
     *   - Best method I could think of is a callback that adds things to a list.
     */
    protected static $instance;
    /**
     * @var listener_registration_interface[]
     */
    protected $listeners = [];

    protected function __construct() {
        $callbacks = get_plugins_with_function("rtcomms_listener_registration");
        $temp = [];
        foreach ($callbacks as $plugins) {
            foreach ($plugins as $callback) {
                $temp[] = $callback;
            }
        }
        $this->listeners = array_merge_recursive($temp);
    }

    public static function instance(): dispatcher {
        self::$instance = new dispatcher();
        return self::$instance;
    }

    public function process_event($contextid, $component, $area, $itemid, $payload): void {
        $interestedhandlers = $this->find_interested_handlers($contextid, $component, $area, $itemid);
        foreach($interestedhandlers as $handler) {
            $handler->get_handler()::instance()->process_event($contextid, $component, $area, $itemid, $payload);
        }
    }

    /**
     * @param $contextid
     * @param $component
     * @param $area
     * @param $itemid
     * @return listener_registration_interface[]
     */
    protected function find_interested_handlers($contextid, $component, $area, $itemid): array {
        return array_filter($this->listeners,
            fn($listener) => $listener->is_interested($contextid, $component, $area, $itemid));
    }


}