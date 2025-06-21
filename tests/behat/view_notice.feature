@block @block_notices @javascript
Feature: View notice

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | 98186700 | Sam1      | Student1 | student1@example.com |
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
      | moreinformationurl | http://massey.ac.nz           |
      | owner              | notice1owner                  |
      | owneremail         | Notice1owneremail@noreply.com |
      | createdby          | arowatt                       |
      | modifiedby         | arowatt                       |
    And the following "block_notices > notice" exists:
      | course             | Acceptance test site          |
      | visible            | NOTICE_VISIBLE                |
      | staffonly          | 1                             |
      | title              | Notice2title                  |
      | content            | Notice2content                |
      | moreinformationurl | http://massey.ac.nz           |
      | owner              | notice2owner                  |
      | owneremail         | Notice2owneremail@noreply.com |
      | createdby          | arowatt                       |
      | modifiedby         | arowatt                       |
    And the following "block_notices > notice" exists:
      | course             | Acceptance test site          |
      | visible            | NOTICE_IN_PREVIEW             |
      | staffonly          | 0                             |
      | title              | Notice3title                  |
      | content            | Notice3content                |
      | moreinformationurl | http://massey.ac.nz           |
      | owner              | notice3owner                  |
      | owneremail         | Notice3owneremail@noreply.com |
      | createdby          | arowatt                       |
      | modifiedby         | arowatt                       |

  Scenario: Checking the layout of the Notice block as admin
    When I log in as "admin"
    And I am on site homepage

    # Quick notice count check
    And "#stream-dashboard-notices[data-notices-count=\"3\"]" "css_element" should exist
    And I click on ".swiper-button-playpause" "css_element"

    And I should see "Notice1title"
    And I should see "Notice1content"

    And I click on ".swiper-button-next" "css_element"
    # Wait for next slide
    And I wait "5" seconds
    And I should see "Notice2title"
    And I should see "Notice2content"

    And I click on ".swiper-button-next" "css_element"
    # Wait for next slide
    And I wait "5" seconds
    And I should see "Notice3title"
    And I should see "Notice3content"
    And I should see "Manage notices"

  Scenario: Checking the layout of the Notice block as a student
    When I log in as "98186700"
    And I am on site homepage

    # Quick notice count check
    And "#stream-dashboard-notices[data-notices-count=\"1\"]" "css_element" should exist
    Then I should see "Notice1title"
    And I should see "Notice1content"
    And I should not see "Manage notices"

  Scenario: Checking the layout of the Notice block as a teacher
    When I log in as "arowatt"
    And I am on site homepage

    # Quick notice count check
    And "#stream-dashboard-notices[data-notices-count=\"2\"]" "css_element" should exist
    And I click on ".swiper-button-playpause" "css_element"

    And I should see "Notice1title"
    And I should see "Notice1content"

    And I click on ".swiper-button-next" "css_element"
    # Wait for next slide
    And I wait "5" seconds
    And I should see "Notice2title"
    And I should see "Notice2content"

    And I should not see "Manage notices"
