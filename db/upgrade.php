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
 * Upgrade hooks for block_notices.
 *
 * @package    block_notices
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Run the block_notices upgrade steps.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool
 */
function xmldb_block_notices_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026051700) {
        // Drop the now-unused updatedescription column; the value is computed in JS
        // from timecreated / timemodified.
        $table = new xmldb_table('block_notices');
        $field = new xmldb_field('updatedescription');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_block_savepoint(true, 2026051700, 'notices');
    }

    if ($oldversion < 2026051800) {
        // Per-user read tracking. One row per (noticeid, userid); a notice is
        // unread when timeread is missing or older than the notice's timemodified.
        $table = new xmldb_table('block_notices_read');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('noticeid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('timeread', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('noticeid', XMLDB_KEY_FOREIGN, ['noticeid'], 'block_notices', ['id']);
            $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

            $table->add_index('noticeiduserid', XMLDB_INDEX_UNIQUE, ['noticeid', 'userid']);

            $dbman->create_table($table);
        }

        upgrade_block_savepoint(true, 2026051800, 'notices');
    }

    if ($oldversion < 2026051802) {
        // Grant the capabilities the block_notices_manager role needs to use the user
        // picker on the notice edit form: viewalldetails lets core_user_search_identity
        // run, viewuseridentity lets it return the configured showuseridentity fields.
        $role = $DB->get_record('role', ['shortname' => 'block_notices_manager']);
        if ($role) {
            $systemcontextid = context_system::instance()->id;
            assign_capability('moodle/user:viewalldetails', CAP_ALLOW, $role->id, $systemcontextid, true);
            assign_capability('moodle/site:viewuseridentity', CAP_ALLOW, $role->id, $systemcontextid, true);
        }

        upgrade_block_savepoint(true, 2026051802, 'notices');
    }

    if ($oldversion < 2026051803) {
        // Sites that ran the 2026051802 savepoint earlier only got viewalldetails;
        // viewuseridentity is needed so the picker shows the configured identity
        // fields (username, email, ...). Re-asserting is idempotent.
        $role = $DB->get_record('role', ['shortname' => 'block_notices_manager']);
        if ($role) {
            assign_capability(
                'moodle/site:viewuseridentity',
                CAP_ALLOW,
                $role->id,
                context_system::instance()->id,
                true
            );
        }

        upgrade_block_savepoint(true, 2026051803, 'notices');
    }

    return true;
}
