<?php

namespace App\Controllers\Admin;

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
use \App\Config;
use \App\Mail;
use \Core\View;


class Partnercart extends \Core\Controller
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
            || $_SESSION['userType'] == 'customer')
        {
            header("Location: /");
            exit();

        }
    }




    /**
     * adds item to shopping cart
     *
     * @return View         the show battery page
     */
    public function addToCartAction()
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
        $mvc_model   = $this->route_params['mvcModel'];
        $id          = $this->route_params['id'];
        $pistolMfr   = $this->route_params['pistolMfr'];
        $laser_model = $this->route_params['laserModel'];
        // $tableName = ucfirst($this->route_params['laserSeries']);

        // test
        // echo $refererURL . '<br>';
        // echo 'Model: ' . $mvc_model . '<br>';
        // echo $id . '<br>';
        // echo $pistolMfr . '<br>';
        // echo $laser_model . '<br>';
        // echo $_SESSION['cart_count'] . '<br>';
        // exit();

        // determine query by value of $mvc_model
        switch ($mvc_model) {
            case 'trseries':
                $this->item = Trseries::getTrseriesDetailsForDealerCart($id);
                break;
            case 'gtoflx':
                $this->item = Gtoflx::getGtoflxDetailsForDealerCart($id);
                break;
            case 'stingray':
                $this->item = Stingray::getStingrayDetailsForDealerCart($id);
                break;
            case 'holster':
                $this->item = Holster::getHolsterDetailsForDealerCart($id);
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
                    'price'         => $this->item->price_partner,
                    'pistolMfr'     => $this->item->pistolMfr,
                    'pistol_models' => $this->item->pistol_models,
                    'mvc_model'     => $mvc_model
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
                    'price'         => $this->item->price_partner,
                    'thumb'         => $this->item->thumb,
                    'trseriesModel' => $this->item->trseries_model,
                    'pistolMfr'     => $this->item->pistolMfr,
                    'pistol_models' => $this->item->pistol_models,
                    'mvc_model'     => $mvc_model
                ];
            }
        }


        /* - - - - - - - batteries, tool kits, accessories   - - - - - - - - */
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
                    'price'       => $this->item->price_partner,
                    'thumb'       => $this->item->image_thumb,
                    'mvc_model'   => $mvc_model
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
                    'price'         => $this->item->price_partner,
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

        // store IDs from cart in array
        foreach($_SESSION['cart'] as $key => $value) {
            $ids[] = $key;
        }

        // store in GLOBAL array
        $_SESSION['ids'] = $ids;

        // test
        // echo '$_SESSION[\'ids\']:';
        // echo '<pre>';
        // print_r($_SESSION['ids']);
        // echo '</pre>';
        // exit();

        header("Location: $refererURL");
        exit();
    }

}
