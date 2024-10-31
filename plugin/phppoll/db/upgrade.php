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
 * DB upgrade.
 *
 * @package rtcomms_phppoll
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_rtcomms_phppoll_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2022030700) {
        // Changing type of field itemid on table rtcomms_phppoll to bigint.
        $table = new xmldb_table('rtcomms_phppoll');
        $field = new xmldb_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'area');

        // Launch change of type for field itemid.
        $dbman->change_field_type($table, $field);

        // Phppoll savepoint reached.
        upgrade_plugin_savepoint(true, 2022030700, 'rtcomms', 'phppoll');
    }

    if ($oldversion < 2024071300) {
        $table = new xmldb_table('rtcomms_phppoll');
        $field = new xmldb_field('targetuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, false, null, 'id');
        $index = new xmldb_index('targetuser', XMLDB_TYPE_INTEGER, ['targetuser']);

        $DB->delete_records('rtcomms_phppoll');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2024071300, 'rtcomms', 'phppoll');
    }
    return true;
}
