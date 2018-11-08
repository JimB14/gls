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
use \Core\View;
use \App\Models\State;
use \App\Models\Customer;
use \App\Models\Order;
use \App\Models\Product;
use \App\Models\Coupon;
use \App\Mail;


class Coupons extends \Core\Controller
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
     *  Retrieves all coupon records
     * 
     * @return View
     */
    public function getCouponsAction() 
    {
        $coupons = Coupon::getCoupons();

        // test
            // echo '<pre>';
            // print_r($coupons);
            // echo '</pre>';
            // exit();

        $categories = $this->getCategories();
    
        
        View::renderTemplate('Admin/Armalaser/Show/coupons.html', [
            'pagetitle'  => 'Manage Promotions',
            'coupons'    => $coupons,
            'categories' => $categories
        ]);
    }


    /**
     *  Displays view
     * 
     * @return View
     */
    public function addCouponAction() 
    {
        $categories = $this->getCategories();

        $trseries = Trseries::getLasers();
        $gtoflx = Gtoflx::getLasers();
        $stingray = Stingray::getLasers();
        $flx = Flx::getFlxForDropdown();
        $holsters = Holster::getHolstersForDropdown();
        $accessories = Accessory::getAccessories();
        $batteries = Battery::getBatteries();
        $toolkits = Toolkit::getToolkits();
        
        View::renderTemplate('Admin/Armalaser/Add/coupon.html', [
            'pagetitle'   => 'Create new promotion',
            'categories'  => $categories,
            'trseries'    => $trseries,
            'gtoflx'      => $gtoflx,
            'stingray'    => $stingray,
            'flx'         => $flx,
            'holsters'    => $holsters,
            'accessories' => $accessories,
            'batteries'   => $batteries,
            'toolkits'    =>  $toolkits
        ]);
    }


    /**
     *  Creates new coupon in `coupons` 
     *  & adds items to `coupon_product_lookup`
     * 
     * @return Boolean
     */
    public function createCouponAction() 
    {
        $result = Coupon::createCoupon();

        if ($result) 
        {
            header("Location: /admin/coupons/get-coupons");
            exit();
        }
        else 
        {
            $this->setError('Error creating promotion.');
            exit();
        }
    }



    /**
     *  Retrieves one coupon record
     * 
     * @return View
     */
    public function editCouponAction() 
    {
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT) : '';

        // get coupon record 
        $coupon = Coupon::getCoupon($id);

        View::renderTemplate('Admin/Armalaser/Update/coupon.html', [
            'pagetitle' => 'Edit Promotion',
            'coupon'    => $coupon
        ]);
    }


    /**
     *  Changes enabled field value to true
     * 
     * @return Boolean
     */
    public function enableCouponAction() 
    {
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT) : '';

        $result = Coupon::enableCoupon($id);

        if ($result) 
        {
            // echo '<script>alert("Coupon is now active.")</script>';
            echo '<script>';
            echo 'window.location.href="/admin/coupons/get-coupons";';
            echo '</script>';
        }
        else 
        {
            $this->setError('Error enabling coupon.');
            exit();
        }
    }



    /**
     *  Changes enabled field to false
     * 
     * @return Boolean
     */
    public function disableCouponAction() 
    {
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT) : '';

        $result = Coupon::disableCoupon($id);

        if ($result) 
        {
            // echo '<script>alert("Coupon has been disabled.")</script>';
            echo '<script>';
            echo 'window.location.href="/admin/coupons/get-coupons";';
            echo '</script>';
        }
        else 
        {
            $this->setError('Error disabling coupon.');
            exit();
        }
    }


    /**
     *  Deletes coupon by ID
     * 
     * @return Boolean
     */
    public function deleteCouponAction() 
    {
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT) : '';

        $result = Coupon::deleteCoupon($id);

        if ($result) 
        {
            // echo '<script>alert("Coupon has been deleted.")</script>';
            echo '<script>';
            echo 'window.location.href="/admin/coupons/get-coupons";';
            echo '</script>';
        }
        else 
        {
            $this->setError('Error deleting promotion.');
            exit();
        }
    }


    /**
     *  Updates coupon record 
     * 
     * @return Boolean
     */
    public function updateCouponAction() 
    {
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT) : '';

        $result = Coupon::updateCoupon($id);

        if ($result) 
        {
            // echo '<script>alert("Coupon has been updated.")</script>';
            echo '<script>';
            echo 'window.location.href="/admin/coupons/get-coupons";';
            echo '</script>';
        }
        else 
        {
            $this->setError('Error updating promotion.');
            exit();
        }      
    }


    /**
     *  Retrieves coupon record by name
     * 
     * @return View
     */
    public function searchCouponsByNameAction() 
    {
        // retrieve form data
        $searchword = (isset($_REQUEST['promo'])) ? filter_var($_REQUEST['promo'], FILTER_SANITIZE_STRING) : '';

        // trim data 
        $searchword = str_replace(' ', '', $searchword);

        // get coupons
        $coupons = Coupon::searchCouponsByName($searchword);

        // echo '<pre>'; print_r($coupons); exit();

        View::renderTemplate('Admin/Armalaser/Show/coupons.html', [
            'pagetitle'  => 'Manage Promotions',
            'coupons'    => $coupons,
            'searchword' => $searchword
        ]);

    }
    

    // - - - - - - - - class functions - - - - - - - - - - -  - - //

    private function setError($message) 
    {
        echo $message;
    }
    

    private function getCategories()
    {
        $categories = [
            'trseries'  => 'TR Series',
            'gtoflx'    => 'GTO/FLX',
            'stingray'  => 'Stingray',
            'flx'       => 'FLX',
            'holster'   => 'Holsters',
            'battery'   => 'Batteries',
            'toolkit'   => 'Toolkits',
            'accessory' => 'Accessories'
        ]; 

        return $categories;
    }

}
