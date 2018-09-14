<?php

namespace App\Controllers\Admin\Partners;

use \Core\View;
use \App\Models\Partner;
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

        // redirect logged in user who is not a partner
        if(isset($_SESSION['user']) && $_SESSION['userType'] != 'partner')
        {
            header("Location: /");
            exit();
        }
    }




    /**
     * retrieves partner record
     *
     * @return Object  The partner record
     */
    public function getAccountAction()
    {
        // retrieve partner ID from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get partner
        $partner = Partner::getPartner($id);

        // test
        // echo '<pre>';
        // print_r($partner);
        // echo '</pre>';
        // exit();

        // get states
        $states = State::getStates();

        $firstYear = date('Y') - 55;
        $thisYear = date('Y');

        // create array of years
        $years = range( $firstYear, $thisYear);

        View::renderTemplate('Admin/Partners/Show/account.html', [
            'partner' => $partner,
            'years'  => $years,
            'states' => $states
        ]);
    }




    /**
     *
     * Displays partner account info & partner orders
     *
     * @return View
     */
    public function getOrdersAction()
    {
        // get status from query string
        $id = ( isset($_REQUEST['id'])  ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // get buyer data
        $customer = Partner::getPartner($id);

        // test
        // echo '<pre>';
        // print_r($customer);
        // echo '</pre>';
        // exit();

        // get orders data
        $orders = Order::getMyOrders($type='partner', $id);

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

            View::renderTemplate('Admin/Partners/Show/orders.html', [
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
        $partnerid = ( isset($_REQUEST['buyerid'])  ) ? filter_var($_REQUEST['buyerid'], FILTER_SANITIZE_NUMBER_INT): '';

        $customer = Partner::getPartner($partnerid);

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

        View::renderTemplate('Admin/Partners/Show/trseries.html',[
            'pagetitle'   => 'TR Series - Partners',
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

        View::renderTemplate('Admin/Partners/Show/trseriesnp.html',[
            'pagetitle'   => 'TR Series NP - Partners',
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

        View::renderTemplate('Admin/Partners/Show/gtoflx.html',[
            'pagetitle'   => 'GTO/FLX - Partners',
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
            'pagetitle'   => 'GTO & GTO NP - Partners',
            'lasers'      => $lasers,
            'laserSeries' => 'gtonp' // also serves as name of file
        ]);
    }




    public function getStingraysAction()
    {
        // get gto/flx lasers
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

        View::renderTemplate('Admin/Partners/Show/stingray.html',[
            'pagetitle'   => 'STINGRAY CLASSIC - Partners',
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
            'pagetitle'   => 'STINGRAY CLASSIC NP - Partners',
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
                $pagetitle = 'TR Series - Partners';
                $laser_series = 'trseries';
                break;
            CASE 'gtoflx':
                $lasers = Gtoflx::getLaser($laser_model);
                $pagetitle = 'GTO/FLX - Partners';
                $laser_series = 'gtoflx';
                break;
            CASE 'stingray':
                $lasers = Stingray::getLaser($laser_model);
                $pagetitle = 'STINGRAY CLASSIC - Partners';
                $laser_series = 'stingray';
                break;
        }

        // test
        // echo '<pre>';
        // print_r($lasers);
        // echo '</pre>';
        //exit();

        // render view
        View::renderTemplate('Admin/Partners/Show/'.$laser_series.'.html', [
            'pagetitle'   => $pagetitle,
            'lasers'      => $lasers,
            'searched'    => $laser_model,
            'laserSeries' => $laser_series
        ]);
    }




    /**
     * retrieves holsters for product list for Partners
     *
     * @return
     */
    public function getHolstersAction()
    {
        // get holsters
        $holsters = Holster::getHolstersForPartners();

        // test
        // echo '<pre>';
        // print_r($holsters);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Partners/Show/holsters.html', [
            'holsters'  => $holsters,
            'pagetitle' => 'Holsters - Partners'
        ]);
    }




    /**
     * retrieves batteries for product list for Partners
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

        View::renderTemplate('Admin/Partners/Show/batteries.html', [
            'batteries' => $batteries,
            'pagetitle' => 'Batteries - Partners'
        ]);
    }




    /**
     * retrieves tool kits for product list for Partners
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

        View::renderTemplate('Admin/Partners/Show/toolkits.html', [
            'toolkits'  => $toolkits,
            'pagetitle' => 'Tool Kits - Partners'
        ]);
    }




    /**
     * retrieves accessories for product list for Partners
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

        View::renderTemplate('Admin/Partners/Show/accessories.html', [
            'accessories' => $accessories,
            'pagetitle'   => 'Accessories - Partners'
        ]);
    }




    /**
     * retrieves all flxs for product list for logged in Partner
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

        View::renderTemplate('Admin/Partners/Show/flxs.html', [
            'flxs'      => $flxs,
            'pagetitle' => "FLXs - Partner"
        ]);
    }
}
