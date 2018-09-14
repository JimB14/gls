<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Flx;
use \App\Models\Pistolbrand;


class Flxs extends \Core\Controller
{

    /**
     * retrieves data & displays view
     * @return View     the flx page
     */
    public function indexAction()
    {
        // get flx list

        $flx = Flx::getAllFlx();

        // test
        // echo '<pre>';
        // print_r($flx);
        // echo  '</pre>';
        // exit();

        // get pistolMfr name (limit to names for which there is an FLX product)
        $brands = FLX::getBrandsServed();

        // get pistol brands for drop-down
        // $brands = Pistolbrand::getBrands();

        // test
        // echo '<pre>';
        // print_r($brands);
        // echo  '</pre>';
        // exit();


        View::renderTemplate('FLX/index.html', [
         'flx'     => $flx,
         'brands'  => $brands,
         'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }



    /**
     * retrieve FLX record by ID
     * @return [type] [description]
     */
    public function showFlx()
    {
       // get params
       $pistolMfr = $this->route_params['pistolMfr'];
       $pistolModel = $this->route_params['pistolModel'];
       $id = $this->route_params['id'];

       // test
       // echo  $pistolMfr . '<br>';
       // echo  $pistolModel . '<br>';
       // echo  $id . '<br>';
       // exit();

       // get flx data
       $flx = Flx::getFlx($id);

       // test
       // echo '<pre>';
       // print_r($flx);
       // echo  '</pre>';
       // exit();

       // render view
       View::renderTemplate('ProductDetails/flx.html', [
          'flx'         => $flx,
          'canonURL'    => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
          'flxDetails'  => "true"
       ]);
    }




    public function searchByBrand()
    {
        // retrieve brand id from form
        $id = ( isset($_REQUEST['brand']) ) ? filter_var($_REQUEST['brand'], FILTER_SANITIZE_NUMBER_INT): '';

        if($id == '')
        {
           header("Location: /products/flx");
           exit();
        }

        // get flx list for brand selection & order by brand name
        $flx = Flx::byBrand($id);

        // test
        // echo '<pre>';
        // print_r($flx);
        // echo  '</pre>';
        // exit();

        // get brand name
        $pistolMfr = Pistolbrand::getBrandName($id);

        // get pistolMfr name (limit to names for which there is an FLX product)
        $brands = FLX::getBrandsServed();

        View::renderTemplate('FLX/index.html', [
            'flx'             => $flx,
            'brands'          => $brands,
            'brand_searched'  => $pistolMfr,
            'brand_id'        => $id
        ]);
    }



// = = = = = = refactored updates above = = = = = = = = = = = = = = = = = = = //


}
