<?php
$functions = [
    "rtcomms_phppoll_send" => [
        "classname" => "rtcomms_phppoll\\external\\send",
        "description" => "Endpoint for sending messages to the server",
        "type" => "write",
        "ajax" => true,
        "services" => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ]
    ],
];