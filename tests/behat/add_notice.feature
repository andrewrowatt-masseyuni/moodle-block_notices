@block @block_notices @javascript
Feature: Add a notice
    In order for a notice to be visble to users
    As an admin
    I need to be able to add a notice

  Background:
    And I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    When I add the "Notices" block
    Then I should see "No notices"
    And I should see "Manage notices"
    And I change the window size to "large"

  Scenario: Add a notice as admin
    And I am on site homepage
    And I follow "Manage notices"
    And I follow "Add notice"
    And I should see "Add notice"
    And I set the following fields to these values:
      | Title                    | Notice1title                  |
      | Content                  | Notice1content                |
      | Update description       | Added 1 Jan                   |
      | URL for more information | http://massey.ac.nz           |
      | Owner                    | notice1owner                  |
      | Owner email address      | Notice1owneremail@noreply.com |
      | Notes                    | Remove 1 November 2024       |
    And I press "Save"

    And I should see "Manage notices"
    And I follow "Add notice"
    And I should see "Add notice"
    And I set the following fields to these values:
      | Title                    | Notice2title                  |
      | Content                  | Notice2content                |
      | Update description       | Added 1 Jan                   |
      | URL for more information | http://massey.ac.nz           |
      | Owner                    | notice2owner                  |
      | Owner email address      | Notice2owneremail@noreply.com |
      | Notes                    | Remove 2 November 2024       |
    And I press "Save"
    And I should see "Manage notices"
    Then I should see "Notice1title"
    And I should see "Notice1content"
    Then I should see "Notice2title"
    And I should see "Notice2content"

    And I click on "[data-notice-id=\"1\"] [data-notice-action=\"show\"]" "css_element"
    And I click on "[data-notice-id=\"2\"] [data-notice-action=\"show\"]" "css_element"
