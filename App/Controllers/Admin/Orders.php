<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\Order;
use \App\Models\Order_content;
use \App\Models\Customer;
use \App\Models\Guest;
use \App\Models\Caller;
use \App\Models\Endicia;
use \App\Models\Ups;
use \App\Models\Comment;
use \App\Models\User;
use \App\Models\Warranty;
use \App\Models\Warrantyregistration;
use \App\Models\Paypal;
use \App\Models\Dealer;
use \App\Models\Partner;
use \App\Models\Part;
use \App\Models\Trseries;
use \App\Models\Gtoflx;
use \App\Models\Stingray;
use \App\Models\Holster;
use \App\Models\Accessory;
use \App\Models\Battery;
use \App\Models\Toolkit;
use \App\Models\Flx;

class Orders extends \Core\Controller
{
    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        // redirect user not logged in to root
        if(!isset($_SESSION['user']))
        {
            header("Location: /");
            exit();
        }

        // redirect logged in user that is not supervisor or admin
        if (isset($_SESSION['user']) && $_SESSION['userType'] == 'dealer'
            || $_SESSION['userType'] == 'partner'
            || $_SESSION['userType'] == 'customer')
        {
            header("Location: /");
            exit();

        }
    }


    /**
     * display all records from orders and orders_content tables
     *
     * @return Object  All Orders records
     */
    public function getOrdersAction()
    {
        // get status from query string
        $status = ( isset($_REQUEST['status'])  ) ? filter_var($_REQUEST['status'], FILTER_SANITIZE_STRING): '';

        // get orders data
        $orders = Order::getOrders($status);

        // test
        // echo '<pre>';
        // print_r($orders);
        // echo '</pre>';
        // exit();

        switch($status)
        {
            CASE 'all' :
                $title = 'All orders';
                break;
            CASE 'pending' :
                $title = 'Pending (new) orders';
                break;
            CASE 'shipped' :
                $title = 'Shipped orders';
                break;
            CASE 'return-pending' :
                $title = 'Orders: return pending';
                break;
            CASE 'returned' :
                $title = 'Returned orders';
                break;
            default :
               $title = 'error';
        }

        // render view
        View::renderTemplate('Admin/Armalaser/Show/orders.html', [
            'pagetitle' => $title,
            'orders'    => $orders,
        ]);
    }



    /**
     * returns a single order record by ID
     *
     * @return Object  The order record
     */
    public function getOrderAction()
    {
        // get ID from query string
        $id = ( isset($_REQUEST['id'])  ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT): '';

        // get buyer type
        $results = Order::getOrderBuyerTypeAndId($id);

        // test
        // echo '<h4>$results</h4>';
        // echo '<pre>';
        // print_r($results);
        // echo '</pre>';
        // exit();

        // build RMA return reasons array
        $reasons = [
            "battery",
            "beam",
            "broken housing",
            "changed mind",
            "dimming",
            "flex damage",
            "installation",
            "intermitten",
            "lost part(s)",
            "missing part(s)",
            "no activation",
            "over adjusted",
            "programming",
            "shutting off",
            "stripped screws",
            "switch",
            "windage-elevation loose",
            "windage-elevation stuck",
            "wrong model"
        ];

        // get order details for Customer
        if ($results['type'] == 'customerid' )
        {
            // get customer data
            $customer = Customer::getCustomer($results['customerid']);

            // add buyer type to $customer object; delete 'id' at end of string
            $customer->buyer_type = substr($results['type'], 0, -2);

            // test
            // echo '<h4>Customer:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();

            // get order content
            $order_content = Order::getOrderContent($id);

            // test
            // echo '<h4>Customer order content:</h4>';
            // echo '<pre>';
            // print_r($order_content);
            // echo '</pre>';
            // exit();

            // store min & max for laser IDs
            $min = 1000;
            $max = 3999;
            $serialNumber = '';

            // check for serial number(s) for lasers by ID range
            foreach ($order_content as $content)
            {
                if ( ($content->serial_number != '') && ($content->itemid >= $min) && ($content->itemid <= $max) )
                {
                    $serialNumber = true;
                }
            }

            // get lasers in order
            $lasers = $this->getLasersInOrder($order_content);

            // test
            // echo '<h4>Order content:</h4>';
            // echo '<pre>';
            // print_r($lasers);
            // echo '</pre>';
            // exit();

            // get order data
            $order = Order::getOrderData($id);

            // test
            // echo '<h4>Customer order:</h4>';
            // echo '<pre>';
            // print_r($order);
            // echo '</pre>';
            // exit();

            // get comments
            $comments = Comment::getComments($id);

            // test
            // echo '<h4>Order comments:</h4>';
            // echo '<pre>';
            // print_r($comments);
            // echo '</pre>';
            // exit();

            // get days between today and shipped date
            $daysSinceShipped = $this->getDaysBetweenDates($order->oshippeddate);


            View::renderTemplate('Admin/Armalaser/Show/order-details.html', [
                'pagetitle' => 'Order Details',
                'customer' => $customer,
                'order_content' => $order_content,
                'serialNumber'  => $serialNumber,
                'order' => $order,
                'comments' => $comments,
                'daysSinceShipped' => $daysSinceShipped,
                'lasers' => $lasers,
                'reasons' => $reasons
            ]);
            exit();
        }

        // get order details for Caller
        if ($results['type'] == 'callerid' )
        {
            // get customer data
            $customer = Caller::getCaller($results['customerid']);

            // add buyer type to $customer object; delete 'id' at end of string
            $customer->buyer_type = substr($results['type'], 0, -2);

            // test
            // echo '<h4>Caller:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();

            // get order content
            $order_content = Order::getOrderContent($id);

            // test
            // echo '<h4>Customer order content:</h4>';
            // echo '<pre>';
            // print_r($order_content);
            // echo '</pre>';
            // exit();

            // store min & max for laser IDs
            $min = 1000;
            $max = 3999;
            $serialNumber = '';

            // check for serial number(s) for lasers by ID range
            foreach ($order_content as $content)
            {
                if ( ($content->serial_number != '') && ($content->itemid >= $min) && ($content->itemid <= $max) )
                {
                    $serialNumber = true;
                }
            }

            // get lasers in order
            $lasers = $this->getLasersInOrder($order_content);

            // test
            // echo '<h4>Order content:</h4>';
            // echo '<pre>';
            // print_r($lasers);
            // echo '</pre>';
            // exit();

            // get order data
            $order = Order::getOrderData($id);

            // test
            // echo '<h4>Customer order:</h4>';
            // echo '<pre>';
            // print_r($order);
            // echo '</pre>';
            // exit();

            // get comments
            $comments = Comment::getComments($id);

            // test
            // echo '<h4>Order comments:</h4>';
            // echo '<pre>';
            // print_r($comments);
            // echo '</pre>';
            // exit();

            // get days between today and shipped date
            $daysSinceShipped = $this->getDaysBetweenDates($order->oshippeddate);


            View::renderTemplate('Admin/Armalaser/Show/order-details.html', [
                'pagetitle' => 'Order Details',
                'customer' => $customer,
                'order_content' => $order_content,
                'serialNumber'  => $serialNumber,
                'order' => $order,
                'comments' => $comments,
                'daysSinceShipped' => $daysSinceShipped,
                'lasers' => $lasers,
                'reasons' => $reasons
            ]);
            exit();
        }

        //  get order details for Guest
        if ($results['type'] == 'guestid' )
        {
            // get guest data
            $customer = Guest::getGuest($results['customerid']);

            // add buyer type to $customer array; delete 'id' at end of string
            $customer->buyer_type = substr($results['type'], 0, -2);

            // test
            // echo '<h4>Guest:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();

            // get order content
            $order_content = Order::getOrderContent($id);

            // test
            // echo '<h4>Guest order content:</h4>';
            // echo '<pre>';
            // print_r($order_content);
            // echo '</pre>';
            // exit();

            // store min & max for laser IDs
            $min = 1000;
            $max = 3999;
            $serialNumber = '';

            // check for serial number(s) for lasers by ID range
            foreach ($order_content as $content)
            {
                if ( ($content->serial_number != '') && ($content->itemid >= $min) && ($content->itemid <= $max) )
                {
                    $serialNumber = true;
                }
            }

            // get lasers in order
            $lasers = $this->getLasersInOrder($order_content);

            // test
            // echo '<h4>Order content:</h4>';
            // echo '<pre>';
            // print_r($lasers);
            // echo '</pre>';
            // exit();

            // get data from `orders`
            $order = Order::getOrderData($id);

            // test
            // echo '<h4>Guest order:</h4>';
            // echo '<pre>';
            // print_r($order);
            // echo '</pre>';
            // exit();

            // get comments
            $comments = Comment::getComments($id);

            // test
            // echo '<h4>Order comments:</h4>';
            // echo '<pre>';
            // print_r($comments);
            // echo '</pre>';
            // exit();

            // get days between today and shipped date
            $daysSinceShipped = $this->getDaysBetweenDates($order->oshippeddate);

            View::renderTemplate('Admin/Armalaser/Show/order-details.html', [
                'pagetitle' => 'Order Details',
                'customer' => $customer,
                'order_content' => $order_content,
                'serialNumber'  => $serialNumber,
                'order'  => $order,
                'comments' => $comments,
                'daysSinceShipped' => $daysSinceShipped,
                'lasers' => $lasers,
                'reasons' => $reasons
            ]);
        }

        // get order details for Dealer
        else if ($results['type'] == 'dealerid' )
        {
            // get dealer data
            $customer = Dealer::getDealer($results['customerid']);

            // add buyer type to $customer object; delete 'id' at end of string
            $customer->buyer_type = substr($results['type'], 0, -2);

            // test
            // echo '<h4>Dealer:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();

            // get order content
            $order_content = Order::getOrderContent($id);

            // test
            // echo '<h4>Customer order content:</h4>';
            // echo '<pre>';
            // print_r($order_content);
            // echo '</pre>';
            // exit();

            // store min & max for laser IDs
            $min = 1000;
            $max = 3999;
            $serialNumber = '';

            // check for serial number(s) for lasers by ID range
            foreach ($order_content as $content)
            {
                if ( ($content->serial_number != '') && ($content->itemid >= $min) && ($content->itemid <= $max) )
                {
                    $serialNumber = true;
                }
            }

            // get lasers in order
            $lasers = $this->getLasersInOrder($order_content);

            // test
            // echo '<h4>Order content:</h4>';
            // echo '<pre>';
            // print_r($lasers);
            // echo '</pre>';
            // exit();

            // get order data
            $order = Order::getOrderData($id);

            // test
            // echo '<h4>Dealer order:</h4>';
            // echo '<pre>';
            // print_r($order);
            // echo '</pre>';
            // exit();

            // get comments
            $comments = Comment::getComments($id);

            // test
            // echo '<h4>Order comments:</h4>';
            // echo '<pre>';
            // print_r($comments);
            // echo '</pre>';
            // exit();

            // get days between today and shipped date
            $daysSinceShipped = $this->getDaysBetweenDates($order->oshippeddate);


            View::renderTemplate('Admin/Armalaser/Show/order-details.html', [
                'pagetitle' => 'Order Details',
                'customer' => $customer,
                'order_content' => $order_content,
                'serialNumber'  => $serialNumber,
                'order' => $order,
                'comments' => $comments,
                'daysSinceShipped' => $daysSinceShipped,
                'lasers' => $lasers,
                'reasons' => $reasons
            ]);
            exit();
        }

        // get order details for Partner
        else if ($results['type'] == 'partnerid' )
        {
            // get partner data
            $customer = Partner::getPartner($results['customerid']);

            // test
            // echo '<h4>Partner:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();

            // add buyer type to $customer object; delete 'id' at end of string
            $customer->buyer_type = substr($results['type'], 0, -2);

            // test
            // echo '<h4>Partner:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();

            // get order content
            $order_content = Order::getOrderContent($id);

            // test
            // echo '<h4>Partner order content:</h4>';
            // echo '<pre>';
            // print_r($order_content);
            // echo '</pre>';
            // exit();

            // store min & max for laser IDs
            $min = 1000;
            $max = 3999;
            $serialNumber = '';

            // check for serial number(s) for lasers by ID range
            foreach ($order_content as $content)
            {
                if ( ($content->serial_number != '') && ($content->itemid >= $min) && ($content->itemid <= $max) )
                {
                    $serialNumber = true;
                }
            }

            // get lasers in order
            $lasers = $this->getLasersInOrder($order_content);

            // test
            // echo '<h4>Order content:</h4>';
            // echo '<pre>';
            // print_r($lasers);
            // echo '</pre>';
            // exit();

            // get order data
            $order = Order::getOrderData($id);

            // test
            // echo '<h4>Partner order:</h4>';
            // echo '<pre>';
            // print_r($order);
            // echo '</pre>';
            // exit();

            // get comments
            $comments = Comment::getComments($id);

            // test
            // echo '<h4>Order comments:</h4>';
            // echo '<pre>';
            // print_r($comments);
            // echo '</pre>';
            // exit();

            // get days between today and shipped date
            $daysSinceShipped = $this->getDaysBetweenDates($order->oshippeddate);


            View::renderTemplate('Admin/Armalaser/Show/order-details.html', [
                'pagetitle' => 'Order Details',
                'customer' => $customer,
                'order_content' => $order_content,
                'serialNumber'  => $serialNumber,
                'order' => $order,
                'comments' => $comments,
                'daysSinceShipped' => $daysSinceShipped,
                'lasers' => $lasers,
                'reasons' => $reasons
            ]);
            exit();
        }
    }




    /**
     * returns a single order record by ID
     *
     * @return Object  The order record
     */
    public function getMyOrderAction()
    {
        // get order ID from query string
        $id = ( isset($_REQUEST['id'])  ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT): '';
        $customerid = ( isset($_REQUEST['buyerid'])  ) ? filter_var($_REQUEST['buyerid'], FILTER_SANITIZE_NUMBER_INT): '';
        $type = ( isset($_REQUEST['type'])  ) ? filter_var($_REQUEST['type'], FILTER_SANITIZE_STRING): '';

        if ($type == 'customer')
        {
            $customer = Customer::getCustomer($customerid);

            // test
            // echo '<h4>Customer:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();
        }
        else if ($type == 'dealer')
        {
            $customer = Dealer::getDealer($customerid);

            // test
            // echo '<h4>Dealer:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();
        }
        else if ($type == 'partner')
        {
            $customer = Partner::getPartner($customerid);

            // test
            // echo '<h4>Partner:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();
        }

        // get order content
        $order_content = Order::getOrderContent($id);

        // test
        // echo '<h4>Order content:</h4>';
        // echo '<pre>';
        // print_r($order_content);
        // echo '</pre>';
        // exit();

        // get order data
        $order = Order::getOrderData($id);

        // test
        // echo '<h4>Customer order:</h4>';
        // echo '<pre>';
        // print_r($order);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Armalaser/Show/account-order-details.html', [
            'pagetitle'     => 'Order Details',
            'customer'      => $customer,
            'order_content' => $order_content,
            'order'         => $order
        ]);
    }



    /**
     * returns a single order record by ID
     *
     * @return Object  The order record
     */
    public function getOrderByTrackingNumberAction()
    {
        // get ID
        $trackingNumber = ( isset($_REQUEST['trackingcode'])  ) ? filter_var($_REQUEST['trackingcode'], FILTER_SANITIZE_STRING): '';

        // get order
        $order = Order::getOrderByTrackingNumber($trackingNumber);

        // test
        // echo '<pre>';
        // print_r($order);
        // echo '</pre>';
        // exit();

        // get buyer type
        $results = Order::getOrderBuyerTypeAndId($order->id);

        // test
        // echo '<pre>';
        // print_r($results);
        // echo '</pre>';
        // exit();

        // get order details
        if ($results['type'] == 'customerid' )
        {
            // get customer data
            $customer = Customer::getCustomer($results['customerid']);

            // test
            // echo '<h4>Customer:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();

            // get order content
            $order_content = Order::getOrderContent($order->id);

            // test
            // echo '<h4>Order content:</h4>';
            // echo '<pre>';
            // print_r($order_content);
            // echo '</pre>';
            // exit();

            // get order data
            $order = Order::getOrderData($order->id);

            // test
            // echo '<h4>Customer order:</h4>';
            // echo '<pre>';
            // print_r($order);
            // echo '</pre>';
            // exit();

            View::renderTemplate('Admin/Armalaser/Show/order-details.html', [
                'pagetitle' => 'Order Details',
                'customer' => $customer,
                'order_content' => $order_content,
                'order' => $order
            ]);
        }
        //  guest
        else
        {
            // get customer data
            $customer = Guest::getGuest($results['customerid']);

            // test
            // echo '<h4>Guest:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();

            // get order content
            $order_content = Order::getOrderContent($order->id);

            // test
            // echo '<h4>Order content:</h4>';
            // echo '<pre>';
            // print_r($order_content);
            // echo '</pre>';
            // exit();

            $order = Order::getOrderData($order->id);

            // test
            // echo '<h4>Guest order:</h4>';
            // echo '<pre>';
            // print_r($order);
            // echo '</pre>';
            // exit();

            View::renderTemplate('Admin/Armalaser/Show/order-details.html', [
                'pagetitle' => 'Order Details',
                'customer' => $customer,
                'order_content' => $order_content,
                'order'  => $order
            ]);
        }
    }



    /**
     * retrieves order data & displays view
     *
     * @return View
     */
    public function labelAction()
    {
        // get ID
        $id = ( isset($_REQUEST['id'])  ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT): '';

        // get order content
        $order_content = Order::getOrderContent($id);

        // test
        // echo '<h4>Order content ($order_content):</h4>';
        // echo '<pre>';
        // print_r($order_content);
        // echo '</pre>';
        // exit();

        // get order data
        $order = Order::getOrderData($id);

        // test
        // echo '<h4>Customer order ($order):</h4>';
        // echo '<pre>';
        // print_r($order);
        // echo '</pre>';
        // exit();

        // render view
        View::renderTemplate('Admin/Armalaser/Show/order-create-label.html', [
            'pagetitle' => 'Create Shipping Label',
            'order_content' => $order_content,
            'order'  => $order
        ]);
    }



    /**
     * Gets postage rate
     *
     * @return [type] [description]
     */
    public function getRateAction()
    {
        // retrieve & validate form data
        $name = ( isset($_REQUEST['name'])  ) ? filter_var($_REQUEST['name'], FILTER_SANITIZE_STRING): '';
        $company = ( isset($_REQUEST['company'])  ) ? filter_var($_REQUEST['company'], FILTER_SANITIZE_STRING): '';
        $address = ( isset($_REQUEST['address'])  ) ? filter_var($_REQUEST['address'], FILTER_SANITIZE_STRING): '';
        $address2 = ( isset($_REQUEST['address2'])  ) ? filter_var($_REQUEST['address2'], FILTER_SANITIZE_STRING): '';
        $city = ( isset($_REQUEST['city'])  ) ? filter_var($_REQUEST['city'], FILTER_SANITIZE_STRING): '';
        $state = ( isset($_REQUEST['state'])  ) ? filter_var($_REQUEST['state'], FILTER_SANITIZE_STRING): '';
        $zip = ( isset($_REQUEST['zip'])  ) ? filter_var($_REQUEST['zip'], FILTER_SANITIZE_STRING): '';
        $phone = ( isset($_REQUEST['phone'])  ) ? filter_var($_REQUEST['phone'], FILTER_SANITIZE_STRING): '';
        $email = ( isset($_REQUEST['email'])  ) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_STRING): '';
        $shipping_method = ( isset($_REQUEST['shipping_method'])  ) ? filter_var($_REQUEST['shipping_method'], FILTER_SANITIZE_STRING): '';
        $length = ( isset($_REQUEST['custom_box_length'])  ) ? filter_var($_REQUEST['custom_box_length'], FILTER_SANITIZE_NUMBER_INT): '';
        $width = ( isset($_REQUEST['custom_box_width'])  ) ? filter_var($_REQUEST['custom_box_width'], FILTER_SANITIZE_NUMBER_INT): '';
        $height = ( isset($_REQUEST['custom_box_height'])  ) ? filter_var($_REQUEST['custom_box_height'], FILTER_SANITIZE_NUMBER_INT): '';
        $weight = ( isset($_REQUEST['weight'])  ) ? filter_var($_REQUEST['weight'], FILTER_SANITIZE_NUMBER_INT): '';
        $id = ( isset($_REQUEST['order_id'])  ) ? filter_var($_REQUEST['order_id'], FILTER_SANITIZE_NUMBER_INT): '';

        // UPS API: ShipmentConfirmRequest() Zip Code has five character max
        $zip = trim(substr($zip, 0, 5));

        // weight exceeds USPS First Class limit
        if ($shipping_method == 'First' && $weight > 15)
        {
            echo "Weight limit for First Class must be less than 16 oz. Please
                change Method field to USPS Priority Mail.";
            exit();
        }


        // identify shipper
        switch($shipping_method)
        {
            CASE 'First':
                $shipper = 'USPS';
                $service_code = '';
                break;
            CASE 'Priority':
                $shipper = 'USPS';
                $service_code = '';
                break;
            CASE '03':
                $shipper = 'UPS';
                $service_code = '03';
                $service = 'UPS Ground';
                break;
            CASE '12':
                $shipper = 'UPS';
                $service_code = '12';
                $service = 'UPS 3 Day Select';
                break;
            CASE '02':
                $shipper = 'UPS';
                $service_code = '02';
                $service = 'UPS 2nd Day Air';
                break;
            default :
                $shipper = 'error';
                $service_code = 'error';
                $service = 'error';
        }

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // echo $name . '<br>';
        // echo $company . '<br>';
        // echo $address . '<br>';
        // echo $address2 . '<br>';
        // echo $city . '<br>';
        // echo $state . '<br>';
        // echo $zip . '<br>';
        // echo $phone . '<br>';
        // echo $email . '<br>';
        // echo $shipping_method . '<br>';
        // echo $length . '<br>';
        // echo $width . '<br>';
        // echo $height . '<br>';
        // echo $weight . '<br>';
        // echo $shipper . '<br>';
        // echo $service_code . '<br>';
        // exit();

        // shipper is USPS
        if ($shipper == 'USPS')
        {
            // create instance of Endicia class / model
            $endicia = new Endicia();

            // get response from Endicia API
            $response = $endicia->calculatePostageRate($length, $width, $height, $shipping_method, $weight, $zip);

            // test
            // echo '<h4>Endicia Response for calculatePostageRate:</h4>';
            // echo '<pre>';
            // print_r($response);
            // echo '</pre>';

            // store postage in variable
            $postage = $response->CalculatePostageRateResponse->PostageRateResponse->PostagePrice[0]['TotalAmount'];
            $service = $response->CalculatePostageRateResponse->PostageRateResponse->PostagePrice->Postage->MailService;
            // echo '<br>' . $postage;
            // exit();

            // get order content
            $order_content = Order::getOrderContent($id);

            // test
            // echo '<h4>Order content ($order_content):</h4>';
            // echo '<pre>';
            // print_r($order_content);
            // echo '</pre>';
            // exit();

            // get order data
            $order = Order::getOrderData($id);

            // test
            // echo '<h4>Customer order ($order):</h4>';
            // echo '<pre>';
            // print_r($order);
            // echo '</pre>';
            // exit();

            // render view
            View::renderTemplate('Admin/Armalaser/Show/order-create-label.html', [
                'order_content' => $order_content,
                'order' => $order,
                'postage' => $postage,
                'service' => $service,
                'weight' => $weight,
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'shipping_method' => $shipping_method
            ]);
        }
        // shipper is UPS
        else
        {
            // create instance of Endicia class / model
            $ups = new Ups();

            // create array to pass shipping data
            $data = [
                'shipping_method' => $shipping_method,
                'serviceCode'     => $service_code,
                'weight'          => $weight,
                'company'         => $company,
                'name'            => $name,
                'phone'           => $phone,
                'address1'        => $address,
                'address2'        => $address2,
                'city'            => $city,
                'state'           => $state,
                'zip'             => $zip,
                'length'          => $length,
                'width'           => $width,
                'height'          => $height
            ];

            // get response from Endicia API
            $response = $ups->shipmentConfirmRequest($data);

            // test
            // echo '<h4>UPS Response for shipmentConfirmRequest:</h4>';
            // echo '<pre>';
            // print_r($response);
            // echo '</pre>';
            // exit();

            // store shipping cost in variable
            $postage = $response->ShipmentCharges->TotalCharges->MonetaryValue;
            // $negotiatedRate = $response->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
            $trackingNumber = $response->ShipmentIdentificationNumber;
            $shipmentDigest = $response->ShipmentDigest;

            // test
            // echo '<br>' . $postage;
            // echo '<br>' . $trackingNumber;
            // echo '<br>' . substr($shipmentDigest, 0, 50);
            // exit();

            // get order content
            $order_content = Order::getOrderContent($id);

            // test
            // echo '<h4>Order content ($order_content):</h4>';
            // echo '<pre>';
            // print_r($order_content);
            // echo '</pre>';
            // exit();

            // get order data
            $order = Order::getOrderData($id);

            // test
            // echo '<h4>Customer order ($order):</h4>';
            // echo '<pre>';
            // print_r($order);
            // echo '</pre>';
            // exit();

            // render view
            View::renderTemplate('Admin/Armalaser/Show/order-create-label.html', [
                'order_content' => $order_content,
                'order' => $order,
                'postage' => $postage,
                'service' => $service,
                'weight' => $weight,
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'shipping_method' => $shipping_method,
                'shipmentDigest' => $shipmentDigest
            ]);
        }
    }




    /**
     * Creates shipping label
     *
     * @return [type] [description]
     */
    public function getLabelAction()
    {
        // retrieve query string data
        $shipper = ( isset($_REQUEST['shipper'])  ) ? filter_var($_REQUEST['shipper'], FILTER_SANITIZE_STRING): '';

        // retrieve & validate form data
        $name = ( isset($_REQUEST['name'])  ) ? filter_var($_REQUEST['name'], FILTER_SANITIZE_STRING): '';
        $company = ( isset($_REQUEST['company'])  ) ? filter_var($_REQUEST['company'], FILTER_SANITIZE_STRING): '';
        $address = ( isset($_REQUEST['address'])  ) ? filter_var($_REQUEST['address'], FILTER_SANITIZE_STRING): '';
        $address2 = ( isset($_REQUEST['address2'])  ) ? filter_var($_REQUEST['address2'], FILTER_SANITIZE_STRING): '';
        $city = ( isset($_REQUEST['city'])  ) ? filter_var($_REQUEST['city'], FILTER_SANITIZE_STRING): '';
        $state = ( isset($_REQUEST['state'])  ) ? filter_var($_REQUEST['state'], FILTER_SANITIZE_STRING): '';
        $zip = ( isset($_REQUEST['zip'])  ) ? filter_var($_REQUEST['zip'], FILTER_SANITIZE_STRING): '';
        $phone = ( isset($_REQUEST['phone'])  ) ? filter_var($_REQUEST['phone'], FILTER_SANITIZE_STRING): '';
        $email = ( isset($_REQUEST['email'])  ) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_STRING): '';
        $shipping_method = ( isset($_REQUEST['shipping_method'])  ) ? filter_var($_REQUEST['shipping_method'], FILTER_SANITIZE_STRING): '';
        $length = ( isset($_REQUEST['custom_box_length'])  ) ? filter_var($_REQUEST['custom_box_length'], FILTER_SANITIZE_NUMBER_INT): '';
        $width = ( isset($_REQUEST['custom_box_width'])  ) ? filter_var($_REQUEST['custom_box_width'], FILTER_SANITIZE_NUMBER_INT): '';
        $height = ( isset($_REQUEST['custom_box_height'])  ) ? filter_var($_REQUEST['custom_box_height'], FILTER_SANITIZE_NUMBER_INT): '';
        $weight = ( isset($_REQUEST['weight'])  ) ? filter_var($_REQUEST['weight'], FILTER_SANITIZE_NUMBER_INT): '';
        $id = ( isset($_REQUEST['order_id'])  ) ? filter_var($_REQUEST['order_id'], FILTER_SANITIZE_NUMBER_INT): ''; // hidden
        $shipmentDigest = ( isset($_REQUEST['shipment_digest'])  ) ? filter_var($_REQUEST['shipment_digest'], FILTER_SANITIZE_STRING): ''; // hidden

        // USPS
        if ($shipper == 'USPS')
        {
            // create customer array to pass to Endicia
            $customer = [
                'name'     => $name,
                'company'  => $company,
                'address'  => $address,
                'address2' => $address2,
                'city'     => $city,
                'state'    => $state,
                'zip'      => $zip,
                'phone'    => $phone,
                'email'    => $email
            ];

            // test
            // echo '<h4>Customer:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();
            // echo '<h4>REQUEST:</h4>';
            // echo '<pre>';
            // print_r($_REQUEST);
            // echo '</pre>';
            // exit();
            // echo $name . '<br>';
            // echo $company . '<br>';
            // echo $address . '<br>';
            // echo $address2 . '<br>';
            // echo $city . '<br>';
            // echo $state . '<br>';
            // echo $zip . '<br>';
            // echo $phone . '<br>';
            // echo $email . '<br>';
            // echo $shipping_method . '<br>';
            // echo $length . '<br>';
            // echo $width . '<br>';
            // echo $height . '<br>';
            // echo $weight . '<br>';
            // exit();

            // create instance of Endicia class / model
            $endicia = new Endicia();

            // get response from Endicia API
            $response = $endicia->getPostageLabel($customer, $length, $width, $height, $shipping_method, $weight, $zip);

            // test
            // echo '<h4>Endicia Response for GetPostageLabel:</h4>';
            // echo '<pre>';
            // print_r($response);
            // echo '</pre>';
            // exit();

            // store data in variables
            $trackingNumber = $response->GetPostageLabelResponse->LabelRequestResponse->TrackingNumber;
            $finalPostage = $response->GetPostageLabelResponse->LabelRequestResponse->FinalPostage;
            $transactionId = $response->GetPostageLabelResponse->LabelRequestResponse->TransactionID;
            $transactionDateTime = $response->GetPostageLabelResponse->LabelRequestResponse->TransactionDateTime;
            $postmarkDate = $response->GetPostageLabelResponse->LabelRequestResponse->PostmarkDate;
            $postageBalance = $response->GetPostageLabelResponse->LabelRequestResponse->PostageBalance;
            $deliveryTimeDays = $response->GetPostageLabelResponse->LabelRequestResponse->PostagePrice->DeliveryTimeDays;


            // store data in orders table
            $result = Order::updateOrderAfterLabelCreation($id, $trackingNumber, $finalPostage, $transactionId, $transactionDateTime);

            // success - `orders` updated
            if ($result)
            {
                // success message
                echo '<script>';
                echo 'alert("The label was successfully created!")';
                echo '</script>';

                //  set URL for current window
                echo '<script>';
                echo 'window.location.href="/admin/orders/get-order?id='.$id.'"';
                echo '</script>';
                exit();
            }
            else
            {
                echo "Error updating Orders table.";
                exit();
            }
        }
        // UPS
        else
        {
            // create instance of Ups class
            $ups = new Ups();

            // make request to UPS API to accept
            $acceptResponse = $ups->shipmentAcceptRequest($shipmentDigest);

            // test
            // echo '<h4>UPS Shipment Accept Response:</h4>';
            // echo '<pre>';
            // print_r($acceptResponse);
            // echo '</pre>';
            // exit();

            $trackingNumber = $acceptResponse->trackingNumber;
            $finalPostage = $acceptResponse->totalCharges;

            // store data in orders table
            $result = Order::updateOrderAfterLabelCreation($id, $trackingNumber, $finalPostage, $transactionId = '', $transactionDateTime = '');

            // success - `orders` updated
            if ($result)
            {
                // success message
                echo '<script>';
                echo 'alert("The label was successfully created!")';
                echo '</script>';

                //  set URL for current window
                echo '<script>';
                echo 'window.location.href="/admin/orders/get-order?id='.$id.'"';
                echo '</script>';
                exit();
            }
            else
            {
                echo "Error updating Orders table.";
                exit();
            }
        }
    }



    /**
     * Updates record in orders_content with RMA data
     *
     * @return Boolean
     */
    public function assignRmaAction()
    {
        // form data
        $id = ( isset($_REQUEST['return_orderid'])  ) ? filter_var($_REQUEST['return_orderid'], FILTER_SANITIZE_NUMBER_INT): '';
        $itemid = ( isset($_REQUEST['return_itemid'])  ) ? filter_var($_REQUEST['return_itemid'], FILTER_SANITIZE_NUMBER_INT): '';
        $rma = ( isset($_REQUEST['rma_numbers'][0])  ) ? $_REQUEST['rma_numbers'][0] : '';
        $reason = ( isset($_REQUEST['return_reasons'][0])  ) ? $_REQUEST['return_reasons'][0] : '';

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // echo $id . '<br>';
        // echo $itemid . '<br>';
        // echo $rma . '<br>';
        // echo $reason . '<br>';
        // exit();

        // validate
        if ($rma == '' || $reason == '')
        {
            echo "<h3>Data missing: RMA and Reason are required fields.</h3>";
            exit();
        }

        // update record in `orders_content`
        $result = Order_content::updateAddRma($id, $itemid, $rma, $reason);

        // success
        if ($result)
        {
            // update order status in `orders`
            $result = Order::updateAsReturnPending($id);

            // success
            if ($result)
            {
                // success message
                echo '<script>';
                echo 'alert("RMA added. Order status updated to return-pending")';
                echo '</script>';

                //  set URL for current window
                echo '<script>';
                echo 'window.location.href="/admin/orders/get-order?id='.$id.'"';
                echo '</script>';
                exit();
            }
            // failure
            else
            {
                echo "Error updating order status in orders table.";
                // email webmaster
                exit();
            }
        }
        // failure
        else
        {
            echo "Error updating Orders content table.";
            // email webmaster
            exit();
        }
    }





    /**
     * Issues credit thru PayPal API & updates order status to 'returned'
     *
     * @return Boolean
     */
    public function orderReturnedAction()
    {
        // retrieve form data
        $id = ( isset($_REQUEST['id'])  ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT): '';
        $pnref = ( isset($_REQUEST['return_pnref'])  ) ? filter_var($_REQUEST['return_pnref'], FILTER_SANITIZE_STRING): '';
        $amount = ( isset($_REQUEST['return_amount'])  ) ? filter_var($_REQUEST['return_amount'], FILTER_SANITIZE_STRING): '';

        // format for PayPal
        $amount = number_format($amount, 2, '.', ''); // decimal, no comma

        // call to PayPal API to issue refund
        $response = PayPal::issueRefund($pnref, $amount);

        // test
        // echo 'PayPal API response:<br>';
        // echo '<pre>';
        // print_r($response);
        // echo '</pre>';
        // exit();

        // failure
        if (isset($response['RESULT']) && $response['RESULT'] != 0)
        {
            echo 'Error ' . $response['RESULT'] . ': ' . $response['RESPMSG'];
            // email webmaster
            exit();
        }
        // success
        else
        {
            // update record in `orders`
            $result = Order::updateAsReturned($id, $amount, $response);

            // success
            if ($result)
            {
                // success message
                echo '<script>';
                echo 'alert("A refund was successfully issued!")';
                echo '</script>';

                //  set URL for current window
                echo '<script>';
                echo 'window.location.href="/admin/orders/get-order?id='.$id.'"';
                echo '</script>';
                exit();
            }
            // failure
            else
            {
                echo "Error updating Orders table.";
                exit();
            }
        }
    }



    /**
     * Updates order status to "returned"
     *
     * @return View
     */
    public function updateToReturned()
    {
        // retrieve query string value
        $id = ( isset($_REQUEST['id'])  ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // change order status
        $result = Order::updateOrderStatusToReturned($id);

        // success
        if ($result)
        {
            // success message
            echo '<script>';
            echo 'alert("Order status changed!")';
            echo '</script>';

            //  set URL for current window
            echo '<script>';
            echo 'window.location.href="/admin/orders/get-order?id='.$id.'"';
            echo '</script>';
            exit();
        }
        // failure
        else
        {
            echo "Error updating Orders table. Contact IT Dept.";
            exit();
        }
    }




    /**
     * Retrieves order records by client last name
     * @return Object   The order record
     */
    public function searchByLastNameAction()
    {
        // retrieve form data
        $last_name = ( isset($_REQUEST['last_name'])  ) ? filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING): '';
        $order_status = ( isset($_REQUEST['status'])  ) ? filter_var($_REQUEST['status'], FILTER_SANITIZE_STRING): '';

        $orders = Order::getOrderByLastName($last_name, $order_status);

        // test
        // echo '<pre>';
        // print_r($orders);
        // echo '</pre>';
        // exit();

        // render view
        View::renderTemplate('Admin/Armalaser/Show/orders.html', [
            'pagetitle'      => 'Orders: search results',
            'orders'         => $orders,
            'searched'       => $last_name,
            'order_status'   => $order_status,
            'search_results' => true
        ]);
    }



    /**
     * Inserts new comment into `comments` table
     */
    public function addCommentAction()
    {
        // retrieve form data
        $comment = ( isset($_REQUEST['comment'])  ) ? $_REQUEST['comment'] : '';
        $order_id = ( isset($_REQUEST['order_id'])  ) ? filter_var($_REQUEST['order_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $user_id = ( isset($_REQUEST['user_id'])  ) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_NUMBER_INT): '';

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // exit();

        // get user data
        $user = User::getUserById($user_id);

        // get order
        $order = Order::getOrderData($order_id);

        // test
        // echo '<pre>';
        // print_r($order);
        // echo '</pre>';
        // exit();

        // identify client as customer or guest
        if  ($order->customerid == 0)
        {
            $customer_id = $order->guestid;
            $customer_type = 'guest';
        }
        else
        {
            $customer_id = $order->customerid;
            $customer_type = 'customer';
        }

        // insert into `comments` table
        $result = Comment::insertComment($order, $user, $comment, $customer_id, $customer_type);

        // success
        if ($result)
        {
            // success message
            echo '<script>';
            echo 'alert("The comment was successfully added!")';
            echo '</script>';

            //  set URL for current window
            echo '<script>';
            echo 'window.location.href="/admin/orders/get-order?id='.$order->id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            echo "Error inserting comment into Comments table.";
            // email webmaster
            exit();
        }
    }




    /**
     * adds laser serial number to `orders_content` table
     *
     * @return Boolean
     */
    public function assignSerialNumberAction()
    {
        // get form data from hidden input
        $orderid    = ( isset($_REQUEST['order_id'])  ) ? filter_var($_REQUEST['order_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $buyer_type = ( isset($_REQUEST['buyer_type'])  ) ? filter_var($_REQUEST['buyer_type'], FILTER_SANITIZE_STRING): '';
        $customerid = ( isset($_REQUEST['customer_id'])  ) ? filter_var($_REQUEST['customer_id'], FILTER_SANITIZE_NUMBER_INT): ''; // customer, guest or caller ID
        $first_name = ( isset($_REQUEST['customer_firstname'])  ) ? filter_var($_REQUEST['customer_firstname'], FILTER_SANITIZE_STRING): '';
        $last_name  = ( isset($_REQUEST['customer_lastname'])  ) ? filter_var($_REQUEST['customer_lastname'], FILTER_SANITIZE_STRING): '';
        $address    = ( isset($_REQUEST['customer_address'])  ) ? filter_var($_REQUEST['customer_address'], FILTER_SANITIZE_STRING): '';
        $city       = ( isset($_REQUEST['customer_city'])  ) ? filter_var($_REQUEST['customer_city'], FILTER_SANITIZE_STRING): '';
        $state      = ( isset($_REQUEST['customer_state'])  ) ? filter_var($_REQUEST['customer_state'], FILTER_SANITIZE_STRING): '';
        $zip        = ( isset($_REQUEST['customer_zip'])  ) ? filter_var($_REQUEST['customer_zip'], FILTER_SANITIZE_STRING): '';
        $purchase_date = ( isset($_REQUEST['purchase_date'])  ) ? filter_var($_REQUEST['purchase_date'], FILTER_SANITIZE_STRING): '';
        $email      = ( isset($_REQUEST['customer_email'])  ) ? filter_var($_REQUEST['customer_email'], FILTER_SANITIZE_EMAIL): '';

        // update orders_content record with serial number(s)
        $result = Order::registerLaserWarranty($orderid);

        // success
        if ($result)
        {
            // create array of additional warranty registration data
            $data = [
                'orderid'       => $orderid,
                'type'          => $buyer_type,
                'customerid'    => $customerid,
                'first_name'    => $first_name,
                'last_name'     => $last_name,
                'address'       => $address,
                'city'          => $city,
                'state'         => $state,
                'zipcode'       => $zip,
                'purchase_date' => $purchase_date,
                'email'         => $email
            ];

            // enter data in `warranty` table
            $result = Warrantyregistration::insertData($data, $_REQUEST['lasers'], $_REQUEST['serial_numbers'], $_REQUEST['laserids']);

            // success
            if ($result)
            {
                // success message
                echo '<script>';
                echo 'alert("Serial number & warranty data successfully added to database tables!")';
                echo '</script>';

                //  set URL for current window
                echo '<script>';
                echo 'window.location.href="/admin/orders/get-order?id='.$orderid.'"';
                echo '</script>';
                exit();
            }
            else
            {
                echo "Error inserting data into Warranty table.";
                // email webmaster
                exit();
            }
        }
        // failure
        else
        {
            echo "Error updating orders_content table with serial number.";
            // email webmaster
            exit();
        }
    }




    /**
     * returns view with form for parts and products to create new order
     *
     * @return View
     */
    public function newOrderAction()
    {
        // echo "Connected!"; exit();

        // get order ID from query string
        $id   = ( isset($_REQUEST['id'])  ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT): ''; // customer ID
        $type = ( isset($_REQUEST['type'])  ) ? filter_var($_REQUEST['type'], FILTER_SANITIZE_STRING): ''; // buyer type

        if ($type == 'customer')
        {
            $customer = Customer::getCustomer($id);

            // test
            // echo '<h4>Customer:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();
        }
        if ($type == 'guest')
        {
            $customer = Guest::getGuest($id);

            // test
            // echo '<h4>Customer:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();
        }
        else if ($type == 'caller')
        {
            $customer = Caller::getCaller($id);

            // test
            // echo '<h4>Customer:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();
        }
        else if ($type == 'dealer')
        {
            $customer = Dealer::getDealer($id);

            // test
            // echo '<h4>Dealer:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();
        }
        else if ($type == 'partner')
        {
            $customer = Partner::getPartner($id);

            // test
            // echo '<h4>Partner:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();
        }

        // get parts
        $parts = Part::getParts();

        // test
        // echo '<h4>Parts:</h4>';
        // echo '<pre>';
        // print_r($parts);
        // echo '</pre>';
        // exit();

        // get trseries
        $trseries = Trseries::getLasers();

        // test
        // echo '<h4>TR Series:</h4>';
        // echo '<pre>';
        // print_r($trseries);
        // echo '</pre>';
        // exit();

        // get gto/flx
        $gtoflx = Gtoflx::getLasersForAdmin();

        // test
        // echo '<h4>GTO/FLX:</h4>';
        // echo '<pre>';
        // print_r($gtoflx);
        // echo '</pre>';
        // exit();

        // get stingray
        $stingrays = Stingray::getLasers();

        // test
        // echo '<h4>Stingray:</h4>';
        // echo '<pre>';
        // print_r($stingrays);
        // echo '</pre>';
        // exit();

        // get holsters
        $holsters = Holster::getHolstersForAdmin();

        // test
        // echo '<h4>Holsters:</h4>';
        // echo '<pre>';
        // print_r($holsters);
        // echo '</pre>';
        // exit();

        // get accessories
        $accessories = Accessory::getAccessories();

        // test
        // echo '<h4>Accessories:</h4>';
        // echo '<pre>';
        // print_r($accessories);
        // echo '</pre>';
        // exit();

        // get batteries
        $batteries = Battery::getBatteries();

        // test
        // echo '<h4>Batteries:</h4>';
        // echo '<pre>';
        // print_r($batteries);
        // echo '</pre>';
        // exit();

        // get toolkits
        $toolkits = Toolkit::getToolkits();

        // test
        // echo '<h4>Toolkits:</h4>';
        // echo '<pre>';
        // print_r($toolkits);
        // echo '</pre>';
        // exit();

        // get flx
        $flxs = Flx::getAllFlxForAdmin();

        // test
        // echo '<h4>FLX:</h4>';
        // echo '<pre>';
        // print_r($flxs);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Armalaser/Show/replacement-parts-products.html', [
            'pagetitle'   => 'Create order for replacement parts',
            'type'        => $type,
            'customer'    => $customer,
            'parts'       => $parts,
            'trseries'    => $trseries,
            'gtoflx'      => $gtoflx,
            'stingrays'   => $stingrays,
            'holsters'    => $holsters,
            'accessories' => $accessories,
            'batteries'   => $batteries,
            'toolkits'    => $toolkits,
            'flxs'        => $flxs
        ]);
    }



    // - - - - - - - - - class functions - - - - - - - - - - - - - - - - - - //

    public function getDaysBetweenDates($shipped_date)
    {
        // get current time
        $now = time();

        // convert shipped date to string
        $shipDate = strtotime($shipped_date);

        // calculate difference
        $date_diff = $now - $shipDate;

        // convert to days
        $daysSinceShipped = round($date_diff / (60*60*24));

        return $daysSinceShipped;
    }



    /**
     * Retrieves lasers in order
     * @param  Array            $order_content   The items in the order
     * @return Array or String                   Array of lasers, or false
     */
    public function getLasersInOrder($order_content)
    {
        // check order for lasers & store in array
        foreach ($order_content as $item)
        {
            if ($item->itemid >= 1000 and $item->itemid <= 1999      // TR Series ID range
                || $item->itemid >= 2000  and $item->itemid <= 2999  // GTO/FLX ID range
                || $item->itemid >= 3000  and $item->itemid <= 3999) // Stingray ID range
            {
                $lasers[] = $item->name . ' -  #' . $item->itemid;
            }
        }

        // test
        // echo '<h4>Lasers in order:</h4>';
        // if (!empty($lasers))
        // {
        //     echo '<pre>';
        //     print_r($lasers);
        //     echo '</pre>';
        //     echo 'Count: ' . count($lasers);
        // }
        // else
        // {
        //     echo "No lasers in order.<br>";
        // }
        // exit();

        // return
        if (empty($lasers))
        {
            return false;
        }
        else
        {
            return $lasers;
        }
    }



    //  - - - - - - - - -  new above  - - - - - - - - - - - - - - - - - - - - //
}
