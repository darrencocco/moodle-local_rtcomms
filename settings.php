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
 * @package     local_rtcomms
 * @copyright   2020 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $ADMIN->add('tools', new admin_category('rtcomms', new lang_string('pluginname', 'local_rtcomms')));

    $ADMIN->add('reports', new admin_externalpage('local_rtcomms_report',
        get_string('rtcomms:page', 'local_rtcomms'),
        new moodle_url('/local/rtcomms/index.php')));


    $temp = new admin_settingpage('managertcomms', new lang_string('manage', 'local_rtcomms'));
    $temp->add(new \local_rtcomms\setting_manageplugins());
    $ADMIN->add('rtcomms', $temp);

    $temp->add(new admin_setting_configselect('local_rtcomms/enabled',
            new lang_string('enabledplugin', 'local_rtcomms'),
            new lang_string('enabledplugindesc', 'local_rtcomms'), 'phppoll',
            \local_rtcomms\manager::get_installed_plugins_menu())
    );

    foreach (core_plugin_manager::instance()->get_plugins_of_type('rtcomms') as $plugin) {
        /** @var \local_rtcomms\plugininfo\rtcomms $plugin */
        $plugin->load_settings($ADMIN, 'rtcomms', $hassiteconfig);
    }
}
