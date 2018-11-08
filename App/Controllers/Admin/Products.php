<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\Product;
use \App\Models\Trseries;
use \App\Models\Gtoflx;
use \App\Models\Stingray;
use \App\Models\Flx;
use \App\Models\Holster;
use \App\Models\Accessory;
use \App\Models\Battery;
use \App\Models\Toolkit;


class Products extends \Core\Controller
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




    public function ajaxGetProducts() 
    {
        $category = (isset($_REQUEST['category'])) ? filter_var($_REQUEST['category'], FILTER_SANITIZE_STRING) : '';
        
        // echo "Connected to ajaxGet(): ' . $category;

        switch($category) 
        {
            CASE 'trseries':
                $products = Trseries::getLasers();
                break;
            CASE 'gtoflx':
                $products = Gtoflx::getLasers();
                break;
            CASE 'stingray':
                $products = Stingray::getLasers();
                break;
            CASE 'flx':
                $products = Flx::getAllFlx();
                break;
            CASE 'holster':
                $products = Holster::getHolstersForDropdown();
                break;
            CASE 'accessory':
                $products = Accessory::getAccessories();
                break;
            CASE 'battery':
                $products = Battery::getBatteries();
                break;
            CASE 'toolkit':
                $products = Toolkit::getToolkits();
                break;
        }

        echo json_encode($products);
    }



 
}
