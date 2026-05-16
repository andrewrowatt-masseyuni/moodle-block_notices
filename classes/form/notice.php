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

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $canpickadditionaleditor = !empty($this->_customdata['canpickadditionaleditor']);
        $isedit = !empty($this->_customdata['isedit']);
        $currentvisible = $this->_customdata['currentvisible'] ?? null;

        // Start of general group.

        $mform->addElement('header', 'general', get_string('basicinformationgroup', 'block_notices'));

        $visibilitystringkey = [
            notices::NOTICE_HIDDEN => 'visibility_hidden',
            notices::NOTICE_VISIBLE => 'visibility_visible',
            notices::NOTICE_IN_PREVIEW => 'visibility_preview',
        ];
        if ($isedit && isset($visibilitystringkey[$currentvisible])) {
            $visibilitydisplay = get_string($visibilitystringkey[$currentvisible], 'block_notices');
        } else {
            $visibilitydisplay = get_string('visibility_preview', 'block_notices');
        }
        $mform->addElement(
            'static',
            'visible_label',
            get_string('visible', 'block_notices'),
            $visibilitydisplay
        );

        $mform->addElement('checkbox', 'staffonly', get_string('staffonly', 'block_notices'));

        $mform->addElement('text', 'title', get_string('title', 'block_notices'), ['size' => 64]);
        $mform->setDefault('title', '');
        $mform->setType('title', PARAM_TEXT);
        $mform->addHelpButton('title', 'title', 'block_notices');
        $mform->addRule('title', null, 'required', null, 'client');

        $editoroptions = ['maxfiles' => 0, 'noclean' => true, 'context' => null];
        $mform->addElement('editor', 'content', get_string('content', 'block_notices'), null, $editoroptions);
        $mform->setDefault('content', ['text' => '']);
        $mform->addRule('content', null, 'required', null, 'client');
        $mform->setType('content', PARAM_RAW); // XSS is prevented when printing the block contents and serving files.

        // Arguably a bit of a hack to get the help text to display in my preferred place.
        $mform->addElement('html', html_writer::div(
            '<i class="icon fa fa-info-circle " aria-hidden="true"></i>' . get_string('content_help_additional', 'block_notices'),
            'alert alert-info help_text'
        ));
        $mform->addElement('html', html_writer::tag('hr', ''));

        $mform->addElement('text', 'moreinformationurl', get_string('moreinformationurl', 'block_notices'), ['size' => 128]);
        $mform->setDefault('moreinformationurl', '');
        $mform->setType('moreinformationurl', PARAM_TEXT);
        $mform->addHelpButton('moreinformationurl', 'moreinformationurl', 'block_notices');
        // End of general group. Don't need to close this group as it is automatically closed by the next group.

        // Start of owner group.

        $mform->addElement('html', html_writer::tag('h3', get_string('ownergroup', 'block_notices')));

        $mform->addElement('html', html_writer::div(
            get_string('ownergroupdescription', 'block_notices'),
            'alert alert-warning help_text'
        ));

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

        // End of owner group.

        if ($canpickadditionaleditor) {
            $mform->addElement('html', html_writer::div(
                get_string('additionaleditordescription', 'block_notices'),
                'alert alert-info help_text'
            ));

            $emptylabel = $isedit
                ? get_string('additionaleditorid_none', 'block_notices')
                : get_string('additionaleditorid_unset', 'block_notices');

            // AJAX-backed user picker (core_user/form_user_selector → core_user_search_identity
            // web service). The previous implementation loaded every active user into PHP
            // memory, which doesn't scale past a few thousand users.
            $attributes = [
                'multiple' => false,
                'ajax' => 'core_user/form_user_selector',
                'noselectionstring' => $emptylabel,
                'valuehtmlcallback' => function ($userid) {
                    global $OUTPUT;

                    if (empty($userid)) {
                        return false;
                    }
                    $context = \context_system::instance();
                    $fields = \core_user\fields::for_name()->with_identity($context, false);
                    $record = \core_user::get_user(
                        $userid,
                        'id ' . $fields->get_sql()->selects,
                        IGNORE_MISSING
                    );
                    if (!$record) {
                        return false;
                    }

                    $user = (object)[
                        'id' => $record->id,
                        'fullname' => fullname($record, has_capability('moodle/site:viewfullnames', $context)),
                        'extrafields' => [],
                    ];
                    foreach ($fields->get_required_fields([\core_user\fields::PURPOSE_IDENTITY]) as $extrafield) {
                        $user->extrafields[] = (object)[
                            'name' => $extrafield,
                            'value' => s($record->$extrafield),
                        ];
                    }
                    return $OUTPUT->render_from_template('core_user/form_user_selector_suggestion', $user);
                },
            ];
            $mform->addElement(
                'autocomplete',
                'additionaleditorid',
                get_string('additionaleditorid', 'block_notices'),
                [],
                $attributes
            );
            $mform->setType('additionaleditorid', PARAM_INT);
            $mform->addHelpButton('additionaleditorid', 'additionaleditorid', 'block_notices');
        } else {
            $mform->addElement('hidden', 'additionaleditorid', 0);
            $mform->setType('additionaleditorid', PARAM_INT);
        }

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
