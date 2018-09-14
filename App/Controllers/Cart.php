<?php

namespace App\Controllers;

use \App\Models\Trseries;
use \App\Models\Gtoflx;
use \App\Models\Stingray;
use \App\Models\Holster;
use \App\Models\Battery;
use \App\Models\Toolkit;
use \App\Models\Accessory;
use \App\Models\Flx;
use \App\Models\Customer;
use \App\Models\Guest;
use \App\Models\Caller;
use \App\Models\State;
use \App\Models\Paypal;
use \App\Models\Ordoro;
use \App\Models\Endicia;
use \App\Models\Ups;
use \App\Models\Order;
use \App\Models\Dealer;
use \App\Models\Partner;
use \App\Config;
use \App\Mail;
use \Core\View;


class Cart extends \Core\Controller
{
    // properties
    private $salesTax = 0;
    private $florida_sales_tax_rate = .06;
    private $subtotal = 0;
    private $pretax_total = 0;
    private $grand_total = 0;
    private $shipping = 0;
    private $free_shipping = 0;
    private $free_shipping_minimum = 100;
    private $priority_mail = 7.50;
    private $numberOfItems = 0;

    // private $sales_tax_county = '';
    // private $sales_tax_county_rate = 0.00;
    // private $sales_tax_combined_rate = 0.00;

    // private $total_weight = 0;
    private $item = [];
    private $newItem = [];
    private $cartContent = [];
    private $cartMetaData = [];
    private $stdBoxDimensions = [
        'length' => 6,
        'width'  => 4,
        'height' => 4
    ];
    private $priorityMailRetail = Config::PRIORITY;
    private $upsGroundRetail = Config::UPSGROUND;
    private $upsThreeDaySelectRetail = Config::UPS3DAYSELECT;
    private $upsSecondDayAirRetail = Config::UPS2NDDAYAIR;



    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = //

    /**
     * adds item to shopping cart
     *
     * @return View         the show battery page
     */
    public function addToCart()
    {
        // test
        // echo 'Connected to AddToCart() method in Cart controller';
        // echo '<pre>';
        // print_r($_SERVER);
        // echo '</pre>';
        // exit();

        // store referer URL in variable
        $refererURL = $_SERVER['HTTP_REFERER'];

        // retrieve params
        $mvc_model      = $this->route_params['mvcModel'];
        $id             = $this->route_params['id'];
        $pistolMfr      = $this->route_params['pistolMfr'];
        $trseries_model = $this->route_params['laserModel'];
        // $tableName = ucfirst($this->route_params['laserSeries']);

        // test
        // echo $refererURL . '<br>';
        // echo 'Model: ' . $mvc_model . '<br>';
        // echo $id . '<br>';
        // echo $pistolMfr . '<br>';
        // echo $trseries_model . '<br>';
        // echo $_SESSION['cart_count'] . '<br>';
        // exit();

        // determine query by value of $mvc_model
        switch ($mvc_model) {
            case 'trseries':
                $this->item = Trseries::getTrseriesDetailsForCart($id);
                break;
            case 'gtoflx':
                $this->item = Gtoflx::getGtoflxDetailsForCart($id);
                break;
            case 'stingray':
                $this->item = Stingray::getStingrayDetailsForCart($id, $pistolMfr);
                break;
            case 'holster':
                $this->item = Holster::getHolsterDetailsForCart($id, $trseries_model);
                break;
            case 'battery':
                $this->item = Battery::getBattery($id);
                break;
            case 'toolkit':
                $this->item = Toolkit::getToolkit($id);
                break;
            case 'accessory':
                $this->item = Accessory::getAccessory($id);
                break;
            case 'flx':
                $this->item = Flx::getFlx($id);
                break;
            default:
                echo "Error with value of table in switch statement.";
        }

        // test
        // echo "Data from query";
        // echo '<pre>';
        // print_r($this->item);
        // echo '</pre>';
        // exit();

        // set starting quanity value
        $quantity = 1;

        /* - - - - - - - lasers  - - - - - - - - */
        if ($mvc_model == 'trseries' || $mvc_model == 'gtoflx' || $mvc_model == 'stingray')
        {
            if(isset($_SESSION['cart'][$this->item->id]))
            {
                View::renderTemplate('Error/index.html', [
                    'errorMessage' => 'Item ('.strtoupper($this->item->series) . ' ' . strtoupper($this->item->model).')
                    already in cart. You can modify quantity from inside the
                    <a style="color:blue;" href="/cart/view/shopping-cart">cart</a>'
                ]);
                exit();
            }
            else
            {
                // store items in array
                $this->newItem = [
                    'id'            => $this->item->id,
                    'name'          => $this->item->name,
                    'thumb'         => $this->item->thumb,
                    'series'        => $this->item->series,
                    'model'         => $this->item->model,
                    'beam'          => $this->item->beam,
                    'quantity'      => $quantity,
                    'price'         => $this->item->price,
                    'pistolMfr'     => $this->item->pistolMfr,
                    'pistol_models' => $this->item->pistol_models,
                    'mvc_model'     => $mvc_model,
                    'weight'        => $this->item->weight
                ];
            }
        }

        /* - - - - - - - holsters  - - - - - - - - */
        if ($mvc_model == 'holster')
        {
            if(isset($_SESSION['cart'][$this->item->id]))
            {
                View::renderTemplate('Error/index.html', [
                    'errorMessage' => 'Item ('.strtoupper($this->item->holsterMfr) . ' ' . strtoupper($this->item->holster_model).') already in cart. You can modify
                    quantity from inside the <a style="color:blue;" href="/cart/view/shopping-cart">cart</a>'
                ]);
                exit();
            }
            if ($mvc_model == 'holster')
            {
                // store items in array
                $this->newItem = [
                    'id'            => $this->item->id,
                    'name'          => $this->item->name,
                    'holsterMfr'    => $this->item->holsterMfr,
                    'model'         => $this->item->holster_model,
                    'waist'         => $this->item->waist,
                    'hand'          => $this->item->hand,
                    'quantity'      => $quantity,
                    'price'         => $this->item->price,
                    'thumb'         => $this->item->thumb,
                    'trseriesModel' => $this->item->trseries_model,
                    'pistolMfr'     => $this->item->pistolMfr,
                    'pistol_models' => $this->item->pistol_models,
                    'mvc_model'     => $mvc_model,
                    'weight'        => $this->item->weight
                ];
            }
        }


        /* - - - - - - - batteries  - - - - - - - - */
        if ($mvc_model == 'battery' || $mvc_model == 'toolkit' || $mvc_model == 'accessory')
        {
            if(isset($_SESSION['cart'][$this->item->id]))
            {
                View::renderTemplate('Error/index.html', [
                    'errorMessage' => 'Item ('.strtoupper($this->item->name) .') already in cart. You can modify
                    quantity from inside the <a style="color:blue;" href="/cart/view/shopping-cart">cart</a>'
                ]);
                exit();
            }
            if ($mvc_model == 'battery' || $mvc_model == 'toolkit' || $mvc_model == 'accessory')
            {
                // store items in array
                $this->newItem = [
                    'id'          => $this->item->id,
                    'name'        => $this->item->name,
                    'description' => $this->item->description,
                    'quantity'    => $quantity,
                    'price'       => $this->item->price,
                    'thumb'       => $this->item->image_thumb,
                    'mvc_model'   => $mvc_model,
                    'weight'      => $this->item->weight
                ];
            }
        }


        /* - - - - - - - flx  - - - - - - - - */
        if ($mvc_model == 'flx')
        {
            if(isset($_SESSION['cart'][$this->item->id]))
            {
                View::renderTemplate('Error/index.html', [
                    'errorMessage' => 'Item ('.strtoupper($this->item->flx_model) . ') already in cart. You can modify
                    quantity from inside the <a style="color:blue;" href="/cart/view/shopping-cart">cart</a>'
                ]);
                exit();
            }
            else
            {
                // store items in array
                $this->newItem = [
                    'id'            => $this->item->id,
                    'name'          => $this->item->name,
                    'thumb'         => $this->item->thumb,
                    'model'         => $this->item->flx_model,
                    'quantity'      => $quantity,
                    'price'         => $this->item->price,
                    'pistolMfr'     => $this->item->pistolMfr,
                    'pistol_models' => $this->item->pistol_models,
                    'mvc_model'     => $mvc_model,
                    'weight'        => $this->item->weight
                ];
            }
        }

        // test
        // echo 'newitem array';
        // echo '<pre>';
        // print_r($this->newItem);
        // echo '</pre>';
        // exit();

        // add new item to SESSION cart
        $_SESSION['cart'][$this->item->id] = $this->newItem;

        // test
        // echo 'SESSION cart array.';
        // echo '<pre>';
        // print_r($_SESSION['cart']);
        // echo '</pre>';
        // exit();

        // get count (best to use TWIG for count, e.g. {{ session.cart|length }})
        $_SESSION['cart_count'] = count($_SESSION['cart']);

        // test
        // echo 'Cart count: ' . $_SESSION['cart_count'] . '<br>';
        // echo 'SESSION array.';
        // echo '<pre>';
        // print_r($_SESSION);
        // echo '</pre>';
        // exit();

        // add query string to referer URL
        $refererURL = $refererURL . '?id=itemadded';

        header("Location: $refererURL");
        exit();
    }





    /**
     * Displays content of $_SESSION['cart'] in view
     *
     * @return View
     */
    public function viewCart()
    {
        // test
        // echo 'SESSION cart array:';
        // echo '<pre>';
        // print_r($_SESSION['cart']);
        // echo '</pre>';
        // exit();

        // get cart meta data
        $cartMetaData = $this->getCartMetaData();

        // test
        // echo 'Cart meta data: ';
        // echo '<pre>';
        // print_r($cartMetaData);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Cart/index.html', [
            'cartContent'  => $_SESSION['cart'],
            'cartMetaData' => $cartMetaData,
            'page_title'   => 'Shopping Cart',
            'checkout_btn' => 'show'
        ]);
    }



    /**
     * Updates item quantity in $_SESSION['cart']
     *
     * @return View
     */
    public function updateQuantity()
    {
        // retrieve form data
        $newQty = (isset($_REQUEST['new_quantity']) ? filter_var($_REQUEST['new_quantity'], FILTER_SANITIZE_NUMBER_INT) : '');
        $id = (isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT) : '');
        $series = (isset($_REQUEST['series']) ? filter_var($_REQUEST['series'], FILTER_SANITIZE_STRING) : '');
        $model = (isset($_REQUEST['model']) ? filter_var($_REQUEST['model'], FILTER_SANITIZE_STRING) : '');

        // test
        // echo '<h4>Quantity change:</h4>';
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        // test
        // echo '<h4>SESSION cart array before change.</h4>';
        // echo '<pre>';
        // print_r($_SESSION['cart']);
        // echo '</pre>';
        // exit();

        // loop thru array to find item being updated
        foreach($_SESSION['cart'] as $item)
        {
            if ($item['id'] == $id) {
                // update quantity
                $_SESSION['cart'][$id]['quantity'] = $newQty;
            }
        }

        // test
        // echo '<h4>Updated SESSION cart array.</h4>';
        // echo '<pre>';
        // print_r($_SESSION['cart']);
        // echo '</pre>';
        // exit();

        // get updated cart meta data
        $cartMetaData = $this->getCartMetaData();

        // test
        // echo '<h4>Updated cart meta data: </h4>';
        // echo '<pre>';
        // print_r($cartMetaData);
        // echo '</pre>';
        // exit();

        // render view
        View::renderTemplate('Cart/index.html', [
            'cartContent'  => $_SESSION['cart'],
            'cartMetaData' => $cartMetaData,
            'page_title'   => 'Shopping Cart',
            'checkout_btn' => 'show'
        ]);
    }




    /**
     * Deletes item from $_SESSION['cart']
     *
     * @return [type] [description]
     */
    public function deleteItem()
    {
        // retrieve form data
        $id = (isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT) : '');

        // remove item from array
        unset($_SESSION['cart'][$id]);

        // unset item from dealer/partner cart
        if (isset($_SESSION['ids']))
        {
            // find position of deleted ID in $_SESSION['ids']
            $pos = array_search($id, $_SESSION['ids']);

            // remove from array
            unset($_SESSION['ids'][$pos]);
        }

        // view updated cart
        header('Location: /cart/view/shopping-cart');
        exit();
    }




    /**
     * Directs logged in user to checkout page, or directs non-logged in
     * user to checkout options page
     *
     * @return View
     */
    public function checkoutOptions()
    {
        // get cart meta data (pretax total & number of items)
        $cartMetaData = $this->getCartMetaData();

        // test
        // echo '<h4>Cart:</h4>';
        // echo '<pre>';
        // print_r($_SESSION['cart']);
        // echo '</pre>';
        // echo '<h4>Cart meta data:</h4>';
        // echo '<pre>';
        // print_r($cartMetaData);
        // echo '</pre>';
        // exit();

        // cart has content
        if ($_SESSION['cart_count'] > 0)
        {
            // Admin user entering telephone order
            if (isset($_SESSION['user_id']) && $_SESSION['access_level'] == 4)
            {
                // checkout options page
                View::renderTemplate('Cart/checkout-options.html', [
                    'cartContent'    => $_SESSION['cart'],
                    'cartMetaData'   => $cartMetaData,
                    'page_title'     => 'Checkout',
                    'checkoutguest'  => 'true'
                ]);
                exit();
            }

            // logged in Dealer
            if (isset($_SESSION['user_id']) && $_SESSION['userType'] == 'dealer')
            {

                // get dealer data
                $dealer = Dealer::getDealer($_SESSION['user_id']);

                // test
                // echo '<h4>Dealer data:</h4>';
                // echo '<pre>';
                // print_r($dealer);
                // echo '</pre>';
                // exit();

                // get states
                $states = State::getStates();

                // checkout options page
                View::renderTemplate('Cart/checkout-dealer.html', [
                    'customer'       => $dealer,
                    'states'         => $states,
                    'cartContent'    => $_SESSION['cart'],
                    'cartMetaData'   => $cartMetaData,
                    'page_title'     => 'Checkout - Dealer',
                    'checkoutdealer' => 'true'
                ]);
                exit();
            }

            // logged in Partner
            if (isset($_SESSION['user_id']) && $_SESSION['userType'] == 'partner')
            {

                // get partner data
                $partner = Partner::getPartner($_SESSION['user_id']);

                // test
                // echo '<h4>Partner data:</h4>';
                // echo '<pre>';
                // print_r($partner);
                // echo '</pre>';
                // exit();

                // get states
                $states = State::getStates();

                // checkout options page
                View::renderTemplate('Cart/checkout-partner.html', [
                    'customer'        => $partner,
                    'states'          => $states,
                    'cartContent'     => $_SESSION['cart'],
                    'cartMetaData'    => $cartMetaData,
                    'page_title'      => 'Checkout - Dealer',
                    'checkoutpartner' => 'true'
                ]);
                exit();
            }


            // logged in user
            if (isset($_SESSION['user_id']))
            {
                // get customer data
                $customer = Customer::getCustomer($_SESSION['user_id']);

                // get states
                $states = State::getStates();

                // checkout page
                View::renderTemplate('Cart/checkout.html', [
                    'customer'     => $customer,
                    'states'       => $states,
                    'cartContent'  => $_SESSION['cart'],
                    'cartMetaData' => $cartMetaData,
                    'page_title'   => 'Checkout',
                    'checkout'     => 'true'
                ]);
            }
            // not logged in user
            else
            {
                // checkout options page
                View::renderTemplate('Cart/checkout-options.html', [
                    'cartContent'    => $_SESSION['cart'],
                    'cartMetaData'   => $cartMetaData,
                    'page_title'     => 'Checkout',
                    'checkoutguest'  => 'true'
                ]);
            }
        }
        // cart is empty
        else
        {
            // send to view cart page
            header("Location: /cart/view/shopping-cart");
            exit();
        }
    }



    /**
     * displays checkout page for non-registered 'guest' buyer
     *
     * @return View
     */
    public function guestCheckout()
    {
        // cart has content
        if ($_SESSION['cart'])
        {
            // get cart meta data
            $cartMetaData= $this->getCartMetaData();

            // get states
            $states = State::getStates();

            // checkout page (for guest)
            View::renderTemplate('Cart/checkout.html', [
                'states'        => $states,
                'cartContent'   => $_SESSION['cart'],
                'cartMetaData'  => $cartMetaData,
                'page_title'    => 'Guest Checkout',
                'checkoutguest' => 'true'
            ]);
        }
        // cart empty
        else
        {
            header("Location: /cart/view/shopping-cart");
            exit();
        }
    }




    /**
     * displays checkout page for entering orders internally from telephone calls
     *
     * @return View
     */
    public function internalCheckout()
    {
        // cart has content
        if ($_SESSION['cart'])
        {
            // get cart meta data
            $cartMetaData= $this->getCartMetaData();

            // get states
            $states = State::getStates();

            // checkout page (for guest)
            View::renderTemplate('Cart/checkout.html', [
                'states'        => $states,
                'cartContent'   => $_SESSION['cart'],
                'cartMetaData'  => $cartMetaData,
                'page_title'    => 'Telephone Order Checkout'
            ]);
        }
        // cart empty
        else
        {
            header("Location: /cart/view/shopping-cart");
            exit();
        }
    }




    /**
     * retrieves form data and returns final cost summary view
     *
     * @return  View   The cart content and cart metadata
     */
    public function checkoutCalculate()
    {
        // echo "Connected to checkoutCaluculate()!"; exit();

        // retrieve form data
        $billing_firstname = (isset($_REQUEST['billing_firstname'])) ? filter_var($_REQUEST['billing_firstname'], FILTER_SANITIZE_STRING) : '';
        $billing_lastname = (isset($_REQUEST['billing_lastname'])) ? filter_var($_REQUEST['billing_lastname'], FILTER_SANITIZE_STRING) : '';
        $billing_company = (isset($_REQUEST['billing_company'])) ? filter_var($_REQUEST['billing_company'], FILTER_SANITIZE_STRING) : '';
        $billing_phone = (isset($_REQUEST['billing_phone'])) ? filter_var($_REQUEST['billing_phone'], FILTER_SANITIZE_STRING) : '';
        $billing_address = (isset($_REQUEST['billing_address'])) ? filter_var($_REQUEST['billing_address'], FILTER_SANITIZE_STRING) : '';
        $billing_address2 = (isset($_REQUEST['billing_address2'])) ? filter_var($_REQUEST['billing_address2'], FILTER_SANITIZE_STRING) : '';
        $billing_city = (isset($_REQUEST['billing_city'])) ? filter_var($_REQUEST['billing_city'], FILTER_SANITIZE_STRING) : '';
        $billing_state = (isset($_REQUEST['billing_state'])) ? filter_var($_REQUEST['billing_state'], FILTER_SANITIZE_STRING) : '';
        $billing_zip = (isset($_REQUEST['billing_zip'])) ? filter_var($_REQUEST['billing_zip'], FILTER_SANITIZE_STRING) : '';

        $email = (isset($_REQUEST['email'])) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_STRING) : '';
        $shipping_firstname = (isset($_REQUEST['shipping_firstname'])) ? filter_var($_REQUEST['shipping_firstname'], FILTER_SANITIZE_STRING) : '';
        $shipping_lastname = (isset($_REQUEST['shipping_lastname'])) ? filter_var($_REQUEST['shipping_lastname'], FILTER_SANITIZE_STRING) : '';
        $shipping_company = (isset($_REQUEST['shipping_company'])) ? filter_var($_REQUEST['shipping_company'], FILTER_SANITIZE_STRING) : '';
        $shipping_phone = (isset($_REQUEST['shipping_phone'])) ? filter_var($_REQUEST['shipping_phone'], FILTER_SANITIZE_STRING) : '';
        $shipping_address = (isset($_REQUEST['shipping_address'])) ? filter_var($_REQUEST['shipping_address'], FILTER_SANITIZE_STRING) : '';
        $shipping_address2 = (isset($_REQUEST['shipping_address2'])) ? filter_var($_REQUEST['shipping_address2'], FILTER_SANITIZE_STRING) : '';
        $shipping_city = (isset($_REQUEST['shipping_city'])) ? filter_var($_REQUEST['shipping_city'], FILTER_SANITIZE_STRING) : '';
        $shipping_state = (isset($_REQUEST['shipping_state'])) ? filter_var($_REQUEST['shipping_state'], FILTER_SANITIZE_STRING) : '';
        $shipping_zip = (isset($_REQUEST['shipping_zip'])) ? filter_var($_REQUEST['shipping_zip'], FILTER_SANITIZE_STRING) : '';

        $shipping_method = (isset($_REQUEST['shipping_method'])) ? filter_var($_REQUEST['shipping_method'], FILTER_SANITIZE_STRING) : '';
        $shipping_instructions = (isset($_REQUEST['shipping_instructions'])) ? filter_var($_REQUEST['shipping_instructions'], FILTER_SANITIZE_STRING) : '';

        // shipping cost from hidden fields
        $free_shipping_cost = $_REQUEST['free_shipping_cost'];
        $priority_mail_cost = $_REQUEST['priority_mail_cost'];
        $ups_ground_cost = $_REQUEST['ups_ground_cost'];
        $ups_three_day_select_cost = $_REQUEST['ups_three_day_select_cost'];
        $ups_second_day_air_cost = $_REQUEST['ups_second_day_air_cost'];

        $ups_ground_shipment_digest           = (isset($_REQUEST['ups_ground_shipment_digest'])) ? $_REQUEST['ups_ground_shipment_digest'] : '';
        $ups_three_day_select_shipment_digest = (isset($_REQUEST['ups_three_day_select_shipment_digest'])) ? $_REQUEST['ups_three_day_select_shipment_digest'] : '';
        $ups_second_day_air_shipment_digest   = (isset($_REQUEST['ups_second_day_air_shipment_digest'])) ? $_REQUEST['ups_second_day_air_shipment_digest'] : '';

        // tracking numbers
        $upsGroundTrackingNumber = (isset($_REQUEST['ups_ground_tracking_number'])) ? $_REQUEST['ups_ground_tracking_number'] : '';
        $upsThreeDaySelectTrackingNumber = (isset($_REQUEST['ups_three_day_select_tracking_number'])) ? $_REQUEST['ups_three_day_select_tracking_number'] : '';
        $upsSecondDayAirTrackingNumber = (isset($_REQUEST['ups_second_day_air_tracking_number'])) ? $_REQUEST['ups_second_day_air_tracking_number'] : '';

        // test - display form data
        // echo 'Shipping method: ' . $shipping_method . '<br>';
        // echo '<h4>REQUEST array form data:</h4>';
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        // store UPS shipment digests in array
        $shipDigestsArray = [
            'ground'         => $ups_ground_shipment_digest,
            'threeDaySelect' => $ups_three_day_select_shipment_digest,
            'twoDayAir'      => $ups_second_day_air_shipment_digest
        ];

        // test - display $shipDigestArray
        // echo '<h4>shipDigestArray:</h4>';
        // echo '<pre>';
        // print_r($shipDigestArray);
        // echo '</pre>';
        // exit();

        // identify which UPS service was selected & store shipmentDigest
        if (!empty($shipDigestsArray))
        {
            foreach ($shipDigestsArray as $key => $value)
            {
                if ($value != '')
                {
                    $service = $key;

                    if ($service == 'ground')
                    {
                        $shipmentDigest = $value;
                    }
                    else if ($service == 'threeDaySelect')
                    {
                        $shipmentDigest = $value;
                    }
                    else if ($service == 'twoDayAir')
                    {
                        $shipmentDigest = $value;
                    }
                }
            }

            // store UPS shipmentDigest in global variable
            if (isset($shipmentDigest))
            {
                $_SESSION['shipment_digest'] = $shipmentDigest;
            }
        }

        // store tracking numbers in array
        $trackingNumbersArray = [
            'upsGroundTrackingNumber'         => $upsGroundTrackingNumber,
            'upsThreeDaySelectTrackingNumber' => $upsThreeDaySelectTrackingNumber,
            'upsSecondDayAirTrackingNumber'   => $upsSecondDayAirTrackingNumber
        ];

        // create variable to store tracking number for UPS (null for USPS - no ID supplied until label created)
        $trackingNumber = '';

        // identify UPS service selected and store tracking number in variable
        if (!empty($trackingNumbersArray))
        {
            foreach ($trackingNumbersArray as $key => $value)
            {
                if ($value != '')
                {
                    $service = $key;

                    if ($service == 'upsGroundTrackingNumber')
                    {
                        $trackingNumber = $value;
                    }
                    else if ($service == 'upsThreeDaySelectTrackingNumber')
                    {
                        $trackingNumber = $value;
                    }
                    else if ($service == 'upsSecondDayAirTrackingNumber')
                    {
                        $trackingNumber = $value;
                    }
                }
            }

            // store UPS tracking number in global variable
            if (isset($trackingNumber))
            {
                $_SESSION['trackingNumber'] = $trackingNumber;
            }
        }

        // create "pretty" shipping method desription for view
        switch ($shipping_method)
        {
            CASE 'First':
                $shipping_method = 'USPS First Class';
                $shipping_cost = 0;
                break;
            CASE 'Priority':
                $shipping_method = 'USPS Priority Mail';
                $shipping_cost = $this->priorityMailRetail;
                break;
            CASE 'UPS Ground':
                $shipping_method = 'UPS Ground';
                $shipping_cost = $this->upsGroundRetail;
                break;
            CASE 'UPS Three Day Select':
                $shipping_method = 'UPS 3 Day Select';
                $shipping_cost = $this->upsThreeDaySelectRetail;
                break;
            CASE 'UPS Second Day Air':
                $shipping_method = 'UPS 2nd Day Air';
                $shipping_cost = $this->upsSecondDayAirRetail;
                break;
            default:
            $shipping_method = 'error';
        }

        // test - review collected data
        // echo '<h4>Review collected data:</h4>';
        // echo 'Shipping method: ' . $shipping_method . '<br>';
        // echo 'Shipping cost: ' . $shipping_cost . '<br>';
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // echo $billing_firstname . '<br>';
        // echo $billing_lastname . '<br>';
        // echo $billing_company . '<br>';
        // echo $billing_phone . '<br>';
        // echo $billing_address . '<br>';
        // echo $billing_address2 . '<br>';
        // echo $billing_city . '<br>';
        // echo $billing_state . '<br>';
        // echo $billing_zip . '<br>';
        // echo $email . '<br>';
        // echo  '==================<br>';
        // echo $shipping_firstname . '<br>';
        // echo $shipping_lastname . '<br>';
        // echo $shipping_company . '<br>';
        // echo $shipping_phone . '<br>';
        // echo $shipping_address . '<br>';
        // echo $shipping_address2 . '<br>';
        // echo $shipping_city . '<br>';
        // echo $shipping_state . '<br>';
        // echo $shipping_zip . '<br>';
        // echo $shipping_method . '<br>';
        // exit();

        // store shipping method in global variable
        $_SESSION['shipping_method'] = $shipping_method;

        // get cart meta data (do not call more than once in a function -- it doubles the values!)
        $cartMetaData = $this->getCartMetaData();

        // test
        // echo '<h4>Cart metadata:</h4>';
        // echo '<pre>';
        // print_r($cartMetaData);
        // echo '</pre>';
        // exit();

        // store pretax total in SESSION array
        $_SESSION['pretaxTotal'] = $cartMetaData['pretax_total'];

        // calculate FL sales tax for shipments to Florida
        if ($shipping_state == 'FL')
        {
            // get sales tax rate
            $salesTaxArr = $this->getFloridaSalesTaxByZipcode($shipping_zip);

            // test
            // echo '<pre>';
            // print_r($salesTaxArr);
            // echo '</pre>';
            // exit();

            // store sales tax details in variables
            $sales_tax_state = strtolower($salesTaxArr['state']);
            $sales_tax_county = strtolower(str_replace('.', '', $salesTaxArr['county']));
            $sales_tax_county_rate = number_format($salesTaxArr['county_rate'], 3);
            $sales_tax_state_rate = number_format($salesTaxArr['state_rate'], 2);
            $sales_tax_combined_rate = number_format($salesTaxArr['combined_rate'], 3);

            // store sales tax data for order in SESSION array
            $_SESSION['sales_tax_data']['otax_total'] = number_format($cartMetaData['pretax_total'] * $sales_tax_combined_rate, 2, '.', ''); // PayPal format - no comma
            $_SESSION['sales_tax_data']['otax_state'] = $sales_tax_state;
            $_SESSION['sales_tax_data']['otax_state_amt'] = number_format($cartMetaData['pretax_total'] * $sales_tax_state_rate, 2);
            $_SESSION['sales_tax_data']['otax_county'] = $sales_tax_county;
            $_SESSION['sales_tax_data']['otax_county_amt']  = number_format($cartMetaData['pretax_total'] * $sales_tax_county_rate, 2);

            // test
            // echo '<h4>SESSION["sales_tax_data"]:</h4>';
            // echo '<pre>';
            // print_r($_SESSION['sales_tax_data']);
            // echo '</pre>';
            // exit();
        }
        // non-florida ship to
        else
        {
            $_SESSION['sales_tax_data']['otax_total'] = number_format(0, 2, '.', ''); // PayPal format - no comma
        }

        // store billing data in array
        $billing_data = [
            'billing_firstname' => $billing_firstname,
            'billing_lastname'  => $billing_lastname,
            'billing_company'   => $billing_company,
            'billing_phone'     => $billing_phone,
            'billing_address'   => $billing_address,
            'billing_address2'  => $billing_address2,
            'billing_city'      => $billing_city,
            'billing_state'     => $billing_state,
            'billing_zip'       => $billing_zip
        ];

        // store shipping data in array
        $shipping_data = [
            'shipping_firstname'    => $shipping_firstname,
            'shipping_lastname'     => $shipping_lastname,
            'shipping_company'      => $shipping_company,
            'shipping_phone'        => $shipping_phone,
            'shipping_address'      => $shipping_address,
            'shipping_address2'     => $shipping_address2,
            'shipping_city'         => $shipping_city,
            'shipping_state'        => $shipping_state,
            'shipping_zip'          => $shipping_zip,
            'shipping_instructions' => $shipping_instructions,
            'shipping_method'       => $shipping_method,
            'shipping_cost'         => number_format($shipping_cost, 2, '.', ''),
            'tracking_number'       => $trackingNumber
        ];

        // test
        // echo '<h4>Billing data array</h4>';
        // echo '<pre>';
        // print_r($billing_data);
        // echo '</pre>';
        // echo '<h4>Shipping data array</h4>';
        // echo '<pre>';
        // print_r($shipping_data);
        // echo '</pre>';
        // exit();

        // Cart is empty
        if ( $_SESSION['cart_count'] < 1)
        {
            {
                // view cart
                header("Location: /cart/view/shopping-cart");
                exit();
            }
        }
        // Cart has content
        else
        {
            // Logged in user but NOT logged in employee placing internal order //

            if (isset($_SESSION['user_id']) && $_SESSION['access_level'] != 4)
            {
                // test - display cart &
                // echo '<h4>Cart: </h4>';
                // echo '<pre>';
                // print_r($_SESSION['cart']);
                // echo '</pre>';
                // echo '<h4>Shipping data: </h4>';
                // echo '<pre>';
                // print_r($shipping_data);
                // echo '</pre>';
                // echo '<h4>Cart metadata: </h4>';
                // echo '<pre>';
                // print_r($cartMetaData);
                // echo '</pre>';
                // exit();

                // set variable as numeric
                $grandTotal = 0;

                // store grand total in variable for view
                $grandTotal = $cartMetaData['pretax_total'] + $shipping_cost + $_SESSION['sales_tax_data']['otax_total'];

                // update billing/shipping data in `customers` table
                $result =  Customer::updateBillingShippingInfo($_SESSION['user_id'], $billing_data, $shipping_data);

                // successful db update
                if ($result)
                {
                    // get updated customer data
                    $customer = Customer::getCustomer($_SESSION['user_id']);

                    // render order summary view
                    View::renderTemplate('Cart/order-summary.html', [
                        'page_title'      => 'Checkout',
                        'cartContent'     => $_SESSION['cart'],
                        'customer'        => $customer,
                        'cartMetaData'    => $cartMetaData,
                        'billing_data'    => $billing_data,
                        'shipping_data'   => $shipping_data,
                        'salesTax'        => $_SESSION['sales_tax_data']['otax_total'],
                        'grandTotal'      => $grandTotal
                    ]);
                }
                // error updating
                else
                {
                    View::renderTemplate('Error/index.html', [
                        'errorMessage' => 'Error updating billing info.'
                        // log error and send webmaster email about error
                    ]);
                    exit();
                }
            }
            // - - - - - - checkout telephone caller  - - - - - - - - - - - - //
            else if (isset($_SESSION['user_id']) && $_SESSION['access_level'] == 4)
            {
                // test
                // echo "<h4>Shopping cart:</h4>";
                // echo '<pre>';
                // print_r($_SESSION['cart']);
                // echo '</pre>';
                // echo "<h4>Cart metadata:</h4>";
                // echo '<pre>';
                // print_r($cartMetaData);
                // echo '</pre>';
                // echo "<h4>Billing data:</h4>";
                // echo '<pre>';
                // print_r($billing_data);
                // echo '</pre>';
                // echo "<h4>Shipping data:</h4>";
                // echo '<pre>';
                // print_r($shipping_data);
                // echo '</pre>';
                // echo 'Buyer email: ' . $email;
                // exit();

                // check if buyer's email exists in `customers` table
                $result = Customer::doesCustomerExist($email);

                // test
                // echo "<h4>Is user in customers table?</h4>";
                // if (empty($result)) {
                //     echo "Not in customers table.";
                // } else {
                //     echo "In customers table.";
                // }
                // exit();

                // customer exists
                if (!empty($result) && $result->active == 1)
                {
                    View::renderTemplate('Error/index.html', [
                        'errorMessage' => 'Customer is already registered. Please
                        <a href="/admin/login">Log In</a> to complete the purchase.'
                    ]);
                    exit();
                }
                // buyer is not in `callers` table
                else
                {
                    // check if in `callers` table
                    $caller = Caller::doesCallerExist($email);

                    // test
                    // echo "<h4>Is user in `callers` table?</h4>";
                    // if (empty($guest)) {
                    //     echo "Not in `callers` table.";
                    // } else {
                    //     echo "In `callers` table.";
                    // }
                    // exit();

                    // caller exists - update guest data for this purchase
                    if (!empty($caller))
                    {
                        // update caller billing/shipping data for new purchase
                        $result = Caller::updateBillingShippingInfo($caller->id, $billing_data, $shipping_data);

                        // success
                        if ($result)
                        {
                            // store returning guest ID in variable
                            $id = $caller->id;
                        }
                        else
                        {
                            echo "An error occurred updating callers table.";
                            // email webmaster
                            exit();
                        }
                    }
                    // caller is first-time buyer: add to `callers` table; get ID
                    else
                    {
                        // add new guest to `guests` table
                        $results = Caller::addCaller($email, $billing_data, $shipping_data);

                        // success
                        if ($results)
                        {
                            // store first-time guest ID in variable
                            $id = $results['id'];
                        }
                        else
                        {
                            echo "An error occurred inserting new data into callers table.";
                            // email webmaster
                            exit();
                        }
                    }
                    // get caller data (either returning caller or first-time caller)
                    $caller = Caller::getCaller($id);

                    // test
                    // echo '<pre>';
                    // print_r($caller);
                    // echo '</pre>';
                    // exit();

                    // store grand total in variable to pass to view
                    $grandTotal = $cartMetaData['pretax_total'] + $shipping_cost + $_SESSION['sales_tax_data']['otax_total'];
                }
                View::renderTemplate('Cart/order-summary.html', [
                    'page_title'      => 'Checkout Telephone Buyer',
                    'cartContent'     => $_SESSION['cart'],
                    'customer'        => $caller,
                    'cartMetaData'    => $cartMetaData,
                    'billing_data'    => $billing_data,
                    'shipping_data'   => $shipping_data,
                    'salesTax'        => $_SESSION['sales_tax_data']['otax_total'],
                    'grandTotal'      => $grandTotal,
                    'trackingNumber'  => $trackingNumber,
                    'checkoutcaller'   => 'true'
                ]);
            }
            // - - - - - - checkout as guest  - - - - - - - - - - - - - - //
            else
            {
                // test
                // echo "<h4>Shopping cart:</h4>";
                // echo '<pre>';
                // print_r($_SESSION['cart']);
                // echo '</pre>';
                // echo "<h4>Cart metadata:</h4>";
                // echo '<pre>';
                // print_r($cartMetaData);
                // echo '</pre>';
                // echo "<h4>Billing data:</h4>";
                // echo '<pre>';
                // print_r($billing_data);
                // echo '</pre>';
                // echo "<h4>Shipping data:</h4>";
                // echo '<pre>';
                // print_r($shipping_data);
                // echo '</pre>';
                // echo $email;
                // exit();

                // check if guest buyer's email exists in `customers` table
                $result = Customer::doesCustomerExist($email);

                // test
                // echo "<h4>Is user in customers table?</h4>";
                // if (empty($result)) {
                //     echo "Not in customers table.";
                // } else {
                //     echo "In customers table.";
                // }
                // exit();

                // customer exists
                if (!empty($result) && $result->active == 1)
                {
                    View::renderTemplate('Error/index.html', [
                        'errorMessage' => 'You are already registered. Please
                        <a href="/admin/login">Log In</a> to complete your purchase.'
                    ]);
                    exit();
                }
                // guest not in `customers` table
                else
                {
                    // check if in `guests` table
                    $guest = Guest::doesGuestExist($email);

                    // test
                    // echo "<h4>Is user in `guests` table?</h4>";
                    // if (empty($guest)) {
                    //     echo "Not in `guests` table.";
                    // } else {
                    //     echo "In `guests` table.";
                    // }
                    // exit();

                    // guest exists - update guest data for this purchase
                    if (!empty($guest))
                    {
                        // update guest billing/shipping data for new purchase
                        $result = Guest::updateBillingShippingInfo($guest->id, $billing_data, $shipping_data);

                        // success
                        if ($result)
                        {
                            // store returning guest ID in variable
                            $id = $guest->id;
                        }
                        else
                        {
                            echo "An error occurred updating guests table.";
                            // email webmaster
                            exit();
                        }
                    }
                    // guest is first-time buyer: add to `guests` table; get ID
                    else
                    {
                        // add new guest to `guests` table
                        $results = Guest::addGuest($email, $billing_data, $shipping_data);

                        // success
                        if ($results)
                        {
                            // store first-time guest ID in variable
                            $id = $results['id'];
                        }
                        else
                        {
                            echo "An error occurred inserting new data into guests table.";
                            // email webmaster
                            exit();
                        }
                    }

                    // get guest data (either returning guest or first-time guest)
                    $guest = Guest::getGuest($id);

                    // test
                    // echo '<pre>';
                    // print_r($guest);
                    // echo '</pre>';
                    // exit();

                    // store grand total in variable to pass to view
                    $grandTotal = $cartMetaData['pretax_total'] + $shipping_cost + $_SESSION['sales_tax_data']['otax_total'];
                }

                View::renderTemplate('Cart/order-summary.html', [
                    'page_title'      => 'Checkout',
                    'cartContent'     => $_SESSION['cart'],
                    'customer'        => $guest,
                    'cartMetaData'    => $cartMetaData,
                    'billing_data'    => $billing_data,
                    'shipping_data'   => $shipping_data,
                    'salesTax'        => $_SESSION['sales_tax_data']['otax_total'],
                    'grandTotal'      => $grandTotal,
                    'trackingNumber'  => $trackingNumber,
                    'checkoutguest'   => 'true'
                ]);
            }
        }
    }




    /**
     * retrieves form data and returns final cost summary view
     *
     * @return  View   The cart content and cart metadata
     */
    public function checkoutDealerCalculate()
    {
        // echo "Connected to checkoutDealerCalculate()!"; exit();

        // retrieve form data
        $dealer_firstname = (isset($_REQUEST['dealer_firstname'])) ? filter_var($_REQUEST['dealer_firstname'], FILTER_SANITIZE_STRING) : '';
        $dealer_lastname = (isset($_REQUEST['dealer_lastname'])) ? filter_var($_REQUEST['dealer_lastname'], FILTER_SANITIZE_STRING) : '';
        $dealer_company = (isset($_REQUEST['dealer_company'])) ? filter_var($_REQUEST['dealer_company'], FILTER_SANITIZE_STRING) : '';
        $dealer_phone = (isset($_REQUEST['dealer_phone'])) ? filter_var($_REQUEST['dealer_phone'], FILTER_SANITIZE_STRING) : '';
        $dealer_address = (isset($_REQUEST['dealer_address'])) ? filter_var($_REQUEST['dealer_address'], FILTER_SANITIZE_STRING) : '';
        $dealer_address2 = (isset($_REQUEST['dealer_address2'])) ? filter_var($_REQUEST['dealer_address2'], FILTER_SANITIZE_STRING) : '';
        $dealer_city = (isset($_REQUEST['dealer_city'])) ? filter_var($_REQUEST['dealer_city'], FILTER_SANITIZE_STRING) : '';
        $dealer_state = (isset($_REQUEST['dealer_state'])) ? filter_var($_REQUEST['dealer_state'], FILTER_SANITIZE_STRING) : '';
        $dealer_zip = (isset($_REQUEST['dealer_zip'])) ? filter_var($_REQUEST['dealer_zip'], FILTER_SANITIZE_STRING) : '';

        $email = (isset($_REQUEST['email'])) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_STRING) : '';

        $shipping_method = (isset($_REQUEST['shipping_method'])) ? filter_var($_REQUEST['shipping_method'], FILTER_SANITIZE_STRING) : '';
        $shipping_instructions = (isset($_REQUEST['shipping_instructions'])) ? filter_var($_REQUEST['shipping_instructions'], FILTER_SANITIZE_STRING) : '';

        // shipping cost from hidden fields
        $free_shipping_cost = $_REQUEST['free_shipping_cost'];
        $priority_mail_cost = $_REQUEST['priority_mail_cost'];
        $ups_ground_cost = $_REQUEST['ups_ground_cost'];
        $ups_three_day_select_cost = $_REQUEST['ups_three_day_select_cost'];
        $ups_second_day_air_cost = $_REQUEST['ups_second_day_air_cost'];

        $ups_ground_shipment_digest           = (isset($_REQUEST['ups_ground_shipment_digest'])) ? $_REQUEST['ups_ground_shipment_digest'] : '';
        $ups_three_day_select_shipment_digest = (isset($_REQUEST['ups_three_day_select_shipment_digest'])) ? $_REQUEST['ups_three_day_select_shipment_digest'] : '';
        $ups_second_day_air_shipment_digest   = (isset($_REQUEST['ups_second_day_air_shipment_digest'])) ? $_REQUEST['ups_second_day_air_shipment_digest'] : '';

        // tracking numbers
        $upsGroundTrackingNumber = (isset($_REQUEST['ups_ground_tracking_number'])) ? $_REQUEST['ups_ground_tracking_number'] : '';
        $upsThreeDaySelectTrackingNumber = (isset($_REQUEST['ups_three_day_select_tracking_number'])) ? $_REQUEST['ups_three_day_select_tracking_number'] : '';
        $upsSecondDayAirTrackingNumber = (isset($_REQUEST['ups_second_day_air_tracking_number'])) ? $_REQUEST['ups_second_day_air_tracking_number'] : '';

        // test - display form data
        // echo 'Shipping method: ' . $shipping_method . '<br>';
        // echo '<h4>REQUEST array form data:</h4>';
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        // store UPS shipment digests in array
        $shipDigestsArray = [
            'ground'         => $ups_ground_shipment_digest,
            'threeDaySelect' => $ups_three_day_select_shipment_digest,
            'twoDayAir'      => $ups_second_day_air_shipment_digest
        ];

        // test - display $shipDigestArray
        // echo '<h4>shipDigestArray:</h4>';
        // echo '<pre>';
        // print_r($shipDigestArray);
        // echo '</pre>';
        // exit();

        // identify which UPS service was selected & store shipmentDigest
        if (!empty($shipDigestsArray))
        {
            foreach ($shipDigestsArray as $key => $value)
            {
                if ($value != '')
                {
                    $service = $key;

                    if ($service == 'ground')
                    {
                        $shipmentDigest = $value;
                    }
                    else if ($service == 'threeDaySelect')
                    {
                        $shipmentDigest = $value;
                    }
                    else if ($service == 'twoDayAir')
                    {
                        $shipmentDigest = $value;
                    }
                }
            }

            // store UPS shipmentDigest in global variable
            if (isset($shipmentDigest))
            {
                $_SESSION['shipment_digest'] = $shipmentDigest;
            }
        }

        // store tracking numbers in array
        $trackingNumbersArray = [
            'upsGroundTrackingNumber'         => $upsGroundTrackingNumber,
            'upsThreeDaySelectTrackingNumber' => $upsThreeDaySelectTrackingNumber,
            'upsSecondDayAirTrackingNumber'   => $upsSecondDayAirTrackingNumber
        ];

        // create variable to store tracking number for UPS (null for USPS - no ID supplied until label created)
        $trackingNumber = '';

        // identify UPS service selected and store tracking number in variable
        if (!empty($trackingNumbersArray))
        {
            foreach ($trackingNumbersArray as $key => $value)
            {
                if ($value != '')
                {
                    $service = $key;

                    if ($service == 'upsGroundTrackingNumber')
                    {
                        $trackingNumber = $value;
                    }
                    else if ($service == 'upsThreeDaySelectTrackingNumber')
                    {
                        $trackingNumber = $value;
                    }
                    else if ($service == 'upsSecondDayAirTrackingNumber')
                    {
                        $trackingNumber = $value;
                    }
                }
            }

            // store UPS tracking number in global variable
            if (isset($trackingNumber))
            {
                $_SESSION['trackingNumber'] = $trackingNumber;
            }
        }

        // create "pretty" shipping method desription for view
        switch ($shipping_method)
        {
            CASE 'First':
                $shipping_method = 'USPS First Class';
                $shipping_cost = 0;
                break;
            CASE 'Priority':
                $shipping_method = 'USPS Priority Mail';
                $shipping_cost = $this->priorityMailRetail;
                break;
            CASE 'UPS Ground':
                $shipping_method = 'UPS Ground';
                $shipping_cost = $this->upsGroundRetail;
                break;
            CASE 'UPS Three Day Select':
                $shipping_method = 'UPS 3 Day Select';
                $shipping_cost = $this->upsThreeDaySelectRetail;
                break;
            CASE 'UPS Second Day Air':
                $shipping_method = 'UPS 2nd Day Air';
                $shipping_cost = $this->upsSecondDayAirRetail;
                break;
            default:
            $shipping_method = 'error';
        }

        // test - review collected data
        // echo '<h4>Review collected data:</h4>';
        // echo 'Shipping method: ' . $shipping_method . '<br>';
        // echo 'Shipping cost: ' . $shipping_cost . '<br>';
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // echo $dealer_firstname . '<br>';
        // echo $dealer_lastname . '<br>';
        // echo $dealer_company . '<br>';
        // echo $dealer_phone . '<br>';
        // echo $dealer_address . '<br>';
        // echo $dealer_address2 . '<br>';
        // echo $dealer_city . '<br>';
        // echo $dealer_state . '<br>';
        // echo $dealer_zip . '<br>';
        // echo $email . '<br>';
        // echo $shipping_method . '<br>';
        // echo  '==================<br>';
        // exit();

        // store shipping method in global variable
        $_SESSION['shipping_method'] = $shipping_method;

        // get cart meta data (do not call more than once in a function -- it doubles the values!)
        $cartMetaData = $this->getCartMetaData();

        // test
        // echo '<h4>Cart metadata:</h4>';
        // echo '<pre>';
        // print_r($cartMetaData);
        // echo '</pre>';
        // exit();

        // store pretax total in SESSION array
        $_SESSION['pretaxTotal'] = $cartMetaData['pretax_total'];

        // - - - - - Resellers are exempt from sales tax - - - - - - //

        // store sales tax data for order in SESSION array
        $_SESSION['sales_tax_data']['otax_total']      = number_format(0, 2, '.', ''); // PayPal format - no comma
        $_SESSION['sales_tax_data']['otax_state']      = number_format(0, 2, '.', ''); // PayPal format - no comma
        $_SESSION['sales_tax_data']['otax_state_amt']  = number_format(0, 2, '.', ''); // PayPal format - no comma
        $_SESSION['sales_tax_data']['otax_county']     = number_format(0, 2, '.', ''); // PayPal format - no comma
        $_SESSION['sales_tax_data']['otax_county_amt'] = number_format(0, 2, '.', ''); // PayPal format - no comma

        // store billing data in array
        $billing_data = [
            'billing_firstname' => $dealer_firstname,
            'billing_lastname'  => $dealer_lastname,
            'billing_company'   => $dealer_company,
            'billing_phone'     => $dealer_phone,
            'billing_address'   => $dealer_address,
            'billing_address2'  => $dealer_address2,
            'billing_city'      => $dealer_city,
            'billing_state'     => $dealer_state,
            'billing_zip'       => $dealer_zip
        ];

        // store shipping data in array
        $shipping_data = [
            'shipping_firstname'    => $dealer_firstname,
            'shipping_lastname'     => $dealer_lastname,
            'shipping_company'      => $dealer_company,
            'shipping_phone'        => $dealer_phone,
            'shipping_address'      => $dealer_address,
            'shipping_address2'     => $dealer_address2,
            'shipping_city'         => $dealer_city,
            'shipping_state'        => $dealer_state,
            'shipping_zip'          => $dealer_zip,
            'shipping_instructions' => $shipping_instructions,
            'shipping_method'       => $shipping_method,
            'shipping_cost'         => number_format($shipping_cost, 2, '.', ''),
            'tracking_number'       => $trackingNumber
        ];

        // test
        // echo '<h4>Billing data array</h4>';
        // echo '<pre>';
        // print_r($billing_data);
        // echo '</pre>';
        // echo '<h4>Shipping data array</h4>';
        // echo '<pre>';
        // print_r($shipping_data);
        // echo '</pre>';
        // exit();

        // Cart is empty
        if ( $_SESSION['cart_count'] < 1)
        {
            {
                // display empty cart
                header("Location: /cart/view/shopping-cart");
                exit();
            }
        }
        // Cart has content
        else
        {
            //  - - - -  Logged in dealer - - - - - - -  //

            if (isset($_SESSION['user_id']) && $_SESSION['userType'] == 'dealer')
            {
                // test - display cart &
                // echo '<h4>Cart: </h4>';
                // echo '<pre>';
                // print_r($_SESSION['cart']);
                // echo '</pre>';
                // echo '<h4>Shipping data: </h4>';
                // echo '<pre>';
                // print_r($shipping_data);
                // echo '</pre>';
                // echo '<h4>Cart metadata: </h4>';
                // echo '<pre>';
                // print_r($cartMetaData);
                // echo '</pre>';
                // exit();

                // set variable as numeric
                $grandTotal = 0;

                // store grand total in variable for view
                $grandTotal = $cartMetaData['pretax_total'] + $shipping_cost + $_SESSION['sales_tax_data']['otax_total'];

                // render order summary view
                View::renderTemplate('Cart/order-summary.html', [
                    'page_title'      => 'Checkout - Dealer',
                    'cartContent'     => $_SESSION['cart'],
                    'cartMetaData'    => $cartMetaData,
                    'billing_data'    => $billing_data,
                    'shipping_data'   => $shipping_data,
                    'salesTax'        => $_SESSION['sales_tax_data']['otax_total'],
                    'grandTotal'      => $grandTotal
                ]);
            }
        }
    }




    /**
     * retrieves form data and returns final cost summary view
     *
     * @return  View   The cart content and cart metadata
     */
    public function checkoutPartnerCalculate()
    {
        // echo "Connected to checkoutPartnerCalculate()!"; exit();

        // retrieve form data
        $partner_firstname = (isset($_REQUEST['partner_firstname'])) ? filter_var($_REQUEST['partner_firstname'], FILTER_SANITIZE_STRING) : '';
        $partner_lastname = (isset($_REQUEST['partner_lastname'])) ? filter_var($_REQUEST['partner_lastname'], FILTER_SANITIZE_STRING) : '';
        $partner_company = (isset($_REQUEST['partner_company'])) ? filter_var($_REQUEST['partner_company'], FILTER_SANITIZE_STRING) : '';
        $partner_phone = (isset($_REQUEST['partner_phone'])) ? filter_var($_REQUEST['partner_phone'], FILTER_SANITIZE_STRING) : '';
        $partner_address = (isset($_REQUEST['partner_address'])) ? filter_var($_REQUEST['partner_address'], FILTER_SANITIZE_STRING) : '';
        $partner_address2 = (isset($_REQUEST['partner_address2'])) ? filter_var($_REQUEST['partner_address2'], FILTER_SANITIZE_STRING) : '';
        $partner_city = (isset($_REQUEST['partner_city'])) ? filter_var($_REQUEST['partner_city'], FILTER_SANITIZE_STRING) : '';
        $partner_state = (isset($_REQUEST['partner_state'])) ? filter_var($_REQUEST['partner_state'], FILTER_SANITIZE_STRING) : '';
        $partner_zip = (isset($_REQUEST['partner_zip'])) ? filter_var($_REQUEST['partner_zip'], FILTER_SANITIZE_STRING) : '';

        $email = (isset($_REQUEST['partner_email'])) ? filter_var($_REQUEST['partner_email'], FILTER_SANITIZE_STRING) : '';
        $partner_shipping_firstname = (isset($_REQUEST['partner_shipping_firstname'])) ? filter_var($_REQUEST['partner_shipping_firstname'], FILTER_SANITIZE_STRING) : '';
        $partner_shipping_lastname = (isset($_REQUEST['partner_shipping_lastname'])) ? filter_var($_REQUEST['partner_shipping_lastname'], FILTER_SANITIZE_STRING) : '';
        $partner_shipping_company = (isset($_REQUEST['partner_shipping_company'])) ? filter_var($_REQUEST['partner_shipping_company'], FILTER_SANITIZE_STRING) : '';
        $partner_shipping_phone = (isset($_REQUEST['partner_shipping_phone'])) ? filter_var($_REQUEST['partner_shipping_phone'], FILTER_SANITIZE_STRING) : '';
        $partner_shipping_address = (isset($_REQUEST['partner_shipping_address'])) ? filter_var($_REQUEST['partner_shipping_address'], FILTER_SANITIZE_STRING) : '';
        $partner_shipping_city = (isset($_REQUEST['partner_shipping_city'])) ? filter_var($_REQUEST['partner_shipping_city'], FILTER_SANITIZE_STRING) : '';
        $partner_shipping_state = (isset($_REQUEST['partner_shipping_state'])) ? filter_var($_REQUEST['partner_shipping_state'], FILTER_SANITIZE_STRING) : '';
        $partner_shipping_zip = (isset($_REQUEST['partner_shipping_zip'])) ? filter_var($_REQUEST['partner_shipping_zip'], FILTER_SANITIZE_STRING) : '';

        $shipping_method = (isset($_REQUEST['shipping_method'])) ? filter_var($_REQUEST['shipping_method'], FILTER_SANITIZE_STRING) : '';
        // $shipping_instructions = (isset($_REQUEST['shipping_instructions'])) ? filter_var($_REQUEST['shipping_instructions'], FILTER_SANITIZE_STRING) : '';

        // shipping cost from hidden fields
        $free_shipping_cost = $_REQUEST['free_shipping_cost'];
        $priority_mail_cost = $_REQUEST['priority_mail_cost'];
        $ups_ground_cost = $_REQUEST['ups_ground_cost'];
        $ups_three_day_select_cost = $_REQUEST['ups_three_day_select_cost'];
        $ups_second_day_air_cost = $_REQUEST['ups_second_day_air_cost'];

        $ups_ground_shipment_digest           = (isset($_REQUEST['ups_ground_shipment_digest'])) ? $_REQUEST['ups_ground_shipment_digest'] : '';
        $ups_three_day_select_shipment_digest = (isset($_REQUEST['ups_three_day_select_shipment_digest'])) ? $_REQUEST['ups_three_day_select_shipment_digest'] : '';
        $ups_second_day_air_shipment_digest   = (isset($_REQUEST['ups_second_day_air_shipment_digest'])) ? $_REQUEST['ups_second_day_air_shipment_digest'] : '';

        // tracking numbers
        $upsGroundTrackingNumber = (isset($_REQUEST['ups_ground_tracking_number'])) ? $_REQUEST['ups_ground_tracking_number'] : '';
        $upsThreeDaySelectTrackingNumber = (isset($_REQUEST['ups_three_day_select_tracking_number'])) ? $_REQUEST['ups_three_day_select_tracking_number'] : '';
        $upsSecondDayAirTrackingNumber = (isset($_REQUEST['ups_second_day_air_tracking_number'])) ? $_REQUEST['ups_second_day_air_tracking_number'] : '';

        // test - display form data
        // echo 'Shipping method: ' . $shipping_method . '<br>';
        // echo '<h4>REQUEST array form data:</h4>';
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        // store UPS shipment digests in array
        $shipDigestsArray = [
            'ground'         => $ups_ground_shipment_digest,
            'threeDaySelect' => $ups_three_day_select_shipment_digest,
            'twoDayAir'      => $ups_second_day_air_shipment_digest
        ];

        // test - display $shipDigestArray
        // echo '<h4>shipDigestArray:</h4>';
        // echo '<pre>';
        // print_r($shipDigestArray);
        // echo '</pre>';
        // exit();

        // identify which UPS service was selected & store shipmentDigest
        if (!empty($shipDigestsArray))
        {
            foreach ($shipDigestsArray as $key => $value)
            {
                if ($value != '')
                {
                    $service = $key;

                    if ($service == 'ground')
                    {
                        $shipmentDigest = $value;
                    }
                    else if ($service == 'threeDaySelect')
                    {
                        $shipmentDigest = $value;
                    }
                    else if ($service == 'twoDayAir')
                    {
                        $shipmentDigest = $value;
                    }
                }
            }

            // store UPS shipmentDigest in global variable
            if (isset($shipmentDigest))
            {
                $_SESSION['shipment_digest'] = $shipmentDigest;
            }
        }

        // store tracking numbers in array
        $trackingNumbersArray = [
            'upsGroundTrackingNumber'         => $upsGroundTrackingNumber,
            'upsThreeDaySelectTrackingNumber' => $upsThreeDaySelectTrackingNumber,
            'upsSecondDayAirTrackingNumber'   => $upsSecondDayAirTrackingNumber
        ];

        // create variable to store tracking number for UPS (null for USPS - no ID supplied until label created)
        $trackingNumber = '';

        // identify UPS service selected and store tracking number in variable
        if (!empty($trackingNumbersArray))
        {
            foreach ($trackingNumbersArray as $key => $value)
            {
                if ($value != '')
                {
                    $service = $key;

                    if ($service == 'upsGroundTrackingNumber')
                    {
                        $trackingNumber = $value;
                    }
                    else if ($service == 'upsThreeDaySelectTrackingNumber')
                    {
                        $trackingNumber = $value;
                    }
                    else if ($service == 'upsSecondDayAirTrackingNumber')
                    {
                        $trackingNumber = $value;
                    }
                }
            }

            // store UPS tracking number in global variable
            if (isset($trackingNumber))
            {
                $_SESSION['trackingNumber'] = $trackingNumber;
            }
        }

        // create "pretty" shipping method desription for view
        switch ($shipping_method)
        {
            CASE 'First':
                $shipping_method = 'USPS First Class';
                $shipping_cost = 0;
                break;
            CASE 'Priority':
                $shipping_method = 'USPS Priority Mail';
                $shipping_cost = $this->priorityMailRetail;
                break;
            CASE 'UPS Ground':
                $shipping_method = 'UPS Ground';
                $shipping_cost = $this->upsGroundRetail;
                break;
            CASE 'UPS Three Day Select':
                $shipping_method = 'UPS 3 Day Select';
                $shipping_cost = $this->upsThreeDaySelectRetail;
                break;
            CASE 'UPS Second Day Air':
                $shipping_method = 'UPS 2nd Day Air';
                $shipping_cost = $this->upsSecondDayAirRetail;
                break;
            default:
            $shipping_method = 'error';
        }

        // test - review collected data
        // echo '<h4>Review collected data:</h4>';
        // echo 'Shipping method: ' . $shipping_method . '<br>';
        // echo 'Shipping cost: ' . $shipping_cost . '<br>';
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // echo $dealer_firstname . '<br>';
        // echo $dealer_lastname . '<br>';
        // echo $dealer_company . '<br>';
        // echo $dealer_phone . '<br>';
        // echo $dealer_address . '<br>';
        // echo $dealer_address2 . '<br>';
        // echo $dealer_city . '<br>';
        // echo $dealer_state . '<br>';
        // echo $dealer_zip . '<br>';
        // echo $email . '<br>';
        // echo $shipping_method . '<br>';
        // echo  '==================<br>';
        // exit();

        // store shipping method in global variable
        $_SESSION['shipping_method'] = $shipping_method;

        // get cart meta data (do not call more than once in a function -- it doubles the values!)
        $cartMetaData = $this->getCartMetaData();

        // test
        // echo '<h4>Cart metadata:</h4>';
        // echo '<pre>';
        // print_r($cartMetaData);
        // echo '</pre>';
        // exit();

        // store pretax total in SESSION array
        $_SESSION['pretaxTotal'] = $cartMetaData['pretax_total'];

        // - - - - - Resellers are exempt from sales tax - - - - - - //

        // store sales tax data for order in SESSION array
        $_SESSION['sales_tax_data']['otax_total']      = number_format(0, 2, '.', ''); // PayPal format - no comma
        $_SESSION['sales_tax_data']['otax_state']      = number_format(0, 2, '.', ''); // PayPal format - no comma
        $_SESSION['sales_tax_data']['otax_state_amt']  = number_format(0, 2, '.', ''); // PayPal format - no comma
        $_SESSION['sales_tax_data']['otax_county']     = number_format(0, 2, '.', ''); // PayPal format - no comma
        $_SESSION['sales_tax_data']['otax_county_amt'] = number_format(0, 2, '.', ''); // PayPal format - no comma

        // store billing data in array
        $billing_data = [
            'billing_firstname' => $partner_firstname,
            'billing_lastname'  => $partner_lastname,
            'billing_company'   => $partner_company,
            'billing_phone'     => $partner_phone,
            'billing_address'   => $partner_address,
            'billing_city'      => $partner_city,
            'billing_state'     => $partner_state,
            'billing_zip'       => $partner_zip
        ];

        // store shipping data in array
        $shipping_data = [
            'shipping_firstname'=> $partner_shipping_firstname,
            'shipping_lastname' => $partner_shipping_lastname,
            'shipping_company'  => $partner_shipping_company,
            'shipping_phone'    => $partner_shipping_phone,
            'shipping_address'  => $partner_shipping_address,
            'shipping_city'     => $partner_shipping_city,
            'shipping_state'    => $partner_shipping_state,
            'shipping_zip'      => $partner_shipping_zip,
            'shipping_method'   => $shipping_method,
            'shipping_cost'     => number_format($shipping_cost, 2, '.', ''),
            'tracking_number'   => $trackingNumber
        ];

        // test
        // echo '<h4>Billing data array</h4>';
        // echo '<pre>';
        // print_r($billing_data);
        // echo '</pre>';
        // echo '<h4>Shipping data array</h4>';
        // echo '<pre>';
        // print_r($shipping_data);
        // echo '</pre>';
        // exit();

        // Cart is empty
        if ( $_SESSION['cart_count'] < 1)
        {
            {
                // display empty cart
                header("Location: /cart/view/shopping-cart");
                exit();
            }
        }
        // Cart has content
        else
        {
            //  - - - -  Logged in partner - - - - - - -  //

            if (isset($_SESSION['user_id']) && $_SESSION['userType'] == 'partner')
            {
                // test - display cart &
                // echo '<h4>Cart: </h4>';
                // echo '<pre>';
                // print_r($_SESSION['cart']);
                // echo '</pre>';
                // echo '<h4>Shipping data: </h4>';
                // echo '<pre>';
                // print_r($shipping_data);
                // echo '</pre>';
                // echo '<h4>Cart metadata: </h4>';
                // echo '<pre>';
                // print_r($cartMetaData);
                // echo '</pre>';
                // exit();

                // set variable as numeric
                $grandTotal = 0;

                // store grand total in variable for view
                $grandTotal = $cartMetaData['pretax_total'] + $shipping_cost + $_SESSION['sales_tax_data']['otax_total'];

                // render order summary view
                View::renderTemplate('Cart/order-summary.html', [
                    'page_title'      => 'Checkout - Partner',
                    'cartContent'     => $_SESSION['cart'],
                    'cartMetaData'    => $cartMetaData,
                    'billing_data'    => $billing_data,
                    'shipping_data'   => $shipping_data,
                    'salesTax'        => $_SESSION['sales_tax_data']['otax_total'],
                    'grandTotal'      => $grandTotal
                ]);
            }
        }
    }




    /**
     * processes payment using PayPal Pro & executes other functions
     *
     * @return boolean   The success view or error
     */
    public function processPayment()
    {
        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

    // - - logged in user but not internal phone order payment processing - - //

        if(isset($_SESSION['user_id']) && $_SESSION['access_level'] != 4)
        {
            // process the payment through PayPal API
            $ppResults = Paypal::processPayment($_SESSION['user_id']);

            // test
            // echo 'User ID: ' . $_SESSION['user_id'] . '<br>';
            // echo '<h4>PayPal response:</h4>';
            // echo '<pre>';
            // print_r($ppResults);
            // echo '</pre>';
            // exit();


            // = = = =  Customer checking out  = = = = = = =  //
            if (isset($_SESSION['userType']) && $_SESSION['userType'] == 'customer')
            {
                // get customer
                $customer = Customer::getCustomer($_SESSION['user_id']);

                // test
                // echo 'In Cart controller.<br>';
                // echo '<pre>';
                // print_r($customer);
                // echo '</pre>';
                // exit();

                // failure -- order amt exceeds total purchase price ceiling (set in PayPal Manager)
                if (isset($ppResults['response']) && $ppResults['response']['RESULT'] == 126)
                {

                    // mail office and client re: alert
                    $result = Mail::fpsAlert($customer);

                    // mail successful
                    if ($result)
                    {
                        // unset SESSION variables
                        $x = $this->unsetSessionVariables();
                        // success
                        if ($x == true)
                        {
                            // store purchase total in variable
                            $amount = $results['response']['AMT'];

                            View::renderTemplate('Error/index.html', [
                                'errorMessage' => 'Purchase amount of
                                $' . number_format($amount, 2) . ' exceeds
                                website purchase limit. You should receive an automated
                                email shortly with instructions how to complete your
                                purchase.'
                            ]);
                        }
                        // failure to unset SESSION variables
                        else
                        {
                            View::renderTemplate('Error/index.html', [
                                'errorMessage' => 'An error occurred unsetting session
                                    variables.'
                            ]);
                            exit();
                        }
                    }
                    // mail failure
                    else
                    {
                        View::renderTemplate('Error/index.html', [
                            'errorMessage' => 'Error occurred sending email.'
                        ]);
                        exit();
                    }
                }
                // - - successful purchase under "purchase price ceiling limit" setting in PayPal Manager - - //
                else
                {
                    // get cart meta data
                    $cartMetaData = $this->getCartMetaData();

                    // test
                    // echo '<h4>Payflow results:</h4>';
                    // echo '<pre>';
                    // print_r($ppResults);
                    // echo '</pre>';
                    // echo '<h4>Shopping cart:</h4>';
                    // echo '<pre>';
                    // print_r($_SESSION['cart']);
                    // echo '</pre>';
                    // echo '<h4>Cart meta data:</h4>';
                    // echo '<pre>';
                    // print_r($cartMetaData);
                    // echo '</pre>';
                    // exit();

                    // store number of items in SESSION array
                    $_SESSION['numberOfItems'] = $cartMetaData['numberOfItems'];

                    // store PayPal transaction ID & total amount of order
                    $pnref = $ppResults['response']['PNREF'];
                    $totalAmt = $ppResults['response']['AMT'];

                    // store cart in variable
                    $cart = $_SESSION['cart'];

                    // store sales tax data in variable
                    $sales_tax_data = $_SESSION['sales_tax_data'];

                    // - - - store order in `orders` table - - - - - - - - //
                    $order = new Order();

                    $results = $order->insertOrder($buyer = 'customer', $customer, $cart, $totalAmt, $pnref, $sales_tax_data);

                    // test
                    // echo '<h4>Results []:</h4>';
                    // echo '<pre>';
                    // print_r($results);
                    // echo '</pre>';
                    // exit();

                    // db insertion failure
                    if ($results['result'] == false)
                    {
                        echo 'Error inserting order data into database';
                        // send email to webmaster
                        exit();
                    }
                    // db insertion success
                    else
                    {
                        // store shipping method in variable
                        $shipping_method = $_SESSION['shipping_method'];

                        // store $updatedCart array in variable
                        $updatedCart = $results['updatedCart'];

                        // test
                        // echo '<<h4>Updated cart:</h4>';
                        // echo '<pre>';
                        // print_r($updatedCart);
                        // echo '</pre>';
                        // exit();
                        // test - display table
                        // echo '<table>';
                        // echo '<tr>';
                        // echo '<th>qty</th>';
                        // echo '<th>Product</th>';
                        // echo '<th>Unit price</th>';
                        // echo '<th>Extended price</th>';
                        // echo '</tr>';
                        //     foreach ($updatedCart as    $value)
                        //     {
                        //         echo '<tr>';
                        //         echo '<td>'. $value['qty'] . '</td>';
                        //         echo '<td>'. $value['name'] . '</td>';
                        //         echo '<td>'. $value['unitprice'] . '</td>';
                        //         echo '<td>'. $value['itemtotal'] . '</td>';
                        //         echo '</tr>';
                        //     }
                        // echo '</table>';
                        // exit();

                        // send email to customer
                        $mailResult = Mail::sendOrderData($customer, $updatedCart, $shipping_method, $ppResults);

                        // mail success
                        if ($mailResult)
                        {
                            // test - display SESSION array
                            // echo '<h4>SESSION variable:</h4>';
                            // echo '<pre>';
                            // print_r($_SESSION);
                            // echo '</pre>';
                            // exit();

                            // unset session variables
                            $x = $this->unsetSessionVariables();

                            // success
                            if ($x == true)
                            {
                                // success view
                                View::renderTemplate('Success/index.html', [
                                    'first_name' => $customer->billing_firstname,
                                    'last_name'  => $customer->billing_lastname,
                                    'purchase'   => 'true'
                                ]);
                                exit();
                            }
                            else
                            {
                                // error view
                                View::renderTemplate('Error/index.html', [
                                    'errorMessage' => 'An error occurred unsetting session variables.'
                                ]);
                                exit();
                            }
                        }
                        // mail failure
                        else
                        {
                            // unset session variables
                            $x = $this->unsetSessionVariables();

                            // success
                            if ($x == true)
                            {
                                // error view
                                View::renderTemplate('Error/index.html', [
                                    'errorMessage'   => 'Your order was placed but an error occurred
                                        sending your confirmation email.'
                                ]);
                                exit();
                            }
                            // unset session variables function failure
                            else
                            {
                                // error view
                                View::renderTemplate('Error/index.html', [
                                    'errorMessage' => 'An error occurred unsetting session variables.'
                                ]);
                                exit();
                            }
                        }
                    }
                }
            }


            //  - - Dealer checking out - - - - - //
            if (isset($_SESSION['userType']) && $_SESSION['userType'] == 'dealer')
            {
                $dealer = Dealer::getDealer($_SESSION['user_id']);

                // test
                // echo 'Dealer data:<br>';
                // echo '<pre>';
                // print_r($dealer);
                // echo '</pre>';
                // exit();

                // failure -- order amt exceeds total purchase price ceiling (set in PayPal Manager)
                if (isset($ppResults['response']) && $ppResults['response']['RESULT'] == 126)
                {

                    // mail office and client re: alert
                    $result = Mail::fpsAlertDealer($dealer);

                    // mail successful
                    if ($result)
                    {
                        // unset SESSION variables
                        $x = $this->unsetSessionVariables();
                        // success
                        if ($x == true)
                        {
                            // store purchase total in variable
                            $amount = $results['response']['AMT'];

                            View::renderTemplate('Error/index.html', [
                                'errorMessage' => 'Purchase amount of
                                $' . number_format($amount, 2) . ' exceeds
                                website purchase limit. You should receive an automated
                                email shortly with instructions how to complete your
                                purchase.'
                            ]);
                        }
                        // failure to unset SESSION variables
                        else
                        {
                            View::renderTemplate('Error/index.html', [
                                'errorMessage' => 'An error occurred unsetting session
                                    variables.'
                            ]);
                            exit();
                        }
                    }
                    // mail failure
                    else
                    {
                        View::renderTemplate('Error/index.html', [
                            'errorMessage' => 'Error occurred sending email.'
                        ]);
                        exit();
                    }
                }
                // - - successful purchase under "purchase price ceiling limit" setting in PayPal Manager - - //
                else
                {
                    // get cart meta data
                    $cartMetaData = $this->getCartMetaData();

                    // test
                    // echo '<h4>Payflow results:</h4>';
                    // echo '<pre>';
                    // print_r($ppResults);
                    // echo '</pre>';
                    // echo '<h4>Shopping cart:</h4>';
                    // echo '<pre>';
                    // print_r($_SESSION['cart']);
                    // echo '</pre>';
                    // echo '<h4>Cart meta data:</h4>';
                    // echo '<pre>';
                    // print_r($cartMetaData);
                    // echo '</pre>';
                    // exit();

                    // store number of items in SESSION array
                    $_SESSION['numberOfItems'] = $cartMetaData['numberOfItems'];


                    // store PayPal transaction ID & total amount of order
                    $pnref = $ppResults['response']['PNREF'];
                    $totalAmt = $ppResults['response']['AMT'];

                    // store cart in variable
                    $cart = $_SESSION['cart'];

                    // store sales tax data in variable
                    $sales_tax_data = $_SESSION['sales_tax_data'];

                    // - - - store order in `orders` table - - - - - - - - //
                    $order = new Order();

                    $results = $order->insertOrder($buyer = 'dealer', $dealer, $cart, $totalAmt, $pnref, $sales_tax_data);

                    // test
                    // echo '<h4>Results []:</h4>';
                    // echo '<pre>';
                    // print_r($results);
                    // echo '</pre>';
                    // exit();

                    // db insertion failure
                    if ($results['result'] == false)
                    {
                        echo 'Error inserting order data into database';
                        // send email to webmaster
                        exit();
                    }
                    // db insertion success
                    else
                    {
                        // store shipping method in variable
                        $shipping_method = $_SESSION['shipping_method'];

                        // store $updatedCart array in variable
                        $updatedCart = $results['updatedCart'];

                        // test
                        // echo '<<h4>Updated cart:</h4>';
                        // echo '<pre>';
                        // print_r($updatedCart);
                        // echo '</pre>';
                        // exit();
                        // test - display table
                        // echo '<table>';
                        // echo '<tr>';
                        // echo '<th>qty</th>';
                        // echo '<th>Product</th>';
                        // echo '<th>Unit price</th>';
                        // echo '<th>Extended price</th>';
                        // echo '</tr>';
                        //     foreach ($updatedCart as    $value)
                        //     {
                        //         echo '<tr>';
                        //         echo '<td>'. $value['qty'] . '</td>';
                        //         echo '<td>'. $value['name'] . '</td>';
                        //         echo '<td>'. $value['unitprice'] . '</td>';
                        //         echo '<td>'. $value['itemtotal'] . '</td>';
                        //         echo '</tr>';
                        //     }
                        // echo '</table>';
                        // exit();

                        // send email to dealer
                        $mailResult = Mail::sendOrderDataToDealer($dealer, $updatedCart, $shipping_method, $ppResults);

                        // mail success
                        if ($mailResult)
                        {
                            // test - display SESSION array
                            // echo '<h4>SESSION variable:</h4>';
                            // echo '<pre>';
                            // print_r($_SESSION);
                            // echo '</pre>';
                            // exit();

                            // unset session variables
                            $x = $this->unsetSessionVariables();

                            // success
                            if ($x == true)
                            {
                                // success view
                                View::renderTemplate('Success/index.html', [
                                    'first_name' => $dealer->first_name,
                                    'last_name'  => $dealer->last_name,
                                    'purchase'   => 'true'
                                ]);
                                exit();
                            }
                            else
                            {
                                // error view
                                View::renderTemplate('Error/index.html', [
                                    'errorMessage' => 'An error occurred unsetting session variables.'
                                ]);
                                exit();
                            }
                        }
                        // mail failure
                        else
                        {
                            // unset session variables
                            $x = $this->unsetSessionVariables();

                            // success
                            if ($x == true)
                            {
                                // error view
                                View::renderTemplate('Error/index.html', [
                                    'errorMessage'   => 'Your order was placed but an error occurred
                                        sending your confirmation email.'
                                ]);
                                exit();
                            }
                            // unset session variables function failure
                            else
                            {
                                // error view
                                View::renderTemplate('Error/index.html', [
                                    'errorMessage' => 'An error occurred unsetting session variables.'
                                ]);
                                exit();
                            }
                        }
                    }
                }
            }


            //  - - Partner checking out - - - - - //
            if (isset($_SESSION['userType']) && $_SESSION['userType'] == 'partner')
            {
                $partner = Partner::getPartner($_SESSION['user_id']);

                // test
                // echo 'Partner data:<br>';
                // echo '<pre>';
                // print_r($partner);
                // echo '</pre>';
                // exit();

                // failure -- order amt exceeds total purchase price ceiling (set in PayPal Manager)
                if (isset($ppResults['response']) && $ppResults['response']['RESULT'] == 126)
                {

                    // mail office and client re: alert
                    $result = Mail::fpsAlertDealer($dealer);

                    // mail successful
                    if ($result)
                    {
                        // unset SESSION variables
                        $x = $this->unsetSessionVariables();
                        // success
                        if ($x == true)
                        {
                            // store purchase total in variable
                            $amount = $results['response']['AMT'];

                            View::renderTemplate('Error/index.html', [
                                'errorMessage' => 'Purchase amount of
                                $' . number_format($amount, 2) . ' exceeds
                                website purchase limit. You should receive an automated
                                email shortly with instructions how to complete your
                                purchase.'
                            ]);
                        }
                        // failure to unset SESSION variables
                        else
                        {
                            View::renderTemplate('Error/index.html', [
                                'errorMessage' => 'An error occurred unsetting session
                                    variables.'
                            ]);
                            exit();
                        }
                    }
                    // mail failure
                    else
                    {
                        View::renderTemplate('Error/index.html', [
                            'errorMessage' => 'Error occurred sending email.'
                        ]);
                        exit();
                    }
                }
                // - - successful purchase under "purchase price ceiling limit" setting in PayPal Manager - - //
                else
                {
                    // get cart meta data
                    $cartMetaData = $this->getCartMetaData();

                    // test
                    // echo '<h4>Payflow results:</h4>';
                    // echo '<pre>';
                    // print_r($ppResults);
                    // echo '</pre>';
                    // echo '<h4>Shopping cart:</h4>';
                    // echo '<pre>';
                    // print_r($_SESSION['cart']);
                    // echo '</pre>';
                    // echo '<h4>Cart meta data:</h4>';
                    // echo '<pre>';
                    // print_r($cartMetaData);
                    // echo '</pre>';
                    // exit();

                    // store number of items in SESSION array
                    $_SESSION['numberOfItems'] = $cartMetaData['numberOfItems'];


                    // store PayPal transaction ID & total amount of order
                    $pnref = $ppResults['response']['PNREF'];
                    $totalAmt = $ppResults['response']['AMT'];

                    // store cart in variable
                    $cart = $_SESSION['cart'];

                    // store sales tax data in variable
                    $sales_tax_data = $_SESSION['sales_tax_data'];

                    // - - - store order in `orders` table - - - - - - - - //
                    $order = new Order();

                    $results = $order->insertOrder($buyer = 'partner', $partner, $cart, $totalAmt, $pnref, $sales_tax_data);

                    // test
                    // echo '<h4>Results []:</h4>';
                    // echo '<pre>';
                    // print_r($results);
                    // echo '</pre>';
                    // exit();

                    // db insertion failure
                    if ($results['result'] == false)
                    {
                        echo 'Error inserting order data into database';
                        // send email to webmaster
                        exit();
                    }
                    // db insertion success
                    else
                    {
                        // store shipping method in variable
                        $shipping_method = $_SESSION['shipping_method'];

                        // store $updatedCart array in variable
                        $updatedCart = $results['updatedCart'];

                        // test
                        // echo '<<h4>Updated cart:</h4>';
                        // echo '<pre>';
                        // print_r($updatedCart);
                        // echo '</pre>';
                        // exit();
                        // test - display table
                        // echo '<table>';
                        // echo '<tr>';
                        // echo '<th>qty</th>';
                        // echo '<th>Product</th>';
                        // echo '<th>Unit price</th>';
                        // echo '<th>Extended price</th>';
                        // echo '</tr>';
                        //     foreach ($updatedCart as    $value)
                        //     {
                        //         echo '<tr>';
                        //         echo '<td>'. $value['qty'] . '</td>';
                        //         echo '<td>'. $value['name'] . '</td>';
                        //         echo '<td>'. $value['unitprice'] . '</td>';
                        //         echo '<td>'. $value['itemtotal'] . '</td>';
                        //         echo '</tr>';
                        //     }
                        // echo '</table>';
                        // exit();

                        // send email to dealer
                        $mailResult = Mail::sendOrderDataToPartner($partner, $updatedCart, $shipping_method, $ppResults);

                        // mail success
                        if ($mailResult)
                        {
                            // test - display SESSION array
                            // echo '<h4>SESSION variable:</h4>';
                            // echo '<pre>';
                            // print_r($_SESSION);
                            // echo '</pre>';
                            // exit();

                            // unset session variables
                            $x = $this->unsetSessionVariables();

                            // success
                            if ($x == true)
                            {
                                // success view
                                View::renderTemplate('Success/index.html', [
                                    'first_name' => $partner->first_name,
                                    'last_name'  => $partner->last_name,
                                    'purchase'   => 'true'
                                ]);
                                exit();
                            }
                            else
                            {
                                // error view
                                View::renderTemplate('Error/index.html', [
                                    'errorMessage' => 'An error occurred unsetting session variables.'
                                ]);
                                exit();
                            }
                        }
                        // mail failure
                        else
                        {
                            // unset session variables
                            $x = $this->unsetSessionVariables();

                            // success
                            if ($x == true)
                            {
                                // error view
                                View::renderTemplate('Error/index.html', [
                                    'errorMessage'   => 'Your order was placed but an error occurred
                                        sending your confirmation email.'
                                ]);
                                exit();
                            }
                            // unset session variables function failure
                            else
                            {
                                // error view
                                View::renderTemplate('Error/index.html', [
                                    'errorMessage' => 'An error occurred unsetting session variables.'
                                ]);
                                exit();
                            }
                        }
                    }
                }
            }
        } // end access_level != 4 (internal telephone order user)

        // - - - - - - - caller payment processing - - - - - - - - //
        else if (isset($_SESSION['user_id']) && $_SESSION['access_level'] == 4)
        {
            // process the payment & store response in variable
            $ppResults = Paypal::processPaymentGuest();

            // test
            // echo '<h4>Caller checkout PayPal response:</h4>';
            // echo '<pre>';
            // print_r($ppResults);
            // echo '</pre>';
            // exit();

            // get customer
            $caller = Caller::getCallerByEmail($ppResults['email']);

            // test
            // echo '<h4>Caller:</h4>';
            // echo '<pre>';
            // print_r($caller);
            // echo '</pre>';
            // exit();

            // failure -- order amt exceeds total purchase price ceiling
            if (isset($ppResults['response']) && $ppResults['response']['RESULT'] == 126)
            {

                // mail office and client re: alert
                $result = Mail::fpsAlert($guest);

                // mail successful
                if ($result)
                {
                    // unset SESSION variables
                    $x = $this->unsetSessionVariables();
                    // success
                    if ($x == true)
                    {
                        $amount = $results['response']['AMT'];

                        View::renderTemplate('Error/index.html', [
                            'errorMessage' => 'Purchase amount of
                            $' . number_format($amount, 2) . ' exceeds
                            website purchase limit. You should receive an automated
                            email shortly with instructions how to complete your
                            purchase.'
                        ]);
                    }
                    // failure unsetting SESSION variables
                    else
                    {
                        View::renderTemplate('Error/index.html', [
                            'errorMessage' => 'An error occurred unsetting session variables.'
                        ]);
                        exit();
                    }
                }
                // mail failure
                else
                {
                    View::renderTemplate('Error/index.html', [
                        'errorMessage' => 'Error occurred sending email.'
                    ]);
                    exit();
                }
            }

            // - - successful purchase within purchase price ceiling limit - - //
            else
            {
                // get cart meta data
                $cartMetaData = $this->getCartMetaData();

                // test
                // echo '<h4>Payflow results:</h4>';
                // echo '<pre>';
                // print_r($ppResults);
                // echo '</pre>';
                // echo '<h4>Shopping cart:</h4>';
                // echo '<pre>';
                // print_r($_SESSION['cart']);
                // echo '</pre>';
                // echo '<h4>Cart meta data:</h4>';
                // echo '<pre>';
                // print_r($cartMetaData);
                // echo '</pre>';
                // exit();

                // store number of items in SESSION array
                $_SESSION['numberOfItems'] = $cartMetaData['numberOfItems'];

                // store PayPal transaction ID & total amount of order
                $pnref = $ppResults['response']['PNREF'];
                $totalAmt = $ppResults['response']['AMT'];

                // store cart in variable
                $cart = $_SESSION['cart'];

                // store sales tax data in variable
                $sales_tax_data = $_SESSION['sales_tax_data'];

                // - - - store order in `orders` table - - - - - - - - //
                $order = new Order();

                $results = $order->insertOrder($buyer = 'caller', $caller, $cart, $totalAmt, $pnref, $sales_tax_data);

                // db insertion failure
                if ($results['result'] == false)
                {
                    echo 'Error inserting order data into database';
                    // send email to webmaster
                    exit();
                }
                // db insertion success
                else
                {
                    // store shipping method in variable
                    $shipping_method = $_SESSION['shipping_method'];

                    // store $updatedCart array in variable
                    $updatedCart = $results['updatedCart'];

                    // test
                    // echo '<<h4>Updated cart:</h4>';
                    // echo '<pre>';
                    // print_r($updatedCart);
                    // echo '</pre>';
                    // exit();
                    // test - display table
                    // echo '<table>';
                    // echo '<tr>';
                    // echo '<th>qty</th>';
                    // echo '<th>Product</th>';
                    // echo '<th>Unit price</th>';
                    // echo '<th>Extended price</th>';
                    // echo '</tr>';
                    //     foreach ($updatedCart as    $value)
                    //     {
                    //         echo '<tr>';
                    //         echo '<td>'. $value['qty'] . '</td>';
                    //         echo '<td>'. $value['name'] . '</td>';
                    //         echo '<td>'. $value['unitprice'] . '</td>';
                    //         echo '<td>'. $value['itemtotal'] . '</td>';
                    //         echo '</tr>';
                    //     }
                    // echo '</table>';
                    // exit();

                    // send email to customer
                    $mailResult = Mail::sendOrderData($caller, $updatedCart, $shipping_method, $ppResults);

                    // mail success
                    if ($mailResult)
                    {
                        // test - display SESSION array
                        // echo '<h4>SESSION variable:</h4>';
                        // echo '<pre>';
                        // print_r($_SESSION);
                        // echo '</pre>';
                        // exit();

                        // unset session variables
                        $x = $this->unsetSessionVariables();

                        // success
                        if ($x == true)
                        {
                            // success view
                            View::renderTemplate('Success/index.html', [
                                'first_name' => $caller->billing_firstname,
                                'last_name'  => $caller->billing_lastname,
                                'purchase'   => 'true'
                            ]);
                            exit();
                        }
                        else
                        {
                            // error view
                            View::renderTemplate('Error/index.html', [
                                'errorMessage' => 'An error occurred unsetting session variables.'
                            ]);
                            exit();
                        }
                    }
                    // mail failure
                    else
                    {
                        // unset session variables
                        $x = $this->unsetSessionVariables();

                        // success
                        if ($x == true)
                        {
                            // error view
                            View::renderTemplate('Error/index.html', [
                                'errorMessage'   => 'Your order was placed but an error occurred
                                    sending your confirmation email.'
                            ]);
                            exit();
                        }
                        // unset session variables function failure
                        else
                        {
                            // error view
                            View::renderTemplate('Error/index.html', [
                                'errorMessage' => 'An error occurred unsetting session variables.'
                            ]);
                            exit();
                        }
                    }
                }
            }
        }

        // - - - - - - - guest payment processing - - - - - - - - //
        else
        {
            // process the payment & store response in variable
            $ppResults = Paypal::processPaymentGuest();

            // test
            // echo '<h4>Guest checkout PayPal response:</h4>';
            // echo '<pre>';
            // print_r($ppResults);
            // echo '</pre>';
            // exit();

            // get customer
            $guest = Guest::getGuestByEmail($ppResults['email']);

            // test
            // echo '<h4>Guest:</h4>';
            // echo '<pre>';
            // print_r($guest);
            // echo '</pre>';
            // exit();

            // failure -- order amt exceeds total purchase price ceiling
            if (isset($ppResults['response']) && $ppResults['response']['RESULT'] == 126)
            {

                // mail office and client re: alert
                $result = Mail::fpsAlert($guest);

                // mail successful
                if ($result)
                {
                    // unset SESSION variables
                    $x = $this->unsetSessionVariables();
                    // success
                    if ($x == true)
                    {
                        $amount = $results['response']['AMT'];

                        View::renderTemplate('Error/index.html', [
                            'errorMessage' => 'Purchase amount of
                            $' . number_format($amount, 2) . ' exceeds
                            website purchase limit. You should receive an automated
                            email shortly with instructions how to complete your
                            purchase.'
                        ]);
                    }
                    // failure unsetting SESSION variables
                    else
                    {
                        View::renderTemplate('Error/index.html', [
                            'errorMessage' => 'An error occurred unsetting session variables.'
                        ]);
                        exit();
                    }
                }
                // mail failure
                else
                {
                    View::renderTemplate('Error/index.html', [
                        'errorMessage' => 'Error occurred sending email.'
                    ]);
                    exit();
                }
            }

            // - - successful purchase within purchase price ceiling limit - - //
            else
            {
                // get cart meta data
                $cartMetaData = $this->getCartMetaData();

                // test
                // echo '<h4>Payflow results:</h4>';
                // echo '<pre>';
                // print_r($ppResults);
                // echo '</pre>';
                // echo '<h4>Shopping cart:</h4>';
                // echo '<pre>';
                // print_r($_SESSION['cart']);
                // echo '</pre>';
                // echo '<h4>Cart meta data:</h4>';
                // echo '<pre>';
                // print_r($cartMetaData);
                // echo '</pre>';
                // exit();

                // store order weight in SESSION array
                // $_SESSION['order_weight'] = $cartMetaData['total_weight'];

                // store number of items in SESSION array
                $_SESSION['numberOfItems'] = $cartMetaData['numberOfItems'];

                // store PayPal transaction ID & total amount of order
                $pnref = $ppResults['response']['PNREF'];
                $totalAmt = $ppResults['response']['AMT'];

                // store cart in variable
                $cart = $_SESSION['cart'];

                // store sales tax data in variable
                $sales_tax_data = $_SESSION['sales_tax_data'];

                // - - - store order in `orders` table - - - - - - - - //
                $order = new Order();

                $results = $order->insertOrder($buyer = 'guest', $guest, $cart, $totalAmt, $pnref, $sales_tax_data);

                // db insertion failure
                if ($results['result'] == false)
                {
                    echo 'Error inserting order data into database';
                    // send email to webmaster
                    exit();
                }
                // db insertion success
                else
                {
                    // store shipping method in variable
                    $shipping_method = $_SESSION['shipping_method'];

                    // store $updatedCart array in variable
                    $updatedCart = $results['updatedCart'];

                    // test
                    // echo '<<h4>Updated cart:</h4>';
                    // echo '<pre>';
                    // print_r($updatedCart);
                    // echo '</pre>';
                    // exit();
                    // test - display table
                    // echo '<table>';
                    // echo '<tr>';
                    // echo '<th>qty</th>';
                    // echo '<th>Product</th>';
                    // echo '<th>Unit price</th>';
                    // echo '<th>Extended price</th>';
                    // echo '</tr>';
                    //     foreach ($updatedCart as    $value)
                    //     {
                    //         echo '<tr>';
                    //         echo '<td>'. $value['qty'] . '</td>';
                    //         echo '<td>'. $value['name'] . '</td>';
                    //         echo '<td>'. $value['unitprice'] . '</td>';
                    //         echo '<td>'. $value['itemtotal'] . '</td>';
                    //         echo '</tr>';
                    //     }
                    // echo '</table>';
                    // exit();

                    // send email to customer
                    $mailResult = Mail::sendOrderData($guest, $updatedCart, $shipping_method, $ppResults);

                    // mail success
                    if ($mailResult)
                    {
                        // test - display SESSION array
                        // echo '<h4>SESSION variable:</h4>';
                        // echo '<pre>';
                        // print_r($_SESSION);
                        // echo '</pre>';
                        // exit();

                        // unset session variables
                        $x = $this->unsetSessionVariables();

                        // success
                        if ($x == true)
                        {
                            // success view
                            View::renderTemplate('Success/index.html', [
                                'first_name' => $guest->billing_firstname,
                                'last_name'  => $guest->billing_lastname,
                                'purchase'   => 'true'
                            ]);
                            exit();
                        }
                        else
                        {
                            // error view
                            View::renderTemplate('Error/index.html', [
                                'errorMessage' => 'An error occurred unsetting session variables.'
                            ]);
                            exit();
                        }
                    }
                    // mail failure
                    else
                    {
                        // unset session variables
                        $x = $this->unsetSessionVariables();

                        // success
                        if ($x == true)
                        {
                            // error view
                            View::renderTemplate('Error/index.html', [
                                'errorMessage'   => 'Your order was placed but an error occurred
                                    sending your confirmation email.'
                            ]);
                            exit();
                        }
                        // unset session variables function failure
                        else
                        {
                            // error view
                            View::renderTemplate('Error/index.html', [
                                'errorMessage' => 'An error occurred unsetting session variables.'
                            ]);
                            exit();
                        }
                    }
                }
            }
        }
    }




    /**
     * calculates new grand total for AMT field & returns it to the ajax request
     *
     * @return INT      The new grand total
     */
    public function processDiscount()
    {
        // echo "Connected to processDiscount() in Cart controller."; exit();

        $discount_coupon = (isset($_REQUEST['discount_coupon'])) ? filter_var($_REQUEST['discount_coupon'], FILTER_SANITIZE_STRING) : '';
        $shipping_method = (isset($_REQUEST['shipping_method'])) ? filter_var($_REQUEST['shipping_method'], FILTER_SANITIZE_STRING) : '';
        $shipping_cost = $_REQUEST['shipping_cost'];
        $sales_tax = (isset($_REQUEST['sales_tax'])) ? $_REQUEST['sales_tax'] : '';
        $pretax_total = (isset($_REQUEST['pretax_total'])) ? $_REQUEST['pretax_total'] : '';
        $total = (isset($_REQUEST['total'])) ? $_REQUEST['total'] : '';

        // format: set two digits after decimal without a comma
        $pretax_total = number_format($pretax_total, 2, '.', '');
        $total = number_format($total, 2, '.', '');

        // store pretax total in SESSION array
        $_SESSION['pretaxTotal'] = $pretax_total;

        // test
        // echo $discount_coupon . ', ';
        // echo $shipping_method . ', ';
        // echo $shipping_cost. ', ';
        // echo $sales_tax. ', ';
        // echo $pretax_total. ', ';
        // echo $total;
        // exit();

        // remove all whitespace
        $discount_coupon = str_replace(' ', '', $discount_coupon);

        // convert to lowercase
        $discount_coupon = strtolower($discount_coupon);

        // store discount coupon in SESSION array
        $_SESSION['coupon'] = $discount_coupon;

        // get multiplier to calculate discounted price
        $multiplier = $this->getMultiplier($discount_coupon);

        // validate submitted coupon
        if ($multiplier == 'error')
        {
            echo "error";
            exit();
        }

        // echo 'Multiplier: ' . $multiplier; exit();

        // store multiplier in SESSION variable
        $_SESSION['discount_multiplier'] = $multiplier;

        // create as numeric
        $discounted_total = 0;

        // apply discount to pretax total (no shipping or sales tax)
        $discounted_total = $pretax_total * $_SESSION['discount_multiplier'] ;

        // format
        $discounted_total = number_format($discounted_total, 2, '.', ''); // no comma

        // discount amount
        $discount_amount = $pretax_total - $discounted_total;

        // format
        $discount_amount = number_format($discount_amount, 2, '.', '');

        // calculate new sales tax amount
        if ($sales_tax > 0)
        {
            $new_sales_tax = $discounted_total * $this->florida_sales_tax_rate;

            // format
            $new_sales_tax  = number_format($new_sales_tax, 2, '.', ''); // no comma

            // store in SESSIONS array
            $_SESSION['salesTax'] = $new_sales_tax;

        }
        else
        {
            $new_sales_tax = $sales_tax;

            // format
            $new_sales_tax = number_format($new_sales_tax, 2, '.', '');

            // store in SESSIONS array
            $_SESSION['salesTax'] = $new_sales_tax;
        }

        // echo $discount_amount; exit();

        // set variable as numeric
        $grand_total = 0;

        // calculate new grand total
        $grand_total = $discounted_total + $_SESSION['salesTax'] + $shipping_cost;

        // format
        $grand_total = number_format($grand_total, 2, '.', '');

        // calculate savings
        $savings = $total - $grand_total;

        // format
        $savings = number_format($savings, 2, '.', '');

        // echo $grand_total; exit();

        // array
        $discount_data = [
            'discounted_grand_total' => $grand_total,
            'discount_amount'        => $savings
        ];

        // encode as JSON & return array to ajax request
        echo json_encode($discount_data);
    }




    /**
     * Ajax request:  retrieves priority mail rate from Endicia API
     *
     * @return String   The rate
     */
    public function getPostageRate()
    {
        // echo "Connected to getPostageRate() method in Cart Controller!"; exit();

        $shipping_method = (isset($_REQUEST['shipping_method'])) ? filter_var($_REQUEST['shipping_method'], FILTER_SANITIZE_STRING) : '';
        $weight = (isset($_REQUEST['total_weight'])) ? filter_var($_REQUEST['total_weight'], FILTER_SANITIZE_NUMBER_INT) : '';
        $numberOfItems = (isset($_REQUEST['numberOfItems'])) ? filter_var($_REQUEST['numberOfItems'], FILTER_SANITIZE_NUMBER_INT) : '';
        $address1 = (isset($_REQUEST['address1'])) ? filter_var($_REQUEST['address1'], FILTER_SANITIZE_STRING) : '';
        $address2 = (isset($_REQUEST['address2'])) ? filter_var($_REQUEST['address2'], FILTER_SANITIZE_STRING) : '';
        $city = (isset($_REQUEST['city'])) ? filter_var($_REQUEST['city'], FILTER_SANITIZE_STRING) : '';
        $state = (isset($_REQUEST['state'])) ? filter_var($_REQUEST['state'], FILTER_SANITIZE_STRING) : '';
        $zip = (isset($_REQUEST['zip'])) ? filter_var($_REQUEST['zip'], FILTER_SANITIZE_STRING) : '';

        // Endicia API: CalculatePostageRate() Zip Code has max of five characters
        if (strlen($zip) > 5) {
            $zip = trim(substr($zip, 0, 5));
        } else {
            $zip = $zip;
        }

        // create new instance of Endicia Object
        $endicia = new Endicia();

        $xml_response = $endicia->calculatePostageRate($this->stdBoxDimensions, $shipping_method, $weight, $zip);

        // must be returned to AJAX request at JSON
        // echo to AJAX request
        echo json_encode($xml_response);
    }




    public function getUpsRate()
    {
        // only displays in console.log if dataType: 'json' is commented out
        // echo "Connected to getUpsRate() method in Cart controller!"; exit();

        $shipping_method = (isset($_REQUEST['shipping_method'])) ? filter_var($_REQUEST['shipping_method'], FILTER_SANITIZE_STRING) : '';
        $serviceCode = (isset($_REQUEST['code'])) ? filter_var($_REQUEST['code'], FILTER_SANITIZE_STRING) : '';
        $weight = (isset($_REQUEST['total_weight'])) ? filter_var($_REQUEST['total_weight'], FILTER_SANITIZE_NUMBER_INT) : '';
        $numberOfItems = (isset($_REQUEST['numberOfItems'])) ? filter_var($_REQUEST['numberOfItems'], FILTER_SANITIZE_NUMBER_INT) : '';
        $company = (isset($_REQUEST['company'])) ? filter_var($_REQUEST['company'], FILTER_SANITIZE_STRING) : '';
        $firstName = (isset($_REQUEST['firstName'])) ? filter_var($_REQUEST['firstName'], FILTER_SANITIZE_STRING) : '';
        $lastName = (isset($_REQUEST['lastName'])) ? filter_var($_REQUEST['lastName'], FILTER_SANITIZE_STRING) : '';
        $phone = (isset($_REQUEST['phone'])) ? filter_var($_REQUEST['phone'], FILTER_SANITIZE_STRING) : '';
        $address1 = (isset($_REQUEST['address1'])) ? filter_var($_REQUEST['address1'], FILTER_SANITIZE_STRING) : '';
        $address2 = (isset($_REQUEST['address2'])) ? filter_var($_REQUEST['address2'], FILTER_SANITIZE_STRING) : '';
        $city = (isset($_REQUEST['city'])) ? filter_var($_REQUEST['city'], FILTER_SANITIZE_STRING) : '';
        $state = (isset($_REQUEST['state'])) ? filter_var($_REQUEST['state'], FILTER_SANITIZE_STRING) : '';
        $zip = (isset($_REQUEST['zip'])) ? filter_var($_REQUEST['zip'], FILTER_SANITIZE_STRING) : '';

        // UPS API: ShipmentConfirmRequest() Zip Code has five character max
        $zip = trim(substr($zip, 0, 5));

        // test
        // $arr = json_encode($_REQUEST);
        // echo $arr;
        // exit();

        // create array to pass shipping data
        $data = [
            'shipping_method' => $shipping_method,
            'serviceCode'     => $serviceCode,
            'weight'          => $weight,
            'company'         => $company,
            'firstName'       => $firstName,
            'lastName'        => $lastName,
            'phone'           => $phone,
            'address1'        => $address1,
            'address2'        => $address2,
            'city'            => $city,
            'state'           => $state,
            'zip'             => $zip
        ];

        // test
        // $arr = json_encode($data);
        // echo $arr;
        // exit();

        // create new instance of Ups Object
        $ups = new Ups();

        // call shipmentConfirmRequest
        $response = $ups->shipmentConfirmRequest($data);

        // must be returned to AJAX request at JSON
        // echo to AJAX request
        echo json_encode($response);
    }




    //  - - - - Class methods  - - - - - - - - - - - - - - - - - - - - - -  //

    private function getCartMetaData()
    {
        // test
        // echo "<h4>Shopping Cart:</h4>";
        // echo '<pre>';
        // print_r($_SESSION['cart']);
        // echo '</pre>';
        // exit();

        // loop thru cart & store sums for subtotal & tax
        foreach($_SESSION['cart'] as $item)
        {
            $this->subtotal += $item['price'] * $item['quantity'];
            $this->numberOfItems += $item['quantity'];
            // $this->total_weight += $item['quantity'] * $item['weight'];
        }

        // store total in variable
        $this->pretax_total = $this->subtotal;

        // store data in array
        $this->cartMetaData = [
            'pretax_total'  => number_format($this->pretax_total, 2, '.', ''),
            'numberOfItems' => $this->numberOfItems
            // 'total_weight'  => $this->total_weight
        ];

        return $this->cartMetaData;
    }




    private function updateCartMetaData($multiplier)
    {
        // test
        // echo "Shopping Cart:";
        // echo '<pre>';
        // print_r($_SESSION['cart']);
        // echo '</pre>';
        // exit();

        // loop thru cart & store sums for subtotal & tax
        foreach($_SESSION['cart'] as $item)
        {
            $this->subtotal += $item['price'] * $multiplier * $item['quantity'];
            $this->numberOfItems += $item['quantity'];
            $this->total_weight += $item['quantity'] * $item['weight'];
        }

        // store total in variable
        $this->pretax_total = $this->subtotal;

        // store data in array
        $this->cartMetaData = [
            'pretax_total'  => number_format($this->pretax_total, 2, '.', ''),
            'numberOfItems' => $this->numberOfItems,
            'total_weight'  => $this->total_weight
        ];

        return $this->cartMetaData;
    }




    private function getWeight($cart)
    {
        // loop thru cart & store sum of weight
        foreach($_SESSION['cart'] as $item)
        {
            $this->total_weight += $item['quantity'] * $item['weight'];
        }

        return number_format($this->total_weight, 0, '', ''); // integer, no comma
    }




    private function getMultiplier($discount_coupon)
    {
        // set value of discount coupon
        switch ($discount_coupon)
        {
            // Bersa
            CASE 'rgh196':
                $multiplier = .8;
                break;
            // SCCY Code 5
            CASE 'ljh677':
                $multiplier = .8;
                break;
            // SCCY Code 4
            CASE 'ta84blk':
                $multiplier = .8;
                break;
            // SCCY Code 3
            CASE '488gtb':
                $multiplier = .8;
                break;
            // SCCY Code 2
            CASE 'aqw455':
                $multiplier = .8;
                break;
            // SCCY
            CASE 'at48klb':
                $multiplier = .8;
                break;
            // Military
            CASE 'service':
                $multiplier = .85;
                break;
            // AGAG18
            CASE 'agag18':
                $multiplier = 0;
                break;
            // NRA Insructor - 69754810
            CASE '69754810':
                $multiplier = .8;
                break;
            // NRA Insructor - 17220450
            CASE '17220450':
                $multiplier = .8;
                break;
            // NRA Insructor - 61872495
            CASE '61872495':
                $multiplier = .8;
                break;
            // TWAW Promotion
            CASE 'perme18':
                $multiplier = .9;
                break;
            // Satisfaction
            CASE 'satisfaction':
                $multiplier = .9;
                break;
            // SHOT Show 2018
            CASE 'alss18':
                $multiplier = .6;
                break;
            default:
                $multiplier = 'error';
        }

        return $multiplier;
    }




    public function unsetSessionVariables()
    {
        unset($_SESSION['cart']);
        unset($_SESSION['cart_count']);
        unset($_SESSION['shipping_method']);
        unset($_SESSION['shipment_digest']);
        unset($_SESSION['discount_multiplier']);
        unset($_SESSION['trackingNumber']);
        unset($_SESSION['coupon']);
        unset($_SESSION['order_weight']);
        unset($_SESSION['numberOfItems']);
        unset($_SESSION['salesTax']);
        unset($_SESSION['pretaxTotal']);
        unset($_SESSION['sales_tax_data']['otax_total']);
        unset($_SESSION['sales_tax_data']['otax_state']);
        unset($_SESSION['sales_tax_data']['otax_state_amt']);
        unset($_SESSION['sales_tax_data']['otax_county']);
        unset($_SESSION['sales_tax_data']['otax_county_amt']);
        unset($_SESSION['ids']); // product IDs in Controllers/Admin/Dealercart

        return true;
    }




    private function getFloridaSalesTaxByZipcodeArray()
    {
        // production
        if ($_SERVER['SERVER_NAME'] != 'localhost')
        {
            // path for live server @IMH
            $file_path = '/home/pamska5/public_html/gunlaser.store/App/Controllers/Admin/Library/data.csv';
        }
        else
        // development
        {
            // path for local machine
            $file_path = 'C:\xampp\htdocs\gunlaser.store\App\Controllers\Admin\Library\data.csv';
        }

        // http://php.net/manual/en/function.str-getcsv.php
        $csv = array_map('str_getcsv', file($file_path));

        // test
        // echo '<pre>';
        // print_r($csv);
        // echo '</pre>';
        // exit();

        return $csv;
    }




    private function getFloridaSalesTaxByZipcode($shipping_zip)
    {
        $csv = $this->getFloridaSalesTaxByZipcodeArray();

        // test
        // echo '<h4>Sales Tax Array:</h4>';
        // echo '<pre>';
        // print_r($csv);
        // exit();

        // convert to Zip5 if longer
        if (strlen($shipping_zip > 5))
        {
            $zip5 = trim(substr($shipping_zip, 0, 5));
        }
        else
        {
            $zip5 = trim($shipping_zip);
        }

        // create empty array
        $salesTaxArr = [];

        // loop to find $zip5 in array
        foreach ($csv as $value)
        {
            if ($value[1] == $zip5)
            {
                $salesTaxArr['state'] = $value[0];
                $salesTaxArr['county'] = $value[2];
                $salesTaxArr['state_rate'] = $value[3];
                $salesTaxArr['combined_rate'] = $value[4];
                $salesTaxArr['county_rate'] = $value[5];
            }
        }

        // test
        // echo '<h4>Sales Tax Array:</h4>';
        // echo '<pre>';
        // print_r($salesTaxArr);
        // exit();

        return $salesTaxArr;
    }



// = = = = =  refactored updates above  = = = = = = = = = = = = = = = = = = = //

}