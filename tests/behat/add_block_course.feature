@block @block_notices
Feature: Notices block a course
  In order to have one Notice block on a course
  As a teacher
  I need to be able to create such blocks

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Terry1    | Teacher1 | teacher@example.com  |
      | student1 | Sam1      | Student1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Notices" block

  Scenario: Adding Notice block on a course
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    Then I should see "There are no notices. Have a great day!"
    Then I should see "Manage notices"

  Scenario: Block is visible to students
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "There are no notices. Have a great day!"
    Then I should not see "Manage notices"
