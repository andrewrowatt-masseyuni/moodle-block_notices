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

namespace block_notices\privacy;

use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\writer;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;

/**
 * Privacy Subsystem for blocks_notices.
 *
 * @package    block_notices
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin has data.
    \core_privacy\local\metadata\provider,

    // This plugin currently implements the original plugin\provider interface.
    \core_privacy\local\request\plugin\provider,

    // This plugin is capable of determining which users have data within it.
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Return the fields which contain personal data.
     *
     * @param collection $collection a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $collection): collection {
        // The 'local_notices' table stores information about individual fault notices.
        $collection->add_database_table(
            'block_notices',
            [
                'createdby' => 'privacy:metadata:blocks_notices:createdby',
                'modifiedby' => 'privacy:metadata:blocks_notices:modifiedby',
                'content' => 'privacy:metadata:blocks_notices:content',
            ],
            'privacy:metadata:blocks_notices'
        );

        return $collection;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        // Get the list of users who have data in this context.

        $notices = \block_notices\notices::get_notices_admin($context->instanceid);

        foreach ($notices as $notice) {
            // Note that the add_user function convieniently handles duplicates.
            $userlist->add_user($notice->createdby);
            $userlist->add_user($notice->modifiedby);
        }
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int           $userid       The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {

        $contextlist = new contextlist();

        $params = [
            'contextlevel'  => CONTEXT_COURSE,
            'createdby' => $userid,
            'modifiedby' => $userid,
        ];

        // Coures with the block.
        $sql = "SELECT c.id
                  FROM {block_notices} b
                  JOIN {context} c on c.instanceid = b.courseid and c.contextlevel = :contextlevel
                  WHERE b.createdby = :createdby or b.modifiedby = :modifiedby
        ";
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Implements delete_data_for_user
     * @param \core_privacy\local\request\approved_contextlist $contextlist
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist as $context) {
            \block_notices\notices::delete_notices_by_user($context->instanceid, $userid);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();

        list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params = array_merge(['courseid' => $context->instanceid], $userinparams);

        $DB->delete_records_select(
            'block_notices',
            "courseid = :courseid AND (createdby {$userinsql} or modifiedby {$userinsql})", $params);
    }

    /**
     * Implements delete_data_for_all_users_in_context
     * @param \context $context
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if (!is_a($context, \context_system::class)) {
            return;
        }

        \block_notices\notices::delete_all_notices();
    }



    /**
     * Implements export_user_data
     * @param \core_privacy\local\request\approved_contextlist $contextlist
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        $context = \context_system::instance();

        $userid = $contextlist->get_user()->id;
        $notices = \block_notices\notices::get_notices_by_user($userid);

        if (count($notices) == 0) {
            return;
        }

        $noticesdata = [];

        $datalabel = get_string('notices', 'block_notices');

        foreach ($notices as $notice) {
            $noticedata = [$datalabel => $notice->payload];
            $noticesdata[] = (object)$noticedata;
        }

        $context = \context_system::instance();

        // Add the data to the context.
        writer::with_context($context)->export_data(
            [get_string('notices', 'block_notices')],
            (object)[get_string('notices', 'block_notices') => $noticesdata]
        );
    }
}
