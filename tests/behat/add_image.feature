@block @block_notices @javascript @_file_upload
Feature: Add or remove a decorative image on a notice
    In order to enhance a notice with a visual
    As an admin
    I need to be able to attach and remove a single image when authoring a notice

  Background:
    Given I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Notices" block
    And I am on site homepage
    And I follow "Manage notices"

  Scenario: Add a notice with a decorative image
    When I follow "Add notice"
    And I set the following fields to these values:
      | Title                    | NoticeWithImage              |
      | URL for more information | http://massey.ac.nz          |
      | Owner                    | imgowner                     |
      | Owner email address      | img@noreply.com              |
      | Notes                    | n/a                          |
    And I set the notice Quill editor to "Content with image"
    And I upload "lib/tests/fixtures/gd-logo.png" file to "Image" filemanager
    And I click on "Save" "button" in the ".modal-dialog" "css_element"
    # The manage list also renders the image alongside the notice.
    Then ".block-notices-item.item__has_image .img" "css_element" should exist
    And the "style" attribute of ".block-notices-item.item__has_image .img" "css_element" should contain "pluginfile.php"
    # Newly added notices land in preview; promote to visible.
    And I click on "[data-notice-title=\"NoticeWithImage\"] [data-notice-action=\"show\"]" "css_element"
    And I am on site homepage
    Then ".item.item__has_image" "css_element" should exist
    And the "style" attribute of ".item.item__has_image .img" "css_element" should contain "pluginfile.php"

  Scenario: Remove the image from an existing notice
    When I follow "Add notice"
    And I set the following fields to these values:
      | Title                    | ImageRemovalNotice           |
      | URL for more information | http://massey.ac.nz          |
      | Owner                    | imgowner                     |
      | Owner email address      | img@noreply.com              |
      | Notes                    | n/a                          |
    And I set the notice Quill editor to "Content with image"
    And I upload "lib/tests/fixtures/gd-logo.png" file to "Image" filemanager
    And I click on "Save" "button" in the ".modal-dialog" "css_element"
    And I click on "[data-notice-title=\"ImageRemovalNotice\"] [data-notice-action=\"show\"]" "css_element"
    And I am on site homepage
    And ".item.item__has_image" "css_element" should exist
    # Re-open the edit modal and remove the file.
    And I follow "Manage notices"
    And I click on "[data-notice-title=\"ImageRemovalNotice\"] [data-notice-action=\"edit\"]" "css_element"
    And I delete "gd-logo.png" from "Image" filemanager
    And I click on "Save" "button" in the ".modal-dialog" "css_element"
    And I am on site homepage
    Then ".item.item__has_image" "css_element" should not exist
