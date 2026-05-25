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
     * Filemanager options for the optional notice image.
     *
     * @return array
     */
    private static function image_filemanager_options(): array {
        global $CFG;
        return [
            'subdirs' => 0,
            'maxfiles' => 1,
            'maxbytes' => $CFG->maxbytes,
            'accepted_types' => ['web_image'],
            'areamaxbytes' => FILE_AREA_MAX_BYTES_UNLIMITED,
        ];
    }

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

        // Wrapper for CSS scoping. The form is a dynamic_form rendered inside a modal
        // on the manage page, so we can't target it by page-id.
        $mform->addElement('html', '<div class="block_notices-edit-form">');

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

        $mform->addElement('html', '<div class="block_notices-row block_notices-row--visibility">');
        // Render Visibility as an inline "Label: Value" pair rather than the standard
        // two-column static element so the short value sits next to its label.
        $mform->addElement('html', html_writer::div(
            html_writer::tag(
                'span',
                get_string('visibility', 'block_notices'),
                ['class' => 'block_notices-visibility__label']
            ) . ' ' . html_writer::tag(
                'span',
                s($visibilitydisplay),
                ['class' => 'block_notices-visibility__value']
            ),
            'block_notices-visibility'
        ));

        $mform->addElement('checkbox', 'staffonly', get_string('staffonly', 'block_notices'));
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="block_notices-row block_notices-row--owner block_notices--container">');

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

        $mform->addElement('html', '</div>');

        // Content.
        $mform->addElement('html', '<div class="block_notices--container">');
        $mform->addElement('text', 'title', get_string('title', 'block_notices'), ['style' => 'width: 100%;', 'maxlength' => 64]);
        $mform->addRule('title', get_string('maximumchars', '', 64), 'maxlength', 64, 'client');
        $mform->setDefault('title', '');
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');

        // The notice content is captured by a Quill editor (initialised by block_notices/notice_editor
        // when the modal form renders). The underlying textarea remains in the DOM (hidden by JS) so
        // mform serialises its value at submit time; the JS mirror keeps that value in sync with the
        // Quill editor's HTML on every text change.
        // Label is visually hidden via CSS; the placeholder inside Quill (passed
        // through to JS via data-quill-placeholder) serves as the visible label.
        $mform->addElement('textarea', 'content', get_string('content', 'block_notices'), [
            'rows' => 10,
            'cols' => 80,
            'class' => 'block_notices-quill-source',
            'data-block-notices-quill' => '1',
            'data-quill-placeholder' => get_string('content_help_additional', 'block_notices'),
        ]);
        $mform->setDefault('content', '');
        $mform->setType('content', PARAM_RAW); // XSS is prevented when printing the block contents and serving files.

        $mform->addElement('html', '</div>');

        // Optional or admin / non-content details.
        $mform->addElement('html', '<div class="block_notices--container optional-details">');

        // Use image.
        $mform->addElement('html', '<div class="inline-help-text d-flex align-items-top">');
        $mform->addElement(
            'advcheckbox',
            'useimage',
            get_string('useimage', 'block_notices'),
            null,
            ['class' => 'block_notices-useimage']
        );
        $mform->addElement('html', html_writer::div(
            '<i class="fa-regular fa-circle-question" style="line-height: 1.5;"></i><div>' . get_string(
                'image_help',
                'block_notices'
            ) . '</div>',
            'help_text d-flex',
            ['style' => 'gap: 0.25rem;']
        ));
        $mform->addElement('html', '</div>');

        $mform->addElement(
            'filemanager',
            'image_filemanager',
            'Image',
            ['class' => 'block_notices-image-filemanager'],
            self::image_filemanager_options()
        );
        $mform->hideIf('image_filemanager', 'useimage', 'notchecked');

        // More information.
        $mform->addElement('html', '<div class="inline-help-text d-flex align-items-center">');
        $mform->addElement(
            'advcheckbox',
            'moreinformationurl_enable',
            get_string('moreinformationurl', 'block_notices'),
            null,
            ['class' => 'block_notices-usemoreinformationurl']
        );

        $mform->addElement('html', html_writer::div(
            '<i class="fa-regular fa-circle-question"></i> ' . get_string(
                'moreinformationurl_help',
                'block_notices',
                get_string(
                    'moreinformation',
                    'block_notices'
                )
            ),
            'help_text'
        ));
        $mform->addElement('html', '</div>');

        $mform->addElement('text', 'moreinformationurl', '', ['size' => 128]);
        $mform->setDefault('moreinformationurl', '');
        $mform->setType('moreinformationurl', PARAM_TEXT);
        $mform->hideIf('moreinformationurl', 'moreinformationurl_enable', 'notchecked');

        // Optional close date. When set and in the past, the auto-close scheduled task
        // hides the notice, clears any exclusive flag and emails a responsible person.
        // The on/off toggle is a plain advcheckbox (rather than date_time_selector's
        // built-in "Enable" optional flag) so the checkbox can carry a meaningful label.

        $mform->addElement('html', '<div class="inline-help-text d-flex align-items-center">');

        $mform->addElement(
            'advcheckbox',
            'closedate_enable',
            get_string('closedate', 'block_notices'),
            null,
            ['class' => 'block_notices-useclosedate']
        );

        // Inline help text shown only when closedate is disabled.
        $helpstatic = $mform->addElement('static', 'closedate_help_static', '', html_writer::div(
            '<i class="fa-regular fa-circle-question"></i> ' . get_string('closedate_help', 'block_notices'),
            'help_text'
        ));
        $helpstaticattributes = $helpstatic->getAttributes();
        $helpstaticattributes['class'] = 'hide-first-element';
        $helpstatic->setAttributes($helpstaticattributes);
        $mform->hideIf('closedate_help_static', 'closedate_enable', 'checked');

        $mform->addElement(
            'date_time_selector',
            'closedate',
            ''
        );
        $mform->hideIf('closedate', 'closedate_enable', 'notchecked');

        $mform->addElement('html', '</div>');

        if ($canpickadditionaleditor) {
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

            $mform->addElement('html', '<div class="inline-help-text d-flex align-items-center">');
            $mform->addElement(
                'advcheckbox',
                'additionaleditor_enable',
                get_string('additionaleditorid', 'block_notices'),
                null,
                ['class' => 'block_notices-useadditionaleditor']
            );

            $mform->addElement('html', html_writer::div(
                '<i class="fa-regular fa-circle-question"></i> ' . get_string('additionaleditordescription', 'block_notices'),
                'help_text'
            ));
            $mform->addElement('html', '</div>');

            $mform->addElement('html', '<div class="block_notices-hide-label">');
            $mform->addElement(
                'autocomplete',
                'additionaleditorid',
                get_string('additionaleditorselector', 'block_notices'),
                [],
                $attributes
            );
            $mform->addElement('html', '</div>');

            $mform->setType('additionaleditorid', PARAM_INT);
            $mform->hideIf('additionaleditorid', 'additionaleditor_enable', 'notchecked');
        } else {
            $mform->addElement('hidden', 'additionaleditorid', 0);
            $mform->setType('additionaleditorid', PARAM_INT);
        }

        $mform->addElement('html', '<div class="inline-help-text d-flex align-items-center">');
        $mform->addElement(
            'advcheckbox',
            'notes_enable',
            get_string('notes', 'block_notices'),
            null,
            ['class' => 'block_notices-usenotes']
        );
        $mform->addElement('html', html_writer::div(
            '<i class="fa-regular fa-circle-question"></i> ' . get_string('notes_help', 'block_notices'),
            'help_text'
        ));
        $mform->addElement('html', '</div>');

        $mform->addElement('text', 'notes', '', ['size' => 128]);
        $mform->setDefault('notes', '');
        $mform->setType('notes', PARAM_TEXT);
        $mform->hideIf('notes', 'notes_enable', 'notchecked');

        $mform->addElement('html', '</div>');

        $mform->addElement('html', '</div>');
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
        $options = self::image_filemanager_options();

        if ($noticeid > 0) {
            $notice = notices::get_notice($noticeid);
            // Pass the raw HTML content straight to the textarea element; the JS Quill
            // initialiser seeds itself from textarea.value when the modal renders.
            $notice['content'] = $notice['content'] ?? '';
            $notice['noticeid'] = $noticeid;
            $notice['courseid'] = $courseid;
            // Default the "Use image" checkbox to checked when an image already exists.
            $noticecontext = notices::get_notice_context((int)$courseid);
            $existingfiles = get_file_storage()->get_area_files(
                $noticecontext->id,
                'block_notices',
                'image',
                $noticeid,
                '',
                false
            );
            $notice['useimage'] = !empty($existingfiles) ? 1 : 0;
            $notice['closedate_enable'] = !empty($notice['closedate']) ? 1 : 0;
            $notice['additionaleditor_enable'] = !empty($notice['additionaleditorid']) ? 1 : 0;
            $notice['moreinformationurl_enable'] = !empty($notice['moreinformationurl']) ? 1 : 0;
            $notice['notes_enable'] = !empty($notice['notes']) ? 1 : 0;
            $data = (object)$notice;
            file_prepare_standard_filemanager(
                $data,
                'image',
                $options,
                $noticecontext,
                'block_notices',
                'image',
                $noticeid
            );
            $this->set_data($data);
            return;
        }

        $data = (object)[
            'id' => 0,
            'noticeid' => 0,
            'courseid' => $courseid,
            'content' => '',
            'useimage' => 0,
        ];
        // New notice: stage an empty draft area so the filemanager renders correctly.
        file_prepare_standard_filemanager($data, 'image', $options, null, null, null, null);
        $this->set_data($data);
    }

    /**
     * Server-side validation. Rejects whitespace-only Quill content (Quill's "empty"
     * representation is "<p><br></p>", which the standard required rule treats as filled).
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (trim((string)($data['title'] ?? '')) === '') {
            $errors['title'] = get_string('required');
        }
        $contenttext = trim(html_to_text((string)($data['content'] ?? ''), 0, false));
        if ($contenttext === '') {
            $errors['content'] = get_string('required');
        }
        if (trim((string)($data['owner'] ?? '')) === '') {
            $errors['owner'] = get_string('required');
        }
        if (trim((string)($data['owneremail'] ?? '')) === '') {
            $errors['owneremail'] = get_string('required');
        }
        return $errors;
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
            'content' => $formdata->content,
            'contentformat' => FORMAT_HTML,
            'moreinformationurl' => !empty($formdata->moreinformationurl_enable)
                ? $formdata->moreinformationurl
                : '',
            'owner' => $formdata->owner,
            'owneremail' => $formdata->owneremail,
            'notes' => !empty($formdata->notes_enable)
                ? $formdata->notes
                : '',
            // Field closedate_enable gates whether the date is persisted; the non-optional
            // date_time_selector always submits a timestamp regardless of the checkbox.
            'closedate' => !empty($formdata->closedate_enable) ? (int)$formdata->closedate : null,
        ];

        $noticeid = (int)($formdata->noticeid ?? 0);
        if ($noticeid > 0) {
            $data['id'] = $noticeid;
            // Only manage-all users can reassign the additional editor; an empty selection
            // clears it (so only Notices managers can edit thereafter).
            if ($canpickadditionaleditor) {
                $data['additionaleditorid'] = (!empty($formdata->additionaleditor_enable) && !empty($formdata->additionaleditorid))
                    ? (int)$formdata->additionaleditorid
                    : null;
            }
            notices::update_notice($data);
        } else {
            $data['additionaleditorid'] = (!empty($formdata->additionaleditor_enable) && !empty($formdata->additionaleditorid))
                ? (int)$formdata->additionaleditorid
                : null;
            $noticeid = notices::add_notice($courseid, $data);
        }

        $noticecontext = notices::get_notice_context($courseid);
        if (!empty($formdata->useimage)) {
            // Move any uploaded image from the draft area into the notice's permanent filearea.
            file_postupdate_standard_filemanager(
                $formdata,
                'image',
                self::image_filemanager_options(),
                $noticecontext,
                'block_notices',
                'image',
                $noticeid
            );
        } else {
            // Unticked → drop any previously stored image for this notice.
            get_file_storage()->delete_area_files(
                $noticecontext->id,
                'block_notices',
                'image',
                $noticeid
            );
        }

        $url = new moodle_url('/blocks/notices/manage.php', ['courseid' => $courseid]);
        return [
            'result' => true,
            'url' => $url->out(false),
        ];
    }
}
