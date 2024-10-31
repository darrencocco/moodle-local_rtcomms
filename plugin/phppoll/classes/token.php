<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Token processor.
 * @package rtcomms_phppoll
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace rtcomms_phppoll;

/**
 * Helpers for verifying the user token.
 */
class token {
    /**
     * Get token for current user and current session
     *
     * @return string
     */
    public static function get_token() {
        global $USER;
        $sid = session_id();
        return self::get_token_for_user($USER->id, $sid);
    }

    /**
     * Get token for a given user and given session
     *
     * @param int $userid
     * @param string $sid
     * @return false|string
     */
    protected static function get_token_for_user(int $userid, string $sid) {
        return substr(md5($sid . '/' . $userid . '/' . get_site_identifier()), 0, 10);
    }

    /**
     * Validate that a token corresponds to one of the users open sessions
     *
     * @param int $userid
     * @param string $token
     * @return bool
     */
    public static function validate_token(int $userid, string $token) {
        global $DB;
        $sessions = $DB->get_records('sessions', ['userid' => $userid]);
        foreach ($sessions as $session) {
            if (self::get_token_for_user($userid, $session->sid) === $token) {
                return true;
            }
        }
        return false;
    }
}
