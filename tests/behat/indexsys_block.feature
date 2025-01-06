@block @block_notices
Feature: Notices block on the dashboard (my) page
  In order to have one Notice block on the dashboard (my) page
  As a teacher
  I need to be able to create such blocks
  
  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | student1 | Sam1      | Student1 | student1@example.com |
    And the following "blocks" exist: 
      | blockname  | contextlevel | reference | pagetypepattern | defaultregion | defaultweight |
      | notices    | System       | 1         | my-index        | content       | -3            | 

  Scenario: Checking the layout of the Notice block on the my dashboard page
    When I log in as "admin"
    And I follow "Dashboard"
    And I should see "No notices" in the "Notices" "block"
    And I should see "Manage notices" in the "Notices" "block"
    And I log out
    When I log in as "student1"
    And I follow "Dashboard"
    And I should see "No notices" in the "Notices" "block"
    And I should not see "Manage notices" in the "Notices" "block"
    