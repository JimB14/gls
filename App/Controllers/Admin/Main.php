<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\User;
use \App\Models\Customer;
use \App\Models\Guest;
use \App\Models\Caller;
use \App\Models\Partner;
use \App\Models\Dealeruser;
use \App\Models\Dealer;
use \App\Models\Product;


/**
 * Main controller in Admin
 *
 * PHP version 7.0
 */
class Main extends \Core\Controller
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
    }


    /**
     * Show the Admin Panel index page
     *
     * @return void
     */
    public function indexAction()
    {
        // retrieve GET variable
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';
        // customer or partner or dealer
        $type = (isset($_REQUEST['type'])) ? filter_var($_REQUEST['type'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo $user_id . '<br>';
        // echo $type . '<br>';
        // exit();

        // admin user
        if ($type == 'admin')
        {
            // get user data from User model
            $user = User::getUserData($user_id);

            // test
            // echo '<pre>';
            // print_r($user);
            // echo '</pre>';
            // exit();

            // render view & pass $broker object
            View::renderTemplate('Admin/index.html', [
                'user' => $user
            ]);
        }
        // admin user
        else if ($type == 'supervisor')
        {
            // get user data from User model
            $user = User::getUserData($user_id);

            // test
            // echo '<pre>';
            // print_r($user);
            // echo '</pre>';
            // exit();

            // render view & pass $broker object
            View::renderTemplate('Admin/index.html', [
                'user' => $user
            ]);
        }
        else if ($type == 'customer')
        {
            // get user data from User model
            $user = Customer::getCustomer($user_id);

            // test
            // echo '<pre>';
            // print_r($user);
            // echo '</pre>';
            // exit();

            // render view & pass $broker object
            View::renderTemplate('Admin/index.html', [
                'user' => $user
            ]);
        }
        else if ($type == 'partner')
        {
            // get partner data from Partner model
            $partner = Partner::getPartner($user_id);

            // test
            // echo '<pre>';
            // print_r($partner);
            // echo '</pre>';
            // exit();

            // render view & pass $broker object
            View::renderTemplate('Admin/index.html', [
                'user' => $partner
            ]);
        }
        else if ($type == 'dealer')
        {
            // get dealer data from Dealeruser model
            $dealer = Dealer::getDealer($user_id);

            // test
            // echo '<pre>';
            // print_r($dealer);
            // echo '</pre>';
            // exit();

            // render view & pass $broker object
            View::renderTemplate('Admin/index.html', [
                'user' => $dealer
            ]);
        }
    }



    /**
     * retrieves customer data from one of three tables
     *
     * @return Object   The customer record
     */
    public function getCustomerAction()
    {
        // retrieve param data (customer, caller or guest)
        $buyer_type = $this->route_params['buyerType'];
        $id = $this->route_params['id'];

        if ($buyer_type == 'customer')
        {
            // get customer data
            $customer = Customer::getCustomer($id);

            // add buyer type
            $customer->type = $buyer_type;
        }
        else if ($buyer_type == 'guest')
        {
            // get guest data
            $customer = Guest::getGuest($id);

            // add buyer type
            $customer->type = $buyer_type;
        }
        else if ($buyer_type == 'caller')
        {
            // get caller data
            $customer = Caller::getCaller($id);

            // add buyer type
            $customer->type = $buyer_type;
        }
        else if ($buyer_type == 'dealer')
        {
            // get dealer data
            $customer = Dealer::getDealer($id);

            // add buyer type
            $customer->type = $buyer_type;
        }
        else if ($buyer_type == 'partner')
        {
            // get caller data
            $customer = Partner::getPartner($id);

            // add buyer type
            $customer->type = $buyer_type;
        }

        // test
        // echo '<pre>';
        // print_r($customer);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Armalaser/Show/customer.html', [
            'pagetitle' => ucfirst($customer->type) . ' Details (read only)',
            'customer'  => $customer
        ]);
    }

}
