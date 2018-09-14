<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Dealer;
use \App\Models\Instructor;
use \App\Models\State;
use \App\Mail;


class Instructors extends \Core\Controller
{
    public function indexAction()
    {
        // get states for drop-down
        $states = State::getStates();

        $years = [
            'New',
            'Less than 1 year',
            '1 - 2 years',
            '3 - 5 years',
            '6 - 9 years',
            '10 - 19 years',
            'over 20 years'
        ];

        $pagetitle = 'Certified Firearm Instructor Program';

        View::renderTemplate('Instructors/index.html', [
            'pagetitle'  => $pagetitle,
            'states'     => $states,
            'years'      => $years,
            'activemenu' => 'instructors',
            'canonURL'   => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }



    public function learnMore()
    {
        // retrieve posted form data
        $email = ( isset($_REQUEST['email']) ) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL) : '';
        $first_name = ( isset($_REQUEST['first_name']) ) ? filter_var($_REQUEST['first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = ( isset($_REQUEST['last_name']) ) ? filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING) : '';
        $state = ( isset($_REQUEST['state']) ) ? filter_var($_REQUEST['state'], FILTER_SANITIZE_STRING) : '';
        $experience = ( isset($_REQUEST['experience']) ) ? filter_var($_REQUEST['experience'], FILTER_SANITIZE_STRING) : '';

        // check for robots
        if ($_REQUEST['honeypot'] != '') {
            echo "Error";
            exit();
        }

        // backup server-side validation
        if ($email == '' || $first_name == '' || $last_name == '' || $state == ''
            || $experience == '') {
            echo "All fields are required.";
            exit();
        }

        // store form input values in array
        $instructor = [
            'email'      => $email,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'state'      => $state,
            'experience' => $experience
        ];

        // store in instructors table
        $result = Instructor::addInstructor($instructor);

        // if successful
        if ($result)
        {
            // send email
            $result = Mail::instructorInterest($instructor);

            if ($result)
            {
                // render view
                View::renderTemplate('Success/index.html', [
                    'instructorsuccess' => 'true',
                    'first_name'        => $instructor['first_name']
                ]);
            }
        }
        else
        {
            echo "Error storing data.";
            exit();
        }
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
            'dealers'     => $dealers,
            'activemenu'  => 'finddealer',
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
