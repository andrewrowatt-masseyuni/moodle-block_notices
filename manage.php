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
notices::require_notice_block($courseid);

// Add some test data if there are no notices. This code will probably be removed in the final version.
if (notices::get_notice_count($courseid) == -1) {
    notices::add_notice_test_data($courseid);
}

$url = new moodle_url('/blocks/notices/manage.php', ['courseid' => $courseid]);
$PAGE->set_url($url);
$PAGE->set_title(get_string('managenotices', 'block_notices'));
$PAGE->set_heading(get_string('managenotices', 'block_notices'));

$action = optional_param('action', null, PARAM_TEXT);
$noticeid = optional_param('id', null, PARAM_INT);

if ($action && $noticeid) {
    require_sesskey();

    switch ($action) {
        case 'hide':
            notices::hide_notice($noticeid);
            break;
        case 'show':
            notices::show_notice($noticeid);
            break;
        case 'delete':
            notices::delete_notice($noticeid);
            break;
        case 'moveup':
            notices::move_up($noticeid);
            break;
        case 'movedown':
            notices::move_down($noticeid);
            break;

    }

    // Redirect as Moodle good practice to remove the session key from the URL.
    redirect($url);
}

/*
    Setup notice "groups" (based on visibility) for the template.
    Could be alternatively done using separate hardcoded templates.
*/

$noticegrouphidden = [
    'description' => get_string('visibility_hidden', 'block_notices'),
    'css' => notices::NOTICE_VISIBLITY_BOOTSTRAP_CSS_CLASS[notices::NOTICE_HIDDEN],
    'notices' => [
    ],
];

$noticegroupvisible = [
    'description' => get_string('visibility_visible', 'block_notices'),
    'css' => notices::NOTICE_VISIBLITY_BOOTSTRAP_CSS_CLASS[notices::NOTICE_VISIBLE],
    'notices' => [
    ],
];

$noticegroupinpreview = [
    'description' => get_string('visibility_preview', 'block_notices'),
    'css' => notices::NOTICE_VISIBLITY_BOOTSTRAP_CSS_CLASS[notices::NOTICE_IN_PREVIEW],
    'notices' => [
    ],
];

// Iterate over all notices, add additional properties to improve the template output, and then add them to the correct "group".
foreach (notices::get_notices_admin($courseid) as $noticeobject) {
    // Convert the dataset to an array ready for using with a template.
    $noticearray = (array)$noticeobject;

    // Add extra properties to improve the template output.
    switch ($noticearray['visible']) {
        case notices::NOTICE_HIDDEN:
            $noticearray += [
                'canshow' => true,
            ];
            $noticegrouphidden['notices'][] = $noticearray;
            break;
        case notices::NOTICE_VISIBLE:
            $noticearray += [
                'canhide' => true,
                'canmoveup' => !$noticearray['isfirst'],
                'canmovedown' => !$noticearray['islast'],
                'showsortorder' => true,
            ];
            $noticegroupvisible['notices'][] = $noticearray;
            break;
        case notices::NOTICE_IN_PREVIEW:
            $noticearray += [
                'canhide' => true,
                'canshow' => true,
            ];
            $noticegroupinpreview['notices'][] = $noticearray;
            break;
    }
}

// Prepare the data for the template.
$data = [
    'sesskey' => sesskey(),
    'courseid' => $courseid,
    'groups' => [
        $noticegroupinpreview,
        $noticegroupvisible,
        $noticegrouphidden,
    ],
];

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('block_notices/notices_admin', $data);

echo $OUTPUT->footer();
