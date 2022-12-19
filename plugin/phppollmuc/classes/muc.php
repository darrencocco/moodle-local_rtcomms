<?php
namespace realtimeplugin_phppollmuc;

defined('MOODLE_INTERNAL') || die();

/**
 * Class realtimeplugin_phppollmuc\muc
 *
 * @package     realtimeplugin_phppollmuc
 * @copyright   2024 Darren Cocco
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class muc {
    /** @var \\cache_application */
    protected $eventtracker;
    /** @var \\cache_application */
    protected $eventcache;

    protected $indexmin = 0;
    protected $indexmax = 99999;

    static protected $singleton = null;

    protected function __construct() {
        $this->eventcache = \cache::make('realtimeplugin_phppollmuc', 'events');
        $this->eventtracker = \cache::make('realtimeplugin_phppollmuc', 'tracker');
    }

    public static function get_instance() {
        if (is_null(self::$singleton)) {
            self::$singleton = new muc();
        }
        return self::$singleton;
    }


    public function write_event(array $data, int $targetuserid): bool{
        $lastwrittentracker = $this->generate_cache_item_id($targetuserid);
        // Only one notification can be written at a time per user, gated by this cache element.
        $locked = self::$eventtracker->acquire_lock($lastwrittentracker);
        if (!$locked) {
            return;
        }

        // Work out what the key will be for this event.
        $lastwrittenidnumber = self::$eventtracker->get($lastwrittentracker) ?: 0;
        // FIXME: What happens if there is a cache miss?
        $nextidnumber = $this->get_next_id_number($lastwrittenidnumber);
        $nextkey = $this->generate_cache_item_id($targetuserid, $nextidnumber);
        $data['index'] = $nextidnumber;

        // Write the data.
        self::$eventcache->set($nextkey, $data);
        self::$eventtracker->set($lastwrittentracker, $nextidnumber);

        // Release the write-lock on this notifications store.
        self::$eventtracker->release_lock($lastwrittentracker);
    }

    /**
     * Get all notifications for a given user
     *
     * @param int $userid
     * @param int $fromindex
     * @param int $fromtimestamp
     * @return array
     */
    public function get_all(int $userid, int $fromindex, int $fromtimestamp): array {
        $ids = $this->ids_for_retrieval($userid, $fromindex);

        $fromtimestampseconds = floor($fromtimestamp / 1000) - 10;

        $events = $this->eventcache->get_many($ids);

        $events = array_filter($events, function($event)  use ($fromtimestampseconds) {
            return $event !== false &&
                $event["timecreated"] > $fromtimestampseconds;
        });
        array_walk($events, function(&$item) {
            $context = \context::instance_by_id($item["contextid"]);
            $item["context"] = ['id' => $context->id, 'contextlevel' => $context->contextlevel,
                'instanceid' => $context->instanceid];
            unset($item["contextid"]);
        });
        return $events;
    }

    protected function ids_for_retrieval($userid, $index = -1) {
        if ($index > -1) {
            $range = $this->next_n_indices($index);
        } else {
            $eventtracker = \cache::make('realtimeplugin_phppollmuc', 'tracker');
            $lastwritten = $eventtracker->get($this->generate_cache_item_tracker($userid, $index));
            $range = $this->index_range_from_last_written($lastwritten);
        }
        return array_map(function ($index) use ($userid) {
            return $this->generate_cache_item_id($userid, $index);
        }, $range);
    }

    protected function next_n_indices(int $start, int $count = 100) {
        $min = $start + 1;
        $max = $start + $count;

        if ($min > $this->indexmax) {
            return range($this->indexmin,$count - 1);
        }

        if ($max > $this->indexmax) {
            return array_merge(range($min, $this->indexmax), range($this->indexmin, $max - $this->indexmax));
        }

        return range($min, $max);
    }

    protected function previous_n_indices(int $start, int $count = 20) {
        $max = $start - 1;
        $min = $start - $count;

        if ($max < $this->indexmin) {
            return range($this->indexmax - $count, $this->indexmax);
        }

        if ($min < $this->indexmin) {
            $below = $this->indexmin - $start + $count;
            $above = $start - $this->indexmin;
            return array_merge(range($this->indexmax - $below, $this->indexmax), range($this->indexmin, $above));
        }

        return range($min, $max);
    }

    protected function generate_cache_item_id($userid, $index) {
        return "$userid-$index";
    }

    protected function generate_cache_item_tracker($userid) {
        return "$userid-lastwrittenindex";
    }

    protected function index_range_from_last_written($lastwrittenindex) {
        $before = 200;
        $after = 10;
        return array_merge(
            $this->previous_n_indices($lastwrittenindex, $before),
            [$lastwrittenindex],
            $this->next_n_indices($lastwrittenindex, $after));
    }
}