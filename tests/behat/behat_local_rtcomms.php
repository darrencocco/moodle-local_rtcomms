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
 * local_rtcomms related steps definitions.
 *
 * @package    local_rtcomms
 * @category   test
 * @copyright  2024 Marina Glancy, Darren Cocco
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * local_rtcomms related steps definitions.
 *
 * @package    local_rtcomms
 * @category   test
 * @copyright  2020 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_rtcomms extends behat_base {

    /**
     * Navigate to the send to server test page.
     *
     * @Given /^I'm on the real time comms send fixture page$/
     */
    public function i_am_on_the_realtime_comms_send_fixture_page() {
        $fixtureurl = '/local/rtcomms/tests/fixtures/send_to_server.php';
        $this->execute('behat_general::i_visit', [$fixtureurl]);
    }

    /**
     * Navigate to the receive only test page.
     *
     * @Given /^I'm on the real time comms receive fixture page$/
     */
    public function i_am_on_the_realtime_comms_receive_fixture_page() {
        $fixtureurl = '/local/rtcomms/tests/fixtures/receive_only.php';
        $this->execute('behat_general::i_visit', [$fixtureurl]);
    }

    /**
     * Sends a message to a user via the real time comms api.
     *
     * Channel format is {contextid}/{component}/{module}/{itemid}
     * There are two special options for the contextid
     * which are "{user}"(user context) or "{system}"(system context).
     *
     * The payload is expected to be a JSON object.
     *
     * @Given /^A message is sent to "(?P<username_string>(?:[^"]|\\")*)" in the channel "(?P<channel_string>(?:[^"]|\\")*)" with the contents "(?P<payload_string>(?:[^"]|\\")*)"$/
     */
    public function a_message_is_sent_to_the_client(string $username, string $channel, string $payload) {
        global $DB;
        $targetuser = $DB->get_record('user', ["username" => $username], "*", MUST_EXIST);

        $decodedpayload = json_decode($payload, true);

        list($contextid, $component, $area, $itemid) = explode("/", $channel);
        if (is_numeric($contextid)) {
            $contextid = (int) $contextid;
            $context = context::instance_by_id($contextid);
        } else if ($contextid === "{user}") {
            $context = context_user::instance($targetuser->id);
        } else if ($contextid === "{system}") {
            $context = context_system::instance();
        } else {
            throw new coding_exception("Unknown context");
        }

        \local_rtcomms\api::send_to_clients($context, $component, $area, $itemid, fn() => [$targetuser->id],
            $decodedpayload);
    }
}
