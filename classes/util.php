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

namespace block_notices;

/**
 * Class util
 *
 * @package    block_notices
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class util {
    /**
     * Returns true if username is 8 digits
     *
     * @return bool
     */
    public static function is_student(): bool {
        global $USER;

        return preg_match('/^\d{8}$/', $USER->username) == 1;
    }

    /**
     * Returns true if username is "st" account format
     *
     * @return bool
     */
    public static function is_st_account(): bool {
        global $USER;

        return preg_match('/^st\d{6}$/', $USER->username) == 1;
    }

    /**
     * Returns true if username is not a student or "st" account
     *
     * @return bool
     */
    public static function is_staff(): bool {
        return !self::is_student() && !self::is_st_account();
    }

    /**
     * Create the default 'Notices manager' system role shipped with block_notices, if not already present.
     *
     * Called from db/install.php (fresh installs) and db/upgrade.php (upgrades from before 2026051500).
     * Safe to re-run — the role is only created when its shortname is absent.
     *
     * @return void
     */
    public static function provision_default_roles(): void {
        global $CFG, $DB;
        require_once($CFG->libdir . '/accesslib.php');

        // When called from db/upgrade.php, Moodle has not yet processed the updated
        // db/access.php for this plugin (that happens after xmldb_*_upgrade returns).
        // Force capabilities to be installed first so assign_capability() can find them.
        update_capabilities('block_notices');

        if ($DB->record_exists('role', ['shortname' => 'block_notices_manager'])) {
            return;
        }

        $roleid = create_role(
            get_string('role_manager_name', 'block_notices'),
            'block_notices_manager',
            get_string('role_manager_description', 'block_notices')
        );
        set_role_contextlevels($roleid, [CONTEXT_SYSTEM]);
        $systemcontextid = \context_system::instance()->id;
        assign_capability('block/notices:manageallnotices', CAP_ALLOW, $roleid, $systemcontextid);
        // Required so the role can drive the user picker on the notice edit form:
        // viewalldetails lets core_user_search_identity run, viewuseridentity lets it
        // return the configured showuseridentity fields (username, email, ...).
        assign_capability('moodle/user:viewalldetails', CAP_ALLOW, $roleid, $systemcontextid);
        assign_capability('moodle/site:viewuseridentity', CAP_ALLOW, $roleid, $systemcontextid);
    }
}
