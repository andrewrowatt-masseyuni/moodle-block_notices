@block @block_notices
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
      | timecreated        | 1                             |
      | timemodified       | 1                             |
      | timepromoted       | 1                             |
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
      | timecreated        | 2                             |
      | timemodified       | 2                             |
      | timepromoted       | 2                             |
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
      | timecreated        | 3                             |
      | timemodified       | 3                             |
      | timepromoted       | 3                             |
    And I change the window size to "large"

  Scenario: Checking the layout of the Notice block as admin
    When I log in as "admin"
    And I am on site homepage
    And I should see "Manage notices"

    # Quick notice count check
    And "#stream-dashboard-notices[data-notices-count=\"3\"]" "css_element" should exist

    # check slide order
    And "//div[@id='stream-dashboard-notices']//div[contains(@class,'swiper-slide')][1]//h6[normalize-space()=\"Notice1title\"]" "xpath_element" should exist
    And "//div[@id='stream-dashboard-notices']//div[contains(@class,'swiper-slide')][1][.//div[@class=\"item__content\"][normalize-space()=\"Notice1content\"]]" "xpath_element" should exist

    And "//div[@id='stream-dashboard-notices']//div[contains(@class,'swiper-slide')][2]//h6[normalize-space()=\"Notice2title\"]" "xpath_element" should exist
    And "//div[@id='stream-dashboard-notices']//div[contains(@class,'swiper-slide')][2][.//div[@class=\"item__content\"][normalize-space()=\"Notice2content\"]]" "xpath_element" should exist

    And "//div[@id='stream-dashboard-notices']//div[contains(@class,'swiper-slide')][3]//h6[normalize-space()=\"Notice3title\"]" "xpath_element" should exist
    And "//div[@id='stream-dashboard-notices']//div[contains(@class,'swiper-slide')][3][.//div[@class=\"item__content\"][normalize-space()=\"Notice3content\"]]" "xpath_element" should exist
    
  Scenario: Checking the layout of the Notice block as a student
    When I log in as "98186700"
    And I am on site homepage

    # Quick notice count check
    And "#stream-dashboard-notices[data-notices-count=\"1\"]" "css_element" should exist

    And "//div[@id='stream-dashboard-notices']//div[contains(@class,'swiper-slide')][1]//h6[normalize-space()=\"Notice1title\"]" "xpath_element" should exist
    And "//div[@id='stream-dashboard-notices']//div[contains(@class,'swiper-slide')][1][.//div[@class=\"item__content\"][normalize-space()=\"Notice1content\"]]" "xpath_element" should exist

  Scenario: Checking the layout of the Notice block as a teacher
    When I log in as "arowatt"
    And I am on site homepage

    # Quick notice count check
    And "#stream-dashboard-notices[data-notices-count=\"2\"]" "css_element" should exist
    And "//div[@id='stream-dashboard-notices']//div[contains(@class,'swiper-slide')][1]//h6[normalize-space()=\"Notice1title\"]" "xpath_element" should exist
    And "//div[@id='stream-dashboard-notices']//div[contains(@class,'swiper-slide')][1][.//div[@class=\"item__content\"][normalize-space()=\"Notice1content\"]]" "xpath_element" should exist

    And "//div[@id='stream-dashboard-notices']//div[contains(@class,'swiper-slide')][2]//h6[normalize-space()=\"Notice2title\"]" "xpath_element" should exist
    And "//div[@id='stream-dashboard-notices']//div[contains(@class,'swiper-slide')][2][.//div[@class=\"item__content\"][normalize-space()=\"Notice2content\"]]" "xpath_element" should exist

    And I should not see "Manage notices"
