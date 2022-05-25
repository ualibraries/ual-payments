@transactions
Feature: Processing the POST request from Payflow Link

  Scenario: A successful payment from Payflow Link is posted
    Given I have a transaction with 2 fees of amount 5.00
    And I successfully pay for the transaction in Payflow Link
    Then I should receive a response from the results endpoint with status code "200" and body "Success"

  @javascript
  Scenario: A successful payment from Payflow Link is posted and the homepage is checked for fees
    Given I have a transaction with 2 fees of amount 5.00
    And I successfully pay for the transaction in Payflow Link
    And I am on "/login"
    And I fill in "username" with the ENV variable "TEST_ID"
    And I fill in "password" with the ENV variable "TEST_PASS"
    And I press "Login"
    And the fees checklist should not contain the ids of the test transaction fees
    And I should see "Thanks for your payment. We’ve received your payment and applied it toward your account."

  Scenario: A declined payment from Payflow Link is posted
    Given I have a transaction with 2 fees of amount 5.00
    And my transaction in Payflow Link is declined
    Then I should receive a response from the results endpoint with status code "200" and body "Declined by Payflow"

  Scenario: A payment declined because of AVS is posted from Payflow Link
    Given I have a transaction with 2 fees of amount 5.00
    And my transaction in Payflow Link is AVS declined
    Then I should receive a response from the results endpoint with status code "200" and body "Declined by Payflow"

  Scenario: A payment declined because of CSC is posted from Payflow Link
    Given I have a transaction with 2 fees of amount 5.00
    And my transaction in Payflow Link is CSC declined
    Then I should receive a response from the results endpoint with status code "200" and body "Declined by Payflow"

  @javascript
  Scenario: A successful payment from Payflow Link is posted and the homepage is checked for fees
    Given I have a transaction with 2 fees of amount 5.00
    And my transaction in Payflow Link is declined
    And I am on "/login"
    And I fill in "username" with the ENV variable "TEST_ID"
    And I fill in "password" with the ENV variable "TEST_PASS"
    And I press "Login"
    Then the fees checklist should contain the ids of the test transaction fees
    And I should see "Your payment of $10.00 was declined by PayPal."
