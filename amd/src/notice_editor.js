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
 * Quill-based editor for the notice content field on the dynamic add/edit form.
 *
 * The Quill library is bundled under blocks/notices/thirdparty/quill/ and lazy-loaded
 * the first time the modal form opens. The form keeps a hidden textarea so mform's
 * `form.serialize()` carries the HTML at submit time; the Quill `text-change` event
 * mirrors `editor.root.innerHTML` back into that textarea on every keystroke.
 *
 * @module     block_notices/notice_editor
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const TEXTAREA_SELECTOR = 'textarea[data-block-notices-quill]';
const READY_ATTR = 'data-block-notices-quill-ready';
const QUILL_BASE = M.cfg.wwwroot + '/blocks/notices/thirdparty/quill';
const QUILL_CSS = QUILL_BASE + '/quill.snow.css';
const QUILL_JS = QUILL_BASE + '/quill.js';

let quillPromise = null;

/**
 * Lazy-load Quill from the bundled thirdparty directory. Quill's UMD bundle prefers
 * AMD format when `window.define` is present, which would never set `window.Quill`;
 * we temporarily clear `define` while the script tag executes so the browser-global
 * branch runs.
 *
 * @returns {Promise<Function>} Resolves to the global `Quill` constructor.
 */
const loadQuill = () => {
    if (window.Quill) {
        return Promise.resolve(window.Quill);
    }
    if (quillPromise) {
        return quillPromise;
    }
    if (!document.querySelector('link[data-block-notices-quill-css]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = QUILL_CSS;
        link.dataset.blockNoticesQuillCss = '1';
        document.head.appendChild(link);
    }
    quillPromise = new Promise((resolve, reject) => {
        const savedDefine = window.define;
        window.define = undefined;
        const restore = () => {
            window.define = savedDefine;
        };
        const script = document.createElement('script');
        script.src = QUILL_JS;
        script.async = true;
        script.onload = () => {
            restore();
            resolve(window.Quill);
        };
        script.onerror = () => {
            restore();
            reject(new Error('Failed to load Quill'));
        };
        document.head.appendChild(script);
    });
    return quillPromise;
};

/**
 * Mirror the Quill HTML into the underlying textarea, dispatching `input` so any
 * change-checkers downstream pick up the update. Writes an empty string when the
 * editor is genuinely empty so the mform required rule treats it as missing.
 *
 * @param {Object} editor Quill instance.
 * @param {HTMLTextAreaElement} textarea The source textarea.
 */
const syncToTextarea = (editor, textarea) => {
    const empty = editor.getText().trim() === '';
    textarea.value = empty ? '' : editor.root.innerHTML;
    textarea.dispatchEvent(new Event('input', {bubbles: true}));
};

/**
 * Initialise Quill against a single textarea: hide the textarea, insert a sibling
 * mount point, construct the Quill editor with our restricted toolbar, seed it from
 * the textarea's current value, and wire `text-change` to mirror back.
 *
 * @param {HTMLTextAreaElement} textarea
 * @returns {Promise<Object>} Resolves to the Quill instance.
 */
const init = async(textarea) => {
    const Quill = await loadQuill();
    const wrapper = document.createElement('div');
    wrapper.className = 'block_notices-quill-editor';
    textarea.parentNode.insertBefore(wrapper, textarea.nextSibling);
    textarea.style.display = 'none';

    const editor = new Quill(wrapper, {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{align: ''}, {align: 'center'}, {align: 'right'}],
                ['link'],
                ['clean'],
            ],
        },
        placeholder: '',
    });

    const initial = (textarea.value || '').trim();
    if (initial !== '') {
        editor.root.innerHTML = initial;
    }
    syncToTextarea(editor, textarea);
    editor.on('text-change', () => syncToTextarea(editor, textarea));
    return editor;
};

/**
 * Watch `root` for the notice content textarea to appear (including after dynamic-form
 * re-renders from server validation errors or no-submit buttons), and initialise Quill
 * each time. The ModalForm wholesale-replaces the modal body via `setBodyContent` with
 * no event, so MutationObserver is the only reliable hook for those re-renders.
 *
 * @param {HTMLElement} root
 * @returns {Function} A `stop()` callback that disconnects the observer.
 */
export const observe = (root) => {
    const scan = () => {
        const textarea = root.querySelector(TEXTAREA_SELECTOR + ':not([' + READY_ATTR + '])');
        if (!textarea) {
            return;
        }
        textarea.setAttribute(READY_ATTR, '1');
        init(textarea).catch(() => {
            // Quill failed to load; clear the ready flag so a subsequent scan can retry,
            // and leave the textarea visible so the user can still type plain text.
            textarea.removeAttribute(READY_ATTR);
            textarea.style.display = '';
        });
    };

    const observer = new MutationObserver(scan);
    observer.observe(root, {childList: true, subtree: true});
    scan();
    return () => observer.disconnect();
};
