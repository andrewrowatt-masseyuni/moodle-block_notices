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
 * Opens the Add / Edit notice modal on the Manage notices page.
 *
 * @module     block_notices/manage
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalForm from 'core_form/modalform';
import ModalEvents from 'core/modal_events';
import {getString} from 'core/str';
import * as NoticeEditor from 'block_notices/notice_editor';

const FORM_CLASS = 'block_notices\\form\\notice';
const SELECTOR_ADD = '[data-action="notice-add"]';
const SELECTOR_EDIT = '[data-action="notice-edit"]';

/**
 * Open a notice modal form. On save, reload the manage page so the new/edited
 * notice appears in the right group (server-side grouping/sortorder).
 *
 * @param {HTMLElement} trigger The element that was clicked.
 * @param {number} courseid The course the manage page is scoped to.
 * @param {number} noticeid 0 for add; existing id for edit.
 * @param {Promise<string>} titlePromise Modal title string promise.
 */
const openModal = (trigger, courseid, noticeid, titlePromise) => {
    const modalForm = new ModalForm({
        formClass: FORM_CLASS,
        modalConfig: {
            title: titlePromise,
            large: true,
            removeOnClose: true,
        },
        args: {
            noticeid: noticeid,
            courseid: courseid,
        },
        saveButtonText: getString('save', 'core'),
        returnFocus: trigger,
    });

    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (event) => {
        if (event.detail && event.detail.url) {
            window.location.assign(event.detail.url);
        } else {
            window.location.reload();
        }
    });

    // Install a MutationObserver on the modal body so we (re-)initialise Quill whenever
    // the form HTML appears or is re-rendered. ModalForm fires LOADED synchronously
    // before the form body promise resolves and emits no event for the no-submit /
    // server-validation re-renders, so a one-shot LOADED handler isn't enough.
    modalForm.addEventListener(modalForm.events.LOADED, () => {
        const modalRoot = modalForm.modal.getRoot();
        const modalBody = modalForm.modal.getBody()[0];
        const stop = NoticeEditor.observe(modalBody);
        modalRoot.on(ModalEvents.hidden, () => stop());
    });

    modalForm.show();
};

/**
 * Wire the Add / Edit triggers on the Manage notices page.
 *
 * @param {number} courseid The course the manage page is scoped to.
 */
export const init = (courseid) => {
    document.addEventListener('click', (event) => {
        const addTrigger = event.target.closest(SELECTOR_ADD);
        if (addTrigger) {
            event.preventDefault();
            openModal(addTrigger, courseid, 0, getString('addnotice', 'block_notices'));
            return;
        }

        const editTrigger = event.target.closest(SELECTOR_EDIT);
        if (editTrigger) {
            event.preventDefault();
            const noticeid = parseInt(editTrigger.dataset.noticeid, 10) || 0;
            openModal(editTrigger, courseid, noticeid, getString('edit', 'core'));
        }
    });
};
