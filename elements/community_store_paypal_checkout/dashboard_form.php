<?php defined('C5_EXECUTE') or die(_("Access Denied."));
extract($vars);
?>

<div class="form-group">
    <?= $form->label('paypalCheckoutColor', t('Style Color'))?>
    <?= $form->select('paypalCheckoutColor',
        [
            'gold' => t('Gold'),
            'blue' => t('Blue'),
            'silver' => t('Silver'),
            'white' => t('White'),
            'black' => t('Black')
        ]
        , $paypalCheckoutColor)?>
</div>

<div class="form-group">
    <?= $form->label('paypalCheckoutCurrency', t('Currency'))?>
    <?= $form->select('paypalCheckoutCurrency', $paypalCheckoutCurrencies, $paypalCheckoutCurrency)?>
</div>

<div class="form-group">
    <?= $form->label('paypalCheckoutMode', t('Mode'))?>
    <?= $form->select('paypalCheckoutMode', ['test' => t('Test'), 'live' => t('Live')], $paypalCheckoutMode)?>
</div>

<div class="form-group">
    <?= $form->label('paypalCheckoutTestClientID', t('Test Client ID'))?>
    <?= $form->text("paypalCheckoutTestClientID", $paypalCheckoutTestClientID); ?>
</div>

<div class="form-group">
    <?= $form->label('paypalCheckoutTestClientSecret', t('Test Secret Key'))?>
    <?= $form->text("paypalCheckoutTestClientSecret", $paypalCheckoutTestClientSecret); ?>
</div>


<div class="form-group">
    <?= $form->label('paypalCheckoutLiveClientID', t('Live Client ID'))?>
    <?= $form->text("paypalCheckoutLiveClientID", $paypalCheckoutLiveClientID); ?>
</div>

<div class="form-group">
    <?= $form->label('paypalCheckoutLiveClientSecret', t('Live Secret Key'))?>
    <?= $form->text("paypalCheckoutLiveClientSecret", $paypalCheckoutLiveClientSecret); ?>
</div>
