<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Dealer;
use \App\Mail;


class Dealers extends \Core\Controller
{
    public function indexAction()
    {
        View::renderTemplate('Dealers/index.html', [
            'activemenu' => 'dealers',
            'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }



    public function findDealer()
    {
        // get dealers
        $dealers = Dealer::getDealers();

        // test
        // echo '<pre>';
        // print_r($dealers);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Dealers/find-dealer.html', [
            'dealers'    => $dealers,
            'activemenu' => 'finddealer',
            'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }


    public function searchState()
    {
        // retrieve data from query string
        $state = ( isset($_REQUEST['state']) ) ? filter_var($_REQUEST['state']): '';

        // get dealers
        $dealers = Dealer::getDealersByState($state);

        // test
        // echo '<pre>';
        // print_r($dealers);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Dealers/find-dealer.html', [
            'dealers'     => $dealers,
            'searched'    => $state,
            'activemenu'  => 'finddealer'
        ]);
    }



    public function searchCity()
    {
        // retrieve data from query string
        $city = ( isset($_REQUEST['city']) ) ? filter_var($_REQUEST['city']): '';

        // get dealers
        $dealers = Dealer::getDealersByCity($city);

        // test
        // echo '<pre>';
        // print_r($dealers);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Dealers/find-dealer.html', [
            'dealers'     => $dealers,
            'searched'    => $city,
            'activemenu'  => 'finddealer'
        ]);
    }


    /**
     * sends dealer inquiry form data to specified mail recipients
     *
     * @return
     */
    public function sendDealerInfo()
    {
        // echo "Connect to sendDealerInfo() method.";
        // exit();

        // process form data & return associative array
        $results = Dealer::processFormData();

        // if processed successfully
        if($results)
        {
            // pass form data & process
            $result = Mail::sendDealerInfo($results);

            if($result)
            {
                // render view
                View::renderTemplate('Success/index.html', [
                    'dealersuccess' => 'true',
                    'results'       => $results // assoc array of form data
                ]);
            }
        }
        else
        {
            echo "Error processing dealer form data.";
            exit();
        }
    }
}
