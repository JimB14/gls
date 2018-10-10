<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Trseries;
use \App\Models\Specification;
use \App\Models\Pistol;


class TrseriesController extends \Core\Controller
{

    /**
     * show laser details
     *
     * @return View
     */
    public function showTrseriesAction()
    {
        // route: ('{pistolMfr:[A-za-z -]+}/tr-series/{laserId:[0-9 -]+}/{laserModel:[A-za-z0-9 -]+}'
        // echo "Connected to showTrseriesAction() in Trseries Controller!<br><br>"; exit();

        // retrieve params
        $pistolMfr  = $this->route_params['pistolMfr'];
        $laserId    = $this->route_params['laserId'];
        $laserModel = $this->route_params['laserModel'];

            // test
            // echo "$pistolMfr <br>";
            // echo "$laserId <br>";
            // echo "$laserModel <br>";
            // exit();

        // get laser
        $laser = Trseries::getTrseriesLaser($laserId);

            // test
            // echo '<pre>';
            // print_r($laser);
            // echo '</pre>';
            // exit();

        // get specifications for laser series & beam color
        $specs = Specification::getSpecs($laser->series, $laser->beam);

            // test
            // echo '<pre>';
            // print_r($specs);
            // echo '</pre>';
            // exit();

        // get links
        $productLinks = Trseries::getTrseriesLinks($laserId);

            // test
            // echo '<pre>';
            // print_r($productLinks);
            // echo '</pre>';
            // exit();

        // store values from $laser object for use in view (use with pistollaser lookup table)
        $laser_name  = explode(' ', $laser->series);
        $second_word = array_pop($laser_name);
        $first_word  = implode(' ', $laser_name);

        // get current date and time in MySQL DATETIME format
        $now = $this->getMySQLDateTime();

        // render view
        View::renderTemplate('ProductDetails/trseries.html', [
            'model'       => $laser,
            'links'       => $productLinks,
            'specs'       => $specs,
            'second_word' => $second_word,
            'first_word'  => $first_word,
            'pistolMfr'   => $pistolMfr,
            'now'         => $now,
            'canonURL'    => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }



    /**
     * retrieves TR Series lasers & displays trseries page with models
     *
     * @return View
     */
    public function trseriesAction()
    {

        // get laser models
        $models = Trseries::getLasers();

            // test
            // echo '<pre>';
            // print_r($models);
            // echo '</pre>';
            // exit();

        View::renderTemplate('TRSeries/index.html', [
            'series'      => 'trseries',
            'models'      => $models,
            'activemenu'  => 'trseries',
            'canonURL'    => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }



    /**
     * displays TR Series owners manual pdf
     *
     * @return void
     */
    public function trSeriesLaserOwnersManual()
    {

        View::renderTemplate('TRSeries/owners-manual.html', [
            'pagetitle' => 'TR Series Owners Manual',
            'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }



    /**
     * retrieves TR Series lasers
     */
    public function getLasers()
    {
        $lasers = Trseries::getLasersForWarrantyRegistrationDropdown();

        // encode as JSON string & return to Ajax request
        echo json_encode($lasers);
    }


    // = = = = = =  Class functions  = = = = = = = = = = = = = = = = = = // 

    function getMySQLDateTime() 
    {
        $timezone =  new \DateTimeZone("America/New_York");
        $date = new \DateTime("now", $timezone);
        // $current_hour = $date->format('H');
        // $today = $date->format('D');

        return $date->format("Y-m-d H:i:s"); // matches MySQL format;
    }



}
