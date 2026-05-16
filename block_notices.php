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

use block_notices\notices;
use block_notices\util;

/**
 * Block notices is defined here.
 *
 * @package     block_notices
 * @copyright   2024 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_notices extends block_base {
    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_notices');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $CFG, $DB, $OUTPUT, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = [];
        $this->content->icons = [];
        $this->content->footer = '';

        $canmanageall = has_capability('block/notices:manageallnotices', context_system::instance());
        $canmanage = $canmanageall || notices::user_can_manage_any();
        $managelabel = $canmanageall
            ? get_string('managenotices', 'block_notices')
            : get_string('managemynotices', 'block_notices');
        $courseid = $this->page->course->id;

        $this->page->requires->js('/blocks/notices/swiper/swiper-bundle.min.js', false);
        $this->page->requires->css('/blocks/notices/swiper/swiper-bundle.min.css');
        $this->page->requires->js_call_amd('block_notices/block_notices', 'init', []);

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            $notices = notices::get_notices(
                $courseid,
                $canmanageall,
                util::is_staff()
            );

            $formatcontext = \context_course::instance($courseid);

            $data = [
                'canmanage' => $canmanage,
                'managelabel' => $managelabel,
                'courseid' => $courseid,
                'wwwroot' => $CFG->wwwroot,
                'singlenotice' => count($notices) === 1,
                'oneormorenotices' => count($notices) >= 1,
                'noticecount' => count($notices),
                'notices' => [],
            ];

            foreach ($notices as $noticeobject) {
                $noticearray = (array)$noticeobject;

                // Run filters on the title (multilang etc.) and run the full text-cleaning
                // pipeline on the content. The manageallnotices capability declares RISK_XSS,
                // so the saved HTML is trusted (noclean) — but filters and link/image
                // processing must still apply.
                $noticearray['title'] = format_string(
                    $noticeobject->title,
                    true,
                    ['context' => $formatcontext]
                );
                $noticearray['content'] = format_text(
                    $noticeobject->content,
                    $noticeobject->contentformat,
                    ['context' => $formatcontext, 'noclean' => true]
                );

                if ($noticearray['moreinformationurl']) {
                    $noticearray += [
                        'moreinformationlink' => $noticearray['moreinformationurl'],
                        'moreinformationtext' => get_string('moreinformation', 'block_notices'),
                    ];
                } else {
                    $noticearray += [
                        'moreinformationlink' => 'mailto:' . $noticearray['owneremail'],
                        'moreinformationtext' => $noticearray['owner'],
                    ];
                }

                $data['notices'][] = $noticearray;
            }

            $text = $OUTPUT->render_from_template('block_notices/notices', $data);

            $this->content->text = $text;
        }

        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {
        if (isset($this->config->title)) {
            $this->title = $this->config->title;
        } else {
            $this->title = '';
        }
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config() {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return [
            'my' => true,
            'site-index' => true,
            'course-view' => true,
          ];
    }

    /**
     * Add custom html attributes to aid with theming and styling.
     *
     * @return array
     */
    public function html_attributes() {
        global $CFG;

        $attributes = parent::html_attributes();

        // If this block is going to render the exclusive notice for its course, tag it for styling.
        if (!empty($this->page->course->id)) {
            $exclusive = notices::get_active_exclusive_notice((int)$this->page->course->id);
            if ($exclusive !== null) {
                $suffix = (int)$exclusive->exclusive === notices::NOTICE_EXCLUSIVE_INFORMATION
                    ? 'information' : 'important';
                $attributes['class'] .= ' block_notices_exclusive block_notices_exclusive_' . $suffix;
            }
        }

        return $attributes;
    }
}
