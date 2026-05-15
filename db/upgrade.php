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
 * Upgrade steps for block_notices.
 *
 * @package    block_notices
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Run upgrade steps for block_notices.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool
 */
function xmldb_block_notices_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026051500) {
        $table = new xmldb_table('block_notices');

        $field = new xmldb_field('ownerid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'modifiedbyuserid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Existing notices become owned by their original creator so they remain editable.
        $DB->execute('UPDATE {block_notices} SET ownerid = createdbyuserid WHERE ownerid IS NULL');

        $key = new xmldb_key('ownerid', XMLDB_KEY_FOREIGN, ['ownerid'], 'user', ['id']);
        $dbman->add_key($table, $key);

        \block_notices\util::provision_default_roles();

        upgrade_block_savepoint(true, 2026051500, 'notices');
    }

    if ($oldversion < 2026051501) {
        $table = new xmldb_table('block_notices');
        $field = new xmldb_field(
            'exclusive',
            XMLDB_TYPE_INTEGER,
            '1',
            null,
            XMLDB_NOTNULL,
            null,
            '0',
            'staffonly'
        );
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_block_savepoint(true, 2026051501, 'notices');
    }

    return true;
}
