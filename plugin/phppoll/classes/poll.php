<?php

namespace rtcomms_phppoll;

class poll {
    protected $tokenprocessor;
    protected $tablename;
    function __construct($tokenprocessor, $tablename) {
        $this->tokenprocessor = $tokenprocessor;
        $this->tablename = $tablename;
    }

    /**
     * Get all notifications for a given user
     *
     * @param int $userid
     * @param int $fromid
     * @param int $fromtimestamp
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function get_all(int $userid, int $fromid, int $fromtimestamp): array {
        global $DB;
        $events = [];
        if ($fromid == -1) {
            $events = $DB->get_records_select($this->tablename,
                'targetuser = :userid
               AND timecreated > :fromtimestamp',
                [
                    'userid' => $userid,
                    'fromtimestamp' => $fromtimestamp,
                ],
                'id', 'id, contextid, component, area, itemid, payload');
        } else {
            $events = $DB->get_records_select($this->tablename,
                'targetuser = :userid
               AND id > :fromid',
                [
                    'userid' => $userid,
                    'fromid' => $fromid,
                ],
                'id', 'id, contextid, component, area, itemid, payload');
        }

        array_walk($events, function(&$item) {
            $item->payload = @json_decode($item->payload, true);
            $context = \context::instance_by_id($item->contextid);
            $item->context = ['id' => $context->id, 'contextlevel' => $context->contextlevel,
                'instanceid' => $context->instanceid];
            unset($item->contextid);
        });
        return $events;
    }

    /**
     * Delay between checks (or between short poll requests), ms
     *
     * @return int sleep time between checks, in milliseconds
     */
    public function get_delay_between_checks(): int {
        $period = get_config('rtcomms_phppoll', 'checkinterval');
        return max($period, 200);
    }

    /**
     * Maximum duration for poll requests
     *
     * @return int time in seconds
     */
    public function get_request_timeout(): float {
        $duration = get_config('rtcomms_phppoll', 'requesttimeout');
        return (isset($duration) && $duration !== false) ? (float)$duration : 30;
    }

    function longpoll($userid, $token, $lastidseen, $since) {
        \core_php_time_limit::raise();
        $starttime = microtime(true);
        $maxduration = $this->get_request_timeout(); // In seconds as float.
        $sleepinterval = $this->get_delay_between_checks() * 1000; // In microseconds.

        while (true) {
            if (!$this->tokenprocessor::validate_token($userid, $token)) {
                // User is no longer logged in or token is wrong. Do not poll any more.
                // We check this in a loop because user session may end while we are still waiting.
                echo json_encode(['error' => 'Can not find an active user session']);
                exit;
            }

            $events = $this->get_all($userid, $lastidseen, $since);

            if (count($events) > 0) {
                echo json_encode(['success' => 1, 'events' => array_values($events)]);
                exit;
            }

            // Nothing new for this user. Sleep and check again.
            if (microtime(true) - $starttime > $maxduration) {
                echo json_encode(['success' => 1, 'events' => []]);
                exit;
            }
            usleep($sleepinterval);
        }
    }

    function shortpoll($userid, $token, $lastidseen, $since) {
        if (!$this->tokenprocessor::validate_token($userid, $token)) {
            // User is no longer logged in or token is wrong. Do not poll any more.
            // We check this in a loop because user session may end while we are still waiting.
            echo json_encode(['error' => 'Can not find an active user session']);
            exit;
        }

        $events = $this->get_all($userid, $lastidseen, $since);

        if (count($events) > 0) {
            echo json_encode(['success' => 1, 'events' => array_values($events)]);
            exit;
        }

        echo json_encode(['success' => 1, 'events' => []]);
        exit;
    }
}