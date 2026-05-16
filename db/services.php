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
 * External functions for block_notices.
 *
 * @package    block_notices
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_notices_mark_read' => [
        'classname' => 'block_notices\external\mark_read',
        'methodname' => 'execute',
        'description' => 'Mark one or more notices as read for the current user.',
        'type' => 'write',
        'ajax' => true,
        // Guests can hit this; the function returns an empty acknowledged list for them
        // rather than producing a 401 that the carousel would log on the site-index page.
        'loginrequired' => false,
    ],
];
