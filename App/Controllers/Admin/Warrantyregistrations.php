<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\Warrantyregistration;
use \App\Models\State;
use \App\Models\Dealer;
use \App\Models\Show;
use \App\Mail;


/**
 * Warrantyregistrations controller
 *
 * PHP version 7.0
 */
class Warrantyregistrations extends \Core\Controller
{
    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        // redirect non-logged in user to root
        //
        if (!isset($_SESSION['user']))
        {
            header("Location: /");
            exit();
        }

        // redirect logged in user that is not supervisor or admin
        if (isset($_SESSION['user']) && $_SESSION['userType'] == 'dealer'
            || $_SESSION['userType'] == 'partner'
            || $_SESSION['userType'] == 'customer')
        {
            header("Location: /");
            exit();

        }
    }



    /**
     *
     *
     * @return void
     */
    public function getWarrantyRegistrationsAction()
    {
        // echo "Connected!."; exit();

        // get user data from User model
        $registrations = Warrantyregistration::getRegistrations();

        // test
        // echo '<pre>';
        // print_r($registrations);
        // echo '</pre>';
        // exit();

        // render view & pass $registrations object
        View::renderTemplate('Admin/Armalaser/Show/warrantyregistrations.html', [
            'registrations' => $registrations
        ]);
    }



    /**
     * retrieves dealers by state field
     *
     * @return object  The dealers for searched state
     */
    public function searchRegistrationsByLastNameAction()
    {
        // retrieve data from query string
        $last_name = ( isset($_REQUEST['last_name']) ) ? filter_var($_REQUEST['last_name']): '';

        if($last_name === '')
        {
            header("Location: /admin/warrantyregistrations/get-warranty-registrations");
            exit();
        }

        // get registrations
        $registrations = Warrantyregistration::getRegistrationsByLastName($last_name);

        // test
        // echo '<pre>';
        // print_r($registrations);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Armalaser/Show/warrantyregistrations.html', [
            'registrations'   => $registrations,
            'searched'        => $last_name
        ]);
    }



    /**
     * displays populated form to allow user to edit registration record
     *
     * @return
     */
    public function editRegistrationAction()
    {
        // retrieve dealer ID from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get states
        $states = State::getStates();

        // get registrations
        $registration = Warrantyregistration::getRegistration($id);

        // // test
        // echo '<pre>';
        // print_r($registrations);
        // echo '</pre>';

        // dealer array
        $dealers = [
          'ArmaLaser.com',
          'ArmaLaser representative at Gun Show',
          'ArmaLaser on Amazon.com',
          'OpticsPlanet on Amazon.com',
          'OpticsPlanet.com',
          'ArmaLaser dealer',
          'Other'
        ];

        View::renderTemplate('Admin/Armalaser/Update/warrantyregistrations.html', [
            'registration'  => $registration,
            'states'        => $states,
            'dealers'       => $dealers
        ]);
    }



    /**
     * posts updated registration data to armalaser.warranty_registrations
     *
     * @return boolean
     */
    public function updateRegistrationAction()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // update dealer
        $result = Warrantyregistration::updateRegistration($id);

        if($result)
        {
            echo '<script>alert("Registration successfully updated!")</script>';
            echo '<script>window.location.href="/admin/warrantyregistrations/get-warranty-registrations"</script>';
        }
    }


    /**
     * delete Registration record by ID
     * @param  Int  $id   The record ID
     * @return boolean
     */
    public static function deleteRegistrationAction()
    {
      // retrieve id from query string
      $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

      // update registration
      $result = Warrantyregistration::deleteRegistration($id);

      if($result)
      {
          echo '<script>alert("Registration successfully deleted!")</script>';
          echo '<script>window.location.href="/admin/warrantyregistrations/get-warranty-registrations"</script>';
      }
    }

}
