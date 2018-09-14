<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Show;

/**
 * Shows controller
 *
 * PHP version 7.0
 */
class Shows extends \Core\Controller
{
    public function indexAction()
    {
        // get show data
        $shows = Show::getAllShows();

        // test
        // echo '<pre>';
        // print_r($shows);
        // echo '</pre>';
        // exit();

        // get cities
        $cities = Show::getCities();

        // create new DateTime object & store in variable
        $date = new \DateTime('America/New_York');

        // store year in variable
        $year = $date->format("Y");

        View::renderTemplate('Shows/index.html', [
            'shows'       => $shows,
            'cities'      => $cities,
            'year'        => $year,
            'activemenu'  => 'shows',
            'canonURL'    => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }



    public function allshowsAction()
    {
        // get show data
        $shows = Show::getAllShows();

        // test
        // echo '<pre>';
        // print_r($shows);
        // echo '</pre>';
        // exit();

        // get cities
        $cities = Show::getCities();

        // create new DateTime object & store in variable
        $date = new \DateTime('America/New_York');

        // store year in variable
        $year = $date->format("Y");

        View::renderTemplate('Shows/index.html', [
            'shows'       => $shows,
            'cities'      => $cities,
            'year'        => $year,
            'activemenu'  => 'shows',
            'canonURL'    => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
            'allshows'    => 'true'
        ]);
    }




    public function searchCity()
    {
        // retrieve form data
        $city = ( isset($_REQUEST['city']) ) ? filter_var($_REQUEST['city'], FILTER_SANITIZE_STRING) : '';

        //echo $city; exit();

        if($city == '')
        {
            header("Location: /gun-shows");
            exit();
        }

        // get show data
        $shows = Show::getShowsByCity($city);

        // test
        // echo '<pre>';
        // print_r($shows);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Shows/index.html', [
            'shows'       => $shows,
            'searched'    => $city,
            'activemenu'  => 'shows'
        ]);
    }



    public function searchState()
    {
        // retrieve query string data
        $state = ( isset($_REQUEST['state']) ) ? filter_var($_REQUEST['state'], FILTER_SANITIZE_STRING) : '';

        if($state == '')
        {
            header("Location: /gun-shows");
            exit();
        }

        // get show data
        $shows = Show::getShowsByState($state);

        // test
        // echo '<pre>';
        // print_r($shows);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Shows/index.html', [
            'shows'       => $shows,
            'searched'    => $state,
            'activemenu'  => 'shows'
        ]);
    }

}
