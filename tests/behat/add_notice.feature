@block @block_notices @javascript
Feature: Add a notice
    In order for a notice to be visble to users
    As an admin
    I need to be able to add a notice
    and set the visibility of the notice
    and set the order of notices

  Background:
    And I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    When I add the "Notices" block
    Then I should see "There are no notices. Have a great day!"
    And I should see "Manage notices"

  Scenario: Add and manage notices as admin
    And I am on site homepage
    And I follow "Manage notices"

    And I should see "No notices" in the ".block-notices-group-visibility-preview .block-notices-count" "css_element"
    And I should see "No notices" in the ".block-notices-group-visibility-visible .block-notices-count" "css_element"
    And I should see "No notices" in the ".block-notices-group-visibility-hidden .block-notices-count" "css_element"

    And I follow "Add notice"
    And I should see "Add notice"
    And I set the following fields to these values:
      | Staff only               | No                            |
      | Title                    | Notice1title                  |
      | Content                  | Notice1content                |
      | Update description       | Added 1 Jan                   |
      | URL for more information | http://massey.ac.nz           |
      | Owner                    | notice1owner                  |
      | Owner email address      | Notice1owneremail@noreply.com |
      | Notes                    | Remove 1 November 2024        |
    And I press "Save"

    And I should see "Manage notices"
    Then I should see "Notice1title"
