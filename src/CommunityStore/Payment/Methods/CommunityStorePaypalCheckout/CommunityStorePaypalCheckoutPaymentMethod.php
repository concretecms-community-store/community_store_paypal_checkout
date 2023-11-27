<?php
namespace Concrete\Package\CommunityStorePaypalCheckout\Src\CommunityStore\Payment\Methods\CommunityStorePaypalCheckout;

use PayPal\Http\PayPalClient;
use PayPal\Checkout\Orders\Order;
use PayPal\Checkout\Orders\PurchaseUnit;
use PayPal\Checkout\Orders\AmountBreakdown;
use PayPal\Http\Environment\SandboxEnvironment;
use PayPal\Checkout\Requests\OrderCreateRequest;
use PayPal\Checkout\Requests\OrderCaptureRequest;
use PayPal\Http\Environment\ProductionEnvironment;

use Concrete\Core\Http\Request;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Support\Facade\Config;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;

class CommunityStorePaypalCheckoutPaymentMethod extends StorePaymentMethod
{
    private function getCurrencies()
    {
        return [
            'USD' => t('US Dollar'),
            'EUR' => t('Euro'),
            'GBP' => t('British Pounds Sterling'),
            'AUD' => t('Australian Dollar'),
            'BRL' => t('Brazilian Real'),
            'CAD' => t('Canadian Dollar'),
            'CLP' => t('Chilean Peso'),
            'CZK' => t('Czech Koruna'),
            'DKK' => t('Danish Krone'),
            'HKD' => t('Hong Kong Dollar'),
            'HUF' => t('Hungarian Forint'),
            'IRR' => t('Iranian Rial'),
            'ILS' => t('Israeli Shekel'),
            'JPY' => t('Japanese Yen'),
            'MYR' => t('Malaysian Ringgit'),
            'MXN' => t('Mexican Peso'),
            'NZD' => t('New Zealand Dollar'),
            'NOK' => t('Norwegian Krone'),
            'PHP' => t('Philippine Peso'),
            'PLN' => t('Polish Zloty'),
            'RUB' => t('Russian Rubles'),
            'SGD' => t('Singapore Dollar'),
            'KRW' => t('South Korean Won'),
            'SEK' => t('Swedish Krona'),
            'CHF' => t('Swiss Franc)'),
            'TWD' => t('Taiwan New Dollar'),
            'THB' => t('Thai Baht'),
            'TRY' => t('Turkish Lira'),
            'VND' => t('Vietnamese Dong'),
        ];
    }

    public function dashboardForm()
    {
      $this->set('form', app()->make("helper/form"));

        $this->set('paypalCheckoutColor', Config::get('community_store_paypal_checkout.color'));
        $this->set('paypalCheckoutCurrency', Config::get('community_store_paypal_checkout.currency'));
        $this->set('paypalCheckoutMode', Config::get('community_store_paypal_checkout.mode'));
        $this->set('paypalCheckoutTestClientID', Config::get('community_store_paypal_checkout.testPublicApiKey'));
        $this->set('paypalCheckoutTestClientSecret', Config::get('community_store_paypal_checkout.livePublicApiKey'));
        $this->set('paypalCheckoutLiveClientID', Config::get('community_store_paypal_checkout.testPrivateApiKey'));
        $this->set('paypalCheckoutLiveClientSecret', Config::get('community_store_paypal_checkout.livePrivateApiKey'));
        $this->set('paypalCheckoutCurrencies', $this->getCurrencies());
    }

    public function save(array $data = [])
    {
        Config::save('community_store_paypal_checkout.color', $data['paypalCheckoutColor']);
        Config::save('community_store_paypal_checkout.mode', $data['paypalCheckoutMode']);
        Config::save('community_store_paypal_checkout.currency', $data['paypalCheckoutCurrency']);
        Config::save('community_store_paypal_checkout.testPublicApiKey', $data['paypalCheckoutTestClientID']);
        Config::save('community_store_paypal_checkout.livePublicApiKey', $data['paypalCheckoutTestClientSecret']);
        Config::save('community_store_paypal_checkout.testPrivateApiKey', $data['paypalCheckoutLiveClientID']);
        Config::save('community_store_paypal_checkout.livePrivateApiKey', $data['paypalCheckoutLiveClientSecret']);
    }

    public function validate($args, $e)
    {
        return $e;
    }

    public function checkoutForm()
    {
        $request = app()->make(\Concrete\Core\Http\Request::class);
        $referrer = $request->server->get('HTTP_REFERER');
        $c = \Concrete\Core\Page\Page::getByPath(parse_url($referrer, PHP_URL_PATH));
        $al = \Concrete\Core\Multilingual\Page\Section\Section::getBySectionOfSite($c);
        $langpath = '';
        if ($al !== null) {
            $langpath = $al->getCollectionHandle();
        }
        $returnUrl = \Concrete\Core\Support\Facade\Url::to($langpath . '/checkout/complete');
        $this->set('returnUrl', $returnUrl);

        $pmID = StorePaymentMethod::getByHandle('community_store_paypal_checkout')->getID();
        $this->set('pmID', $pmID);

        $this->set('paypalCheckoutColor', Config::get('community_store_paypal_checkout.color'));
    }

    public function submitPayment()
    {
        return ['error' => 0, 'transactionReference' => ''];
    }

    public function getPaymentMinimum()
    {
        return 0.5;
    }

    public function getName()
    {
        return 'Paypal Checkout';
    }

    public function isExternal()
    {
        return false;
    }

    public function headerScripts($view) {
        $paypalCheckoutMode = Config::get('community_store_paypal_checkout.mode');
        $paypalCheckoutTestClientID = Config::get('community_store_paypal_checkout.testPublicApiKey');
        $paypalCheckoutLiveClientID = Config::get('community_store_paypal_checkout.testPrivateApiKey');
        if ($paypalCheckoutMode == 'live') {
            $clientID = $paypalCheckoutLiveClientID;
        } else {
            $clientID = $paypalCheckoutTestClientID;
        }


        $paypalCheckoutCurrency = Config::get('community_store_paypal_checkout.currency');
        $view->addHeaderItem('<script src="https://www.paypal.com/sdk/js?client-id='. $clientID . '&currency='. $paypalCheckoutCurrency .'&intent=capture" ></script>');
    }


    public function createOrder() {

        require __DIR__ . '../../../../../../vendor/autoload.php';

        $paypalCheckoutMode = Config::get('community_store_paypal_checkout.mode');
        $paypalCheckoutCurrency = Config::get('community_store_paypal_checkout.currency');
        $paypalCheckoutTestClientID = Config::get('community_store_paypal_checkout.testPublicApiKey');
        $paypalCheckoutTestClientSecret = Config::get('community_store_paypal_checkout.livePublicApiKey');
        $paypalCheckoutLiveClientID = Config::get('community_store_paypal_checkout.testPrivateApiKey');
        $paypalCheckoutLiveClientSecret = Config::get('community_store_paypal_checkout.livePrivateApiKey');

        if ($paypalCheckoutMode == 'live') {
            $environment = new ProductionEnvironment($paypalCheckoutLiveClientID, $paypalCheckoutLiveClientSecret);
        } else {
            $environment = new SandboxEnvironment($paypalCheckoutTestClientID, $paypalCheckoutTestClientSecret);
        }

        // create a new client
        $client = new PayPalClient($environment);

        $total = StoreCalculator::getGrandTotal();
        if ($total > 0 ) {
            $purchase_unit = new PurchaseUnit(AmountBreakdown::of($total, $paypalCheckoutCurrency));

            // Create & add item to purchase unit
            // Create a new order with intent to capture a payment
            $order = (new Order())->addPurchaseUnit($purchase_unit);

            // Send request to PayPal
            $response = $client->send(new OrderCreateRequest($order));

            // Get results
            $result = json_decode($response->getBody()->getContents());

            return new JsonResponse($result);
        }
    }

    public function captureOrder() {

        require __DIR__ . '../../../../../../vendor/autoload.php';

        $paypalCheckoutMode = Config::get('community_store_paypal_checkout.mode');
        $paypalCheckoutCurrency = Config::get('community_store_paypal_checkout.currency');
        $paypalCheckoutTestClientID = Config::get('community_store_paypal_checkout.testPublicApiKey');
        $paypalCheckoutTestClientSecret = Config::get('community_store_paypal_checkout.livePublicApiKey');
        $paypalCheckoutLiveClientID = Config::get('community_store_paypal_checkout.testPrivateApiKey');
        $paypalCheckoutLiveClientSecret = Config::get('community_store_paypal_checkout.livePrivateApiKey');

        if ($paypalCheckoutMode == 'live') {
            $environment = new ProductionEnvironment($paypalCheckoutLiveClientID, $paypalCheckoutLiveClientSecret);
        } else {
            $environment = new SandboxEnvironment($paypalCheckoutTestClientID, $paypalCheckoutTestClientSecret);
        }

        // create a new client
        $client = new PayPalClient($environment);

        $request = $this->app->make(Request::class);

        $response = json_decode($request->getContent());

        // Get order id from database or request
        $order_id = $response->orderID;

        // Create an order show http request
        $request = new OrderCaptureRequest($order_id);

        // Send request to PayPal
        $response = $client->send($request);

        // Get results
        $result = json_decode($response->getBody()->getContents());

        $pm = PaymentMethod::getByHandle('community_store_paypal_checkout');
        $order = \Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order::add($pm, $result->purchase_units[0]->payments->captures[0]->id);

        // unset the shipping type, as next order might be unshippable
        Session::set('community_store.smID', '');
        Session::set('notes', '');
        //return Redirect::to($order->getOrderCompleteDestination());

        $redirect = Url::to($order->getOrderCompleteDestination()) . '';

        return new JsonResponse(['redirect'=>$redirect]);
    }

}


return __NAMESPACE__;
