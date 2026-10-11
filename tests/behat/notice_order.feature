@block @block_notices
Feature: Notice display order
    In order to see notices I have not seen recently
    As a user
    I need unread notices first, then read notices ordered by when I last read them

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | 98186700 | Sam1      | Student1 | student1@example.com |
    And I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Notices" block
    # All notices share the default sortorder, so without last-read ordering they would display in creation order.
    And the following "block_notices > notice" exists:
      | course             | Acceptance test site |
      | visible            | NOTICE_VISIBLE       |
      | staffonly          | 0                    |
      | title              | NoticeA              |
      | content            | NoticeAcontent       |
      | moreinformationurl | http://massey.ac.nz  |
      | owner              | ownera               |
      | owneremail         | a@example.com        |
      | timecreated        | 1                    |
      | timemodified       | 1                    |
      | timepromoted       | 1                    |
    And the following "block_notices > notice" exists:
      | course             | Acceptance test site |
      | visible            | NOTICE_VISIBLE       |
      | staffonly          | 0                    |
      | title              | NoticeB              |
      | content            | NoticeBcontent       |
      | moreinformationurl | http://massey.ac.nz  |
      | owner              | ownerb               |
      | owneremail         | b@example.com        |
      | timecreated        | 2                    |
      | timemodified       | 2                    |
      | timepromoted       | 2                    |
    And the following "block_notices > notice" exists:
      | course             | Acceptance test site |
      | visible            | NOTICE_VISIBLE       |
      | staffonly          | 0                    |
      | title              | NoticeC              |
      | content            | NoticeCcontent       |
      | moreinformationurl | http://massey.ac.nz  |
      | owner              | ownerc               |
      | owneremail         | c@example.com        |
      | timecreated        | 3                    |
      | timemodified       | 3                    |
      | timepromoted       | 3                    |
    And the following "block_notices > notice" exists:
      | course             | Acceptance test site |
      | visible            | NOTICE_VISIBLE       |
      | staffonly          | 0                    |
      | title              | NoticeD              |
      | content            | NoticeDcontent       |
      | moreinformationurl | http://massey.ac.nz  |
      | owner              | ownerd               |
      | owneremail         | d@example.com        |
      | timecreated        | 4                    |
      | timemodified       | 4                    |
      | timepromoted       | 4                    |
    # Student read A most recently, B longest ago, and has never read D.
    And the following "block_notices > notice read" exists:
      | user     | 98186700 |
      | notice   | NoticeA  |
      | timeread | 300      |
    And the following "block_notices > notice read" exists:
      | user     | 98186700 |
      | notice   | NoticeB  |
      | timeread | 100      |
    And the following "block_notices > notice read" exists:
      | user     | 98186700 |
      | notice   | NoticeC  |
      | timeread | 200      |

  Scenario: Unread notices come first, then read notices from least to most recently read
    When I log in as "98186700"
    And I am on site homepage
    Then "#stream-dashboard-notices[data-notices-count=\"4\"]" "css_element" should exist
    And ".swiper-wrapper .swiper-slide:nth-child(1)[data-notice-title=\"NoticeD\"]" "css_element" should exist
    And ".swiper-wrapper .swiper-slide:nth-child(2)[data-notice-title=\"NoticeB\"]" "css_element" should exist
    And ".swiper-wrapper .swiper-slide:nth-child(3)[data-notice-title=\"NoticeC\"]" "css_element" should exist
    And ".swiper-wrapper .swiper-slide:nth-child(4)[data-notice-title=\"NoticeA\"]" "css_element" should exist
