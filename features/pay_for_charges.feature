@fee
Feature: Pay for charges

  @javascript
  Scenario: The submit button should be disabled if no fees are checked
    Given I am on "/login"
    And I fill in "username" with the ENV variable "TEST_ID"
    And I fill in "password" with the ENV variable "TEST_PASS"
    And I press "Login"
    And I am on "/"
    Then the element "#submitButton" should be disabled

  @javascript
  Scenario: Selecting a fee to pay
    Given I am on "/login"
    And I fill in "username" with the ENV variable "TEST_ID"
    And I fill in "password" with the ENV variable "TEST_PASS"
    And I press "Login"
    And I am on "/"
    When I check "fee[]"
    Then the element with class ".charges__total-amount-number" should equal the element with class ".charges__total-amount-selected-number"

  @javascript @additionalfee
  Scenario: Selecting multiple fees
    Given I am on "/login"
    And I fill in "username" with the ENV variable "TEST_ID"
    And I fill in "password" with the ENV variable "TEST_PASS"
    And I press "Login"
    And I am on "/"
    When I check all fees
    And I press "Pay now"
    Then I should be on "/pay"
    And I should see "You’re about to pay $14.00"
