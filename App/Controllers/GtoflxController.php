<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Gtoflx;
use \App\Models\Specification;
use \App\Models\Pistol;


class GtoflxController extends \Core\Controller
{

    /**
     * retrieves gto-flx laser record by ID and displays view
     *
     * @return View
     */
    public function showGtoflxAction()
    {
        // route: ('{pistolMfr:[A-za-z -]+}/{pistolModel:[a-zA-z0-9 -]+}/gto-flx/{laserId:[0-9 -]+}/{laserModel:[A-za-z0-9 -]+}'
        // echo "Connected to showGtoflxAction() in Lasers Controller!<br><br>"; exit();

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
        $laser = Gtoflx::getGtoflxLaser($laserId);

        // test
        // echo '<h4>Laser:</h4>';
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

        $productLinks = Gtoflx::getGtoflxLinks($laserId);

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
        View::renderTemplate('ProductDetails/gtoflx.html', [
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
     * displays GTO-FLX owners manual pdf
     *
     * @return void
     */
    public function gtoFlxSeriesLaserOwnersManual()
    {
        View::renderTemplate('GTOFLXSeries/owners-manual.html', [
            'pagetitle' => 'GTO-FLX Owners Manual',
            'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }



//  - - - - - -   gtoflxAction() under construction 3/7/18 - - - - - - - - - //

    /**
     * displays GTO-FLX page
     *
     * @return void
     */
    public function gtoflxAction()
    {

        $gto = Gtoflx::getLasers();

        // test
        // echo '<pre>';
        // print_r($gto);
        // echo '</pre>';
        // exit();

        View::renderTemplate('GTOFLXSeries/index.html', [
            'gto'         => $gto,
            'series'      => 'gtoflxseries',
            'activemenu'  => 'gtoflxseries',
            'canonURL'    => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }



    public function getLasers()
    {
        $lasers = Gtoflx::getLasersForWarrantyRegistrationDropdown();

        // return to Ajax request
        echo json_encode($lasers);
    }



// = = = = refactored updates above  = = = = = = = = = = = = = = = = = = = = = //






    /**
     * sorts GTO-FLX matches by laser
     *
     * @return object Matches sorted by laser not pistol (default)
     */
    public function sortByGto()
    {
        // get red gto-flx lasers & order by laser model
        $gto_red_by_flx = Laser::getLasers($series='"GTO FLX"', $color='AND laser_color = "red"', $orderby='laser_model', $limit='');

        // get red gto-flx lasers & order by laser model
        $gto_green_by_flx = Laser::getLasers($series='"GTO FLX"', $color='AND laser_color = "green"', $orderby='laser_model', $limit='');

        View::renderTemplate('GTOFLXSeries/index.html', [
            'gto_red_by_flx'   => $gto_red_by_flx,
            'gto_green_by_flx' => $gto_green_by_flx
        ]);
    }


}
