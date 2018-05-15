@fee
Feature: Pay for charges

  @javascript
  Scenario: Submitting pay form with no fees selected
    Given I am on "/"
    When  I press "Pay now"
    Then I should be on "/"

  @javascript
  Scenario: Selecting a fee to pay
    Given I am on "/"
    When I check "fee[]"
    Then the element with class ".charges__total-amount-number" should equal the element with class ".charges__total-amount-selected-number"
