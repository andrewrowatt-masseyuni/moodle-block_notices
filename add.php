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
 * TODO describe file add
 *
 * @package    block_notices
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

use block_notices\notices;

// Setup page context and course and check permissions.
$courseid = required_param('courseid', PARAM_INT);
if ($courseid == 1) {
    require_login();
    $context = context_system::instance();
    $PAGE->set_context($context);
} else {
    require_login($courseid);
}
require_capability('block/notices:managenotices', $PAGE->context);

$url = new moodle_url('/blocks/notices/add.php', ['courseid' => $courseid]);
$PAGE->set_url($url);
$PAGE->set_title(get_string('addnotice', 'block_notices'));
$PAGE->set_heading(get_string('addnotice', 'block_notices'));

$noticeform = new \block_notices\form\notice($url);

if ($noticeform->is_cancelled()) {
    redirect(new moodle_url('/blocks/notices/manage.php', ['courseid' => $courseid]));
} else if ($formdata = $noticeform->get_data()) {
    $data = [
        'staffonly' => !empty($formdata->staffonly),
        'title' => $formdata->title,
        'content' => $formdata->content['text'],
        'contentformat' => $formdata->content['format'],
        'updatedescription' => $formdata->updatedescription,
        'moreinformationurl' => $formdata->moreinformationurl,
        'owner' => $formdata->owner,
        'owneremail' => $formdata->owneremail,
        'notes' => $formdata->notes,
    ];

    notices::add_notice($courseid, $data);

    redirect(new moodle_url('/blocks/notices/manage.php', ['courseid' => $courseid]));
}

echo $OUTPUT->header();

$noticeform->display();

echo $OUTPUT->footer();
