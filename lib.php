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
 * Callback implementations for Notices.
 *
 * @package    block_notices
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Serve files for the block_notices "image" filearea.
 *
 * Files live under the notice's course context (or the system context for site/dashboard
 * notices). Access tracks the course: a user who can view the course (or the front page)
 * can fetch the image. Staff-only notices require staff access.
 *
 * @param stdClass $course course object (or null for system-context notices)
 * @param stdClass $birecord block instance record (unused)
 * @param context $context file context
 * @param string $filearea
 * @param array $args [itemid, ...filepath, filename]
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function block_notices_pluginfile($course, $birecord, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $CFG, $DB;

    if ($filearea !== 'image') {
        send_file_not_found();
    }
    if (!in_array($context->contextlevel, [CONTEXT_COURSE, CONTEXT_SYSTEM], true)) {
        send_file_not_found();
    }

    if ($context->contextlevel == CONTEXT_COURSE) {
        require_course_login($course);
    } else if (!empty($CFG->forcelogin)) {
        require_login();
    }

    $itemid = (int)array_shift($args);
    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    $notice = $DB->get_record('block_notices', ['id' => $itemid], 'id, courseid, staffonly');
    if (!$notice) {
        send_file_not_found();
    }

    // Confirm the notice is hosted in the requested context — keeps the URL from being used
    // to probe arbitrary stored files in another course's context.
    $expectedcontextid = \block_notices\notices::get_notice_context((int)$notice->courseid)->id;
    if ((int)$context->id !== (int)$expectedcontextid) {
        send_file_not_found();
    }

    if (!empty($notice->staffonly) && !\block_notices\util::is_staff()) {
        send_file_not_found();
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'block_notices', 'image', $itemid, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }

    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}
