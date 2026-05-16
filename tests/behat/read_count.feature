@block @block_notices @javascript
Feature: Notice read count
  In order to see how many users have seen each notice
  As an admin
  The manage notices page shows a per-notice read count that goes up as new users view it

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | 98186700 | Sam1      | Student1 | student1@example.com |
      | arowatt  | Andrew1   | Teacher1 | teacher1@example.com |
    And I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Notices" block
    And the following "block_notices > notice" exists:
      | course             | Acceptance test site          |
      | visible            | NOTICE_VISIBLE                |
      | staffonly          | 0                             |
      | title              | Notice1title                  |
      | content            | Notice1content                |
      | moreinformationurl | http://massey.ac.nz           |
      | owner              | notice1owner                  |
      | owneremail         | Notice1owneremail@noreply.com |
      | createdby          | arowatt                       |
      | modifiedby         | arowatt                       |

  Scenario: Read count increments as different users view the notice
    # Baseline: no reads recorded yet.
    When I log in as "admin"
    And I am on site homepage
    And I follow "Manage notices"
    Then I should see "0 read(s)" in the "[data-notice-title=\"Notice1title\"] .block-notices-readcount" "css_element"

    # One user records a read.
    Given the following "block_notices > notice read" exists:
      | user   | 98186700     |
      | notice | Notice1title |
    When I log in as "admin"
    And I am on site homepage
    And I follow "Manage notices"
    Then I should see "1 read(s)" in the "[data-notice-title=\"Notice1title\"] .block-notices-readcount" "css_element"

    # A second, different user records a read.
    Given the following "block_notices > notice read" exists:
      | user   | arowatt      |
      | notice | Notice1title |
    When I log in as "admin"
    And I am on site homepage
    And I follow "Manage notices"
    Then I should see "2 read(s)" in the "[data-notice-title=\"Notice1title\"] .block-notices-readcount" "css_element"

  Scenario: Edited notices show reads since the last update alongside the total
    # NoticeModified has timecreated < timemodified (forced by setting timecreated to epoch)
    # so the manage page treats it as modified.
    Given the following "block_notices > notice" exists:
      | course             | Acceptance test site         |
      | visible            | NOTICE_VISIBLE               |
      | staffonly          | 0                            |
      | title              | NoticeModified               |
      | content            | NoticeModifiedcontent        |
      | moreinformationurl | http://massey.ac.nz          |
      | owner              | mowner                       |
      | owneremail         | m@example.com                |
      | createdby          | arowatt                      |
      | modifiedby         | arowatt                      |
      | timecreated        | 1                            |
    # A stale read (recorded at epoch + 1s) — i.e., before the notice's last modification.
    And the following "block_notices > notice read" exists:
      | user     | 98186700       |
      | notice   | NoticeModified |
      | timeread | 1              |
    When I log in as "admin"
    And I am on site homepage
    And I follow "Manage notices"
    Then I should see "0 / 1 read(s)" in the "[data-notice-title=\"NoticeModified\"] .block-notices-readcount" "css_element"
