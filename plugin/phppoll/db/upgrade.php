<?php

function xmldb_realtimeplugin_phppoll_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2022030700) {
        // Changing type of field itemid on table realtimeplugin_phppoll to bigint.
        $table = new xmldb_table('realtimeplugin_phppoll');
        $field = new xmldb_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'area');

        // Launch change of type for field itemid.
        $dbman->change_field_type($table, $field);

        // Phppoll savepoint reached.
        upgrade_plugin_savepoint(true, 2022030700, 'realtimeplugin', 'phppoll');
    }

    if ($oldversion < 2024071300) {
        $table = new xmldb_table('realtimeplugin_phppoll');
        $field = new xmldb_field('targetuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, false, null, 'id');
        $index = new xmldb_index('targetuser', XMLDB_TYPE_INTEGER, ['targetuser']);

        $DB->delete_records('realtimeplugin_phppoll');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2024071300, 'realtimeplugin', 'phppoll');
    }
}
