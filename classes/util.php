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
 * Class util
 *
 * @package    block_notices
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class util {
    /**
     * Returns true if username is 8 digits
     *
     * @return bool
     */
    public static function is_student(): bool {
        global $USER;

        return preg_match('/^\d{8}$/', $USER->username) == 1;
    }

    /**
     * Returns true if username is "st" account format
     *
     * @return bool
     */
    public static function is_st_account(): bool {
        global $USER;

        return preg_match('/^st\d{6}$/', $USER->username) == 1;
    }

    /**
     * Returns true if username is not a student or "st" account
     *
     * @return bool
     */
    public static function is_staff(): bool {
        return !self::is_student() && !self::is_st_account();
    }
}
