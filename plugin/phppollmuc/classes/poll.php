<?php
namespace rtcomms_phppollmuc;

use rtcomms_phppoll\token;

class poll extends \rtcomms_phppoll\poll {

    /**
     * @param token $token
     */
    public function __construct($token) {
        $this->tokenprocessor = $token;
    }

    public function get_all(int $userid, int $fromid, int $fromtimestamp): array {
        return muc::get_instance()->get_all($userid, $fromid, $fromtimestamp);
    }

    /**
     * Delay between checks (or between short poll requests), ms
     *
     * @return int sleep time between checks, in milliseconds
     */
    public function get_delay_between_checks(): int {
        $period = get_config('rtcomms_phppollmuc', 'checkinterval');
        return max($period, 200);
    }

    /**
     * Maximum duration for poll requests
     *
     * @return int time in seconds
     */
    public function get_request_timeout(): float {
        $duration = get_config('rtcomms_phppollmuc', 'requesttimeout');
        return (isset($duration) && $duration !== false) ? (float)$duration : 30;
    }
}