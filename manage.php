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
if (!notices::user_can_manage_any()) {
    throw new moodle_exception('errornopermission', 'block_notices');
}
notices::require_notice_block($courseid);

$canmanageall = has_capability('block/notices:manageallnotices', context_system::instance());

$url = new moodle_url('/blocks/notices/manage.php', ['courseid' => $courseid]);
$PAGE->set_url($url);
$PAGE->set_title(get_string('managenotices', 'block_notices'));
$PAGE->set_heading(get_string('managenotices', 'block_notices'));

$action = optional_param('action', null, PARAM_TEXT);
$noticeid = optional_param('id', null, PARAM_INT);
$exclusivevalue = optional_param('exclusive', null, PARAM_INT);

if ($action && $noticeid) {
    require_sesskey();

    // Per-action edit-rights check: prevents crafted URLs from acting on
    // notices the user is not the additional editor for.
    $targetnotice = notices::get_notice($noticeid);
    if (!$targetnotice || !notices::user_can_edit($targetnotice)) {
        throw new moodle_exception('errornopermission', 'block_notices');
    }

    // Reorder, delete and exclusive-flagging are reserved for manage-all users;
    // additional editors can only show/hide and edit.
    if (!$canmanageall && in_array($action, ['delete', 'moveup', 'movedown', 'setexclusive'], true)) {
        throw new moodle_exception('errornopermission', 'block_notices');
    }

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
        case 'setexclusive':
            $allowedexclusive = [
                notices::NOTICE_EXCLUSIVE_NONE,
                notices::NOTICE_EXCLUSIVE_IMPORTANT,
                notices::NOTICE_EXCLUSIVE_INFORMATION,
            ];
            if (!in_array($exclusivevalue, $allowedexclusive, true)) {
                throw new moodle_exception('errornopermission', 'block_notices');
            }
            notices::set_exclusive($noticeid, $exclusivevalue);
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
    'groupcssclasssuffix' => 'visibility-hidden',
    'description' => get_string('visibility_hidden', 'block_notices'),
    'css' => notices::NOTICE_VISIBLITY_BOOTSTRAP_CSS_CLASS[notices::NOTICE_HIDDEN],
    'notices' => [
    ],
    'count' => 0,
];

$noticegroupvisible = [
    'groupcssclasssuffix' => 'visibility-visible',
    'description' => get_string('visibility_visible', 'block_notices'),
    'css' => notices::NOTICE_VISIBLITY_BOOTSTRAP_CSS_CLASS[notices::NOTICE_VISIBLE],
    'notices' => [
    ],
    'count' => 0,
];

$noticegroupinpreview = [
    'groupcssclasssuffix' => 'visibility-preview',
    'description' => get_string('visibility_preview', 'block_notices'),
    'css' => notices::NOTICE_VISIBLITY_BOOTSTRAP_CSS_CLASS[notices::NOTICE_IN_PREVIEW],
    'notices' => [
    ],
    'count' => 0,
];

// Iterate over all notices, add additional properties to improve the template output, and then add them to the correct "group".
// Manage-own users only see notices they have been assigned as an additional editor on.
$additionaleditorfilter = $canmanageall ? null : (int)$USER->id;
foreach (notices::get_notices_admin($courseid, $additionaleditorfilter) as $noticeobject) {
    // Convert the dataset to an array ready for using with a template.
    $noticearray = (array)$noticeobject;

    // Render inplace-editable HTML for the title and updatedescription so they can be edited from the list.
    // export_for_template() requires a real renderer_base (the global $OUTPUT is the bootstrap_renderer
    // shim), and the renderable's own render() method targets the standard core/inplace_editable template.
    $renderer = $PAGE->get_renderer('core');
    $noticearray['titlehtml'] = (new \block_notices\output\editable_notice_field('title', $noticeobject))
        ->render($renderer);
    $noticearray['updatedescriptionhtml'] =
        (new \block_notices\output\editable_notice_field('updatedescription', $noticeobject))
        ->render($renderer);

    // Add extra properties to improve the template output. Reorder and delete are restricted to manage-all.
    $noticearray['candelete'] = $canmanageall;
    $exclusivevalue = (int)$noticearray['exclusive'];
    $noticearray['isexclusive_important'] = $exclusivevalue === notices::NOTICE_EXCLUSIVE_IMPORTANT;
    $noticearray['isexclusive_information'] = $exclusivevalue === notices::NOTICE_EXCLUSIVE_INFORMATION;
    switch ($noticearray['visible']) {
        case notices::NOTICE_HIDDEN:
            $noticearray += [
                'canshow' => true,
            ];
            $noticegrouphidden['notices'][] = $noticearray;
            $noticegrouphidden['count']++;
            break;
        case notices::NOTICE_VISIBLE:
            $noticearray += [
                'canhide' => true,
                'canmoveup' => $canmanageall && $noticearray['isfirst'] == 'f',
                'canmovedown' => $canmanageall && $noticearray['islast'] == 'f',
                'cansetexclusive_important' => $canmanageall
                    && $exclusivevalue !== notices::NOTICE_EXCLUSIVE_IMPORTANT,
                'cansetexclusive_information' => $canmanageall
                    && $exclusivevalue !== notices::NOTICE_EXCLUSIVE_INFORMATION,
                'canclearexclusive' => $canmanageall
                    && $exclusivevalue !== notices::NOTICE_EXCLUSIVE_NONE,
                'showsortorder' => true,
            ];
            $noticegroupvisible['notices'][] = $noticearray;
            $noticegroupvisible['count']++;
            break;
        case notices::NOTICE_IN_PREVIEW:
            $noticearray += [
                'canhide' => true,
                'canshow' => true,
            ];
            $noticegroupinpreview['notices'][] = $noticearray;
            $noticegroupinpreview['count']++;
            break;
    }
}

// Prepare the data for the template.
$data = [
    'sesskey' => sesskey(),
    'courseid' => $courseid,
    'canmanageall' => $canmanageall,
    'groups' => [
        $noticegroupinpreview,
        $noticegroupvisible,
        $noticegrouphidden,
    ],
];

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('block_notices/notices_admin', $data);

echo $OUTPUT->footer();
