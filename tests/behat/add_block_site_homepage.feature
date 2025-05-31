@block @block_notices
Feature: Notices block on the dashboard (my) page
  In order to have one Notice block on the site homepage
  As a admin
  I need to be able to create such blocks

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | student1 | Sam1      | Student1 | student1@example.com |
    And I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Notices" block

  Scenario: Checking the empty layout of the Notice block on the my dashboard page as admin
    When I log in as "admin"
    And I am on site homepage
    Then I should see "No notices"
    And I should see "Manage notices"

  Scenario: Checking the empty layout of the Notice block on the my dashboard page as a regular user
    When I log in as "student1"
    And I am on site homepage
    Then I should see "No notices"
    And I should not see "Manage notices"
