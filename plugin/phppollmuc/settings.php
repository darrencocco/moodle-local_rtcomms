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
 * Plugin administration pages are defined here.
 *
 * @package     realtimeplugin_phppollmuc
 * @copyright   2024 Darren Cocco
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings->add(new admin_setting_configduration('realtimeplugin_phppollmuc/requesttimeout',
            new lang_string('requesttimeout', 'realtimeplugin_phppollmuc'),
            new lang_string('requesttimeoutdesc', 'realtimeplugin_phppollmuc'), 30)
    );

    $settings->add(new admin_setting_configtext('realtimeplugin_phppollmuc/checkinterval',
            new lang_string('checkinterval', 'realtimeplugin_phppollmuc'),
            new lang_string('checkintervaldesc', 'realtimeplugin_phppollmuc', 200), 1000, PARAM_INT)
    );

    $settings->add(new admin_setting_configtext('realtimeplugin_phppollmuc/maxfailures',
            new lang_string('maxfailures', 'realtimeplugin_phppollmuc'),
            new lang_string('maxfailuresdesc', 'realtimeplugin_phppollmuc'), 5, PARAM_INT)
    );

    $settings->add(new admin_setting_configselect('realtimeplugin_phppollmuc/polltype',
        new lang_string('polltype', 'realtimeplugin_phppollmuc'),
        new lang_string('polltypedesc', 'realtimeplugin_phppollmuc'),
        'short',
        [
            'short' => new lang_string('shortpoll', 'realtimeplugin_phppollmuc'),
            'long' => new lang_string('longpoll', 'realtimeplugin_phppollmuc')
        ]));
}
