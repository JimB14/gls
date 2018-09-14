<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Pistolbrand;
use \App\Models\Trseries;
use \App\Models\Gtoflx;
use \App\Models\Stingray;
use \App\Models\Holster;
use \App\Models\Accessory;
use \App\Models\Battery;
use \App\Models\Toolkit;
use \App\Models\Pistol;

/**
 * Search controller
 *
 * PHP version 7.0
 */
class Search extends \Core\Controller
{
    /**
    * Retrieves products by brand name
    *
    * @return objects  The products by categories for this brand
    */
    public function byPistolMakeModel()
    {
        // get param from URL
        $name  = strtolower($this->route_params['manufacturer']);
        $model = strtolower($this->route_params['model']);
        $param = strtolower($this->route_params['param']);

        // prefix 'g' for Glock
        if ($name == 'glock') {
            $model = 'g'.$model;
        } else {
            $model = $model;
        }

        // get pistol model ID
        $results = Pistol::getIdByMfrModel($name, $model);

        // test
        // echo '<pre>';
        // print_r($results);
        // echo '</pre>';
        // exit();


        if ($results)
        {
            // get TR series lasres for this pistol
            $trseries = Trseries::byPistolId($results->pistolid);

            // test
            // echo '<pre>';
            // print_r($trseries);
            // echo '</pre>';
            // exit();

            // get GTO-FLX lasers for this pistol
            $gtoflx = Gtoflx::byPistolId($results->pistolid);

            // test
            // echo '<pre>';
            // print_r($gtoflx);
            // echo '</pre>';
            // if (empty($gtoflx)) {
            //     echo "<h3>Empty - no GTO/FLX match</h3>";
            // }
            // exit();

            // get stingray record for this pistol
            $stingray = Stingray::byPistolId($results->pistolid);

            // test
            // echo '<pre>';
            // print_r($stingray);
            // echo '</pre>';
            // if (empty($stingray)) {
            //     echo "<h3>Empty - no Stingray match</h3>";
            // }
            // exit();

            // get holsters for this pistol
            $holsters = Holster::byPistolId($results->pistolid);

            // test
            // echo '<pre>';
            // print_r($holsters);
            // echo '</pre>';
            // if (empty($holsters)) {
            //     echo "<h3>Empty - no holster match</h3>";
            // }
            // exit();
        }
        else
        {
            $trseries = [];
            $gtoflx   = [];
            $stingray = [];
            $holsters = [];
        }

        // render view
        View::renderTemplate('Search/index.html', [
            'mfr'       => $name,
            'model'     => $model,
            'param'     => $param,
            'pistolMfr' => $name,
            'trseries'  => $trseries,
            'gtoseries' => $gtoflx,
            'stingray'  => $stingray,
            'holsters'  => $holsters
        ]);
    }




    //  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = //


    // public function indexAction()
    // {
    //     // get pistol brand logos
    //     $logos = Pistolbrand::getLogos();
    //
    //     // test
    //     // echo '<pre>';
    //     // print_r($logos);
    //     // echo '</pre>';
    //     // exit();
    //
    //     // get current date and time in MySQL DATETIME format
    //     $timezone =  new \DateTimeZone("America/New_York");
    //     $date = new \DateTime("now", $timezone);
    //     $now = $date->format("Y-m-d H:i:s"); // matches MySQL format
    //     $current_hour = $date->format('H');
    //     $today = $date->format('D');
    //
    //     // render view
    //     View::renderTemplate('Home/index.html', [
    //         'logos'        => $logos,
    //         'now'          => $now,
    //         'current_hour' => $current_hour,
    //         'today'        => $today,
    //         'homeindex'    => 'true',
    //         'canonURL'     => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
    //     ]);
    // }



   /**
   * Retrieves products by brand name
   *
   * @return objects  The products by categories for this brand
   */
   // public function brandProducts()
   // {

        // get param from URL (see front controller #77)
        // $pistol_brand_name = $this->route_params['manufacturer'];

        // get pistol brand ID
        // $id = Pistolbrand::getBrandId($pistol_brand_name);

        // test
        // echo $id . '<br>';
        // echo $pistol_brand_name;
        // exit();

        // find TR series matches for this brand
        // $trseries = Laser::getLasersByBrand($id, $series='"TR Series"', $color=null, $orderby='alpha_name', $limit=null);

        // test
        // echo '<pre>';
        // print_r($trseries);
        // echo '</pre>';
        // exit();

        // find GTO-FLX series matches for this brand
        // $gtoseries = Laser::getLasersByBrand($id, $series='"GTO FLX"', $color=null, $orderby='alpha_name', $limit=null);

        // test
        // echo '<pre>';
        // print_r($gtoseries);
        // echo '</pre>';
        // exit();

        // find GTO-FLX series matches for this brand
        // $stingray = Laser::getLasersByBrand($id, $series='"Stingray"', $color=null, $orderby='laser_model', $limit=null);

        // get stingray record for selected pistol brand
        // $stingray = Stingray::getStingrayRecordByBrand($pistol_brand_name);

        // test
        // echo '<pre>';
        // print_r($stingray);
        // echo '</pre>';
        // exit();

        // $stingray_model_match = $stingray->mfr_model_match;

        // echo $stingray_model_match; exit();

        // get accessories; filter by brand products not empty
        // $accessories = Accessory::getAccessories($trseries, $gtoseries, $stingray_model_match);

        // test
        // echo '<pre>';
        // print_r($accessories);
        // echo '</pre>';
        // exit();

        // get holsters
        //$holsters = Holster::getHolsters($id);

        // get unique images of matching holsters
        // $results = Holster::findMatchingHolsterImages($id);

         // test
         // echo '<pre>';
         // print_r($results['holster_images']);
         // echo '</pre>';
         // exit();

         // test
         // echo '<pre>';
         // print_r($results['holsters']);
         // echo '</pre>';
         // exit();

         // $holsters = Holster::getHolstersByPistolBrand($pistol_brand_name);

         // test
         // echo '<pre>';
         // print_r($holsters);
         // echo '</pre>';
         //  exit();

         // echo $pistol_brand_name . '<br>';
        // exit();

        // change name for display purposes (must be below all functions using name from db)
        // if($pistol_brand_name == 'Smith-Wesson'){
        //      $pistol_brand_name = 'Smith & Wesson';
        // }

        // echo $pistol_brand_name . '<br>';
        // exit();

         // $holster_title01 = 'LASER-FIT™ HOLSTERS';
         // $holster_title02 = '<em>for ArmaLaser-equipped</em> '. $pistol_brand_name . '®';

        // render view
    //     View::renderTemplate('ProductsByBrand/index.html', [
    //         'brand_name'      => $pistol_brand_name,
    //         'trseries'        => $trseries,
    //         'gtoseries'       => $gtoseries,
    //         'stingray'        => $stingray,
    //         'accessories'     => $accessories,
    //         'holsters'        => $holsters,
    //         'holster_title01' => $holster_title01,
    //         'holster_title02' => $holster_title02,
    //         'canonURL'        => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
    //     ]);
    // }


}
