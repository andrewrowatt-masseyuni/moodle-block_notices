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
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;

// phpcs:disable Universal.OOStructures.AlphabeticExtendsImplements.ImplementsWrongOrderWithComments
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
// phpcs:enable Universal.OOStructures.AlphabeticExtendsImplements.ImplementsWrongOrderWithComments

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
                'createdbyuserid' => 'privacy:metadata:blocks_notices:createdbyuserid',
                'modifiedbyuserid' => 'privacy:metadata:blocks_notices:modifiedbyuserid',
                'additionaleditorid' => 'privacy:metadata:blocks_notices:additionaleditorid',
                'content' => 'privacy:metadata:blocks_notices:content',
            ],
            'privacy:metadata:blocks_notices'
        );

        $collection->add_database_table(
            'block_notices_read',
            [
                'userid' => 'privacy:metadata:blocks_notices_read:userid',
                'noticeid' => 'privacy:metadata:blocks_notices_read:noticeid',
                'timeread' => 'privacy:metadata:blocks_notices_read:timeread',
            ],
            'privacy:metadata:blocks_notices_read'
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
        if (!is_a($context, \context_course::class)) {
            return;
        }
        // Get the list of users who have data in this context.

        $notices = \block_notices\notices::get_notices_admin($context->instanceid);

        foreach ($notices as $notice) {
            // Note that the add_user function convieniently handles duplicates.
            $userlist->add_user($notice->createdbyuserid);
            $userlist->add_user($notice->modifiedbyuserid);
            if (!empty($notice->additionaleditorid)) {
                $userlist->add_user($notice->additionaleditorid);
            }
        }

        // Users who have read tracking rows against any notice in this course.
        $userlist->add_from_sql(
            'userid',
            'SELECT r.userid FROM {block_notices_read} r
              JOIN {block_notices} b ON b.id = r.noticeid
              WHERE b.courseid = :courseid',
            ['courseid' => $context->instanceid]
        );
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
            'createdbyuserid' => $userid,
            'modifiedbyuserid' => $userid,
            'additionaleditorid' => $userid,
        ];

        // Coures with the block.
        $sql = "SELECT c.id
                  FROM {block_notices} b
                  JOIN {context} c on c.instanceid = b.courseid and c.contextlevel = :contextlevel
                  WHERE b.createdbyuserid = :createdbyuserid
                     or b.modifiedbyuserid = :modifiedbyuserid
                     or b.additionaleditorid = :additionaleditorid
        ";
        $contextlist->add_from_sql($sql, $params);

        // Courses where the user has read tracking rows.
        $readsql = "SELECT c.id
                      FROM {block_notices_read} r
                      JOIN {block_notices} b ON b.id = r.noticeid
                      JOIN {context} c ON c.instanceid = b.courseid AND c.contextlevel = :contextlevel
                      WHERE r.userid = :userid";
        $contextlist->add_from_sql($readsql, [
            'contextlevel' => CONTEXT_COURSE,
            'userid' => $userid,
        ]);

        return $contextlist;
    }

    /**
     * Implements delete_data_for_user
     * @param \core_privacy\local\request\approved_contextlist $contextlist
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist as $context) {
            // Delete this user's reads against notices in this course before the notices
            // themselves are removed, so the IN-subquery can still resolve.
            $DB->delete_records_select(
                'block_notices_read',
                'userid = :userid AND noticeid IN (SELECT id FROM {block_notices} WHERE courseid = :courseid)',
                ['userid' => $userid, 'courseid' => $context->instanceid]
            );

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
        if (!is_a($context, \context_course::class)) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        // Delete read rows for the supplied users before deleting notices, so the
        // IN-subquery can still resolve to notice ids in this course.
        [$readuseridsql, $readuseridparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $DB->delete_records_select(
            'block_notices_read',
            "userid {$readuseridsql}
                AND noticeid IN (SELECT id FROM {block_notices} WHERE courseid = :courseid)",
            array_merge(['courseid' => $context->instanceid], $readuseridparams)
        );

        [$createdbyuseriduserinsql, $userinparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params = array_merge(['courseid' => $context->instanceid], $userinparams);
        [$modifiedbyuseriduserinsql, $userinparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params = array_merge($params, $userinparams);
        [$additionaleditoriduserinsql, $userinparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params = array_merge($params, $userinparams);

        $DB->delete_records_select(
            'block_notices',
            "courseid = :courseid
                AND (createdbyuserid {$createdbyuseriduserinsql}
                     OR modifiedbyuserid {$modifiedbyuseriduserinsql}
                     OR additionaleditorid {$additionaleditoriduserinsql})",
            $params
        );
    }

    /**
     * Implements delete_data_for_all_users_in_context
     * @param \context $context
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if (!is_a($context, \context_course::class)) {
            return;
        }

        \block_notices\notices::delete_all_notices($context->instanceid);
    }



    /**
     * Implements export_user_data
     * @param \core_privacy\local\request\approved_contextlist $contextlist
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $context = \context_system::instance();

        $userid = $contextlist->get_user()->id;
        $notices = \block_notices\notices::get_notices_by_user($userid);

        if (count($notices) > 0) {
            $noticesdata = [];

            foreach ($notices as $notice) {
                $noticedata = [
                    'title' => $notice->title,
                    'content' => $notice->content, ];
                $noticesdata[] = (object)$noticedata;

                // Add the data to the context.
                writer::with_context($context)->export_data(
                    [get_string('notices', 'block_notices')],
                    (object)[get_string('notices', 'block_notices') => $noticesdata]
                );
            }
        }

        // Per-context export of the read tracking rows for this user.
        foreach ($contextlist as $usercontext) {
            if (!is_a($usercontext, \context_course::class)) {
                continue;
            }
            $reads = $DB->get_records_sql(
                'SELECT r.id, r.timeread, b.title
                   FROM {block_notices_read} r
                   JOIN {block_notices} b ON b.id = r.noticeid
                  WHERE r.userid = :userid AND b.courseid = :courseid',
                ['userid' => $userid, 'courseid' => $usercontext->instanceid]
            );
            if (empty($reads)) {
                continue;
            }
            $readsdata = [];
            foreach ($reads as $read) {
                $readsdata[] = (object)[
                    'title' => $read->title,
                    'timeread' => transform::datetime($read->timeread),
                ];
            }
            writer::with_context($usercontext)->export_data(
                [get_string('reads', 'block_notices')],
                (object)[get_string('reads', 'block_notices') => $readsdata]
            );
        }
    }
}
