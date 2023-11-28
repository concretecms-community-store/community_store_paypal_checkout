# Community Store Paypal Checkout
Paypal Checkout payment method for Community Store for Concrete CMS

Requires version 2.5+ of Community Store and PHP8.1+
(a PHP7.4 compatible version is available, see release notes)

This method supports Paypal (and card payments through Paypal) directly within the checkout page.

To create credentials for this payment method, visit https://developer.paypal.com/ and log in with your Paypal account.
Within "Apps and Credentials" create a new REST API app, and copy the Client ID and Secret into the payment method configuration.

Ensure that you have set the correct matching currency.

See https://developer.paypal.com/api/rest/sandbox/card-testing/ for test cards.
