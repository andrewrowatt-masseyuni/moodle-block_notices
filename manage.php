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
 * TODO describe file manage
 *
 * @package    block_notices
 * @copyright  2025 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

require_login();

$instanceid = required_param('instanceid', PARAM_INT);
$url = new moodle_url('/blocks/notices/manage.php', ['instanceid' => $instanceid]);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
echo html_writer::link(
    new moodle_url('/blocks/notices/add.php', ['instanceid' => $instanceid,]),
    $OUTPUT->pix_icon('t/preferences', get_string('addnotice', 'block_notices')) . get_string('addnotice', 'block_notices'),
    ['role' => 'button']
);
echo $OUTPUT->footer();
