[![Moodle Plugin CI](https://github.com/andrewrowatt-masseyuni/moodle-block_notices/actions/workflows/moodle-ci.yml/badge.svg)](https://github.com/andrewrowatt-masseyuni/moodle-block_notices/actions/workflows/moodle-ci.yml)

# Notices

Provides a mechanism for managing notices, events, and items of interest and displaying them in a carousel-style presentation.

This block is used to display one or more semi-structured notices, events, and items of interest using a carousel-style presentation. Notices have a title, content, and a link to more information. Notice visibility can be toggled.

Additionally, administrative information can also be included with the notice. This information is not displayed but provides information for notice managers to maintain the list of notices.

A block is used as the display mechanism for notices and as the path for teachers or administrators to manage notices.

\*\* Note this has primarily been designed and tested as a Dashboard notice block \*\*

While a block is used to display notices, the actual notices are *course-specific*. That is, if you added a second block to the course, the existing set of notices will be displayed. This includes the Dashboard and Frontpage, which are both technically course 1.

This plugin was based on an H5P widget:

<https://github.com/andrewrowatt-masseyuni/H5P.MUNoticesCarousel>

## Key features

-   Carousel display of one or more notices using Swiper, with previous/next, pagination, and play/pause controls.
-   Notice content edited with the Quill WYSIWYG editor to gracefully restrict the complexity of formatting options.
-   Optional decorative notice image (hidden from screen readers) which is displayed at a fixed location on the notice.
-   Three visibility states: **Hidden**, **Preview** (visible to managers only), and **Visible**.
-   Staff-only notices are restricted to users with a non-student or non-“ST” account.
-   Per-course **Exclusive** notice styles: **Important** (red) and **Information** (blue), each with distinct presentation. Only the exclusive notice will display, i.e., no carousel functionality.
-   Auto-hide scheduling — notices automatically stop displaying on or after their configured close date.
-   Read tracking — records when each user last viewed each notice version and surfaces read counts in management. UNread 
-   **Promote** action to bump a notice to the top of the carousel.
-   Internal-only administrative notes for notice managers.
-   Optional per-notice additional editor user.
-   Capability-based permissions and a dedicated **Notices manager** role archetype for site-wide notice administration.

## Screenshots

### Add notice examples

![Add notice](<docs/images/SS01 - Add Notice - 2026-05-26_09-23-06.png>)

![Add notice](<docs/images/SS01 - Add Notice - 2026-05-26_09-26-30.png>)

![Add notice](<docs/images/SS02 - Add Notice - 2026-05-26_09-23-50.png>)

### Manage notices example

![Manage notices](<docs/images/SS02 - Manage notices - 2026-05-26_09-24-42.png>)

### Student view
![Student view ](<docs/images/SS03 - Student view - 2026-05-26_09-27-19.png>)

### Student view - exclusive notice examples
![Student view](<docs/images/SS03 - Student view 2026-05-26_11-40-18.png>)

![Student view](<docs/images/SS03 - Student view 2026-05-26_11-42-36.png>)


## Snap theme

The notices block UI has been optimised for the [Snap theme](https://moodle.org/plugins/theme_snap). It remains functional and visually acceptable under Boost and other standard Moodle themes, but spacing, typography, and colour choices have been tuned with Snap in mind.

## Third-party libraries

This plugin bundles two third-party libraries, declared in <thirdpartylibs.xml>:

-   [Quill](https://quilljs.com/) **2.0.3** (BSD 3-clause) — rich-text editor used by the add/edit notice form. Quill was chosen deliberately to tightly constrain the formatting options available to notice authors. At the time of writing, this type of restriction was not possible using Moodle's built-in TinyMCE editor. Located at <thirdparty/quill/>.
-   [Swiper](https://swiperjs.com/) **11.0.5** (MIT) — touch-friendly carousel that powers the notices block display. Located at <thirdparty/swiper/>.

Both libraries are vendored in the plugin and managed using Moodle's standard third-party library convention.

## Installing via uploaded ZIP file

1.  Log in to your Moodle site as an admin and go to *Site administration \> Plugins \> Install plugins*.
2.  Upload the ZIP file with the plugin code. You should only be prompted to add extra details if your plugin type is not automatically detected.
3.  Check the plugin validation report and finish the installation.

## Installing manually

The plugin can be also installed by putting the contents of this directory to

```
{your/moodle/dirroot}/blocks/notices
```

Afterwards, log in to your Moodle site as an admin and go to *Site administration \> Notifications* to complete the installation.

Alternatively, you can run

```
$ php admin/cli/upgrade.php
```

to complete the installation from the command line.

## License

2025 Andrew Rowatt [A.J.Rowatt@massey.ac.nz](mailto:A.J.Rowatt@massey.ac.nz)

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
