@block @block_notices @javascript
Feature: Inline edit a notice's title and update description
    In order to make small corrections without a full form round-trip
    As an admin
    I need to edit the title and update description directly in the notices list

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
      | updatedescription  | Added 1 January               |
      | moreinformationurl | http://massey.ac.nz           |
      | owner              | notice1owner                  |
      | owneremail         | Notice1owneremail@noreply.com |
      | createdby          | arowatt                       |
      | modifiedby         | arowatt                       |

  Scenario: Inline-edit the title and update description from the manage page
    Given I am on site homepage
    And I follow "Manage notices"
    Then I should see "Notice1title"
    And I should see "Added 1 January"

    # Edit the title in place.
    When I set the field "Edit notice title" in the "[data-notice-title=\"Notice1title\"]" "css_element" to "Notice1updated"
    And I reload the page
    Then I should see "Notice1updated"
    And I should not see "Notice1title"

    # The notice should still be in the visible group (lightweight inline edits do not push to preview).
    And I should see "1 notice(s)" in the ".block-notices-group-visibility-visible .block-notices-count" "css_element"

    # Edit the update description in place.
    When I set the field "Edit update description" in the "[data-notice-title=\"Notice1updated\"]" "css_element" to "Updated 5 January"
    And I reload the page
    Then I should see "Updated 5 January"
    And I should not see "Added 1 January"
