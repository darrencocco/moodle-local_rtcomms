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
}
