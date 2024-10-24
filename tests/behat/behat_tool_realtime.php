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
 * @copyright  2020 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

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
     * Visit a fixture page for testing stuff that is not available in core.
     *
     * @Given /^I am on realtime fixture page$/
     */
    public function i_am_on_realtime_fixture_page() {
        $url = '/local/rtcomms/tests/behat/fixtures/realtime.php';
        $this->getSession()->visit($this->locate_path($url));
    }

}
