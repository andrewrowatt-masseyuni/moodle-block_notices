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

namespace block_notices\task;

use block_notices\notices;

/**
 * Scheduled task that auto-closes notices whose closedate has passed.
 *
 * @package    block_notices
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auto_close_notices extends \core\task\scheduled_task {
    /**
     * Name shown in the scheduled tasks admin UI.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_auto_close_notices', 'block_notices');
    }

    /**
     * Find every notice whose closedate has passed and close it.
     */
    public function execute() {
        $now = time();
        $ids = notices::get_notices_due_for_close($now);
        foreach ($ids as $id) {
            notices::auto_close_notice((int)$id);
        }
    }
}
