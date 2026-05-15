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
 * The main notice/s class functions. Inspired by mod_board et al.
 *
 * @package    block_notices
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notices {
    /**
     * @var int defining so it can be used for filtering
     */
    public const NOTICE_VISIBLE = 1;

    /**
     * @var int defining so it can be used for filtering
     */
    public const NOTICE_HIDDEN = 0;

    /**
     * @var int defining so it can be used for filtering
     */
    public const NOTICE_IN_PREVIEW = 2;

    /**
     * @var int defining so it can be used for filtering
     */
    public const NOTICE_CRITICAL = 3;

    /**
     * @var int notice.exclusive value: not exclusive.
     */
    public const NOTICE_EXCLUSIVE_NONE = 0;

    /**
     * @var int notice.exclusive value: exclusive with red/urgent styling.
     */
    public const NOTICE_EXCLUSIVE_IMPORTANT = 1;

    /**
     * @var int notice.exclusive value: exclusive with blue/information styling.
     */
    public const NOTICE_EXCLUSIVE_INFORMATION = 2;

    /**
     * @var array lookup table for attributes based on notice visibility
     */
    public const NOTICE_VISIBLITY_BOOTSTRAP_CSS_CLASS = [
        self::NOTICE_VISIBLE => 'success',
        self::NOTICE_HIDDEN => 'light',
        self::NOTICE_IN_PREVIEW => 'warning',
    ];

    /**
     * @var array test data for notices. May move to lib_test
     */
    public const TEST_DATA = [
        [
            'visible' => 1,
            'title' => 'MOST (Massey Online Survey Tool)',
            'content' => '<p>Course evaluations are now open
                . <a href="https://ost.massey.ac.nz" target="_blank">
                Click here to tell us what you think</a>.</p>',
            'contentformat' => 1,
            'updatedescription' => 'Added 14 October',
            'moreinformationurl' => 'https://ost.massey.ac.nz',
            'owner' => 'Ema Alter',
            'owneremail' => 'E.J.Alter@massey.ac.nz',
            'notes' => 'Remove 12 November 2024',
        ],
        [
            'visible' => 1,
            'title' => 'Copyright Notice',
            'content' => '<p>All Stream course materials are copyrighted and
                intended solely for the University\'s educational purposes.
                They may contain extracts from copyrighted works used under licenses.
                You may create a single copy for personal use, but further copying or
                distribution of any course materials, including powerpoints,
                readings, tests, and exam papers, to others or online platforms is
                prohibited. Non-compliance with this warning may lead to legal action
                for copyright infringement and/or disciplinary measures by the University.</p>',
            'contentformat' => 1,
            'updatedescription' => 'Updated 11 January',
            'moreinformationurl' => 'https://www.massey.ac.nz/
                study/study-and-assignment-support-and-guides/student-copyright-guide/',
            'owner' => 'Andrew Rowatt',
            'owneremail' => 'A.J.Rowatt@massey.ac.nz',
            'notes' => 'Contact Jean if this needs to be updated.',
        ],
        [
            'visible' => 0,
            'title' => 'Student Portal Unavailable Friday, 21 June',
            'content' => '<p>The Student Portal will be unavailable from <strong>&nbsp;Friday,
            21 June at 6pm until Saturday, 22 June at 6pm</strong>&nbsp;while an update occurs.</p>
	        <p style="text-align: center;"><strong>Stream will remain accessible during this time.</strong>
	        </p><p>Students are encouraged to complete any applications in the portal ahead of time to
            avoid inconvenience. This includes semester two enrolments, scholarships
	        , and special circumstance requests.</p>',
            'contentformat' => 1,
            'updatedescription' => 'Updated 6pm, 19 June',
            'moreinformationurl' => 'https://www.massey.ac.nz/student-life/
			services-and-support-for-students/it-services-and-support/',
            'owner' => 'Hayden Burnett',
            'owneremail' => 'h.burnett@massey.ac.nz',
            'notes' => 'n/a',
        ],
        [
            'visible' => 1,
            'title' => 'MyHub',
            'content' => '<p><a href="https://myhub.massey.ac.nz"
                target="_blank">MyHub</a> is your go-to platform to enhance your career prospects
                with job searches and skill workshops, engage in wellbeing activities, influence
                university decisions through Student Voice, and explore extensive support for
                study planning, course selection, and essential student resources.</p>',
            'contentformat' => 1,
            'updatedescription' => 'Updated 8 January',
            'moreinformationurl' => 'https://myhub.massey.ac.nz',
            'owner' => 'Andrew Rowatt',
            'owneremail' => 'A.J.Rowatt@massey.ac.nz',
            'notes' => 'n/a',
        ],
        [
            'visible' => 2,
            'title' => 'Office 365 free for Students',
            'content' => '<p>Ensure you\'re fully equipped for your Massey University courses.
                Office 365 is free for you and helps with accessing course materials and submitting
                assignments and exams in the right format.
                <a href="https://massey.ac.nz/freeoffice" target="_blank">Get Office 365 here</a>.</p>',
            'contentformat' => 1,
            'updatedescription' => 'Updated 8 January',
            'moreinformationurl' => 'https://massey.ac.nz/freeoffice',
            'owner' => 'Andrew Rowatt',
            'owneremail' => 'A.J.Rowatt@massey.ac.nz',
            'notes' => 'ITS manage this resource.',
        ],
    ];

    /**
     * Can the current user create new notices?
     *
     * Only manage-all users may create. The ownerid is set at creation and never changes,
     * so manage-own users have no entry point for authoring.
     *
     * @param \context|null $context Defaults to the system context (where the new caps live).
     * @return bool
     */
    public static function user_can_create(?\context $context = null): bool {
        $context = $context ?? \context_system::instance();
        return has_capability('block/notices:manageallnotices', $context);
    }

    /**
     * Can the current user edit (or delete / reorder / show / hide) the given notice?
     *
     * Editing rights are granted to two groups:
     *  - users with the block/notices:manageallnotices capability (typically admins / notices managers); and
     *  - the assigned owner of the notice (ownerid). Ownership alone grants edit rights — no role is required.
     *
     * @param \stdClass|array $notice A notice record (must contain ownerid).
     * @param \context|null $context Defaults to the system context.
     * @return bool
     */
    public static function user_can_edit($notice, ?\context $context = null): bool {
        global $USER;
        $context = $context ?? \context_system::instance();
        if (has_capability('block/notices:manageallnotices', $context)) {
            return true;
        }
        $ownerid = is_array($notice) ? ($notice['ownerid'] ?? null) : ($notice->ownerid ?? null);
        return $ownerid !== null && (int)$ownerid === (int)$USER->id;
    }

    /**
     * Does the current user have any access (manage-all or as an owner of at least one notice)?
     *
     * Used to decide whether to show a 'Manage' link on the block.
     *
     * @param \context|null $context Defaults to the system context.
     * @return bool
     */
    public static function user_can_manage_any(?\context $context = null): bool {
        global $DB, $USER;
        $context = $context ?? \context_system::instance();
        if (has_capability('block/notices:manageallnotices', $context)) {
            return true;
        }
        return $DB->record_exists('block_notices', ['ownerid' => $USER->id]);
    }

    /**
     * Get the count of notices for a given instance
     *
     * @param int $courseid
     * @param bool $includepreview
     * @param bool $includestaffonly
     * @return int
     */
    public static function get_notice_count(int $courseid, bool $includepreview = false, bool $includestaffonly = false): int {
        // Rather that duplicating the logic to get the count of notices,
        // we can just call the get_notices method and count the results.

        $notices = self::get_notices($courseid, $includepreview, $includestaffonly);

        return count($notices);
    }

    /**
     * Get all notices for a given instance.
     *
     * @param int $courseid
     * @param bool $includepreview
     * @param bool $includestaffonly
     * @return array
     */
    public static function get_notices(int $courseid, bool $includepreview = false, bool $includestaffonly = false): array {
        global $DB;

        // If there is an exclusive notice for this course and it is currently visible, only return that.
        $exclusive = self::get_active_exclusive_notice($courseid);
        if ($exclusive !== null) {
            return [$exclusive->id => $exclusive];
        }

        $criticalnotice = self::get_critical_notice($courseid);
        if (count($criticalnotice) != 0) {
            // If there is a critical notice, we only return that.
            return $criticalnotice;
        }

        $visible = [self::NOTICE_VISIBLE];
        if ($includepreview) {
            $visible[] = self::NOTICE_IN_PREVIEW;
        }

        [$insql, $inparams] = $DB->get_in_or_equal($visible);

        $sql = "SELECT * FROM {block_notices}
            WHERE courseid = ? and staffonly <= ? and
            visible $insql
            order by sortorder asc";

        return $DB->get_records_sql($sql, ['courseid' => $courseid, 'staffonly' => (int)$includestaffonly] + $inparams);
    }

    /**
     * Resolve the active exclusive notice for the given course, if any.
     *
     * Returns the notice record only when one notice in $courseid has a non-zero
     * exclusive value AND is currently NOTICE_VISIBLE. Anything else falls back to
     * normal notice behaviour (no exclusive set / hidden / deleted => null).
     *
     * @param int $courseid
     * @return \stdClass|null
     */
    public static function get_active_exclusive_notice(int $courseid): ?\stdClass {
        global $DB;

        $records = $DB->get_records_select(
            'block_notices',
            'courseid = :courseid AND exclusive > 0 AND visible = :visible',
            ['courseid' => $courseid, 'visible' => self::NOTICE_VISIBLE],
            'id ASC',
            '*',
            0,
            1
        );

        return $records ? reset($records) : null;
    }

    /**
     * Set the per-course exclusive flag for a notice.
     *
     * Enforces the "at most one exclusive notice per course" invariant by zeroing
     * any other exclusive notices in the same course inside a transaction.
     *
     * @param int $noticeid
     * @param int $value One of NOTICE_EXCLUSIVE_NONE/IMPORTANT/INFORMATION.
     */
    public static function set_exclusive(int $noticeid, int $value): void {
        global $DB, $USER;

        $allowed = [
            self::NOTICE_EXCLUSIVE_NONE,
            self::NOTICE_EXCLUSIVE_IMPORTANT,
            self::NOTICE_EXCLUSIVE_INFORMATION,
        ];
        if (!\in_array($value, $allowed, true)) {
            throw new \coding_exception("Invalid exclusive value: $value");
        }

        $notice = $DB->get_record('block_notices', ['id' => $noticeid], 'id, courseid', MUST_EXIST);

        $transaction = $DB->start_delegated_transaction();

        if ($value !== self::NOTICE_EXCLUSIVE_NONE) {
            $DB->execute(
                'UPDATE {block_notices} SET exclusive = 0
                  WHERE courseid = :courseid AND exclusive > 0 AND id <> :id',
                ['courseid' => $notice->courseid, 'id' => $noticeid]
            );
        }

        $DB->update_record('block_notices', (object)[
            'id' => $noticeid,
            'exclusive' => $value,
            'timemodified' => time(),
            'modifiedbyuserid' => $USER->id,
        ]);

        $transaction->allow_commit();

        \block_notices\event\notice_updated::create([
            'objectid' => $noticeid,
            'userid' => $USER->id,
            'context' => \context_course::instance($notice->courseid),
        ])->trigger();
    }

    /**
     * Get the critical notice for a given course.
     *
     * @param int $courseid
     * @return array
     */
    public static function get_critical_notice(int $courseid): array {
        global $DB;

        $sql = "SELECT * FROM {block_notices}
            WHERE courseid = :courseid and visible = :visible";

        $criticalnotice = $DB->get_record_sql($sql, ['courseid' => $courseid, 'visible' => self::NOTICE_CRITICAL]);

        if ($criticalnotice) {
            // If there is a critical notice, we only return that.
            return (array)$criticalnotice;
        }

        // No critical notice found, return an empty array.
        return [];
    }

    /**
     * Delete all notices for a given user. Used for privacy purposes.
     *
     * @param int $courseid
     * @param int $userid
     * @return array
     */
    public static function delete_notices_by_user(int $courseid, int $userid): void {
        global $DB;

        $DB->delete_records_select(
            'block_notices',
            'courseid = :courseid and (createdbyuserid = :createdbyuserid or
                modifiedbyuserid = :modifiedbyuserid or ownerid = :ownerid)',
            ['courseid' => $courseid,
            'createdbyuserid' => $userid,
            'modifiedbyuserid' => $userid,
            'ownerid' => $userid]
        );
    }

    /**
     * Get full details for all notices for a given course.
     *
     * @param int $courseid
     * @param int|null $ownerid If provided, restricts results to notices with this ownerid (used for manage-own users).
     * @return array
     */
    public static function get_notices_admin(int $courseid, ?int $ownerid = null): array {
        global $DB;

        $params = ['courseid' => $courseid, 'visiblemin' => self::NOTICE_VISIBLE, 'visiblemax' => self::NOTICE_VISIBLE];
        $ownerwhere = '';
        if ($ownerid !== null) {
            $ownerwhere = ' AND b.ownerid = :ownerid';
            $params['ownerid'] = $ownerid;
        }

        $sql = "SELECT b.*,
            trim(concat(cb.firstname, ' ', cb.lastname)) as createdby,
            trim(concat(mb.firstname, ' ', mb.lastname)) as modifiedby,
            b.sortorder = (select min(sortorder) from {block_notices}
                where courseid = b.courseid and visible=:visiblemin) as isfirst,
            b.sortorder = (select max(sortorder) from {block_notices}
                where courseid = b.courseid and visible=:visiblemax) as islast
            FROM {block_notices} b
            join {user} cb on b.createdbyuserid = cb.id
            join {user} mb on b.modifiedbyuserid = mb.id
            WHERE b.courseid = :courseid{$ownerwhere} order by b.visible, b.sortorder";
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get a single notice.
     *
     * @param int $id
     * @return object
     */
    public static function get_notice($id): array {
        global $DB;

        return (array)$DB->get_record('block_notices', ['id' => $id]);
    }

    /**
     * Get a single notice.
     *
     * @param int $userid
     * @return object
     */
    public static function get_notices_by_user(int $userid): array {
        global $DB;

        $sql = "select * from {block_notices}
            where createdbyuserid = :createdbyuseridid
               or modifiedbyuserid = :modifiedbyuseridid
               or ownerid = :ownerid";

        return (array)$DB->get_records_sql($sql, [
            'createdbyuseridid' => $userid,
            'modifiedbyuseridid' => $userid,
            'ownerid' => $userid,
        ]);
    }

    /**
     * Delete a notice.
     *
     * @param int $id
     */
    public static function delete_notice($id) {
        global $DB;

        $DB->delete_records('block_notices', ['id' => $id]);
    }

    /**
     * Delete all notices for a course.
     *
     * @param int $courseid
     */
    public static function delete_all_notices(int $courseid) {
        global $DB;

        $DB->delete_records('block_notices', ['courseid' => $courseid]);
    }

    /**
     * Update a single inline-editable field on a notice.
     *
     * Updates timemodified and modifiedbyuserid; intentionally leaves visibility
     * and sortorder untouched (unlike update_notice() which resets to PREVIEW).
     *
     * @param int $id
     * @param string $field One of 'title', 'updatedescription'.
     * @param string $newvalue
     */
    public static function update_notice_field(int $id, string $field, string $newvalue): void {
        global $DB, $USER;
        if (!in_array($field, ['title', 'updatedescription'], true)) {
            throw new \coding_exception("Field not inline-editable: $field");
        }
        $notice = $DB->get_record('block_notices', ['id' => $id], 'id, courseid', MUST_EXIST);
        $DB->update_record('block_notices', (object)[
            'id' => $id,
            $field => $newvalue,
            'timemodified' => time(),
            'modifiedbyuserid' => $USER->id,
        ]);
        \block_notices\event\notice_updated::create([
            'objectid' => $id,
            'userid' => $USER->id,
            'context' => \context_course::instance($notice->courseid),
        ])->trigger();
    }

    /**
     * Update a notice.
     *
     * @param array $data
     */
    public static function update_notice(array $data) {
        global $DB, $USER;

        $noticepreviousversion = self::get_notice($data['id']);

        $data['visible'] = self::NOTICE_IN_PREVIEW;
        $data['sortorder'] = 0;
        $data['timemodified'] = time();
        $data['modifiedbyuserid'] = $USER->id;

        $DB->update_record('block_notices', (object)$data);

        // Through this method, visibility may change from visible to preview
        // so we will need to recalculate the sortorder in that case.
        if ($noticepreviousversion['visible'] == self::NOTICE_VISIBLE) {
            self::recalc_visible_notices_sortorder($noticepreviousversion['courseid']);
        }

        $event = \block_notices\event\notice_updated::create([
            'objectid' => $data['id'],
            'userid' => $data['modifiedbyuserid'],
            'context' => \context_course::instance($noticepreviousversion['courseid']),
        ]);

        $event->trigger();
    }

    /**
     * Add a notice.
     *
     * @param int $courseid
     * @param array $data
     * @return int The id of the newly created notice.
     */
    public static function add_notice(int $courseid, array $data): int {
        global $DB, $USER;

        $timecreated = time(); // So timecreated and timemodified are the same.

        $presets = [
            'courseid' => $courseid,
            'visible' => self::NOTICE_IN_PREVIEW,
            'timecreated' => $timecreated,
            'timemodified' => $timecreated,
            'createdbyuserid' => $USER->id,
            'modifiedbyuserid' => $USER->id,
            'sortorder' => 0,
        ];

        $record = $presets + $data;
        // If the caller didn't supply an explicit ownerid (e.g. seed/test data), default to the creator.
        if (empty($record['ownerid'])) {
            $record['ownerid'] = $USER->id;
        }

        return $DB->insert_record('block_notices', $record);
    }

    /**
     * Move a notice up in the display order
     * @param int $id
     * @return void
     */
    public static function move_up(int $id): void {
        global $DB;

        $notice = self::get_notice($id);

        $sortorder = $notice['sortorder'];
        $prevnotice = $DB->get_record_sql(
            'select * from {block_notices}
                where courseid = :courseid and visible=:visible and
                sortorder < :sortorder order by sortorder desc limit 1',
            ['courseid' => $notice['courseid'], 'visible' => self::NOTICE_VISIBLE, 'sortorder' => $sortorder]
        );

        if ($prevnotice) {
            $notice['sortorder'] = $prevnotice->sortorder;
            $prevnotice->sortorder = $sortorder;

            $DB->update_record('block_notices', $notice);
            $DB->update_record('block_notices', $prevnotice);
        }
    }

    /**
     * Move a notice down in the display order
     * @param int $id
     * @return void
     */
    public static function move_down(int $id): void {
        global $DB;

        $notice = self::get_notice($id);

        $sortorder = $notice['sortorder'];
        $nextnotice = $DB->get_record_sql(
            'select * from {block_notices}
                where courseid = :courseid and visible=:visible and
                sortorder > :sortorder order by sortorder asc limit 1',
            ['courseid' => $notice['courseid'], 'visible' => self::NOTICE_VISIBLE, 'sortorder' => $sortorder]
        );

        if ($nextnotice) {
            $notice['sortorder'] = $nextnotice->sortorder;
            $nextnotice->sortorder = $sortorder;

            $DB->update_record('block_notices', $notice);
            $DB->update_record('block_notices', $nextnotice);
        }
    }

    /**
     * Show a notice.
     *
     * @param int $id
     */
    public static function show_notice(int $id): void {
        global $DB;

        $notice = self::get_notice($id);

        // ...TODO: Check that the notice is not already visible.

        // When a notice is shown, we also need to set the sortorder
        // By default is is added to end of the list.
        $maxsortorder = $DB->get_field_sql(
            'SELECT MAX(sortorder) FROM {block_notices}
                where courseid = :courseid and visible=:visible',
            ['courseid' => $notice['courseid'], 'visible' => self::NOTICE_VISIBLE]
        );

        $notice['sortorder'] = $maxsortorder + 1;
        $notice['visible'] = self::NOTICE_VISIBLE;

        $DB->update_record('block_notices', $notice);
    }

    /**
     * Hide a notice.
     *
     * @param int $id
     */
    public static function hide_notice(int $id): void {
        global $DB;

        $notice = self::get_notice($id);

        // ...TODO: Check that the notice is not already hidden.

        $notice['sortorder'] = 0;
        $notice['visible'] = self::NOTICE_HIDDEN;

        $DB->update_record('block_notices', $notice);

        // When a notice is hidden, it may leave gaps in the sortorder sequence.
        self::recalc_visible_notices_sortorder($notice['courseid']);
    }

    /**
     * Recalcuate the sortorder for all visible notices.
     *
     * This function is called when a visible notice is deleted as this may leave gaps in the sortorder.
     *
     * @param int $courseid
     * @return void
     */
    private static function recalc_visible_notices_sortorder(int $courseid): void {
        global $DB;

        $sortorder = 1;
        $notices = $DB->get_records(
            'block_notices',
            ['courseid' => $courseid, 'visible' => self::NOTICE_VISIBLE],
            'sortorder ASC'
        );

        foreach ($notices as $notice) {
            $notice->sortorder = $sortorder++;
            $DB->update_record('block_notices', $notice);
        }
    }

    /**
     * Check if a course has a notice block added.
     *
     * @param int $courseid
     * @return bool
     */
    public static function has_notice_block(int $courseid): bool {
        global $DB;

        if ($courseid == 1) {
            $contextids = [1, 2]; // ...1 is system/my-index, 2 is site homepage (site-index)
        } else {
            $context = \context_course::instance($courseid);
            $contextids = [$context->id];
        }

        [$insql, $inparams] = $DB->get_in_or_equal($contextids);

        $sql = "SELECT * FROM {block_instances}
            WHERE blockname = ? and
            parentcontextid $insql";

        return $DB->record_exists_sql($sql, ['blockname' => 'notices'] + $inparams);
    }

    /**
     * Check if a course has a notice block added and throw an exception if not.
     *
     * @param int $courseid
     * @throws \moodle_exception
     */
    public static function require_notice_block($courseid): void {
        if (!self::has_notice_block($courseid)) {
            throw new \moodle_exception('block_notices:missingblock', 'block_notices');
        }
    }

    /**
     * Add test data to the notices table.
     *
     * @param int $courseid
     */
    public static function add_notice_test_data(int $courseid): void {
        foreach (self::TEST_DATA as $notice) {
            $id = self::add_notice($courseid, $notice);

            // New notices are always in preview mode, update to visible or hidden if required.
            if ($notice['visible'] == self::NOTICE_VISIBLE) {
                self::show_notice($id);
            } else if ($notice['visible'] == self::NOTICE_HIDDEN) {
                self::hide_notice($id);
            }
        }
    }
}
