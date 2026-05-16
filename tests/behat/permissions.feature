@block @block_notices @javascript
Feature: Additional editor assignment grants edit rights without needing a role
    In order to delegate notice maintenance safely
    As an admin
    I need notices managers to handle everything and additional editors to be able to edit only their own notices

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | manager1 | Mary      | Manager  | manager1@example.com |
      | editor1  | Emma      | Editor1  | editor1@example.com  |
      | editor2  | Ethan     | Editor2  | editor2@example.com  |
    And the following "role assigns" exist:
      | user     | role                  | contextlevel | reference |
      | manager1 | block_notices_manager | System       |           |
    And I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Notices" block
    And the following "block_notices > notice" exists:
      | course             | Acceptance test site |
      | visible            | NOTICE_VISIBLE       |
      | staffonly          | 0                    |
      | title              | EditorOneNotice      |
      | content            | Owned by Emma        |
      | moreinformationurl | http://massey.ac.nz  |
      | owner              | notice1owner         |
      | owneremail         | notice1@noreply.com  |
      | createdby          | admin                |
      | modifiedby         | admin                |
      | additionaleditor   | editor1              |
    And the following "block_notices > notice" exists:
      | course             | Acceptance test site |
      | visible            | NOTICE_VISIBLE       |
      | staffonly          | 0                    |
      | title              | EditorTwoNotice      |
      | content            | Owned by Ethan       |
      | moreinformationurl | http://massey.ac.nz  |
      | owner              | notice2owner         |
      | owneremail         | notice2@noreply.com  |
      | createdby          | admin                |
      | modifiedby         | admin                |
      | additionaleditor   | editor2              |
    And I change window size to "large"

  Scenario: Notices manager sees the full manage page and can add notices
    Given I log in as "manager1"
    And I am on site homepage
    And I follow "Manage notices"
    Then I should see "EditorOneNotice"
    And I should see "EditorTwoNotice"
    And "Add notice" "link" should exist

  Scenario: Additional editor sees only their own notice and cannot add new ones
    Given I log in as "editor1"
    And I am on site homepage
    Then I should see "Manage my notices"
    And I should not see "Manage notices ("
    And I follow "Manage my notices"
    Then I should see "EditorOneNotice"
    And I should not see "EditorTwoNotice"
    And "Add notice" "link" should not exist

  Scenario: User who is not an additional editor on any notice sees no manage link at all
    Given the following "users" exist:
      | username   | firstname | lastname | email                  |
      | nonowner1  | Nina      | NonOwner | nonowner1@example.com  |
    And I log in as "nonowner1"
    And I am on site homepage
    Then I should not see "Manage my notices"
    And I should not see "Manage notices"

  Scenario: Notices manager can reassign a notice's additional editor from the edit form
    Given I log in as "manager1"
    And I am on site homepage
    And I follow "Manage notices"
    And I click on "[data-notice-title=\"EditorOneNotice\"] [data-notice-action=\"edit\"]" "css_element"
    And I set the field "Additional editor" to "Ethan Editor2"
    And I press "Save"
    # After reassignment, editor1 should no longer have anything to manage.
    And I log in as "editor1"
    And I am on site homepage
    Then I should not see "Manage my notices"
    # editor2 is now the additional editor for both notices.
    And I log in as "editor2"
    And I am on site homepage
    And I follow "Manage my notices"
    Then I should see "EditorOneNotice"
    And I should see "EditorTwoNotice"

  Scenario: Additional editor sees no delete or move buttons and is rejected on direct URL
    Given I log in as "editor1"
    And I am on site homepage
    And I follow "Manage my notices"
    Then "[data-notice-title=\"EditorOneNotice\"] [data-notice-action=\"delete\"]" "css_element" should not exist
    And "[data-notice-title=\"EditorOneNotice\"] [data-notice-action=\"moveup\"]" "css_element" should not exist
    And "[data-notice-title=\"EditorOneNotice\"] [data-notice-action=\"movedown\"]" "css_element" should not exist
