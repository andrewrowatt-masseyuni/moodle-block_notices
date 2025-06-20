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
 * Tests for Notices
 *
 * @package    block_notices
 * @category   test
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider_test extends \core_privacy\tests\provider_testcase {
    /** @var \stdClass User object to share across tests. */
    protected \stdClass $user1;

    /** @var \stdClass User object to share across tests. */
    protected \stdClass $user2;

    /** @var \stdClass User object to share across tests. */
    protected \stdClass $user3;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $user1 = $this->getDataGenerator()->create_user();
        $this->user1 = $user1;

        $user2 = $this->getDataGenerator()->create_user();
        $this->user2 = $user2;

        $user3 = $this->getDataGenerator()->create_user();
        $this->user3 = $user3;

        $this->setUser($user1);
        $id = notices::add_notice(1, notices::TEST_DATA[0]);

        $notice = notices::get_notice($id);

        $this->setUser($user2);
        notices::update_notice($notice);
        $id = notices::add_notice(1, notices::TEST_DATA[1]);
        $id = notices::add_notice(1, notices::TEST_DATA[2]);
    }

    /**
     * Test for provider::get_metadata().
     *
     * @covers \local_faultreporting\privacy
     */
    public function test_get_metadata() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $collection = new \core_privacy\local\metadata\collection('block_notices');
        $classname = privacy\provider::class;
        $classname::get_metadata($collection);

        // Check that the collection contains the expected items.
        $this->assertCount(1, $collection->get_collection());
    }

    /**
     * Test for provider::get_users_in_context().
     *
     * @covers \local_faultreporting\privacy
     */
    public function test_get_users_in_context() {
        $cmcontext = \context_course::instance(1);

        $userlist = new \core_privacy\local\request\userlist($cmcontext, 'block_notices');
        privacy\provider::get_users_in_context($userlist);

        // Check that the userlist contains the expected users - order agnostic.
        $this->assertEquals(
            [],
            array_diff(
            [$this->user1->id, $this->user2->id],
            $userlist->get_userids()
        )
        );
    }

    /**
     * Test for provider::get_contexts_for_userid().
     *
     * @covers \local_faultreporting\privacy
     */
    public function test_get_contexts_for_userid() {
        $contextlist = privacy\provider::get_contexts_for_userid($this->user1->id);
        $this->assertCount(1, $contextlist);

        $contextlist = privacy\provider::get_contexts_for_userid($this->user3->id);
        $this->assertCount(0, $contextlist);
    }

    /**
     * Test for provider::delete_data_for_user
     *
     * @covers \local_faultreporting\privacy
     */
    public function test_delete_data_for_user() {
        $notices = notices::get_notices_admin(1);
        $this->assertCount(3, $notices);

        $contextlist = new \core_privacy\local\request\approved_contextlist(
            $this->user1, 'block_notices', [\context_course::instance(1)->id]);
        privacy\provider::delete_data_for_user($contextlist);

        $notices = notices::get_notices_admin(1);
        $this->assertCount(2, $notices);
    }

    /**
     * Test for provider::delete_data_for_users
     *
     * @covers \local_faultreporting\privacy
     */
    public function test_delete_data_for_users() {
        $notices = notices::get_notices_admin(1);
        $this->assertCount(3, $notices);

        $approveduserlist = new \core_privacy\local\request\approved_userlist(
            \context_course::instance(1), 'local_faultreporting',
            [$this->user1->id, $this->user3->id]);
        privacy\provider::delete_data_for_users($approveduserlist);

        $notices = notices::get_notices_admin(1);
        $this->assertCount(2, $notices);
    }

    /**
     * Test for provider::delete_data_for_all_users_in_context
     *
     * @covers \local_faultreporting\privacy
     */
    public function test_delete_data_for_all_users_in_context() {
        $context = \context_course::instance(1);

        privacy\provider::delete_data_for_all_users_in_context($context);

        // Check that the fault reports have been deleted.
        $notices = notices::get_notices_admin(1);
        $this->assertCount(0, $notices);
    }

    /**
     * Test for provider::export_user_data
     *
     * @covers \local_faultreporting\privacy
     */
    public function test_export_user_data() {
        $contextlist = new \core_privacy\local\request\approved_contextlist(
            $this->user1, 'block_notices', [\context_course::instance(1)->id]);

        privacy\provider::export_user_data($contextlist);
    }
}
