<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Pistolbrand;
use \App\Models\Laser;
use \App\Models\Stingray;
use \App\Models\Accessory;
use \App\Models\Holster;
use \App\Models\Coupon;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Home extends \Core\Controller
{
    public function index()
    {
        // get pistol brand logos
        $logos = Pistolbrand::getLogos();

        // test & notes
            // echo '<pre>';
            // print_r($logos);
            // echo '</pre>';
            // exit();

            // get current date and time in MySQL DATETIME format
        $timezone =  new \DateTimeZone("America/New_York");
        $date = new \DateTime("now", $timezone);
        $now = $date->format("Y-m-d H:i:s"); // matches MySQL format
        $current_hour = $date->format('H');
        $today = $date->format('D');

        View::renderTemplate('Home/index.html', [
            'logos'        => $logos,
            'now'          => $now,
            'current_hour' => $current_hour,
            'today'        => $today,
            'homeindex'    => 'true',
            'canonURL'     => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }

}
