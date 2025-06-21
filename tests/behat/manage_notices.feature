@block @block_notices @javascript
Feature: Add and manage notices
    In order for a notice to be visble to users
    As an admin
    I need to be able to add a notice
    and set the visibility of the notice
    and set the order of notices

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

  Scenario: Add and manage notices as admin
    And I am on site homepage
    And I follow "Manage notices"

    And I should see "Manage notices"
    Then I should see "Notice1title"
    And I should see "Notice1content"
    Then I should see "Notice2title"
    And I should see "Notice2content"
    Then I should see "Notice3title"
    And I should see "Notice3content"

    # Notice3
    And I should see "1 notice(s)" in the ".block-notices-group-visibility-preview .block-notices-count" "css_element"

    # Notice1 and Notice2
    And I should see "2 notice(s)" in the ".block-notices-group-visibility-visible .block-notices-count" "css_element"
    And I should see "No notices" in the ".block-notices-group-visibility-hidden .block-notices-count" "css_element"

    And I click on ".block-notices-group-visibility-visible [data-notice-title=\"Notice1title\"] [data-notice-action=\"hide\"]" "css_element"

    # Notice3
    And I should see "1 notice(s)" in the ".block-notices-group-visibility-preview .block-notices-count" "css_element"

    # Notice2
    And I should see "1 notice(s)" in the ".block-notices-group-visibility-visible .block-notices-count" "css_element"

    # Notice1
    And I should see "1 notice(s)" in the ".block-notices-group-visibility-hidden .block-notices-count" "css_element"

    And I click on ".block-notices-group-visibility-preview [data-notice-title=\"Notice3title\"] [data-notice-action=\"show\"]" "css_element"
    And I should see "No notices" in the ".block-notices-group-visibility-preview .block-notices-count" "css_element"

    # Notice2 and Notice3
    And I should see "2 notice(s)" in the ".block-notices-group-visibility-visible .block-notices-count" "css_element"

    # Notice1
    And I should see "1 notice(s)" in the ".block-notices-group-visibility-hidden .block-notices-count" "css_element"

    And I should see "Move down" in the "[data-notice-title=\"Notice2title\"]" "css_element"
    And I should see "Move up" in the "[data-notice-title=\"Notice3title\"]" "css_element"

    And I click on "[data-notice-title=\"Notice2title\"] [data-notice-action=\"movedown\"]" "css_element"

    And I should see "Move up" in the "[data-notice-title=\"Notice2title\"]" "css_element"
    And I should see "Move down" in the "[data-notice-title=\"Notice3title\"]" "css_element"

    And I click on "[data-notice-title=\"Notice1title\"] [data-notice-action=\"delete\"]" "css_element"
    And I should see "No notices" in the ".block-notices-group-visibility-hidden .block-notices-count" "css_element"

    And I should see "Staff only" in the "[data-notice-title=\"Notice2title\"] .staffonly" "css_element"
