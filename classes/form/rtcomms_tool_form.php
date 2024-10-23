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
 * Class rtcomms_tool_form
 *
 * @package     local_rtcomms
 * @copyright  2020 Daniel Conquit, Matthew Gray, Nicholas Parker, Dan Thistlethwaite
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rtcomms\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Class rtcomms_tool_form
 *
 * @package     local_rtcomms
 * @copyright   2020 Daniel Conquit, Matthew Gray, Nicholas Parker, Dan Thistlethwaite
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rtcomms_tool_form extends \moodleform {
    /**
     * Definition for the Moodle form on the realtime tool reporting page
     */
    public function definition() {
        global $CFG;
        $mform = $this->_form;

        $mform->addElement('text', 'context', get_string('context', 'local_rtcomms'));
        $mform->setType('context', PARAM_INT);

        $mform->addElement('text', 'component', get_string('component', 'local_rtcomms'));
        $mform->setType('component', PARAM_TEXT);

        $mform->addElement('text', 'area', get_string('area', 'local_rtcomms'));
        $mform->setType('area', PARAM_TEXT);

        $mform->addElement('text', 'itemid', get_string('itemid', 'local_rtcomms'));
        $mform->setType('itemid', PARAM_INT);

        $this->add_action_buttons();
    }

    /**
     * Validation for the Moodle form on the realtime tool reporting page
     *
     * @param array $data
     * @param array $files
     *
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // TODO: context and component Moodle checks exists.
        if (preg_match('/[^A-Za-z0-9]/', $data['component'])) {
            $errors['component'] = "Only English letters and digits allowed.";
        }
        if (!preg_match('/[^-]/', $data['area'])) {
            $errors['area'] = "No hyphens (-) allowed.";
        }
        return $errors;
    }
}