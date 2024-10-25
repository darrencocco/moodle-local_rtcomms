<?php
function local_rtcomms_rtcomms_listener_registration() {
    return [
        \tool_rtcomms\default_listener_registration::instance((object)[
            "contextid" => "*",
            "component" => "local_rtcomms",
            "area" => "test",
            "itemid" => "*",
            "handler" => "\\local_rtcomms\\test_handler",
        ]),
    ];
}