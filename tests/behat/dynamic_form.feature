@block @block_notices @javascript
Feature: Add and edit notices via the modal dynamic form
    In order to manage notices without leaving the Manage notices page
    As an admin
    I need to add and edit notices in an overlay form

  Background:
    Given I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Notices" block
    And the following "block_notices > notice" exists:
      | course             | Acceptance test site          |
      | visible            | NOTICE_VISIBLE                |
      | staffonly          | 0                             |
      | title              | OriginalTitle                 |
      | content            | OriginalContent               |
      | moreinformationurl | http://massey.ac.nz           |
      | owner              | originalowner                 |
      | owneremail         | originalowner@noreply.com     |
    And I am on site homepage
    And I follow "Manage notices"

  Scenario: Add a notice using the modal form
    And I follow "Add notice"
    And I should see "Add notice" in the ".modal-dialog" "css_element"
    And I set the following fields to these values:
      | Staff only               | No                            |
      | Title                    | Notice1title                  |
      | URL for more information | http://massey.ac.nz           |
      | Owner                    | notice1owner                  |
      | Owner email address      | Notice1owneremail@noreply.com |
      | Notes                    | Remove 1 November 2024        |
    And I set the notice Quill editor to "Notice1content"
    And I click on "Save" "button" in the ".modal-dialog" "css_element"
    And I should see "Manage notices"
    Then I should see "Notice1title" in the ".block-notices-group-visibility-preview" "css_element"

  Scenario: Edit a notice using the modal form
    And I click on "[data-notice-title=\"OriginalTitle\"] [data-notice-action=\"edit\"]" "css_element"
    And I should see "Edit" in the ".modal-dialog" "css_element"
    And the field "Title" matches value "OriginalTitle"
    And I set the field "Title" to "UpdatedTitle"
    And I click on "Save" "button" in the ".modal-dialog" "css_element"
    And I should see "Manage notices"
    Then I should see "UpdatedTitle" in the ".block-notices-group-visibility-visible" "css_element"
    And I should not see "OriginalTitle"
