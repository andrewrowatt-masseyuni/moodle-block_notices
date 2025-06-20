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
     * Get the count of notices for a given instance
     *
     * @param int $courseid
     * @return int
     */
    public static function get_notice_count(int $courseid): int {
        global $DB;

        // ...TODO: Implement "bool $includepreview = false".

        return $DB->count_records('block_notices', ['courseid' => $courseid]);
    }

    /**
     * Get all notices for a given instance.
     *
     * @param int $courseid
     * @param bool $includepreview
     * @return array
     */
    public static function get_notices(int $courseid, bool $includepreview = false, bool $includestaffonly = false): array {
        global $DB;

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
            'courseid = :courseid and (createdby = :createdby or modifiedby = :modifiedby)',
            ['courseid' => $courseid,
            'createdby' => $userid,
            'modifiedby' => $userid]);
    }

    /**
     * Get full details for all notices for a given course.
     *
     * @param int $courseid
     * @return array
     */
    public static function get_notices_admin($courseid): array {
        global $DB;

        $sql = 'SELECT b.*,
            trim(concat(cb.firstname, \' \', cb.lastname)) as createdbyname,
            trim(concat(mb.firstname, \' \', mb.lastname)) as modifiedbyname,
            b.sortorder = (select min(sortorder) from {block_notices}
                where courseid = b.courseid and visible=:visiblemin) as isfirst,
            b.sortorder = (select max(sortorder) from {block_notices}
                where courseid = b.courseid and visible=:visiblemax) as islast
            FROM {block_notices} b
            join {user} cb on b.createdby = cb.id
            join {user} mb on b.modifiedby = mb.id
            WHERE b.courseid = :courseid order by b.visible, b.sortorder';
        return $DB->get_records_sql($sql,
             ['courseid' => $courseid, 'visiblemin' => self::NOTICE_VISIBLE, 'visiblemax' => self::NOTICE_VISIBLE]);
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
     * Delete a notice.
     *
     * @param int $id
     */
    public static function delete_notice($id) {
        global $DB;

        $DB->delete_records('block_notices', ['id' => $id]);
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
        $data['modifiedby'] = $USER->id;

        $DB->update_record('block_notices', $data);

        // Through this method, visibility may change from visible to preview
        // so we will need to recalculate the sortorder in that case.
        if ($noticepreviousversion['visible'] == self::NOTICE_VISIBLE) {
            self::recalc_visible_notices_sortorder($noticepreviousversion['courseid']);
        }

        $event = \block_notices\event\notice_updated::create([
            'objectid' => $data['id'],
            'userid' => $data['modifiedby'],
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
            'createdby' => $USER->id,
            'modifiedby' => $USER->id,
            'sortorder' => 0,
        ];

        return $DB->insert_record('block_notices', $presets + $data);
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
        $prevnotice = $DB->get_record_sql('select * from {block_notices}
                where courseid = :courseid and visible=:visible and
                sortorder < :sortorder order by sortorder desc limit 1',
            ['courseid' => $notice['courseid'], 'visible' => self::NOTICE_VISIBLE, 'sortorder' => $sortorder]);

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
        $nextnotice = $DB->get_record_sql('select * from {block_notices}
                where courseid = :courseid and visible=:visible and
                sortorder > :sortorder order by sortorder asc limit 1',
            ['courseid' => $notice['courseid'], 'visible' => self::NOTICE_VISIBLE, 'sortorder' => $sortorder]);

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
            ['courseid' => $notice['courseid'], 'visible' => self::NOTICE_VISIBLE]);

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
        $notices = $DB->get_records('block_notices',
            ['courseid' => $courseid, 'visible' => self::NOTICE_VISIBLE],
            'sortorder ASC');

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
