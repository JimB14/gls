<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\State;
use \App\Models\Dealer;
use \App\Models\Show;
use \App\Models\Contactmodel;
use \App\Models\Warrantyregistration;
use \App\Mail;


class Warranty extends \Core\Controller
{
    public function indexAction()
    {
        View::renderTemplate('Warranty/index.html', [
            'pagetitle' => 'Warranty',
            'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }




    public function registrationAction()
    {
        // get states for drop-down
        $states = State::getStates();

        // sellers array
        $sellers = [
          'amazon' => 'Amazon',
          'midwayusa' => 'MidwayUSA',
          'opticsplanet' => 'OpticsPlanet',
          'buds' => 'Buds Gun Shop',
          'arm_rep_gunshow' => 'ArmaLaser rep at Gun Show',
          'dealer' => 'ArmaLaser authorized dealer',
          'other' => 'Other'
        ];

        // get dealers
        $dealers = Dealer::getDealers();

        //get gun shows
        $shows = Show::getShows();

        // laser series[]
        $series =
        [
            'trseries' => 'TR SERIES',
            'gtoflx'   => 'GTO/FLX',
            'stingray' => 'STINGRAY CLASSIC'
        ];

        // render view
        View::renderTemplate('Warranty/registration.html', [
            'pagetitle' => 'Warranty registration',
            'states'    => $states,
            'sellers'   => $sellers,
            'dealers'   => $dealers,
            'shows'     => $shows,
            'series'    => $series
        ]);
    }



    public function submitWarrantyRegistration()
    {
        // process/validate form data
        $data = Warrantyregistration::processWarrantyRegistrationData();
        $honeypot = (isset($_REQUEST['honeypot'])) ? filter_var($_REQUEST['honeypot'], FILTER_SANITIZE_STRING): '';

       // check honeypot for robot content
       $honeypot = filter_var($_REQUEST['honeypot'], FILTER_SANITIZE_STRING);

       if($honeypot != '')
       {
          return false;
          exit();
       }

        // test
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // exit();

        // store user name in variable
        $name = $data['first_name'] . ' ' . $data['last_name'];

        // store in warranty table in database
        $result = Warrantyregistration::storeWarrantyRegistration($data);

        // send courtesy email to company recipients
        if($result)
        {
            // send data to mail recipient(s)
            $mail_result = Mail::mailWarrantyRegistrationData($data);
        }

        // display success message to user
        if($mail_result)
        {
            $message = "Your warranty registration information was sent!";

            View::renderTemplate('Success/index.html', [
                'warrantysuccess' => 'true',
                'name'            => $name,
                'message'         => $message
            ]);
        }
        else
        {
            echo "Mailer error";
            exit();
        }
    }
}
