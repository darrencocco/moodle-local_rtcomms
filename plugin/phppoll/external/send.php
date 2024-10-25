<?php
namespace rtcomms_phppoll\external;

use external_value;
use local_rtcomms\api;

class send extends \core_external\external_api {
    public static function execute_parameters(): \external_function_parameters {
        return new \external_function_parameters([
            "contextid" => new \external_value(PARAM_INT, "context for message"),
            "component" => new \external_value(PARAM_TEXT, "component/plugin name"),
            "area" => new external_value(PARAM_TEXT, "plugin area"),
            "itemid" => new external_value(PARAM_INT, "item id for area"),
            "payload" => new external_value(PARAM_RAW, "message payload"),
        ]);
    }
    public static function execute_returns() {}
    public static function execute($contextid, $component, $area, $itemid, $payload) {
        $params = self::validate_parameters(self::execute_parameters(), [
            "contextid" => $contextid,
            "component" => $component,
            "area" => $area,
            "itemid" => $itemid,
            "payload" => $payload,
        ]);
        api::send_to_server($params['contextid'], $params['component'],
            $params['area'], $params['itemid'],
            json_decode($params['payload'], false));
    }
}