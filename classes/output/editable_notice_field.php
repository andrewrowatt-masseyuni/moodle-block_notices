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

namespace block_notices\output;

use block_notices\notices;
use core\output\inplace_editable;

/**
 * Inplace-editable renderable for short text fields on a notice (title and updatedescription).
 *
 * @package    block_notices
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class editable_notice_field extends inplace_editable {
    /** @var array Allowed itemtypes mapped to their lang string keys. */
    private const FIELDS = [
        'title' => [
            'edithint' => 'edittitle',
            'editlabel' => 'newtitlefor',
        ],
        'updatedescription' => [
            'edithint' => 'editupdatedescription',
            'editlabel' => 'newupdatedescriptionfor',
        ],
    ];

    /** @var int Maximum length of either field, matches db/install.xml CHAR(64). */
    public const MAX_LENGTH = 64;

    /**
     * Construct an inplace editable for a notice field.
     *
     * @param string $itemtype Either 'title' or 'updatedescription'.
     * @param \stdClass $notice Full notice record (must include id, courseid, title, updatedescription).
     */
    public function __construct(string $itemtype, \stdClass $notice) {
        if (!isset(self::FIELDS[$itemtype])) {
            throw new \coding_exception("Unknown notice field: $itemtype");
        }
        $context = \context_course::instance($notice->courseid);
        $editable = has_capability('block/notices:managenotices', $context);
        $value = (string)$notice->{$itemtype};
        $displayvalue = format_string($value, true, ['context' => $context]);

        $edithint = new \lang_string(self::FIELDS[$itemtype]['edithint'], 'block_notices');
        $editlabel = new \lang_string(
            self::FIELDS[$itemtype]['editlabel'],
            'block_notices',
            format_string($notice->title, true, ['context' => $context])
        );

        parent::__construct(
            'block_notices',
            $itemtype,
            (int)$notice->id,
            $editable,
            $displayvalue,
            $value,
            $edithint,
            $editlabel
        );
    }

    /**
     * Persist a new value for the given field. Called from block_notices_inplace_editable().
     *
     * @param string $itemtype
     * @param int $itemid
     * @param string $newvalue
     * @return self
     */
    public static function update(string $itemtype, int $itemid, string $newvalue): self {
        global $DB;
        if (!isset(self::FIELDS[$itemtype])) {
            throw new \coding_exception("Unknown notice field: $itemtype");
        }
        $notice = $DB->get_record('block_notices', ['id' => $itemid], '*', MUST_EXIST);
        $context = \context_course::instance($notice->courseid);
        \core_external\external_api::validate_context($context);
        require_capability('block/notices:managenotices', $context);

        $newvalue = trim(clean_param($newvalue, PARAM_TEXT));
        if (\core_text::strlen($newvalue) > self::MAX_LENGTH) {
            throw new \moodle_exception('errorfieldtoolong', 'block_notices');
        }
        if ($itemtype === 'title' && $newvalue === '') {
            throw new \moodle_exception('errortitlerequired', 'block_notices');
        }

        notices::update_notice_field($itemid, $itemtype, $newvalue);

        $notice->{$itemtype} = $newvalue;
        return new self($itemtype, $notice);
    }
}
