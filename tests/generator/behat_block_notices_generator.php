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
 * Behat plugin generator
 *
 * @package    block_notices
 * @category   test
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_block_notices_generator extends behat_generator_base {
    /**
     * Define the creatable entity.
     *
     * @return array
     */
    protected function get_creatable_entities(): array {
        return [
            'notices' => [
                'singular' => 'notice',
                'datagenerator' => 'notice',
                'required' => [
                    'course',
                    'visible',
                    'title',
                    'content',
                    'moreinformationurl',
                    'owner',
                    'owneremail',
                ],
                'switchids' => [
                    'course' => 'courseid',
                    'visible' => 'visible',
                    'createdby' => 'createdbyuserid',
                    'modifiedby' => 'modifiedbyuserid',
                ],
            ],
        ];
    }

    /**
     * Gets the user id from it's username.
     *
     * @throws Exception
     * @param string $username
     * @return int
     */
    protected function get_createdby_id(string $username): int {
        global $DB;

        if (!$id = $DB->get_field('user', 'id', ['username' => $username])) {
            throw new Exception('The specified user with username "' . $username . '" does not exist');
        }
        return $id;
    }

    /**
     * Gets the user id from it's username.
     *
     * @throws Exception
     * @param string $username
     * @return int
     */
    protected function get_modifiedby_id(string $username): int {
        global $DB;

        if (!$id = $DB->get_field('user', 'id', ['username' => $username])) {
            throw new Exception('The specified user with username "' . $username . '" does not exist');
        }
        return $id;
    }

    /**
     * Returns the visibility id based on the string provided.
     *
     * @param string $visible
     * @return int
     * @throws Exception
     */
    protected function get_visible_id(string $visible): int {
        switch ($visible) {
            case 'NOTICE_VISIBLE':
                return 1;
            case 'NOTICE_HIDDEN':
                return 0;
            case 'NOTICE_IN_PREVIEW':
                return 2;
            default:
                throw new Exception('The specified visibility "' . $visible
                    . '" is not valid. Use NOTICE_VISIBLE, NOTICE_HIDDEN, or NOTICE_IN_PREVIEW.');
        }
    }
}
