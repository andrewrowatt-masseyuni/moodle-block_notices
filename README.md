# Notices

Provides a mechanism for managing notices, events, and items of interest and displaying them in a carousel-style presentation.

This block is used to display one or more semi-structured notices, events, and items of interest using a carousel-style presentation. Notices have a title, content, and a link to more information. Notice visibility can be toggled.

Additionally, administrative information can also be included with the notice. This information is not displayed but provides information for notice managers to maintain the list of notices.

A block is used as the display mechanism for notices and as the path for teachers or administrators to manage notices. While a block is used to display notices, the actual notices are *course-specific*. That is, if you added a second block to the course, the existing set of notices will be displayed. This includes the Dashboard and Frontpage, which are both technically course 1.

This plugin was based on an H5P widget:

<https://github.com/andrewrowatt-masseyuni/H5P.MUNoticesCarousel>

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
