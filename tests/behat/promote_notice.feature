@block @block_notices @javascript
Feature: Promote a notice
    In order to push an older notice back to the top of the carousel
    As a notice manager
    I need to be able to promote a notice without modifying it

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | 98186700 | Sam1      | Student1 | student1@example.com |
    And I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Notices" block
    # Two visible notices with timepromoted set in the past so reads recorded "now" count as up-to-date.
    And the following "block_notices > notice" exists:
      | course             | Acceptance test site         |
      | visible            | NOTICE_VISIBLE               |
      | staffonly          | 0                            |
      | title              | OlderNotice                  |
      | content            | OlderNoticecontent           |
      | moreinformationurl | http://massey.ac.nz          |
      | owner              | olderowner                   |
      | owneremail         | older@example.com            |
      | timecreated        | 1                            |
      | timemodified       | 1                            |
      | timepromoted       | 1                            |
    And the following "block_notices > notice" exists:
      | course             | Acceptance test site         |
      | visible            | NOTICE_VISIBLE               |
      | staffonly          | 0                            |
      | title              | NewerNotice                  |
      | content            | NewerNoticecontent           |
      | moreinformationurl | http://massey.ac.nz          |
      | owner              | newerowner                   |
      | owneremail         | newer@example.com            |
      | timecreated        | 2                            |
      | timemodified       | 2                            |
      | timepromoted       | 2                            |
    # Student has read both, recorded "now" — both notices count as read for them.
    And the following "block_notices > notice read" exists:
      | user   | 98186700    |
      | notice | OlderNotice |
    And the following "block_notices > notice read" exists:
      | user   | 98186700    |
      | notice | NewerNotice |
    And I change the window size to "large"

  Scenario: Promoting a notice bubbles it to the top of the carousel for users who have already read it
    # Admin promotes OlderNotice via the manage page.
    When I am on site homepage
    And I follow "Manage notices"
    Then "[data-notice-title=\"OlderNotice\"] [data-notice-action=\"promote\"]" "css_element" should exist
    And I click on "[data-notice-title=\"OlderNotice\"] [data-notice-action=\"promote\"]" "css_element"

    # Student sees the promoted notice first in the carousel.
    And I log out
    And I log in as "98186700"
    And I am on site homepage
    Then "#stream-dashboard-notices[data-notices-count=\"2\"]" "css_element" should exist
    And ".swiper-wrapper .swiper-slide:nth-child(1)[data-notice-title=\"OlderNotice\"]" "css_element" should exist
    And ".swiper-wrapper .swiper-slide:nth-child(2)[data-notice-title=\"NewerNotice\"]" "css_element" should exist
