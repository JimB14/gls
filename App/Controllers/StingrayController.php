<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Stingray;
use \App\Models\Specification;
use \App\Models\Pistol;


class StingrayController extends \Core\Controller
{

    /**
     * retrieve stingray record & display view
     *
     * @return View
     */
    public function showStingrayAction()
    {
        // route: ('products/lasers/{pistolMfr:[A-za-z -]+}/{laserId:[0-9 -]+}/{laserModel:[A-za-z0-9 -]+}'
        // echo "Connected to showStingrayAction() in Stingray Controller!<br><br>"; exit();

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
        $laser = Stingray::getStingrayLaser($laserId, $pistolMfr);

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

        $productLinks = Stingray::getStingrayProductLinks($laserId);

        // test
        // echo '<pre>';
        // print_r($productLinks);
        // echo '</pre>';
        // exit();

        // store values from $model object for use in view (use with pistollaser lookup table)
        $laser_name = explode(' ', $laser->series);
        $second_word = array_pop($laser_name);
        $first_word = implode(' ', $laser_name);


        // get current date and time in MySQL DATETIME format
        $timezone =  new \DateTimeZone("America/New_York");
        $date = new \DateTime("now", $timezone);
        $now = $date->format("Y-m-d H:i:s"); // matches MySQL format
        $current_hour = $date->format('H');
        $today = $date->format('D');

        // render view
        View::renderTemplate('ProductDetails/stingray.html', [
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
     * displays stingray page
     *
     * @return void
     */
    public function stingrayAction()
    {
        View::renderTemplate('Stingray/index.html', [
            'series'      => 'stingrayclassic',
            'activemenu'  => 'stingray',
            'canonURL'    => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }

    /**
     * displays Stingray Classic owners manual pdf
     *
     * @return void
     */
    public function stingrayClassicLaserOwnersManual()
    {
        View::renderTemplate('Stingray/owners-manual.html', [
            'pagetitle' => 'Stingray Classic Owners Manual',
            'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }
    /* - - - - - // Stingray - - - - - */



    /**
     * retrieves stingray record
     *
     * @return object,strings Data for ProductDetails view
     */
    public function getStingrayModelAction()
    {
        // retrieve param & query string variables
        $stingray_id = $this->route_params['id'];
        //$pistol_id = ( isset($_REQUEST['pistol_id']) ) ? filter_var($_REQUEST['pistol_id'], FILTER_SANITIZE_STRING) : '';

        // get laser product details
        $stingray_model = Stingray::getModelDetails($stingray_id);

        // test
        // echo '<pre>';
        // print_r($stingray_model);
        // echo '</pre>';
        // exit();

        // store values from $model object for use in view (use with pistollaser lookup table)
        $laser_name = explode(' ', $stingray_model->laser_series);
        $second_word = array_pop($laser_name);
        $first_word = implode(' ', $laser_name);

        // get specifications for laser series
        $specs = Specification::getStingraySpecs($id=5);

        // test
        // echo '<pre>';
        // print_r($specs);
        // echo '</pre>';
        // exit();

        // get laser product links from Lasers table
        $stingray_links = Stingray::getStingrayProductLinks($id=169);

        // test
        // echo '<pre>';
        // print_r($stingray_links);
        // echo '</pre>';
        // exit();


        // get current date and time in MySQL DATETIME format
        $timezone =  new \DateTimeZone("America/New_York");
        $date = new \DateTime("now", $timezone);
        $now = $date->format("Y-m-d H:i:s"); // matches MySQL format
        $current_hour = $date->format('H');
        $today = $date->format('D');

        // render view
        View::renderTemplate('ProductDetails/index.html', [
            'stingray_model'  => $stingray_model,
            'now'             => $now,
            'second_word'     => $second_word,
            'first_word'      => $first_word,
            'specs'           => $specs,
            'stingray_links'  => $stingray_links,
            'stingray'        => 'true',
            'canonURL'        => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }




    public function getLasers()
    {
        $lasers = Stingray::getLasersForWarrantyRegistrationDropdown();

        // return to Ajax request
        echo json_encode($lasers);
    }

}
