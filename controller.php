<?php

namespace Concrete\Package\CommunityStorePaypalCheckout;

use \Concrete\Core\Package\Package;
use Whoops\Exception\ErrorException;
use \Concrete\Core\Support\Facade\Route;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;

class Controller extends Package
{
    protected $pkgHandle = 'community_store_paypal_checkout';
    protected $appVersionRequired = '8.0';
    protected $pkgVersion = '1.0.1';
    protected $packageDependencies = ['community_store' => '2.5'];
    protected $pkgAutoloaderRegistries = [
        'src/CommunityStore' => '\Concrete\Package\CommunityStorePaypalCheckout\Src\CommunityStore',
    ];

    public function on_start()
    {
        Route::register('/checkout/paypalcheckoutcreateorder','\Concrete\Package\CommunityStorePaypalCheckout\Src\CommunityStore\Payment\Methods\CommunityStorePaypalCheckout\CommunityStorePaypalCheckoutPaymentMethod::createOrder');
        Route::register('/checkout/paypalcheckoutcaptureorder','\Concrete\Package\CommunityStorePaypalCheckout\Src\CommunityStore\Payment\Methods\CommunityStorePaypalCheckout\CommunityStorePaypalCheckoutPaymentMethod::captureOrder');
    }

    public function getPackageDescription()
    {
        return t("Paypal Checkout Payment Method for Community Store");
    }

    public function getPackageName()
    {
        return t("Paypal Checkout Payment Method");
    }

    public function install()
    {
        if (!@include(__DIR__ . '/vendor/autoload.php')) {
            throw new ErrorException(t('Third party libraries not installed. Use a release version of this add-on with libraries pre-installed, or run composer install against the package folder.'));
        }

        $pkg = parent::install();
        $pm = new PaymentMethod();
        $pm->add('community_store_paypal_checkout', 'Paypal Checkout', $pkg);
    }

    public function uninstall()
    {
        $pm = PaymentMethod::getByHandle('community_store_paypal_checkout');
        if ($pm) {
            $pm->delete();
        }
        $pkg = parent::uninstall();
    }

}

?>
