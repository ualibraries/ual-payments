---
layout: default
title: Payflow Response
---
University of Arizona Libraries - Payments - Payflow Response
========================

The full documentation for Payflow Link Legacy is [here](https://www.paypalobjects.com/webstatic/en_US/developer/docs/pdf/pp_payflowlink_guide.pdf).

Payflow sends POST requests to Return URL and Silent POST URL. Both URLs have to be set at manager.paypal.com. The Post request for Silent POST is sent after user click Submit Transaction button. And the other request is sent when user return to Payments application.

## Payflow Post Request Parameters
* RESULT
    * A value of 0 indicates that no errors occurred and the transaction was approved.
    * A value less than zero indicates that a communication error occurred. In this case, no transaction is attempted.
    * A value greater than zero indicates a decline or error.
    * For a full list of result codes, please check the [documentation](https://www.paypalobjects.com/webstatic/en_US/developer/docs/pdf/pp_payflowlink_guide.pdf).
* RESPMSG
    * The response message provides a brief description for decline or error results.
    * For a full list of response messages, please check the [documentation](https://www.paypalobjects.com/webstatic/en_US/developer/docs/pdf/pp_payflowlink_guide.pdf).
* AUTHCODE
    * For credit card transactions. Transactions approved by the issuing bank receive this bank authorization code.
* AVSDATA
    * Address Verification Service. The AVS result is for advice only. Banks do not decline transactions based on the AVS result—you make the decision to approve or decline each transaction.
* PNREF
    * This number is generated by PayPal. This value is displayed on PayPal Manager reports as Transaction ID, on the Receipt page as Order ID, and appears in email receipts to both merchant and customer.
* HOSTCODE
    * For ECHECK payment only. Not relevant to our application.
* INVOICE
    * A uniqie number generated by us. It appears in email receipts to both merchant and customer.
* PONUM
    * Purchase Order Number. This number is the same as INVOICE if not set explicitly in the request sent to Payflow. This number can be used to search transactions on PayPal.
* AMOUNT
* TAX
* METHOD
    * C or CC for credit card.
* TYPE
    * S for Sale or A for Authorization. We use S.
* CSCMATCH
    * Card Security Code match response. The cardholder’s bank returns a Y, N, or X response on whether the submitted CSC matches the number on file at the bank.
* DESCRIPTION
    * Displayed on the Transaction Confirmation page and in email receipts to both merchant and customer. This information is not stored on PayPal.
* CUSTID
    * Customer ID. It appears in email receipts to both merchant and customer. This number is not stored on PayPal. We use UA ID here.
* NAME
* ADDRESS
* CITY
* STATE
* ZIP
* COUNTRY
* PHONE
* FAX
* EMAIL
* USER1 through USER10
    * These ten string type parameters are intended to store temporary data. These values are not displayed to the customer and are not stored on PayPal.