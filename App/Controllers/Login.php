<?php

namespace App\Controllers;

use \App\Mail;
use \Core\View;
use \App\Models\User;
use \App\Models\Partner;
use \App\Models\Customer;
use \App\Models\Dealeruser;
use \App\Models\Dealer;



class Login extends \Core\Controller
{
    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        if(isset($_SESSION['user']) && $_SESSION['userType'] != 'admin')
        {
            echo "<p>Error. You are already logged in.</p>";
            exit();
        }
    }


    protected function after()
    {
        //echo " (after)";

    }


    /**
     * Serves customer login view
     *
     * @return View
     */
    public function indexAction()
    {
      // retrieve query string variable
      $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';
      $q = (isset($_REQUEST['q'])) ? filter_var($_REQUEST['q'], FILTER_SANITIZE_STRING): '';
      $action = (isset($_REQUEST['action'])) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING): '';

      $timeout_msg = "You were logged out due to inactivity for 60 minutes.<br>Please log back in.";
      $q_msg = "<h4>You must be logged in to use LIVE CHAT. <br><br>Please log in and then click the button in your email.</h4>";

      View::renderTemplate('Login/customer.html', [
         'pagetitle' => 'Customer Log In',
         'timeout'     => $id,
         'timeout_msg' => $timeout_msg,
         'question'    => $q,
         'q_msg'       => $q_msg,
         'action'      => $action
      ]);
    }


    /**
     * Serves admin login view
     *
     * @return View
     */
    public function adminLogInAction()
    {
        // retrieve query string variable
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT): '';

        $timeout_msg = "You were logged out due to inactivity for 60 minutes.<br>Please log back in.";

        View::renderTemplate('Login/admin.html', [
           'pagetitle' => 'Admin Log In',
           'timeout' => $id,
           'timeout_msg' => $timeout_msg,
           'admin' => 'true'
        ]);
    }



    /**
     * Serves LogIn partner view
     *
     * @return View
     */
    public function partnerLogInAction()
    {
        // retrieve query string variable
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';
        $q = (isset($_REQUEST['q'])) ? filter_var($_REQUEST['q'], FILTER_SANITIZE_STRING): '';
        $action = (isset($_REQUEST['action'])) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING): '';

        $timeout_msg = "You were logged out due to inactivity for 60 minutes.<br>Please log back in.";

        View::renderTemplate('Login/partner.html', [
            'pagetitle' => 'Partner Log In',
            'timeout'     => $id,
            'timeout_msg' => $timeout_msg,
            'question'    => $q,
            'action'      => $action
        ]);
    }



    /**
     * Serves LogIn dealer view
     *
     * @return View
     */
    public function dealerLogInAction()
    {
        // retrieve query string variable
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';
        $q = (isset($_REQUEST['q'])) ? filter_var($_REQUEST['q'], FILTER_SANITIZE_STRING): '';
        $action = (isset($_REQUEST['action'])) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING): '';


        $timeout_msg = "You were logged out due to inactivity for 60 minutes.<br>Please log back in.";

        View::renderTemplate('Login/dealer.html', [
            'pagetitle' => 'Dealer Log In',
            'timeout'     => $id,
            'timeout_msg' => $timeout_msg,
            'question'    => $q,
            'action'      => $action
        ]);
    }


//  = = = = = Log In Customer  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =  //

    /**
     * logs in user if matching credentials found
     *
     *  @return user object or null
     */
    public function loginCustomerAction()
    {
       // retrieve form values
       $email = ( isset($_REQUEST['email'])  ) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL) : '';
       $password = ( isset($_REQUEST['password'])  ) ? filter_var($_REQUEST['password'], FILTER_SANITIZE_STRING) : '';

       // test
       // echo $email . "<br>";
       // echo $password  . "<br>";
       // exit();

       // validate customer & find if in database; store user data in $user object
       $user = Customer::validateLoginCredentials($email, $password);

       // test
       // echo '<pre>';
       // print_r($user);
       // echo "</pr>";
       // exit();

       /* - - - returning customer - - - - - - - - - - - - - - - - - - - - - */

        // check if returning user; if true log in
        if( ($user) && ($user->first_login == 0) )
        {
            // log returning user in
            // create unique id & store in SESSION variable
            $_SESSION['user'] = md5($user->id);
            $_SESSION['userUniqId'] = md5($user->id);
            $_SESSION['loggedIn'] = true;
            $_SESSION['userType'] = 'customer';

            // assign user ID & access_level & full_name to SESSION variables
            $_SESSION['user_id'] = $user->id;
            $_SESSION['access_level'] = $user->access_level;
            $_SESSION['first_name'] = $user->billing_firstname;
            $_SESSION['full_name'] = $user->billing_firstname . ' ' . $user->billing_lastname;

            // session timeout code in front-controller public/index.php
            $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

            // test
            // echo "uniqID: " . $_SESSION['user'] . "<br>";
            // echo "Logged in? " . $_SESSION['loggedIn'] . "<br>";
            // echo "user_id: " . $_SESSION['user_id'] . "<br>";
            // echo "access level: " .$_SESSION['access_level'] . "<br>";
            // echo "First name: " . $_SESSION['first_name'] . "<br>";
            // echo "Full name: " . $_SESSION['full_name'] . "<br>";
            // echo "User type: " . $_SESSION['userType'] . "<br>";
            // exit();

            // check if session cart empty
            if ($_SESSION['cart'])
            {
                header("Location: /cart/view/shopping-cart");
                exit();
            }
            else
            {
                header("Location: /");
                exit();
            }
        }


        /* - - - new customer's first attempt to log in - - - - - - - - - - - - - - - - */
        if ( ($user) && ($user->first_login == 1) )
        {

            $firstYear = date('Y') - 55;
            $thisYear = date('Y');

            // create array of years
            $years = range( $firstYear, $thisYear);

            $instructions = 'You\'re almost done '.$user->billing_firstname.'. Please
            answer the following questions to help secure your account.';

            // send to security Questions
            View::renderTemplate('Register/security-questions.html', [
                'partner' => $user,
                'years'   => $years,
                'instructions' => $instructions,
                'type'  => 'customer'
            ]);
            exit();
        }


        /* - - - new customer's first log in after answering security questions - - */
        // check if first login of new user (first_login == 1); if true, special page
        if( ($user) && ($user->first_login == 1 && $user->security1 != '') )
        {
            // update first_login value
            $result = Customer::updateFirstLogin($user->id);

            if ($result)
            {
                // log returning user in
                // create unique id & store in SESSION variable
                $_SESSION['user'] = md5($user->id);
                $_SESSION['userUniqId'] = md5($user->id);
                $_SESSION['loggedIn'] = true;
                $_SESSION['userType'] = 'customer';

                // assign user ID & access_level & full_name to SESSION variables
                $_SESSION['user_id'] = $user->id;
                $_SESSION['access_level'] = $user->access_level;
                $_SESSION['first_name'] = $user->billing_firstname;
                $_SESSION['full_name'] = $user->billing_firstname . ' ' . $user->billing_lastname;

                // session timeout code in front-controller public/index.php
                $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

                // test
                // echo "uniqID: " . $_SESSION['user'] . "<br>";
                // echo "Logged in? " . $_SESSION['loggedIn'] . "<br>";
                // echo "user_id: " . $_SESSION['user_id'] . "<br>";
                // echo "access level: " .$_SESSION['access_level'] . "<br>";
                // echo "First name: " . $_SESSION['first_name'] . "<br>";
                // echo "Full name: " . $_SESSION['full_name'] . "<br>";
                // echo "Full name: " . $_SESSION['full_name'] . "<br>";
                // exit();

                header("Location: /");
                exit();
            }
            else
            {
                echo "Error updating first login status in database.";
                exit();
            }
        }
        else
        {
            echo "Error logging in. Please check credentials and try again.";
            exit();
        }
   }


//  = = = = = Log In Admin  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =  //

    /**
     * logs in Admin user if matching credentials found
     *
     * @return user object or null
     */
    public function loginAdminAction()
    {

        // retrieve form values
        $email = ( isset($_REQUEST['email'])  ) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL) : '';
        $password = ( isset($_REQUEST['password'])  ) ? filter_var($_REQUEST['password'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo $email . "<br>";
        // echo $password  . "<br>";
        // exit();

        // validate user & find if in database; store user data in $user object
        $user = User::validateLoginCredentials($email, $password);

        // test
        // echo '<pre>';
        // print_r($user);
        // echo "</pr>";
        // exit();

        // check if returning user; if true log in
        if( ($user) && ($user->first_login == 0) )
        {
            // log returning user in
            // create unique id & store in SESSION variable
            $_SESSION['user'] = md5($user->id);
            $_SESSION['userUniqId'] = md5($user->id);
            $_SESSION['loggedIn'] = true;
            if ($user->access_level == 5) {
                $_SESSION['userType'] = 'supervisor';
            } else if ($user->access_level == 4) {
                $_SESSION['userType'] = 'telephoneorder';
            } else {
                $_SESSION['userType'] = 'admin';
            }

            // assign user ID & access_level & full_name to SESSION variables
            $_SESSION['user_id'] = $user->id;
            $_SESSION['access_level'] = $user->access_level;
            $_SESSION['first_name'] = $user->first_name;
            $_SESSION['full_name'] = $user->first_name . ' ' . $user->last_name;

            // session timeout code in front-controller public/index.php
            $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

            // test
            // echo "uniqID: " . $_SESSION['user'] . "<br>";
            // echo "Logged in? " . $_SESSION['loggedIn'] . "<br>";
            // echo "user_id: " . $_SESSION['user_id'] . "<br>";
            // echo "access level: " .$_SESSION['access_level'] . "<br>";
            // echo "First name: " . $_SESSION['first_name'] . "<br>";
            // echo "Full name: " . $_SESSION['full_name'] . "<br>";
            // echo "User type: " . $_SESSION['userType'] . "<br>";
            // exit();

            // notify webmaster of sign in
            $result = Mail::signInNotification($user);

            header("Location: /");
            exit();
        }
        else
        {
            echo "Error logging in. Please check credentials and try again.";
            exit();
        }
    }


//  = = = = = Log In Partner  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =  //

    /**
     * logs in partner if matching credentials found
     *
     *  @return user object or null
     */
    public function loginPartnerAction()
    {
        // retrieve form values
        $email = ( isset($_REQUEST['email'])  ) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL) : '';
        $password = ( isset($_REQUEST['password'])  ) ? filter_var($_REQUEST['password'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo $email . "<br>";
        // echo $password  . "<br>";
        // exit();

        // validate user & find if in database; store user data in $user object
        $partner = Partner::validateLoginCredentials($email, $password);

        // test
        // echo '<pre>';
        // print_r($partner);
        // echo "</pre>";
        // exit();

        if ($partner == false)
        {
            View::renderTemplate('Error/index.html', [
                'errorMessage' => 'User cannot be found.'
            ]);
            exit();
        }

        /* - - - returning partner - - - - - - - - - - - - - - - - - - - - - */

        // check if returning user; if true log in
        if( ($partner) && ($partner->first_login == 0) )
        {
            // log returning user in
            // create unique id & store in SESSION variable
            $_SESSION['user'] = md5($partner->id);
            $_SESSION['userUniqId'] = md5($partner->id);
            $_SESSION['loggedIn'] = true;
            $_SESSION['userType'] = 'partner';

            // assign user ID & access_level & full_name to SESSION variables
            $_SESSION['user_id'] = $partner->id;
            $_SESSION['access_level'] = $partner->access_level;
            $_SESSION['first_name'] = $partner->first_name;
            $_SESSION['full_name'] = $partner->first_name . ' ' . $partner->last_name;

            // session timeout code in front-controller public/index.php
            $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

            // test
            // echo "uniqID: " . $_SESSION['user'] . "<br>";
            // echo "Logged in? " . $_SESSION['loggedIn'] . "<br>";
            // echo "user_id: " . $_SESSION['user_id'] . "<br>";
            // echo "access level: " .$_SESSION['access_level'] . "<br>";
            // echo "First name: " . $_SESSION['first_name'] . "<br>";
            // echo "Full name: " . $_SESSION['full_name'] . "<br>";
            // echo "User type: " . $_SESSION['userType'] . "<br>";
            // exit();

            // send to root
            header("Location: /");
            exit();
        }



        /* - - - new partner's first attempt to log in - - - - - - - - - - - - - - - - */
        if ( ($partner) && ($partner->first_login == 1) )
        {

            $firstYear = date('Y') - 55;
            $thisYear = date('Y');

            // create array of years
            $years = range( $firstYear, $thisYear);

            $instructions = 'You\'re almost done '.$partner->first_name.'. Please
            answer the following questions to help secure your account.';

            // send to security Questions
            View::renderTemplate('Register/security-questions.html', [
                'partner'      => $partner,
                'years'        => $years,
                'instructions' => $instructions,
                'type'         => 'partner'
            ]);
            exit();
        }



        /* - - - new partner's first log in after answering security questions - - */
        // check if first login of new user (first_login == 1); if true, special page
        if( ($partner) && ($partner->first_login == 1 && $partner->security1 != '') )
        {
            // update first_login value
            $result = Partner::updateFirstLogin($partner->id);

            if ($result)
            {
                // log returning user in
                // create unique id & store in SESSION variable
                $_SESSION['user'] = md5($partner->id);
                $_SESSION['userUniqId'] = md5($partner->id);
                $_SESSION['loggedIn'] = true;
                $_SESSION['userType'] = 'partner';

                // assign user ID & access_level & full_name to SESSION variables
                $_SESSION['user_id'] = $partner->id;
                $_SESSION['access_level'] = $partner->access_level;
                $_SESSION['first_name'] = $partner->first_name;
                $_SESSION['full_name'] = $partner->first_name . ' ' . $partner->last_name;

                // session timeout code in front-controller public/index.php
                $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

                // test
                // echo "uniqID: " . $_SESSION['user'] . "<br>";
                // echo "Logged in? " . $_SESSION['loggedIn'] . "<br>";
                // echo "user_id: " . $_SESSION['user_id'] . "<br>";
                // echo "access level: " .$_SESSION['access_level'] . "<br>";
                // echo "First name: " . $_SESSION['first_name'] . "<br>";
                // echo "Full name: " . $_SESSION['full_name'] . "<br>";
                // echo "Full name: " . $_SESSION['full_name'] . "<br>";
                // exit();

                header("Location: /");
                exit();
            }
            else
            {
                echo "Error updating first login status in database.";
                exit();
            }
        }
        else
        {
            echo "Error logging in. Please check credentials and try again.";
            exit();
        }
   }


//  = = = = = Log In Dealer  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =  //

   /**
    * logs in dealer if matching credentials found
    *
    *  @return user object or null
    */
   public function loginDealerAction()
   {
       // retrieve form values
       $email = ( isset($_REQUEST['email'])  ) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL) : '';
       $password = ( isset($_REQUEST['password'])  ) ? filter_var($_REQUEST['password'], FILTER_SANITIZE_STRING) : '';

       // test
       // echo $email . "<br>";
       // echo $password  . "<br>";
       // exit();

       // validate dealer & find if in database; store dealer data in $dealer object
       $dealer = Dealer::validateLoginCredentials($email, $password);

       // test
       // echo '<pre>';
       // print_r($dealer);
       // echo "</pre>";
       // exit();

       // not found
       if ($dealer == false)
       {
           View::renderTemplate('Error/index.html', [
               'errorMessage' => 'Dealer cannot be found.'
           ]);
           exit();
       }

       /* - - - returning dealer - - - - - - - - - - - - - - - - - - - - - */

       // check if returning user; if true log in
       if( ($dealer) && ($dealer->first_login == 0) )
       {
           // log returning user in
           // create unique id & store in SESSION variable
           $_SESSION['user'] = md5($dealer->id);
           $_SESSION['userUniqId'] = md5($dealer->id);
           $_SESSION['loggedIn'] = true;
           $_SESSION['userType'] = 'dealer';

           // assign user ID & access_level & full_name to SESSION variables
           $_SESSION['user_id'] = $dealer->id;
           $_SESSION['access_level'] = $dealer->access_level;
           $_SESSION['first_name'] = $dealer->first_name;
           $_SESSION['full_name'] = $dealer->first_name . ' ' . $dealer->last_name;

           // session timeout code in front-controller public/index.php
           $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

           // test
           // echo "uniqID: " . $_SESSION['user'] . "<br>";
           // echo "Logged in? " . $_SESSION['loggedIn'] . "<br>";
           // echo "user_id: " . $_SESSION['user_id'] . "<br>";
           // echo "access level: " .$_SESSION['access_level'] . "<br>";
           // echo "First name: " . $_SESSION['first_name'] . "<br>";
           // echo "Full name: " . $_SESSION['full_name'] . "<br>";
           // echo "User type: " . $_SESSION['userType'] . "<br>";
           // exit();

           // send to root
           header("Location: /");
           exit();
       }



       /* - - - new dealer's first attempt to log in - - - - - - - - - - - - - - - - */
       if ( ($dealer) && ($dealer->first_login == 1) )
       {

           $firstYear = date('Y') - 55;
           $thisYear = date('Y');

           // create array of years
           $years = range( $firstYear, $thisYear);

           $instructions = 'You\'re almost done '.$dealer->first_name.'. Please
           answer the following questions to help secure your account.';

           // send to security Questions
           View::renderTemplate('Register/security-questions.html', [
               'partner'      => $dealer,
               'years'        => $years,
               'instructions' => $instructions,
               'type'         => 'dealer'
           ]);
           exit();
       }



       /* - - - new dealer's first log in after answering security questions - - */
       // check if first login of new user (first_login == 1); if true, special page
       if( ($dealer) && ($dealer->first_login == 1 && $dealer->security1 != '') )
       {
           // update first_login value
           $result = Dealer::updateFirstLogin($partner->id);

           if ($result)
           {
               // log returning user in
               // create unique id & store in SESSION variable
               $_SESSION['user'] = md5($dealer->id);
               $_SESSION['userUniqId'] = md5($dealer->id);
               $_SESSION['loggedIn'] = true;
               $_SESSION['userType'] = 'dealer';

               // assign user ID & access_level & full_name to SESSION variables
               $_SESSION['user_id'] = $dealer->id;
               $_SESSION['access_level'] = $dealer->access_level;
               $_SESSION['first_name'] = $dealer->first_name;
               $_SESSION['full_name'] = $dealer->first_name . ' ' . $dealer->last_name;

               // session timeout code in front-controller public/index.php
               $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

               // test
               // echo "uniqID: " . $_SESSION['user'] . "<br>";
               // echo "Logged in? " . $_SESSION['loggedIn'] . "<br>";
               // echo "user_id: " . $_SESSION['user_id'] . "<br>";
               // echo "access level: " .$_SESSION['access_level'] . "<br>";
               // echo "First name: " . $_SESSION['first_name'] . "<br>";
               // echo "Full name: " . $_SESSION['full_name'] . "<br>";
               // echo "Full name: " . $_SESSION['full_name'] . "<br>";
               // exit();

               header("Location: /");
               exit();
           }
           else
           {
               echo "Error updating first login status in database.";
               exit();
           }
       }
       else
       {
           echo "Error logging in. Please check credentials and try again.";
           exit();
       }
  }





    public function forgotPassword()
    {
        View::renderTemplate('Login/get-new-password.html', [

        ]);
    }




    public static function getNewPassword()
    {
        // Verify that email exists in `users` table
        $email = ( isset($_REQUEST['email_address']) ) ? htmlspecialchars($_REQUEST['email_address']) : '';

        // verify user exists; return $user object
        $customer = Customer::doesCustomerExist($email);

        // test
        // echo '<pre>';
        // print_r($customer);
        // echo '</pre>';
        // exit();

        // customer found
        if($customer)
        {
            // create temp password
            $tmp_pass = bin2hex(openssl_random_pseudo_bytes(4));

            // insert temporary password
            $result = Customer::insertTempPassword($customer->id, $tmp_pass);

            // table updated with temp password
            if ($result)
            {
                // send email to user; pass $customer object & $tmp_pass
                $result = Mail::sendTempPassword($customer, $tmp_pass);

                // mail successful
                if($result)
                {
                    $message = "A temporary password was sent to your email address.
                      Please use it to log in and reset your password.";

                    View::renderTemplate('Success/index.html', [
                        'message' => $message,
                        'temp_pass_sent' => 'true'
                    ]);
                }
                // mail failed
                else
                {
                    echo "Unable to send a temporary password. Pleas try again";
                    exit();
                }
            }
            // table update failed
            else
            {
                $errorMessage = "An error occurred inserting temp password.";

                View::renderTemplate('Error/index.html', [
                   'errorMessage' => $errorMessage,
                ]);
            }
        }
        // customer not found
        else
        {
            $errorMessage = "User not found. Please verify login credentials
            and try again.";

            View::renderTemplate('Error/index.html', [
               'errorMessage' => $errorMessage,
            ]);
        }
    }




    public function tempPassLogin()
    {
        // retrieve query string variable
        $tmp_pass = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        View::renderTemplate('Login/temp-password-login.html', [
           'tmp_pass' => $tmp_pass
        ]);
    }




    public function resetPassword()
    {
        // retrieve form values
        $email = ( isset($_REQUEST['email'])  ) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL) : '';
        $tmp_pass = ( isset($_REQUEST['tmppassword'])  ) ? filter_var($_REQUEST['tmppassword'], FILTER_SANITIZE_STRING) : '';
        $newpassword = ( isset($_REQUEST['newpassword'])  ) ? filter_var($_REQUEST['newpassword'], FILTER_SANITIZE_STRING) : '';
        $confirm_newpassword = ( isset($_REQUEST['confirm_newpassword'])  ) ? filter_var($_REQUEST['confirm_newpassword'], FILTER_SANITIZE_STRING) : '';

        // validation
        if($email == '' || $tmp_pass == '' || $newpassword == '' || $confirm_newpassword == '')
        {
           $errorMessage = 'All fields required.';
           View::renderTemplate('Error/index.html', [
              'errorMessage' => $errorMessage
           ]);
           exit();
        }

        if(strlen($newpassword) < 6 )
        {
           $errorMessage = 'Password must be at least 6 characters in length.';
           View::renderTemplate('Error/index.html', [
              'errorMessage' => $errorMessage
           ]);
           exit();
        }

        if($newpassword != $confirm_newpassword)
        {
           $errorMessage = 'Passwords do not match. Please check and try again.';
           View::renderTemplate('Error/index.html', [
              'errorMessage' => $errorMessage
           ]);
           exit();
        }

        // check if user match; store user record
        $customer = Customer::matchCustomer($email, $tmp_pass);

        // customer found
        if($customer)
        {
           // reset password
           $result = Customer::resetPassword($customer->id, $newpassword);

            // password reset successfully
            if($result)
            {
                // delete tmp_pass from users table
                $result = Customer::deleteTempPassword($customer->id);

                //  success! header to login route
                if ($result)
                {
                    header("Location: /admin/login");
                    exit();
                }
                else
                {
                    $errorMessage = "Unable to delete temp password.";
                    View::renderTemplate('Error/index.html', [
                        'errorMessage' => $errorMessage
                    ]);
                    exit();
                }
            }
            // password reset failed
            else
            {
                $errorMessage = "Unable to reset password.";
                View::renderTemplate('Error/index.html', [
                    'errorMessage' => $errorMessage
                ]);
                exit();
            }
        }
        // customer not found
        else
        {
           $errorMessage = "Unable to match customer with temporary password.";
           View::renderTemplate('Error/index.html', [
              'errorMessage' => $errorMessage
           ]);
           exit();
        }
    }

}
