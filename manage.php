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

require_login();

use block_notices\notices;

$instanceid = required_param('instanceid', PARAM_INT);
require_capability('block/notices:managenotices', context_block::instance($instanceid));

// notices::add_notice_test_data($instanceid);

$url = new moodle_url('/blocks/notices/manage.php', ['instanceid' => $instanceid]);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

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

    redirect($url);
}

// Setup notice "groups" (based on visibility) for the template.
$noticegrouphidden = [
    'description' => notices::NOTICE_VISIBLITY[notices::NOTICE_HIDDEN],
    'css' => notices::NOTICE_VISIBLITY_BOOTSTRAP_CSS_CLASS[notices::NOTICE_HIDDEN],
    'notices' => [
    ],
];

$noticegroupvisible = [
    'description' => notices::NOTICE_VISIBLITY[notices::NOTICE_VISIBLE],
    'css' => notices::NOTICE_VISIBLITY_BOOTSTRAP_CSS_CLASS[notices::NOTICE_VISIBLE],
    'notices' => [
    ],
];

$noticegroupinpreview = [
    'description' => notices::NOTICE_VISIBLITY[notices::NOTICE_IN_PREVIEW],
    'css' => notices::NOTICE_VISIBLITY_BOOTSTRAP_CSS_CLASS[notices::NOTICE_IN_PREVIEW],
    'notices' => [
    ],
];

// Iterate over all notices, add additional properties to improve the template output, and then add them to the correct "group".
foreach (notices::get_notices_admin($instanceid) as $noticeobject) {
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
    'instanceid' => $instanceid,
    'groups' => [ 
        $noticegroupinpreview,
        $noticegroupvisible,
        $noticegrouphidden,
    ],
];

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('block_notices/notices_admin', $data);

echo $OUTPUT->footer();
