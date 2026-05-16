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

namespace block_notices\external;

use block_notices\notices;
use block_notices\util;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External function: record that the current user has seen one or more notices.
 *
 * @package    block_notices
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mark_read extends external_api {
    /**
     * Parameters definition.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course the carousel was viewed on'),
            'noticeids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Notice id'),
                'IDs of notices the user has seen'
            ),
        ]);
    }

    /**
     * Mark notices as read for the current user.
     *
     * @param int $courseid
     * @param int[] $noticeids
     * @return array{acknowledged: int[]}
     */
    public static function execute(int $courseid, array $noticeids): array {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'noticeids' => $noticeids,
        ]);

        if (!isloggedin() || isguestuser()) {
            return ['acknowledged' => []];
        }

        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);

        $acked = notices::mark_read_batch(
            (int)$USER->id,
            $params['noticeids'],
            (int)$params['courseid'],
            util::is_staff()
        );

        return ['acknowledged' => $acked];
    }

    /**
     * Returns definition.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'acknowledged' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Notice id that was acked')
            ),
        ]);
    }
}
