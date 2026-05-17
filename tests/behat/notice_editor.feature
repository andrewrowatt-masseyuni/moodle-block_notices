@block @block_notices @javascript
Feature: Quill editor for notice content
    In order to apply basic formatting to notices
    As an admin
    I need a Quill editor with bold, italic, underline, alignment and link buttons

  Background:
    Given I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Notices" block
    And I am on site homepage
    And I follow "Manage notices"
    And I follow "Add notice"

  Scenario: Quill toolbar exposes only the basic formatting buttons
    Then ".ql-toolbar" "css_element" should exist
    And ".ql-toolbar .ql-bold" "css_element" should exist
    And ".ql-toolbar .ql-italic" "css_element" should exist
    And ".ql-toolbar .ql-underline" "css_element" should exist
    And ".ql-toolbar .ql-link" "css_element" should exist
    And ".ql-toolbar button.ql-align" "css_element" should exist
    And ".ql-toolbar .ql-header" "css_element" should not exist
    And ".ql-toolbar .ql-list" "css_element" should not exist
    And ".ql-toolbar .ql-image" "css_element" should not exist
    And ".ql-toolbar .ql-blockquote" "css_element" should not exist

  Scenario: Save a notice with content typed via the Quill editor
    And I set the field "Title" to "QuillTitle"
    And I set the notice Quill editor to "Hello Quill world"
    And I set the field "URL for more information" to "http://massey.ac.nz"
    And I set the field "Owner" to "qowner"
    And I set the field "Owner email address" to "qowner@noreply.com"
    And I click on "Save" "button" in the ".modal-dialog" "css_element"
    Then I should see "QuillTitle"
