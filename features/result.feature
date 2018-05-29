Feature: Processing the POST request from Payflow Link

  Scenario: A successful payment from Payflow Link is posted
    Given I have a transaction with 2 fees of amount 5.00
    And I successfully pay for the transaction in Payflow Link
    Then I should receive a response from the results endpoint with status code "200" and body "Success"

  Scenario: A successful payment from Payflow Link is posted and the homepage is checked for fees
    Given I have a transaction with 2 fees of amount 5.00
    And I successfully pay for the transaction in Payflow Link
    And I am on "/login"
    And I fill in "username" with the ENV variable "TEST_ID"
    And I fill in "password" with the ENV variable "TEST_PASS"
    And I press "Login"
    Then I am on "/"
    And the fees checklist should not contain the ids of the test transaction fees

  Scenario: A declined payment from Payflow Link is posted
    Given I have a transaction with 2 fees of amount 5.00
    And my transaction in Payflow Link is declined
    Then I should receive a response from the results endpoint with status code "200" and body "Declined by Payflow"

  Scenario: A successful payment from Payflow Link is posted and the homepage is checked for fees
    Given I have a transaction with 2 fees of amount 5.00
    And my transaction in Payflow Link is declined
    And I am on "/login"
    And I fill in "username" with the ENV variable "TEST_ID"
    And I fill in "password" with the ENV variable "TEST_PASS"
    And I press "Login"
    Then the fees checklist should contain the ids of the test transaction fees
