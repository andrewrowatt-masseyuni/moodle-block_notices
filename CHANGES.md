# Changes

Dates are when the release number was bumped in `version.php`. Issue numbers refer to
[GitHub issues](https://github.com/andrewrowatt-masseyuni/moodle-block_notices/issues).

## 1.4.3 (2026-10-11)

-   Per-user display order: unread notices first, then read notices from least to most recently read (#26).

## 1.4.2 (2026-08-10)

-   Scoped block CSS so it no longer affects other pages such as the gradebook (#27).

## 1.4.1 (2026-05-26)

-   Security review fixes, including privacy provider updates.
-   Removed the unused custom CSS setting.
-   Catalyst code review updates; Swiper moved to `thirdparty/swiper`.

## 1.4.0 (2026-05-17)

-   Quill rich-text editor for notice content (#17).
-   Optional decorative image per notice (#16).
-   Auto-hide notices on or after their close date (#3).
-   Streamlined add/edit form and other UI updates (#21, #23).

## 1.3.1 (2026-05-16)

-   Read tracking, with unread notices given higher priority (#11).
-   **Promote** action to move a notice to the top of the carousel (#14).
-   Manage notices edit form converted to a dynamic form (#15).
-   Removed the inplace-editable title (#13).

## 1.2.0 (2026-05-16)

-   Update description generated automatically in real time (#1, #7).
-   Only new notices start in Preview status (#8).
-   Manage notices UI review (#9).
-   Fixed the additional editor defaulting to the current user.

## 1.1.0 (2026-05-15)

-   Optional per-notice additional editor (a named Moodle user who can edit the notice).
-   Exclusive notices with Important and Information styles (#5, #6).

## 1.0.13 (2026-05-15)

-   Inline editing of notice titles and update descriptions.

## 1.0.12 (2025-06-22)

-   Moodle 4.5 support.
-   Fade added to soften vertically clipped text.
-   Empty title enabled by default.
-   Fixed duplicate styles.

## 1.0.11 (2025-06-20)

-   Staff-only notices (#2).
-   Notice title can be left empty.
-   Play/pause carousel control.
-   Improved display when there are zero or one notices.

## 1.0.10 (2025-05-31)

-   Privacy API provider.
-   Foreign keys for user fields.
-   GitHub Actions CI workflow.
-   Fixed a sort order UI bug.

## 1.0.0 – 1.0.9 (2025-01-02 to 2025-01-07)

-   Initial release: carousel display of notices using Swiper, with add, edit and manage notices.
-   Notices are keyed by course rather than block instance, so every Dashboard block shows the same set.
-   Language strings, unit tests and Behat tests.
-   Basic event logging.
