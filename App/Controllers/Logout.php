<?php

namespace App\Controllers;

use \Core\View;


/**
 * Logout controller
 *
 * PHP version 7.0
 */
class Logout extends \Core\Controller
{
    public function indexAction()
    {
        // user not logged in
        if(!isset($_SESSION['user']))
        {
            View::renderTemplate('Error/index.html', [
                'errorMessage' => 'You are not currently logged in.'
            ]);
            exit();
        }
        else
        {
            unset($_SESSION['user']);
            unset($_SESSION['loggedIn']);
            unset($_SESSION['user_id']);
            unset($_SESSION['access_level']);
            unset($_SESSION['full_name']);
            unset($_SESSION['cart']);
            unset($_SESSION['cart_count']);
            unset($_SESSION['shiptostate']);
            unset($_SESSION['userType']);
            unset($_SESSION['discount_multiplier']);

            // unset all session's values
            session_unset();
            // destroy session
            session_destroy();

            $message = "You were logged out";

            View::renderTemplate('Success/index.html', [
                'logout'  => 'true',
                'message' => $message
            ]);
        }
    }



    public function timedout()
    {
        unset($_SESSION['user']);
        unset($_SESSION['loggedIn']);
        unset($_SESSION['user_id']);
        unset($_SESSION['access_level']);
        unset($_SESSION['full_name']);
        unset($_SESSION['cart']);
        unset($_SESSION['cart_count']);
        unset($_SESSION['shiptostate']);
        unset($_SESSION['userType']);
        unset($_SESSION['discount_multiplier']);

        // unset all session values
        session_unset();

        // destroy session
        session_destroy();

        $message = "Your session timed out. You were automatically logged out";

        View::renderTemplate('Success/index.html', [
            'logout'  => 'true',
            'message' => $message
        ]);
    }
    


   public function chatAction()
   {

      // unset specific session values
      unset($_SESSION['chatuserLoggedIn']);
      unset($_SESSION['chat_session_id']);
      unset($_SESSION['chatuser_id']);
      unset($_SESSION['chatuser_access_level']);
      unset($_SESSION['chatuser_first_name']);
      unset($_SESSION['chatuser_email']);
      unset($_SESSION['LAST_CHAT_USER_ACTIVITY']);

      // unset all session's values
      session_unset();
      // destroy session
      session_destroy();

      $message = "You have been logged out";

      View::renderTemplate('Success/index.html', [
          'logout'  => 'true',
          'message' => $message
      ]);
   }
}
