<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Contactmodel;
use \App\Models\State;
use \App\Mail;


class Contact extends \Core\Controller
{
    public function indexAction()
    {
        // get states
        $states = State::getStates();

        // render view
        View::renderTemplate('Contact/index.html', [
            'pagetitle'   => 'Contact Us',
            'states'      => $states,
            'activemenu'  => 'contact',
            'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }




    public function warrantyHelpAction()
    {
        // get states
        $states = State::getStates();

        View::renderTemplate('Contact/index.html', [
            'pagetitle' => 'Contact Us',
            'need'      => 'I need Warranty help. Please contact me.',
            'states'    => $states
        ]);
    }




    public function requestCatalogAction()
    {
        // get states
        $states = State::getStates();

        View::renderTemplate('Contact/index.html', [
            'need'      => 'Please send me your latest catalog.',
            'pagetitle' => 'Contact Us',
            'catalog'   => 'send',
            'states'    => $states
        ]);
    }




    public function getRmaNumberAction()
    {
        // get states
        $states = State::getStates();

        View::renderTemplate('Contact/index.html', [
            'pagetitle' => 'Contact Us',
            'need'      => 'Please send me an RMA number. Here are my product purchase details.',
            'states'    => $states
        ]);
    }



    public function submitContactForm()
    {
        // process/validate form data
        $data = Contactmodel::processContactFormData();

        // test
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // exit();

        if($data)
        {
            // send data to mail recipient(s)
            $result = Mail::mailContactFormData($data['name'], $data['email'], $data['telephone'], $data['message'], $data['catalog_yes'], $data['address'], $data['city'], $data['state'], $data['zip']);
        }

        if($result)
        {
            $message = "Your information was sent!";

            View::renderTemplate('Success/index.html', [
                'submitcontactform' => 'true',
                'name'              => $data['name'],
                'message'           => $message
            ]);
        }
        else
        {
            echo "Mailer error";
            exit();
        }
    }



    public function submitVideoReview()
    {
        // retrieve query string data
        $laser_series = (isset($_REQUEST['laser_series'])) ? filter_var($_REQUEST['laser_series'], FILTER_SANITIZE_STRING) : '';
        $laser_model = (isset($_REQUEST['laser_model'])) ? filter_var($_REQUEST['laser_model'], FILTER_SANITIZE_STRING) : '';


        // display submission form
        View::renderTemplate('Contact/submit-video-review.html', [
          'laser_series'  => $laser_series,
          'laser_model'   => $laser_model,
          'pagetitle'     => 'Submit Video Review'
        ]);
    }



    public function submitVideoReviewData()
    {
        // retrieve query string data
        $laser_model = (isset($_REQUEST['laser_model'])) ? filter_var($_REQUEST['laser_model'], FILTER_SANITIZE_STRING) : '';

        // check honeypot for robot content
       $honeypot = filter_var($_REQUEST['honeypot'], FILTER_SANITIZE_STRING);

       if($honeypot != '')
       {
          return false;
          exit();
       }

        // test
        // echo $laser_model . '<br>';
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        // process form data & return associative array
        $data = Contactmodel::processVideoReviewFormData($laser_model);

        // test
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // exit();

        if($data)
        {
            // mail data to recipient(s)
            $result = Mail::mailVideoReviewSubmission($data);

            if($result)
            {
                // display success message
                View::renderTemplate('Success/index.html', [
                    'videoreview' => 'true',
                    'name'        => $data['name']
                ]);
            }
            else
            {
                echo "An error occurred while mailing data.";
                exit();
            }
        }
        else
        {
            echo "Error processing video review submission data";
            exit();
        }
    }
}
