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

require_login();

$instanceid = required_param('instanceid', PARAM_INT);
$url = new moodle_url('/blocks/notices/add.php', ['instanceid' => $instanceid]);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$PAGE->set_heading($SITE->fullname);

$noticeform = new \block_notices\form\addnotice($url);

if ($data = $noticeform->get_data()) {
        $record = new stdClass;
        $record->instanceid = $instanceid;
        $record->visible = $data->visible ? 1 : 0;
        $record->title = $data->title;
        $record->content = $data->content['text'];
        
        $record->updatedescription = $data->updatedescription;
        $record->moreinformation = ''; // $data->moreinformation;
        $record->owner = ''; // $data->owner;
        $record->owneremail = ''; // $data->owneremail;
        $record->notes = ''; // $data->notes['text'];

        $record->timecreated = time();
        $record->timemodified = $record->timecreated; // To ensure the same value.
        $record->createdby = $USER->id;
        $record->modifiedby = $USER->id;

        $DB->insert_record('block_notices', $record);
        redirect(new moodle_url('/blocks/notices/manage.php', ['instanceid' => $instanceid]));
}

echo $OUTPUT->header();

$noticeform->display();

echo $OUTPUT->footer();
