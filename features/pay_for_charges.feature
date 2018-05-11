Feature: Pay for charges

  @javascript
  Scenario: Submitting pay form with no fees selected
    Given I am on "/"
    When  I press "Pay now"
    Then I should be on "/"

