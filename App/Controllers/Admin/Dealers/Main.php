<?php

namespace App\Controllers\Admin\Dealers;

use \Core\View;
use \App\Models\Dealer;
use \App\Models\State;
use \App\Models\Order;
use \App\Models\Trseries;
use \App\Models\Trseriesnp;
use \App\Models\Gtoflx;
use \App\Models\Gtonp;
use \App\Models\Stingray;
use \App\Models\Stingraynp;
use \App\Models\Holster;
use \App\Models\Battery;
use \App\Models\Toolkit;
use \App\Models\Accessory;
use \App\Models\Flx;
use \App\Models\Customer;
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

        // redirect logged in user who is not a dealer
        if(isset($_SESSION['user']) && $_SESSION['userType'] != 'dealer')
        {
            header("Location: /");
            exit();
        }
    }




    /**
     * retrieves dealer record
     *
     * @return Object  The dealer record
     */
    public function getAccountAction()
    {
        // retrieve dealer ID from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get dealer
        $dealer = Customer::getCustomer($id);

        // test
            // echo '<pre>';
            // print_r($dealer);
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

        View::renderTemplate('Admin/Dealers/Show/account.html', [
            'customer' => $dealer,
            'years'    => $years,
            'states'   => $states,
            'boolArr'  => $boolArr
        ]);
    }



    public function updateAccount()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        $result = Customer::updateCustomerAccount($id);

        if ($result)
        {
            echo '<script>alert("Account updated successfully.")</script>';
            echo '<script>window.location.href = "/admin/dealers/main/get-account?id='.$id.'&type=dealer"</script>';
            exit();
        }
        else
        {
            echo "Error updating account.";
            exit();
        }
    }



    /**
     * Dealer functionality - updates dealer credentials in `customers`
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
        // echo $confirm_newpassword . '<br>';
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

        // update dealer record
        $result = Customer::updateAccountPassword($id, $password);

        if ($result)
        {
            echo '<script>alert("Your Password was successfully changed.\n\n\You will now be logged out.\n\n\You can log back in with your new password.")</script>';
            echo '<script>window.location.href="/admin/logout"</script>';
        }
    }




    /**
     *
     * Displays dealer account info & dealer orders
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
        $orders = Order::getMyOrders($id);

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

            View::renderTemplate('Admin/Dealers/Show/orders.html', [
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
        $dealerid = ( isset($_REQUEST['buyerid'])  ) ? filter_var($_REQUEST['buyerid'], FILTER_SANITIZE_NUMBER_INT): '';

        $customer = Dealer::getDealer($dealerid);

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




    public function getTrseriesAction()
    {
        // get trseries lasers
        $lasers = Trseries::getLasers();

        // test
        // echo '<pre>';
        // print_r($lasers);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Dealers/Show/trseries.html',[
            'pagetitle'   => 'TR Series - Dealers',
            'lasers'      => $lasers,
            'laserSeries' => 'trseries' // also serves as name of file
        ]);
    }




    public function getTrseriesnpAction()
    {
        // get trseriesnp lasers
        $lasers = Trseriesnp::getLasers();

        // test
        // echo '<pre>';
        // print_r($lasers);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Dealers/Show/trseriesnp.html',[
            'pagetitle'   => 'TR Series NP - Dealers',
            'lasers'      => $lasers,
            'laserSeries' => 'trseriesnp' // also serves as name of file
        ]);
    }




    public function getGtoflxAction()
    {
        // get gto/flx lasers
        $lasers = Gtoflx::getLasers();

        // test
        // echo '<pre>';
        // print_r($lasers);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Dealers/Show/gtoflx.html',[
            'pagetitle'   => 'GTO/FLX - Dealers',
            'lasers'      => $lasers,
            'laserSeries' => 'gtoflx' // also serves as name of file
        ]);
    }



    public function getGtonpAction()
    {
        // get gto lasers
        $lasers = Gtonp::getLasers();

        // test
        // echo '<pre>';
        // print_r($lasers);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Dealers/Show/gtonp.html',[
            'pagetitle'   => 'GTO & GTO NP - Dealers',
            'lasers'      => $lasers,
            'laserSeries' => 'gtonp' // also serves as name of file
        ]);
    }




    public function getStingraysAction()
    {
        // get stingray lasers
        $lasers = Stingray::getLasers();

        //  get pistol make & model matches
        $stingray_matches = Stingray::getPistolMatches();

        // test
        // echo '<pre>';
        // print_r($lasers);
        // echo '</pre>';
        // exit();

        // test
        // echo '<pre>';
        // print_r($stingray_matches);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Dealers/Show/stingray.html',[
            'pagetitle'   => 'STINGRAY CLASSIC - Dealers',
            'lasers'      => $lasers,
            'matches'     => $stingray_matches,
            'laserSeries' => 'stingray' // also serves as name of file
        ]);
    }




    public function getStingraysnpAction()
    {
        // get stingray lasers
        $lasers = Stingraynp::getLasers();

        //  get pistol make & model matches
        $stingray_matches = Stingray::getPistolMatches();

        // test
        // echo '<pre>';
        // print_r($lasers);
        // echo '</pre>';
        // exit();

        // test
        // echo '<pre>';
        // print_r($stingray_matches);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Dealers/Show/stingraynp.html',[
            'pagetitle'   => 'STINGRAY CLASSIC NP - Dealers',
            'lasers'      => $lasers,
            'matches'     => $stingray_matches,
            'laserSeries' => 'stingraynp' // also serves as name of file
        ]);
    }








    /**
     * retrieves laser(s) by search criterion
     *
     * @return object  The matching laser(s)
     */
    public function searchByModelAction()
    {

        // get laser model name from form
        $laser_model = ( isset($_REQUEST['laser_model'])  ) ? filter_var($_REQUEST['laser_model']): '';
        $laser_series = ( isset($_REQUEST['laser_series'])  ) ? filter_var($_REQUEST['laser_series']): '';
        $referer = $_SERVER['HTTP_REFERER'];

        if($laser_model == '' || $laser_series == '')
        {
            header("Location: $referer");
            exit();
        }

        // search TR SERIES
        switch($laser_series)
        {
            CASE 'trseries':
                $lasers = Trseries::getLaser($laser_model);
                $pagetitle = 'TR Series - Dealers';
                $laser_series = 'trseries';
                break;
            CASE 'gtoflx':
                $lasers = Gtoflx::getLaser($laser_model);
                $pagetitle = 'GTO/FLX - Dealers';
                $laser_series = 'gtoflx';
                break;
            CASE 'stingray':
                $lasers = Stingray::getLaser($laser_model);
                $pagetitle = 'STINGRAY CLASSIC - Dealers';
                $laser_series = 'stingray';
                break;
        }

        // test
        // echo '<pre>';
        // print_r($lasers);
        // echo '</pre>';
        //exit();

        // render view
        View::renderTemplate('Admin/Dealers/Show/'.$laser_series.'.html', [
            'pagetitle'   => $pagetitle,
            'lasers'      => $lasers,
            'searched'    => $laser_model,
            'laserSeries' => $laser_series
        ]);
    }




    /**
     * retrieves holsters for product list for Dealers
     *
     * @return
     */
    public function getHolstersAction()
    {
        // get holsters
        $holsters = Holster::getHolstersForDealers();

        // test
        // echo '<pre>';
        // print_r($holsters);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Dealers/Show/holsters.html', [
            'holsters'  => $holsters,
            'pagetitle' => 'Holsters - Dealers'
        ]);
    }




    /**
     * retrieves batteries for product list for Dealers
     *
     * @return
     */
    public function getBatteriesAction()
    {
        // get batteries
        $batteries = Battery::getBatteries();

        // test
        // echo '<pre>';
        // print_r($batteries);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Dealers/Show/batteries.html', [
            'batteries' => $batteries,
            'pagetitle' => 'Batteries - Dealers'
        ]);
    }




    /**
     * retrieves tool kits for product list for Dealers
     *
     * @return
     */
    public function getToolkits()
    {
        // get tool kits
        $toolkits = Toolkit::getToolkits();

        // test
        // echo '<pre>';
        // print_r($toolkits);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Dealers/Show/toolkits.html', [
            'toolkits'  => $toolkits,
            'pagetitle' => 'Tool Kits - Dealers'
        ]);
    }




    /**
     * retrieves accessories for product list for Dealers
     *
     * @return
     */
    public function getAccessories()
    {
        // get accessories
        $accessories = Accessory::getAccessories();

        // test
        // echo '<pre>';
        // print_r($accessories);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Dealers/Show/accessories.html', [
            'accessories' => $accessories,
            'pagetitle'   => 'Accessories - Dealers'
        ]);
    }




    /**
     * retrieves all flxs for product list for logged in Dealer
     *
     * @return View
     */
    public function getFlxsAction()
    {
        // get flx
        $flxs = Flx::getAllFlx();

        // test
        // echo '<pre>';
        // print_r($flxs);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Dealers/Show/flxs.html', [
            'flxs'      => $flxs,
            'pagetitle' => "FLXs - Dealer"
        ]);
    }

}
