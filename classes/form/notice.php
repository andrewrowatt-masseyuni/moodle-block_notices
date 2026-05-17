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
use context;
use context_course;
use context_system;
use core_form\dynamic_form;
use html_writer;
use moodle_url;

/**
 * Add / edit a notice via a modal dynamic form.
 *
 * @package    block_notices
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notice extends dynamic_form {
    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form;

        $noticeid = $this->optional_param('noticeid', 0, PARAM_INT);
        $courseid = $this->optional_param('courseid', 0, PARAM_INT);
        $isedit = $noticeid > 0;
        // Only manage-all users can (re)assign the additional editor. Manage-own users
        // get a hidden input that preserves the existing value (set via set_data).
        $canpickadditionaleditor = has_capability(
            'block/notices:manageallnotices',
            context_system::instance()
        );

        $mform->addElement('hidden', 'id', $noticeid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'noticeid', $noticeid);
        $mform->setType('noticeid', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        // Start of general group.

        $mform->addElement('header', 'general', get_string('basicinformationgroup', 'block_notices'));

        $visibilitystringkey = [
            notices::NOTICE_HIDDEN => 'visibility_hidden',
            notices::NOTICE_VISIBLE => 'visibility_visible',
            notices::NOTICE_IN_PREVIEW => 'visibility_preview',
        ];
        $currentvisible = null;
        if ($isedit) {
            $existing = notices::get_notice($noticeid);
            if (!empty($existing)) {
                $currentvisible = (int)$existing['visible'];
            }
        }
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

        $editoroptions = ['maxfiles' => 0, 'noclean' => true, 'context' => $this->get_context_for_dynamic_submission()];
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
                    $context = context_system::instance();
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
    }

    /**
     * Returns the context where this form is used.
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        $courseid = $this->optional_param('courseid', 0, PARAM_INT);
        if ($courseid && (int)$courseid !== SITEID) {
            return context_course::instance($courseid);
        }
        return context_system::instance();
    }

    /**
     * Throws a moodle_exception if the current user cannot use this form for the supplied notice/course.
     */
    protected function check_access_for_dynamic_submission(): void {
        $noticeid = $this->optional_param('noticeid', 0, PARAM_INT);
        if ($noticeid > 0) {
            $existing = notices::get_notice($noticeid);
            if (empty($existing) || !notices::user_can_edit($existing)) {
                throw new \moodle_exception('errornopermission', 'block_notices');
            }
        } else if (!notices::user_can_create()) {
            throw new \moodle_exception('errornopermission', 'block_notices');
        }
    }

    /**
     * Returns the page URL used as $PAGE->set_url() when rendering / submitting via AJAX.
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $courseid = $this->optional_param('courseid', 0, PARAM_INT);
        return new moodle_url('/blocks/notices/manage.php', ['courseid' => $courseid]);
    }

    /**
     * Load the existing notice (edit mode) or the courseid (add mode) into the form.
     */
    public function set_data_for_dynamic_submission(): void {
        $noticeid = $this->optional_param('noticeid', 0, PARAM_INT);
        $courseid = $this->optional_param('courseid', 0, PARAM_INT);

        if ($noticeid > 0) {
            $notice = notices::get_notice($noticeid);
            // Editor element expects ['text' => ..., 'format' => ...]; replace the raw
            // content/contentformat columns with that shape and re-assert noticeid/courseid
            // so the hidden fields match the AJAX args (defends against any oddities in
            // the stored row).
            $notice['content'] = [
                'text' => $notice['content'] ?? '',
                'format' => $notice['contentformat'] ?? FORMAT_HTML,
            ];
            $notice['noticeid'] = $noticeid;
            $notice['courseid'] = $courseid;
            $this->set_data((object)$notice);
            return;
        }

        $this->set_data((object)[
            'id' => 0,
            'noticeid' => 0,
            'courseid' => $courseid,
            'content' => ['text' => '', 'format' => FORMAT_HTML],
        ]);
    }

    /**
     * Persist the submitted notice — add or update — and return the URL to redirect to.
     *
     * @return array {result: bool, url: string}
     */
    public function process_dynamic_submission() {
        $formdata = $this->get_data();
        $courseid = (int)$this->optional_param('courseid', 0, PARAM_INT);
        $canpickadditionaleditor = has_capability(
            'block/notices:manageallnotices',
            context_system::instance()
        );

        $data = [
            'staffonly' => !empty($formdata->staffonly),
            'title' => $formdata->title,
            'content' => $formdata->content['text'],
            'contentformat' => $formdata->content['format'],
            'moreinformationurl' => $formdata->moreinformationurl,
            'owner' => $formdata->owner,
            'owneremail' => $formdata->owneremail,
            'notes' => $formdata->notes,
        ];

        $noticeid = (int)($formdata->noticeid ?? 0);
        if ($noticeid > 0) {
            $data['id'] = $noticeid;
            // Only manage-all users can reassign the additional editor; an empty selection
            // clears it (so only Notices managers can edit thereafter).
            if ($canpickadditionaleditor) {
                $data['additionaleditorid'] = !empty($formdata->additionaleditorid)
                    ? (int)$formdata->additionaleditorid
                    : null;
            }
            notices::update_notice($data);
        } else {
            $data['additionaleditorid'] = !empty($formdata->additionaleditorid)
                ? (int)$formdata->additionaleditorid
                : null;
            notices::add_notice($courseid, $data);
        }

        $url = new moodle_url('/blocks/notices/manage.php', ['courseid' => $courseid]);
        return [
            'result' => true,
            'url' => $url->out(false),
        ];
    }
}
