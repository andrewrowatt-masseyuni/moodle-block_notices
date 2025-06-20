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

$noticeid = required_param('id', PARAM_INT);
$notice = (array)notices::get_notice($noticeid);

// Setup page context and course and check permissions.
$courseid = $notice['courseid'];
if ($courseid == 1) {
    require_login();
    $context = context_system::instance();
    $PAGE->set_context($context);
} else {
    require_login($courseid);
}
require_capability('block/notices:managenotices', $PAGE->context);

$url = new moodle_url('/blocks/notices/edit.php', ['noticeid' => $noticeid]);
$PAGE->set_url($url);
$PAGE->set_title(get_string('addnotice', 'block_notices'));
$PAGE->set_heading(get_string('addnotice', 'block_notices'));

$noticeform = new \block_notices\form\notice($url);

if ($noticeform->is_cancelled()) {
    redirect(new moodle_url('/blocks/notices/manage.php', ['courseid' => $courseid]));
} else if ($formdata = $noticeform->get_data()) {
    $data = [
        'id' => $formdata->id,
        'visible' => notices::NOTICE_IN_PREVIEW, // Force into preview mode.
        'title' => $formdata->title,
        'content' => $formdata->content['text'],
        'contentformat' => $formdata->content['format'],
        'updatedescription' => $formdata->updatedescription,
        'moreinformationurl' => $formdata->moreinformationurl,
        'owner' => $formdata->owner,
        'owneremail' => $formdata->owneremail,
        'notes' => $formdata->notes,
        'staffonly' => !empty($formdata->staffonly),
    ];

    notices::update_notice($data);

    redirect(new moodle_url('/blocks/notices/manage.php', ['courseid' => $courseid]));
} else {
    // This branch is executed if the form is submitted but the data doesn't
    // validate and the form should be redisplayed or on the first display of the form.

    // Set anydefault data (if any).
    $noticeform->set_data(
        ['content' => [
            'text' => $notice['content'],
            'format' => $notice['contentformat'],
            ],
        ] + $notice
    );
}

echo $OUTPUT->header();

$noticeform->display();

echo $OUTPUT->footer();
