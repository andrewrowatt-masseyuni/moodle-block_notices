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
 * Behat step definitions for block_notices.
 *
 * @package    block_notices
 * @category   test
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Behat steps for block_notices.
 *
 * @package    block_notices
 * @category   test
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_block_notices extends behat_base {
    /**
     * Set the Quill-backed notice content editor to the given plain-text value.
     *
     * Wraps the value in a single <p> (matching what Quill emits for a paragraph),
     * mirrors it into the hidden textarea, and dispatches an input event so the
     * dynamic form picks up the change. Waits for the Quill editor to render first.
     *
     * @When /^I set the notice Quill editor to "(?P<value_string>(?:[^"]|\\")*)"$/
     * @param string $value
     */
    public function i_set_the_notice_quill_editor_to($value) {
        // Wait for the Quill editor to be present (it's lazy-loaded on modal render).
        $this->ensure_element_exists('.block_notices-quill-editor .ql-editor', 'css_element');

        $jsvalue = json_encode((string)$value);
        $script = <<<JS
(function() {
    var editor = document.querySelector('.block_notices-quill-editor .ql-editor');
    var textarea = document.querySelector('textarea[data-block-notices-quill]');
    if (!editor || !textarea) {
        throw new Error('Notice Quill editor not found');
    }
    editor.innerHTML = '<p>' + {$jsvalue} + '</p>';
    textarea.value = editor.innerHTML;
    textarea.dispatchEvent(new Event('input', {bubbles: true}));
})();
JS;
        $this->execute_script($script);
    }
}
