@block @block_notices
Feature: Auto-close notices with a passed close date
    In order to remove notices that are no longer relevant
    As an editor
    I want notices to hide themselves once their close date passes

  Background:
    Given I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Notices" block

  Scenario: A notice whose close date has passed is auto-closed
    Given the following "block_notices > notice" exists:
      | course             | Acceptance test site          |
      | visible            | NOTICE_VISIBLE                |
      | title              | Notice1title                  |
      | content            | Notice1content                |
      | moreinformationurl | http://massey.ac.nz           |
      | owner              | notice1owner                  |
      | owneremail         | Notice1owneremail@noreply.com |
      | closedate          | 1000000000                    |
    And I am on site homepage
    And I follow "Manage notices"
    Then "[data-notice-title=\"Notice1title\"]" "css_element" should exist in the ".block-notices-group-visibility-visible" "css_element"

    When I run the scheduled task "\block_notices\task\auto_close_notices"
    And I reload the page
    Then "[data-notice-title=\"Notice1title\"]" "css_element" should exist in the ".block-notices-group-visibility-hidden" "css_element"
