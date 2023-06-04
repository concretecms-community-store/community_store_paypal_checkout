<?php

use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;

defined('C5_EXECUTE') or die("Access Denied.");
extract($vars);
?>

<div id="paypal-button-container"></div>
<input type="hidden" value="" name="transactionID" id="transactionID"/>

<script>
    var paypalButtonsComponent = paypal.Buttons({
        style: {
            color: "<?php echo $paypalCheckoutColor ? $paypalCheckoutColor : 'white'; ?>",
            shape: "rect",
            layout: "vertical",
            tagline: false
        },

        // set up the transaction
        createOrder: function () {
            return fetch("<?= \URL::to('/checkout/paypaycheckoutcreateorder'); ?>", {
                method: "post",
                headers: {
                    "Content-Type": "application/json",
                }
            })
                .then((response) => response.json())
                .then((order) => order.id);
        },

        // finalize the transaction
        onApprove: function (data) {
            return fetch("<?= \URL::to('/checkout/paypaycheckoutcaptureorder'); ?>", {
                method: "post",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    orderID: data.orderID,
                }),
            })
                .then((response) => response.json())
                .then((response) => {
                    // Successful capture! For dev/demo purposes:
                    // console.log(
                    //     "Capture result",
                    //     orderData,
                    //     JSON.stringify(orderData, null, 2)
                    // );
                    // const transaction = orderData.purchase_units[0].payments.captures[0];
                    // alert(
                    //     "Transaction " +
                    //     transaction.status +
                    //     ": " +
                    //     transaction.id +
                    //     "\n\nSee console for all available details"
                    // );

                    // javascript redirect
                    window.location.href = response.redirect;
                });
        },

        // handle unrecoverable errors
        onError: (err) => {
            console.log(err);
            console.error('An error prevented the buyer from checking out with PayPal');
        }
    });

    paypalButtonsComponent
        .render("#paypal-button-container")
        .catch((err) => {
            console.error('PayPal Buttons failed to render');
        });

    var button = document.querySelector("[data-payment-method-id='<?= $pmID; ?>'] .store-btn-complete-order");
    button.remove();
</script>

