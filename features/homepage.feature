Feature: User wants to visit the homepage

  @javascript
  Scenario: Checking the login page
    Given I am on "/"
    Then I should see "Library payments"

  @javascript
  Scenario: Checking the homepage
    Given I am on "/login"
    And I fill in "username" with the ENV variable "TEST_ID"
    And I fill in "password" with the ENV variable "TEST_PASS"
    And I press "Login"
    Then I am on "/"
    And I should see "Hi, tess test test!"
