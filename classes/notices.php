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
     * @var array lookup table for notice visibility
     */
    public const NOTICE_VISIBLITY = [
        self::NOTICE_VISIBLE => 'Visible',
        self::NOTICE_HIDDEN => 'Hidden',
        self::NOTICE_IN_PREVIEW => 'In Preview (visible to Admins only)',
    ];

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
            'content' => '<p>All Stream course materials are copyrighted an
                d intended solely for the University\'s educational purposes
                . They may contain extracts from copyrighted works used under licenses
                . You may create a single copy for personal use, but further copying o
                r distribution of any course materials, including powerpoints, r
                eadings, tests, and exam papers, to others or online platforms i
                s prohibited. Non-compliance with this warning may lead to legal actio
                n for copyright infringement and/or disciplinary measures by the University.</p>',
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
            'content' => '<p>The Student Portal will be unavailable from <strong>&nbsp;Frida
			y, 21 June at 6pm until Saturday, 22 June at 6pm</strong>&nbsp;while an update o
			ccurs.</p>
	        <p style="text-align: center;"><strong>Stream will remain accessible during this time.</strong>
	        </p><p>Students are encouraged to complete any applications in the portal ahead of time t
	        o avoid inconvenience. This includes semester two enrolments, scholarship
	        s, and special circumstance requests.</p>',
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
            'content' => '<p><a href="https://myhub.massey.ac.nz" target="_blank">MyHub</a> is your go-to platform to enhance your career prospects with job searches and skill workshops, engage in wellbeing activities, influence university decisions through Student Voice, and explore extensive support for study planning, course selection, and essential student resources.</p>',
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
            'content' => '<p>Ensure you\'re fully equipped for your Massey University courses. Office 365 is free for you and helps with accessing course materials and submitting assignments and exams in the right format. <a href="https://massey.ac.nz/freeoffice" target="_blank">Get Office 365 here</a>.</p>',
            'contentformat' => 1,
            'updatedescription' => 'Updated 8 January',
            'moreinformationurl' => 'https://massey.ac.nz/freeoffice',
            'owner' => 'Andrew Rowatt',
            'owneremail' => 'A.J.Rowatt@massey.ac.nz',
            'notes' => 'ITS manage this resource.',
        ],
    ];


    public static function get_notices($instanceid): array {
        global $DB;

        $sql = 'SELECT * FROM {block_notices} WHERE instanceid = :instanceid and visible = :visible order by sortorder asc';
        
        return $DB->get_records_sql($sql, ['instanceid' => $instanceid, 'visible' => self::NOTICE_VISIBLE]);
    }

    /**
     * Get all notices for a given instance.
     *
     * @param int $instanceid
     * @return array
     */
    public static function get_notices_admin($instanceid): array {
        global $DB;

        $sql = 'SELECT b.*,
            trim(concat(cb.firstname, \' \', cb.lastname)) as createdbyname,
            trim(concat(mb.firstname, \' \', mb.lastname)) as modifiedbyname,
            b.sortorder = (select min(sortorder) from {block_notices} where instanceid = b.instanceid and visible=:visiblemin) as isfirst,
            b.sortorder = (select max(sortorder) from {block_notices} where instanceid = b.instanceid and visible=:visiblemax) as islast
            FROM {block_notices} b
            join {user} cb on b.createdby = cb.id
            join {user} mb on b.modifiedby = mb.id
            WHERE b.instanceid = :instanceid order by b.visible, b.sortorder';
        return $DB->get_records_sql($sql, ['instanceid' => $instanceid, 'visiblemin' => self::NOTICE_VISIBLE, 'visiblemax' => self::NOTICE_VISIBLE]);
    }

    /**
     * Get a single notice.
     *
     * @param int $id
     * @return object
     */
    public static function get_notice($id): object {
        global $DB;

        return $DB->get_record('block_notices', ['id' => $id]);
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
     * @param int $id
     * @param array $data
     */
    public static function update_notice($id, $data) {
        global $DB;

        $record = new \stdClass;
        $record->id = $id;
        $record->visible = $data->visible ? 1 : 0;
        $record->title = $data->title;
        $record->content = $data->content['text'];
        $record->updatedescription = $data->updatedescription;
        $record->timemodified = time();
        $record->modifiedby = $data->modifiedby;

        $DB->update_record('block_notices', $record);
    }

    /**
     * Add a notice.
     *
     * @param int $instanceid block instance of the notice block
     * @param object $data core data for the notice to add
     */
    public static function add_notice(int $instanceid, array $data): int {
        global $DB, $USER;

        $timecreated = time(); // So timecreated and timemodified are the same.

        $presets = [
            'instanceid' => $instanceid,
            'visible' => self::NOTICE_IN_PREVIEW,
            'timecreated' => $timecreated,
            'timemodified' => $timecreated,
            'createdby' => $USER->id,
            'modifiedby' => $USER->id,
            'sortorder' => 0,
        ];

        return $DB->insert_record('block_notices', $presets + $data);
    }

    public static function move_up(int $id): void {
        global $DB;

        $notice = self::get_notice($id);

        $sortorder = $notice->sortorder;
        $prevnotice = $DB->get_record_sql('select * from {block_notices} 
            where instanceid = :instanceid and visible=:visible and 
            sortorder < :sortorder order by sortorder desc limit 1' 
        , ['instanceid' => $notice->instanceid, 'visible' => self::NOTICE_VISIBLE, 'sortorder' => $sortorder]);

        if ($prevnotice) {
            $notice->sortorder = $prevnotice->sortorder;
            $prevnotice->sortorder = $sortorder;

            $DB->update_record('block_notices', $notice);
            $DB->update_record('block_notices', $prevnotice);
        }
    }

    public static function move_down(int $id): void {
        global $DB;

        $notice = self::get_notice($id);

        $sortorder = $notice->sortorder;
        $nextnotice = $DB->get_record_sql('select * from {block_notices} 
            where instanceid = :instanceid and visible=:visible and 
            sortorder > :sortorder order by sortorder asc limit 1' 
        , ['instanceid' => $notice->instanceid, 'visible' => self::NOTICE_VISIBLE, 'sortorder' => $sortorder]);

        if ($nextnotice) {
            $notice->sortorder = $nextnotice->sortorder;
            $nextnotice->sortorder = $sortorder;

            $DB->update_record('block_notices', $notice);
            $DB->update_record('block_notices', $nextnotice);
        }
    }


    public static function show_notice(int $id): void {
        global $DB;

        $notice = self::get_notice($id);

        // When a notice is shown, we also need to set the sortorder so it is added to the end of the list.
        $maxsortorder = $DB->get_field_sql(
            'SELECT MAX(sortorder) FROM {block_notices} where instanceid = :instanceid and visible=:visible', 
            ['instanceid' => $notice->instanceid, 'visible' => self::NOTICE_VISIBLE]);

        $notice->sortorder = $maxsortorder + 1;
        $notice->visible = self::NOTICE_VISIBLE;

        $DB->update_record('block_notices', $notice);
    }

    public static function hide_notice(int $id): void {
        global $DB;

        $notice = self::get_notice($id);

        // When a notice is shown, we also need to set the sortorder so it is added to the end of the list.
        $maxsortorder = $DB->get_field_sql('SELECT MAX(sortorder) FROM {block_notices} where instanceid = :instanceid', ['instanceid' => $notice->instanceid]);

        $notice->sortorder = 0;
        $notice->visible = self::NOTICE_HIDDEN;

        $DB->update_record('block_notices', $notice);

        self::recalc_visible_notices_sortorder($notice->instanceid);
    }

    private static function recalc_visible_notices_sortorder(int $instanceid): void {
        global $DB;

        $sortorder = 1;
        $notices = $DB->get_records('block_notices', 
            ['instanceid' => $instanceid, 'visible' => self::NOTICE_VISIBLE], 
            'sortorder ASC');

        foreach ($notices as $notice) {
            $notice->sortorder = $sortorder++;
            $DB->update_record('block_notices', $notice);
        }
    }

    /**
     * Add test data to the notices table.
     *
     * @param int $instanceid
     */
    public static function add_notice_test_data(int $instanceid): void {
        foreach (self::TEST_DATA as $notice) {
            $id = self::add_notice($instanceid, $notice);
            if ($notice['visible'] === 1) {
                self::show_notice($id);
            } elseif ($notice['visible'] === 0) {
                self::hide_notice($id);
            }
        }
    }
}
