<?php

namespace App\Controllers\Admin\Customers;

use \Core\View;
use \App\Models\Customer;
use \App\Models\State;
use \App\Models\Order;
use \App\Models\Trseries;
use \App\Models\Gtoflx;
use \App\Models\Stingray;
use \App\Models\Holster;
use \App\Models\Battery;
use \App\Models\Toolkit;
use \App\Models\Accessory;
use \App\Models\Flx;
use \App\Models\Show;
use \App\Models\Dealer;
use \App\Models\Warrantyregistration;
use \App\Mail;


class Main extends \Core\Controller
{

    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        // redirect user not logged in
        if(!isset($_SESSION['user']))
        {
            header("Location: /");
            exit();
        }

        // redirect logged in user who is not a customer
        if(isset($_SESSION['user']) && $_SESSION['userType'] != 'customer')
        {
            header("Location: /");
            exit();
        }
    }




    /**
     * retrieves customer record
     *
     * @return Object  The customer record
     */
    public function getAccountAction()
    {
        // retrieve customer ID from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get customer
        $customer = Customer::getCustomer($id);

        // test
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();

        // get states
        $states = State::getStates();

        $firstYear = date('Y') - 55;
        $thisYear = date('Y');

        // create array of years
        $years = range( $firstYear, $thisYear);

        // create array for email opt-in
        $boolArr = [1,2];

        View::renderTemplate('Admin/Customers/Show/account.html', [
            'customer' => $customer,
            'years'    => $years,
            'states'   => $states,
            'boolArr'  => $boolArr
        ]);
    }




    /**
     *
     * Displays customer account info & customer orders
     *
     * @return View
     */
    public function getOrdersAction()
    {
        // get status from query string
        $id = ( isset($_REQUEST['id'])  ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // get buyer data
        $customer = Customer::getCustomer($id);

        // test
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();

        // get orders data
        $orders = Order::getMyOrders($type='customer', $id);

        // test
            // echo '<pre>';
            // print_r($orders);
            // echo '</pre>';
            // exit();

        // create empty array
        $orderIds = [];

        // get order IDs from multidimensional object
        foreach ($orders as $obj)
        {
            // get value of 'id'
            foreach ($obj as $name => $value)
            {
                if ($name == 'id')
                {
                    $orderIds[] = $value;
                }
            }
        }

        // test
            // echo '<pre>';
            // print_r($orderIds);
            // echo '</pre>';
            // exit();

        // no orders
        if (empty($orderIds))
        {
            echo "No orders found.";
            exit();
        }
        else
        {
            // implde array into comma separated string
            $idString = implode(',', $orderIds);

            // get orders content
            $orders_content = Order::getContentOfOrders($id, $idString);

            // test
                // echo '<pre>';
                // print_r($orders_content);
                // echo '</pre>';
                // exit();

            View::renderTemplate('Admin/Customers/Show/orders.html', [
                'pagetitle'      => 'My Orders',
                'customer'       => $customer,
                'orders'         => $orders,
                'orders_content' => $orders_content,
            ]);
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

        $customer = Customer::getCustomer($customerid);

        // test
            // echo '<h4>Customer:</h4>';
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();

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

        View::renderTemplate('Admin/Customers/Show/order-details.html', [
            'pagetitle'     => 'Order Details',
            'customer'      => $customer,
            'order_content' => $order_content,
            'order'         => $order
        ]);
    }




    public function registrationAction()
    {
        // get states for drop-down
        $states = State::getStates();

        // sellers array
        $sellers = [
          'amazon' => 'Amazon',
          'midwayusa' => 'MidwayUSA',
          'opticsplanet' => 'OpticsPlanet',
          'buds' => 'Buds Gun Shop',
          'arm_rep_gunshow' => 'ArmaLaser rep at Gun Show',
          'dealer' => 'ArmaLaser authorized dealer',
          'other' => 'Other'
        ];

        // get dealers
        $dealers = Dealer::getDealers();

        //get gun shows
        $shows = Show::getShows();

        // laser series[]
        $series =
        [
            'trseries' => 'TR SERIES',
            'gtoflx'   => 'GTO/FLX',
            'stingray' => 'STINGRAY CLASSIC'
        ];

        // render view
        View::renderTemplate('Admin/Customers/Add/warrantyregistration.html', [
            'pagetitle' => 'Warranty registration',
            'states'    => $states,
            'sellers'   => $sellers,
            'dealers'   => $dealers,
            'shows'     => $shows,
            'series'    => $series
        ]);
    }




    public function submitWarrantyRegistrationAction()
    {
        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        // process/validate form data
        $data = Warrantyregistration::processWarrantyRegistrationData();

        // test
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // exit();

        $honeypot = (isset($_REQUEST['honeypot'])) ? filter_var($_REQUEST['honeypot'], FILTER_SANITIZE_STRING): '';

        // check honeypot for robot content
        $honeypot = filter_var($_REQUEST['honeypot'], FILTER_SANITIZE_STRING);

        if($honeypot != '')
        {
            return false;
            exit();
        }

        // store user name in variable
        $name = $data['first_name'] . ' ' . $data['last_name'];

        // store in warranty table in database
        $result = Warrantyregistration::storeWarrantyRegistration($data);

        // send courtesy email to company recipients
        if($result)
        {
            // send data to mail recipient(s)
            $mail_result = Mail::mailWarrantyRegistrationData($data);
        }

        // display success message to user
        if($mail_result)
        {
            $message = "Your warranty registration information was sent!";

            View::renderTemplate('Success/index.html', [
                'warrantysuccess' => 'true',
                'name'            => $name,
                'message'         => $message
            ]);
        }
        else
        {
            echo "Mailer error";
            exit();
        }
    }




    /**
     * Updates customer account in `customer`
     *
     * @return boolean
     */
    public function updateAccountAction()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // update partner account
        $result = Customer::updateCustomerAccount($id);

        if($result)
        {
            echo '<script>alert("Successfully updated!")</script>';
            echo '<script>window.location.href="/admin/customers/main/get-account?id='.$id.'"</script>';
        }
    }




    /**
     * Updates customer password in `customers`
     *
     * @return boolean
     */
    public function updatePasswordAction()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // retrieve form values
        $password = ( isset($_REQUEST['new_password'])  ) ? filter_var($_REQUEST['new_password'], FILTER_SANITIZE_STRING) : '';
        $confirm_password = ( isset($_REQUEST['confirm_password'])  ) ? filter_var($_REQUEST['confirm_password'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // echo $password . '<br>';
        // echo $confirm_password . '<br>';
        // exit();

        // validation if JavaScript fails or is disabled
        if($password == '' || $confirm_password == '')
        {
            $errorMessage = 'All fields required.';
            View::renderTemplate('Error/index.html', [
                'errorMessage' => $errorMessage
            ]);
           exit();
        }

        if(strlen($password) < 6 )
        {
            $errorMessage = 'Password must be at least 6 characters in length.';
            View::renderTemplate('Error/index.html', [
                'errorMessage' => $errorMessage
            ]);
            exit();
        }

        if($password != $confirm_password)
        {
            $errorMessage = 'Passwords do not match. Please check and try again.';
            View::renderTemplate('Error/index.html', [
                'errorMessage' => $errorMessage
            ]);
            exit();
        }

        // hash new password
        $password = password_hash($password, PASSWORD_DEFAULT);

        // update customer record
        $result = Customer::updateAccountPassword($id, $password);

        if ($result)
        {
            echo '<script>alert("Your Password was successfully changed.\n\n\You will now be logged out.\n\n\You can log back in with your new password.")</script>';
            echo '<script>window.location.href="/admin/logout"</script>';
        }
    }
}
