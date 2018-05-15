@fee
Feature: Pay for charges

  @javascript
  Scenario: Submitting pay form with no fees selected
    Given I am on "/"
    When  I press "submitButton"
    Then I should be on "/"

  @javascript
  Scenario: Selecting a fee to pay
    Given I am on "/"
    When I check "fee[]"
    Then the element with class ".charges__total-amount-number" should equal the element with class ".charges__total-amount-selected-number"

  @javascript @additionalfee
  Scenario: Selecting multiple fees
    Given I am on "/"
    When I check all fees
    And I press "submitButton"
    Then I should be on "/pay"
    And I should see "You are about to pay $14.00"
