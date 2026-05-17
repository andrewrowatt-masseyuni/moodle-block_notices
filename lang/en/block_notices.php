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

$string['additionaleditordescription'] = 'You can optionally specify an additional editor. This is a Stream user who can directly edit the notice.';
$string['additionaleditorid'] = 'Additional editor';
$string['additionaleditorid_help'] = 'The Moodle user who will be able to edit this notice in addition to Notices managers. Leave blank for no additional editor (so only Notices managers can edit it).';
$string['additionaleditorid_none'] = 'No additional editor';
$string['additionaleditorid_unset'] = 'No additional editor';
$string['addnotice'] = 'Add notice';
$string['basicinformationgroup'] = 'General';
$string['block_notices:missingblock'] = 'Unable to to manage notices for a course without a Notices block.';
$string['clearexclusive'] = 'Clear exclusive';
$string['content'] = 'Content';
$string['content_help_additional'] = 'Use basic formatting only e.g., bold, italic, and links. Do not use headings, lists, images, and tables.';
$string['createdby'] = 'Created by';
$string['customcss'] = 'Custom CSS';
$string['customcssdesc'] = 'Use this setting to override the base CSS/SCSS. Use for minor changes only.';
$string['errornopermission'] = 'You do not have permission to manage this notice.';
$string['event_notice_created'] = 'Notice created';
$string['event_notice_created_desc'] = 'The user with id \'{$a->userid}\' created notice with id \'{$a->objectid}\'.';
$string['event_notice_deleted'] = 'Notice deleted';
$string['event_notice_deleted_desc'] = 'The user with id \'{$a->userid}\' deleted notice with id \'{$a->objectid}\'.';
$string['event_notice_hidden'] = 'Notice hidden';
$string['event_notice_hidden_desc'] = 'The user with id \'{$a->userid}\' hid notice with id \'{$a->objectid}\'.';
$string['event_notice_promoted'] = 'Notice promoted';
$string['event_notice_promoted_desc'] = 'The user with id \'{$a->userid}\' promoted notice with id \'{$a->objectid}\'.';
$string['event_notice_updated'] = 'Notice updated';
$string['event_notice_updated_desc'] = 'The user with id \'{$a->userid}\' updated notice with id \'{$a->objectid}\'.';
$string['event_notice_visible'] = 'Notice made visible';
$string['event_notice_visible_desc'] = 'The user with id \'{$a->userid}\' made notice with id \'{$a->objectid}\' visible.';
$string['exclusive_important'] = 'Exclusive - Important';
$string['exclusive_information'] = 'Exclusive - Information';
$string['managemynotices'] = 'Manage my notices';
$string['managenotices'] = 'Manage notices';
$string['modifiedby'] = 'Modified by';
$string['moreinformation'] = 'More information';
$string['moreinformationurl'] = 'URL for more information';
$string['moreinformationurl_help'] = 'URL for more information about this notice. If not provided, owner information will be used.';
$string['nonotices'] = 'There are no notices. Have a great day!';
$string['notes'] = 'Notes';
$string['notes_help'] = 'Administrative notes and next steps/actions for this notice e.g., when to hide or remove it. <b>These notes are not displayed on the notice</b>.';
$string['notices'] = 'Notices';
$string['notices:addinstance'] = 'Add Notices block';
$string['notices:manageallnotices'] = 'Manage all notices';
$string['notices:myaddinstance'] = 'Add instance to frontpage.';
$string['nreads'] = '{$a} read(s)';
$string['nreads_currenttotal'] = '{$a->current} / {$a->total} read(s)';
$string['nreads_help'] = 'Reads of the current version of this notice / total reads across all versions.';
$string['owner'] = 'Owner';
$string['owner_help'] = "Owner or person responsible for this notice. This is not displayed unless the 'URL for more information' is blank.";
$string['owneremail'] = 'Owner email address';
$string['owneremail_help'] = "Valid email address for the owner or person responsible for this notice. This is not displayed unless 'URL for more information.";
$string['ownergroup'] = 'Owner details';
$string['ownergroupdescription'] = 'Specify the details for the person responsible ("owner") of the notice i.e., the person who requested or authorized the notice. This will not be displayed on the notice unless the "URL for more information" field is blank.';
$string['pluginname'] = 'Notices';
$string['privacy:metadata'] = 'TBA';
$string['privacy:metadata:blocks_notices'] = 'Information about individual notices';
$string['privacy:metadata:blocks_notices:additionaleditorid'] = 'The user given additional edit rights for the notice';
$string['privacy:metadata:blocks_notices:content'] = 'Content of the notice';
$string['privacy:metadata:blocks_notices:createdbyuserid'] = 'The user who created the notice';
$string['privacy:metadata:blocks_notices:modifiedbyuserid'] = 'The user who last modified the notice';
$string['privacy:metadata:blocks_notices_read'] = 'Records when each user last saw each notice in the carousel.';
$string['privacy:metadata:blocks_notices_read:noticeid'] = 'The notice that was seen';
$string['privacy:metadata:blocks_notices_read:timeread'] = 'The time the user last saw the notice';
$string['privacy:metadata:blocks_notices_read:userid'] = 'The user who saw the notice';
$string['promotenotice'] = 'Promote notice';
$string['reads'] = 'Notice reads';
$string['role_manager_description'] = 'Users with this role can create, edit, delete, reorder, show and hide any notice in any course.';
$string['role_manager_name'] = 'Notices manager';
$string['setexclusiveimportant'] = 'Make exclusive (Important)';
$string['setexclusiveinformation'] = 'Make exclusive (Information)';
$string['staffonly'] = 'Staff only';
$string['title'] = 'Title';
$string['title_help'] = 'Title of notice. Maximum of 64 characters.';
$string['updatedescription_aboutahourago'] = '{$a->prefix} about an hour ago';
$string['updatedescription_aboutamonthago'] = '{$a->prefix} about a month ago';
$string['updatedescription_aboutaweekago'] = '{$a->prefix} about a week ago';
$string['updatedescription_aboutayearago'] = '{$a->prefix} about a year ago';
$string['updatedescription_afewminutesago'] = '{$a->prefix} a few minutes ago';
$string['updatedescription_justnow'] = '{$a->prefix} just now';
$string['updatedescription_ndaysago'] = '{$a->prefix} {$a->amount} days ago';
$string['updatedescription_nhoursago'] = '{$a->prefix} {$a->amount} hours ago';
$string['updatedescription_nminutesago'] = '{$a->prefix} {$a->amount} minutes ago';
$string['updatedescription_nmonthsago'] = '{$a->prefix} {$a->amount} months ago';
$string['updatedescription_nweeksago'] = '{$a->prefix} {$a->amount} weeks ago';
$string['updatedescription_overayearago'] = '{$a->prefix} over a year ago';
$string['updatedescription_prefix_added'] = 'Added';
$string['updatedescription_prefix_updated'] = 'Updated';
$string['updatedescription_yesterday'] = '{$a->prefix} yesterday';
$string['visibility_hidden'] = 'Hidden';
$string['visibility_preview'] = 'Preview (visible to Admins only)';
$string['visibility_visible'] = 'Visible';
$string['visible'] = 'Visible';
