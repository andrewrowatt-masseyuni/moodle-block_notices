<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     block_notices
 * @category    string
 * @copyright   2024 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addnotice'] = 'Add notice';
$string['basicinformationgroup'] = 'General';
$string['block_notices:missingblock'] = 'Unable to to manage notices for a course without a Notices block.';
$string['content'] = 'Content';
$string['content_help_additional'] = 'Use basic formatting only e.g., bold, italic, and links. Do not use headings, lists, images, and tables.';
$string['customcss'] = 'Custom CSS';
$string['customcssdesc'] = 'Use this setting to override the base CSS/SCSS. Use for minor changes only.';
$string['event_notice_updated'] = 'Notice updated';
$string['event_notice_updated_desc'] = 'The user with id \'{$a->userid}\' updated notice with id \'{$a->objectid}\'.';
$string['managenotices'] = 'Manage notices';
$string['moreinformation'] = 'More information';
$string['moreinformationurl'] = 'URL for more information';
$string['moreinformationurl_help'] = 'URL for more information about this notice. If not provided, owner information will be used.';
$string['nonotices'] = 'No notices.';
$string['notes'] = 'Notes';
$string['notes_help'] = 'Administrative notes and next steps/actions for this notice e.g., when to hide or remove it. <b>These notes are not displayed on the notice</b>.';
$string['notices'] = 'Notices';
$string['notices:addinstance'] = 'Add Notices block';
$string['notices:managenotices'] = 'Manage notices';
$string['notices:myaddinstance'] = 'Add instance to frontpage.';
$string['owner'] = 'Owner';
$string['owner_help'] = "Owner or person responsible for this notice. This is not displayed unless the 'URL for more information' is blank.";
$string['owneremail'] = 'Owner email address';
$string['owneremail_help'] = "Valid email address for the owner or person responsible for this notice. This is not displayed unless 'URL for more information.";
$string['ownergroup'] = 'Owner details';
$string['ownergroupdescription'] = 'Specify the details for the person responsible ("owner") of the notice i.e., the person who requested or authorized the notice. This will not be displayed on the notice unless the "URL for more information" field is blank.';
$string['pluginname'] = 'Notices';
$string['privacy:metadata'] = 'TBA';
$string['privacy:metadata:blocks_notices'] = 'Information about individual notices';
$string['privacy:metadata:blocks_notices:content'] = 'Content of the notice';
$string['privacy:metadata:blocks_notices:createdby'] = 'The use who created the notice';
$string['privacy:metadata:blocks_notices:modifiedby'] = 'The user who last modified the notice';
$string['staffonly'] = 'Staff only';
$string['title'] = 'Title';
$string['title_help'] = 'Title of notice. Maximum of 64 characters.';
$string['updatedescription'] = 'Update description';
$string['updatedescription_help'] = 'Use one of the conventions i.e., "Added 5 January", "Updated 6 Janurary", "Updated 4pm, 6 January". Maximum of 64 characters.';
$string['visibility_hidden'] = 'Hidden';
$string['visibility_preview'] = 'Preview (visible to Admins only)';
$string['visibility_visible'] = 'Visible';
$string['visible'] = 'Visible';
