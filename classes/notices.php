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
     * @var int documenting here because we are using the moodleform selectyesno implementation
     */
    public const NOTICE_VISIBLE = 1;

    /**
     * @var int documenting here because we are using the moodleform selectyesno implementation
     */
    public const NOTICE_HIDDEN = 0;

    /**
     * Get all notices for a given instance.
     *
     * @param int $instanceid
     * @return array
     */
    public static function get_notices($instanceid): array {
        global $DB;

        $sql = 'SELECT * FROM {block_notices} WHERE instanceid = :instanceid';
        return $DB->get_records_sql($sql, ['instanceid' => $instanceid]);
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
    public static function add_notice(int $instanceid, object $data): void {
        global $DB, $USER;

        $data->instanceid = $instanceid;

        $data->timecreated = time();
        $data->timemodified = $data->timecreated; // To ensure the same value.
        $data->createdby = $USER->id;
        $data->modifiedby = $USER->id;

        $DB->insert_record('block_notices', $data);
    }
}
