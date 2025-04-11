@block @block_notices @javascript
Feature: View notice

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | student1 | Sam1      | Student1 | student1@example.com |
    And I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Notices" block
    And the following "block_notices > notice" exists:
      | courseid           | 1                             |
      | visible            | 1                             |
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
      | visible            | 2                             |
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

  Scenario: Checking the layout of the Notice block as admin
    When I log in as "admin"
    And I am on site homepage
    Then I should see "Notice1title"
    And I should see "Notice1content"
    And I should see "Notice2title"
    And I should see "Notice2content"
    And I should see "Manage notices"
    
  Scenario: Checking the layout of the Notice block as a non-admin
    When I log in as "student1"
    And I am on site homepage
    Then I should see "Notice1title"
    And I should see "Notice1content"
    # Notice2 is in preview
    And I should not see "Notice2title"
    And I should not see "Notice2content"    
    And I should not see "Manage notices"