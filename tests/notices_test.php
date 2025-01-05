<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace block_notices;

// ..."PS C:\github\moodle405\moodle> vendor/bin/phpunit --filter 'block_notices\\notices_test'"

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * The notices test class.
 *
 * @package     block_notices
 * @category    test
 * @copyright   2024 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class notices_test extends \advanced_testcase {
    /**
     * "Real world" Test data.
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
     * Tests adding a single notice.
     *
     * @covers ::add_notice
     */
    public function test_add_notice(): void {
        $this->resetAfterTest(true);

        $this->assertTrue(notices::get_notice_count(1) == 0);
        $id = notices::add_notice(1, self::TEST_DATA[0]);

        $this->assertTrue(notices::get_notice_count(1) == 1);

        $notice = notices::get_notice($id);
        $this->assertEquals($notice['visible'], notices::NOTICE_IN_PREVIEW);
        $this->assertEquals($notice['sortorder'], 0);
    }

    /**
     * Tests adding multiple notices.
     *
     * @covers ::add_notice
     */
    public function test_add_notice_multiple(): void {
        $this->resetAfterTest(true);

        $this->assertTrue(notices::get_notice_count(1) == 0);
        $id1 = notices::add_notice(1, self::TEST_DATA[0]);
        $id2 = notices::add_notice(1, self::TEST_DATA[1]);
        $this->assertTrue(notices::get_notice_count(1) == 2);
    }

    /**
     * Tests showing (making visible) a notice.
     *
     * @covers ::show_notice
     */
    public function test_notice_show(): void {
        $this->resetAfterTest(true);
        $id1 = notices::add_notice(1, self::TEST_DATA[0]);
        $id2 = notices::add_notice(1, self::TEST_DATA[1]);

        notices::show_notice($id1);
        $this->assertEquals(notices::get_notice($id1)['visible'], notices::NOTICE_VISIBLE);
        $this->assertEquals(notices::get_notice($id1)['sortorder'], 1);

        notices::show_notice($id2);
        $this->assertEquals(notices::get_notice($id2)['visible'], notices::NOTICE_VISIBLE);
        $this->assertEquals(notices::get_notice($id2)['sortorder'], 2);
    }

    /**
     * Tests hiding (making invisible) a notice.
     *
     * @covers ::hide_notice
     */
    public function test_notice_visible_hide(): void {
        $this->resetAfterTest(true);
        $id1 = notices::add_notice(1, self::TEST_DATA[0]);
        $id2 = notices::add_notice(1, self::TEST_DATA[1]);

        notices::show_notice($id1);
        notices::show_notice($id2);

        notices::hide_notice($id1);
        $this->assertEquals(notices::get_notice($id1)['visible'], notices::NOTICE_HIDDEN);
        $this->assertEquals(notices::get_notice($id1)['sortorder'], 0);

        $this->assertEquals(notices::get_notice($id2)['sortorder'], 1);
    }

    /**
     * Tests moving a notice down (a.k.a. increasing sort order).
     *
     * @covers ::move_down
     */
    public function test_notice_visible_movedown(): void {
        $this->resetAfterTest(true);
        $id1 = notices::add_notice(1, self::TEST_DATA[0]);
        $id2 = notices::add_notice(1, self::TEST_DATA[1]);

        notices::show_notice($id1);
        notices::show_notice($id2);
        notices::move_down($id1);

        $this->assertEquals(notices::get_notice($id1)['sortorder'], 2);
        $this->assertEquals(notices::get_notice($id2)['sortorder'], 1);
    }

    /**
     * Tests moving a notice down (a.k.a. increasing sort order) when the notice is already at the top.
     *
     * @covers ::move_down
     */
    public function test_notice_visible_movedown_invalid(): void {
        $this->resetAfterTest(true);
        $id1 = notices::add_notice(1, self::TEST_DATA[0]);
        $id2 = notices::add_notice(1, self::TEST_DATA[1]);

        notices::show_notice($id1);
        notices::show_notice($id2);
        notices::move_down($id2);

        $this->assertEquals(notices::get_notice($id1)['sortorder'], 1);
        $this->assertEquals(notices::get_notice($id2)['sortorder'], 2);
    }

    /**
     * Tests moving a notice up (a.k.a. descreasing sort order).
     *
     * @covers ::move_up
     */
    public function test_notice_visible_moveup(): void {
        $this->resetAfterTest(true);
        $id1 = notices::add_notice(1, self::TEST_DATA[0]);
        $id2 = notices::add_notice(1, self::TEST_DATA[1]);

        notices::show_notice($id1);
        notices::show_notice($id2);
        notices::move_up($id2);

        $this->assertEquals(notices::get_notice($id2)['sortorder'], 1);
        $this->assertEquals(notices::get_notice($id1)['sortorder'], 2);
    }

    /**
     * Tests moving a notice up (a.k.a. descreasing sort order) when the notice is already at the top.
     *
     * @covers ::move_up
     */
    public function test_notice_visible_moveup_invalid(): void {
        $this->resetAfterTest(true);
        $id1 = notices::add_notice(1, self::TEST_DATA[0]);
        $id2 = notices::add_notice(1, self::TEST_DATA[1]);

        notices::show_notice($id1);
        notices::show_notice($id2);
        notices::move_up($id1);

        $this->assertEquals(notices::get_notice($id1)['sortorder'], 1);
        $this->assertEquals(notices::get_notice($id2)['sortorder'], 2);
    }

    /**
     * Tests updating a notice
     *
     * @covers ::update_notice
     */
    public function test_notice_update_basic(): void {
        $this->resetAfterTest(true);
        $id1 = notices::add_notice(1, self::TEST_DATA[0]);
        $id2 = notices::add_notice(1, self::TEST_DATA[1]);

        notices::show_notice($id1);
        notices::show_notice($id2);

        $this->assertEquals(notices::get_notice($id1)['visible'], notices::NOTICE_VISIBLE);

        $notice = notices::get_notice($id1);

        $notice['title'] = 'test_notice_update_basic';
        notices::update_notice($notice);

        $this->assertEquals(notices::get_notice($id1)['sortorder'], 0);
        $this->assertEquals(notices::get_notice($id1)['visible'], notices::NOTICE_IN_PREVIEW);
        $this->assertEquals(notices::get_notice($id1)['title'], 'test_notice_update_basic');

        $this->assertEquals(notices::get_notice($id2)['sortorder'], 1);
    }
}
