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

namespace block_notices\event;

/**
 * Event notice_auto_closed
 *
 * @package    block_notices
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notice_auto_closed extends \core\event\base {
    /**
     * Set basic properties for the event.
     */
    protected function init() {
        $this->data['objecttable'] = 'block_notices';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Get name.
     * @return \lang_string|string
     */
    public static function get_name() {
        return get_string('event_notice_auto_closed', 'block_notices');
    }

    /**
     * Get description.
     * @return \lang_string|string|null
     */
    public function get_description() {
        $obj = new \stdClass();
        $obj->userid = $this->userid;
        $obj->objectid = $this->objectid;
        return get_string('event_notice_auto_closed_desc', 'block_notices', $obj);
    }

    /**
     * Mapping for the objectid when restoring course logs.
     *
     * @return array
     */
    public static function get_objectid_mapping(): array {
        return [
            'db'        => 'notices',
            'restore'   => \core\event\base::NOT_MAPPED,
        ];
    }

    /**
     * The 'other' fields do not need mapping during backup/restore.
     *
     * @return array Empty array
     */
    public static function get_other_mapping(): array {
        return [];
    }
}
