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
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Class addnotice
 *
 * @package    block_notices
 * @copyright  2025 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class addnotice extends \moodleform {
    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('selectyesno', 'visible',get_string('visible', 'block_notices'));
        $mform->setDefault('visible', 0);
        $mform->addHelpButton('visible', 'visible', 'block_notices');

        $mform->addElement('text', 'title',get_string('title', 'block_notices'));
        $mform->setDefault('title', '');
        $mform->setType('title', PARAM_TEXT);
        $mform->addHelpButton('title', 'title', 'block_notices');

        $editoroptions = ['maxfiles' => 0, 'noclean'=>true, 'context'=>null,];
        $mform->addElement('editor', 'content', get_string('content', 'block_notices'), null, $editoroptions);
        $mform->addRule('content', null, 'required', null, 'client');
        $mform->setType('content', PARAM_RAW); // XSS is prevented when printing the block contents and serving files

        $submitlabel = get_string('save');
        $mform->addElement('submit', 'submitmessage', $submitlabel);
    }
}
