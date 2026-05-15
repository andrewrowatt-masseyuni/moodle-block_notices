@block @block_notices @javascript
Feature: Inline edit a notice's title
    In order to make small corrections without a full form round-trip
    As an admin
    I need to edit the title directly in the notices list

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
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

  Scenario: Inline-edit the title from the manage page
    Given I am on site homepage
    And I follow "Manage notices"
    Then I should see "Notice1title"

    # Edit the title in place.
    When I set the field "Edit notice title" in the "[data-notice-title=\"Notice1title\"]" "css_element" to "Notice1updated"
    And I reload the page
    Then I should see "Notice1updated"
    And I should not see "Notice1title"

    # The notice should still be in the visible group (lightweight inline edits do not push to preview).
    And I should see "1 notice(s)" in the ".block-notices-group-visibility-visible .block-notices-count" "css_element"
