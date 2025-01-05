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

namespace block_notices\form;
use block_notices\notices;
use html_writer;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Class addnotice
 *
 * @package    block_notices
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notice extends \moodleform {
    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('hidden', 'id', '0');

        // Start of general group.

        $mform->addElement('header', 'general', get_string('basicinformationgroup', 'block_notices'));

        $mform->addElement('static', 'visible_label',
            get_string('visible', 'block_notices'),
            get_string('visibility_preview', 'block_notices'));

        $mform->addElement('text', 'title', get_string('title', 'block_notices'), ['size' => 64]);
        $mform->setDefault('title', '');
        $mform->setType('title', PARAM_TEXT);
        $mform->addHelpButton('title', 'title', 'block_notices');
        $mform->addRule('title', null, 'required', null, 'client');

        $mform->addElement('text', 'updatedescription', get_string('updatedescription', 'block_notices'));
        $mform->setDefault('updatedescription', '');
        $mform->setType('updatedescription', PARAM_TEXT);
        $mform->addHelpButton('updatedescription', 'updatedescription', 'block_notices');

        $editoroptions = ['maxfiles' => 0, 'noclean' => true, 'context' => null];
        $mform->addElement('editor', 'content', get_string('content', 'block_notices'), null, $editoroptions);
        $mform->setDefault('content', ['text' => '']);
        $mform->addRule('content', null, 'required', null, 'client');
        $mform->setType('content', PARAM_RAW); // XSS is prevented when printing the block contents and serving files.

        // Arguably a bit of a hack to get the help text to display in my preferred place.
        $mform->addElement('html', html_writer::div(
            '<i class="icon fa fa-info-circle " aria-hidden="true"></i>' . get_string('content_help_additional', 'block_notices'),
            'alert alert-info help_text', ['style' => 'margin-left: calc(25% + 7px );']));
        $mform->addElement('html', html_writer::tag('hr', ''));

        $mform->addElement('text', 'moreinformationurl', get_string('moreinformationurl', 'block_notices'), ['size' => 128]);
        $mform->setDefault('moreinformationurl', '');
        $mform->setType('moreinformationurl', PARAM_TEXT);
        $mform->addHelpButton('moreinformationurl', 'moreinformationurl', 'block_notices');
        // End of general group. Don't need to close this group as it is automatically closed by the next group.

        // Start of owner group.
        $mform->addElement('header', 'ownergroup', get_string('ownergroup', 'block_notices'));

        $mform->addElement('html', html_writer::div(
            get_string('ownergroupdescription', 'block_notices'),
            'alert alert-warning help_text'));

        $mform->addElement('text', 'owner', get_string('owner', 'block_notices'));
        $mform->setDefault('owner', '');
        $mform->setType('owner', PARAM_TEXT);
        $mform->addHelpButton('owner', 'owner', 'block_notices');
        $mform->addRule('owner', null, 'required', null, 'client');

        $mform->addElement('text', 'owneremail', get_string('owneremail', 'block_notices'));
        $mform->setDefault('owneremail', '');
        $mform->setType('owneremail', PARAM_TEXT);
        $mform->addHelpButton('owneremail', 'owneremail', 'block_notices');
        $mform->addRule('owneremail', null, 'required', null, 'client');

        $mform->closeHeaderBefore('notes');
        // End of owner group.

        $mform->addElement('text', 'notes', get_string('notes', 'block_notices'), ['size' => 128]);
        $mform->setDefault('notes', '');
        $mform->setType('notes', PARAM_TEXT);
        $mform->addHelpButton('notes', 'notes', 'block_notices');

        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('save'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
    }
}
