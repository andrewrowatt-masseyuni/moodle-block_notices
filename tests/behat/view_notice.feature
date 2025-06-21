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
      | courseid           | 1                             |
      | visible            | 1                             |
      | staffonly          | 0                             |
      | title              | Notice1title                  |
      | content            | Notice1content                |
      | contentformat      | 1                             |
      | updatedescription  | Added 1 Jan                   |
      | moreinformationurl | http://massey.ac.nz           |
      | owner              | notice1owner                  |
      | owneremail         | Notice1owneremail@noreply.com |
      | sortorder          | 0                             |
      | notes              | notice1notes                  |
      | timecreated        | 1736235743                    |
      | timemodified       | 1736235743                    |
      | createdby          | 3                             |
      | modifiedby         | 3                             |
    And the following "block_notices > notice" exists:
      | courseid           | 1                             |
      | visible            | 1                             |
      | staffonly          | 1                             |
      | title              | Notice2title                  |
      | content            | Notice2content                |
      | contentformat      | 1                             |
      | updatedescription  | Added 1 Jan                   |
      | moreinformationurl | http://massey.ac.nz           |
      | owner              | notice2owner                  |
      | owneremail         | Notice2owneremail@noreply.com |
      | sortorder          | 0                             |
      | notes              | notice2notes                  |
      | timecreated        | 1736235743                    |
      | timemodified       | 1736235743                    |
      | createdby          | 3                             |
      | modifiedby         | 3                             |
    And the following "block_notices > notice" exists:
      | courseid           | 1                             |
      | visible            | 2                             |
      | staffonly          | 0                             |
      | title              | Notice3title                  |
      | content            | Notice3content                |
      | contentformat      | 1                             |
      | updatedescription  | Added 1 Jan                   |
      | moreinformationurl | http://massey.ac.nz           |
      | owner              | notice3owner                  |
      | owneremail         | Notice3owneremail@noreply.com |
      | sortorder          | 0                             |
      | notes              | notice3notes                  |
      | timecreated        | 1736235743                    |
      | timemodified       | 1736235743                    |
      | createdby          | 3                             |
      | modifiedby         | 3                             |

  Scenario: Checking the layout of the Notice block as admin
    When I log in as "admin"
    And I am on site homepage

    And I should see "Notice1title"
    And I should see "Notice1content"
    And I click on ".swiper-button-playpause" "css_element"

    And I click on ".swiper-button-next" "css_element"
    And I should see "Notice2title"
    And I should see "Notice2content"
    And I click on ".swiper-button-next" "css_element"

    And I should see "Notice3title"
    And I should see "Notice3content"
    And I should see "Manage notices"

  Scenario: Checking the layout of the Notice block as a student
    When I log in as "98186700"
    And I am on site homepage
    Then I should see "Notice1title"
    And I should see "Notice1content"
    # Notice2 is staff only
    And I should not see "Notice2title"
    And I should not see "Notice2content"
    # Notice3 is in preview
    And I should not see "Notice3title"
    And I should not see "Notice3content"
    And I should not see "Manage notices"

  Scenario: Checking the layout of the Notice block as a teacher
    When I log in as "arowatt"
    And I am on site homepage
    Then I should see "Notice1title"
    And I should see "Notice1content"
    # Notice2 is staff only
    And I should see "Notice2title"
    And I should see "Notice2content"
    # Notice3 is in preview
    And I should not see "Notice3title"
    And I should not see "Notice3content"
    And I should not see "Manage notices"
