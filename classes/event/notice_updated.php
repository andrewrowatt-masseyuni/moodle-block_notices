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

namespace block_notices\event;

/**
 * Event notice_updated
 *
 * @package    block_notices
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notice_updated extends \core\event\base {

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
        return get_string('event_notice_updated', 'block_notices');
    }

    /**
     * Get description.
     * @return \lang_string|string|null
     */
    public function get_description() {
        $obj = new \stdClass;
        $obj->userid = $this->userid;
        $obj->objectid = $this->objectid;
        return get_string('event_notice_updated_desc', 'block_notices', $obj);
    }

    /**
     * This is used when restoring course logs where it is required that we
     * map the objectid to it's new value in the new course.
     *
     * Does nothing in the base class except display a debugging message warning
     * the user that the event does not contain the required functionality to
     * map this information. For events that do not store an objectid this won't
     * be called, so no debugging message will be displayed.
     *
     * @return string the name of the restore mapping the objectid links to
     */
    public static function get_objectid_mapping(): array {
        return [
            'db'        => 'notices',
            'restore'   => \core\event\base::NOT_MAPPED,
        ];
    }

    /**
     * The 'other' fields for this event do not need to mapped during backup and restore as they
     * only contain test values, not IDs for anything on the course.
     *
     * @return array Empty array
     */
    public static function get_other_mapping(): array {
        return [];
    }
}
