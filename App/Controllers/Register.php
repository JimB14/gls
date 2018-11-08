<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Customer;
use \App\Models\UserPending;
use \App\Models\Partnerpending;
use \App\Models\Customerpending;
use \App\Models\Partner;
use \App\Models\Dealeruser;
use \App\Models\Dealer;
use \App\Models\Dealerpending;
use \App\Models\State;
use \App\Mail;


class Register extends \Core\Controller
{

    public function indexAction()
    {
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        if ($id) {
            $couponentered = true;
        } else {
            $couponentered = false;
        }
        
        View::renderTemplate('Register/customer.html',  [
            'pagetitle'     => 'Customer Registration',
            'couponentered' => $couponentered
        ]);
    }



    public function checkIfEmailAvailable()
    {
        // store POST variables from Ajax script
        $email = $_POST['email'];
        $type  = $_POST['type'];

        if ($type == 'customer')
        {
            $response = Customer::checkIfAvailable($email);
        }
        else if ($type == 'dealer')
        {
            // $response = Dealeruser::checkIfAvailable($email);
            $response = Dealer::checkIfAvailable($email);
        }
        else if ($type == 'partner')
        {
            $response = Partner::checkIfAvailable($email);
        }

        // return $response value ('available' or 'not available') to
        // Ajax method for processing
         echo $response;
    }




    public function registerNewCustomer()
    {
        // check honeypot for robot content
        $honeypot = filter_var($_REQUEST['honeypot_register'], FILTER_SANITIZE_STRING);

        if($honeypot != '')
        {
            echo "Form submitted successfully.";
            exit();
        }

        // post new customer & return array of data
        $results = Customer::addNewCustomer();

        // test
        // echo '<pre>';
        // print_r($results);
        // echo '</pre>';
        // exit();

        // assign values from $results associative array to variables
        $token = $results['token'];
        $id = $results['id'];

        // get customer data (type = array)
        $customer = Customer::getCustomer($id);

        // test
        // echo '<pre>';
        // print_r($customer);
        // echo '</pre>';
        // exit();

        // assign values from customer[] to variables
        $email = $customer->email;
        $first_name = $customer->billing_firstname;
        $full_name = $customer->billing_firstname . ' ' . $customer->billing_lastname;

        // add new customer to customers_pending table & pass $token & $customer_id
        $results = Customerpending::addToCustomersPending($token, $id);

        if($results)
        {
            // send verification email to new user's email address
            $result = Mail::sendAccountVerificationEmail($type='customer', $token, $id, $email, $first_name);

            if($result)
            {
               // define message
               $success_registration1 = "Thanks $full_name! You have successfully registered!";

                $success_registration2 = "<p>Please check your email to verify your
                    account.</p> <p>If you do not receive an email from
                    noreply@armalaser.com in the next few minutes, please
                    check your spam folder and white-list armalaser.com.</p>";

               View::renderTemplate('Success/index.html', [
                   'success_registration'  => 'true',
                   'success_registration1' => $success_registration1,
                   'success_registration2' => $success_registration2
               ]);
            }
            else
            {
                echo "Error. Verification email not sent";
                exit();
            }
        }
    }




    /**
     * adds new user to users_pending table
     *
     * @param string $token     Unique string
     * @param integer $user_id  The new user's ID
     */
    public function addToUsersPending($token, $user_id)
    {
        // add user data to users_pending table
        $results = UserPending::addUserToUserPending($token, $user_id);

        // return to $this->Controller
        return $results;
    }




    /**
     * adds new user to partners_pending table
     *
     * @param string $token     Unique string
     * @param integer $user_id  The new user's ID
     */
    public function addToPartnersPending($token, $user_id)
    {
        // add user data to users_pending table
        $results = Partnerpending::addUserToPartnersPending($token, $user_id);

        // return to $this->Controller
        return $results;
    }


    /**
     * Returns partner registration form
     *
     * @return View
     */
    public function partnerRegister()
    {
        // get states
        $states = State::getStates();

        View::renderTemplate('Register/partner.html',  [
            'pagetitle'   => 'Partner Registration',
            'states'    => $states
        ]);
    }



    /**
     * Returns dealer registration form
     *
     * @return View
     */
    public function dealerRegister()
    {
        // get states
        $states = State::getStates();

        View::renderTemplate('Register/dealer.html',  [
            'pagetitle' => 'Dealer Registration',
            'states'    => $states
        ]);
    }



    /**
     * Inserts data into `partners` & `partners_pending` & sends email
     *
     * @return View
     */
    public function registerNewPartner()
    {
        // check honeypot for robot content
        $honeypot = filter_var($_REQUEST['honeypot_register'], FILTER_SANITIZE_STRING);

        if($honeypot != '')
        {
            echo "Form submitted successfully.";
            exit();
        }

        // insert new partner
        $results = Partner::addNewPartner();

        // test
        // echo '<pre>';
        // print_r($results);
        // echo '</pre>';
        // exit();

        // Authorization code does not match
        if ($results['result'] == false)
        {
            View::renderTemplate('Error/index.html', [
                'errorMessage' => 'Incorrect Authorization Code.'
            ]);
            exit();
        }

        // success
        if ($results['result'] == 1)
        {
            // assign values from $results associative array to variables
            $token = $results['token'];
            $id = $results['id'];

            // get customer data (type = array)
            $partner = Partner::getPartner($id);

            // test
            // echo '<pre>';
            // print_r($partner);
            // echo '</pre>';
            // exit();

            // assign values from customer[] to variables
            $email = $partner->email;
            $first_name = $partner->first_name;
            $full_name = $partner->first_name . ' ' . $partner->last_name;

            // add new partner to parnters_pending table & pass $token & $user_id
            $results = Partnerpending::addUserToPartnersPending($token, $id);

            // success
            if($results)
            {
                // send verification email to new user's email address
                $result = Mail::sendAccountVerificationEmail($type='partner', $token, $id, $email, $first_name);

                if($result)
                {
                    // define message
                    $success_registration1 = "Thanks $full_name! You have successfully registered!";

                    $success_registration2 = "<p>Please check your email to verify your
                        account.</p> <p>If you do not receive an email from
                        noreply@armalaser.com in the next few minutes, please
                        check your spam folder and white-list armalaser.com.</p>";

                    View::renderTemplate('Success/index.html', [
                        'success_registration'  => 'true',
                        'success_registration1' => $success_registration1,
                        'success_registration2' => $success_registration2
                   ] );
                }
                else
                {
                    echo "Error. Verification email not sent";
                    exit();
                }
            }
            // failure
            echo "Error adding data to partners_pending table.";
            exit();
        }
        // failure
        echo "Error inserting data.";
        exit();
    }




    /**
     * Inserts data into `dealers` & `dealers_pending` & sends email
     *
     * @return View
     */
    public function registerNewDealer()
    {
        // check honeypot for robot content
        $honeypot = filter_var($_REQUEST['honeypot_register'], FILTER_SANITIZE_STRING);

        if($honeypot != '')
        {
            echo "Form submitted successfully.";
            exit();
        }

        // insert new dealer
        $results = Dealer::addNewDealer();

        // test
        // echo '<pre>';
        // print_r($results);
        // echo '</pre>';
        // exit();

        // Authorization code does not match
        if ($results['result'] == false)
        {
            View::renderTemplate('Error/index.html', [
                'errorMessage' => 'Incorrect Authorization Code.'
            ]);
            exit();
        }

        // success
        if ($results['result'] == 1)
        {
            // assign values from $results associative array to variables
            $token = $results['token'];
            $id = $results['id'];

            // get dealer
            $dealer = Dealer::getDealer($id);

            // test
            // echo '<pre>';
            // print_r($dealer);
            // echo '</pre>';
            // exit();

            // assign values from dealer to variables
            $email = $dealer->email;
            $first_name = $dealer->first_name;
            $full_name = $dealer->first_name . ' ' . $dealer->last_name;

            // add new partner to parnters_pending table & pass $token & $user_id
            $results = Dealerpending::addUserToDealersPending($token, $id);

            // success
            if($results)
            {
                // send verification email to new user's email address
                $result = Mail::sendAccountVerificationEmail($type='dealer', $token, $id, $email, $first_name);

                if($result)
                {
                    // define message
                    $success_registration1 = "Thanks $full_name! You have successfully registered!";

                    $success_registration2 = "<p>Please check your email to verify your
                        account.</p> <p>If you do not receive an email from
                        noreply@armalaser.com in the next few minutes, please
                        check your spam folder and white-list armalaser.com.</p>";

                    View::renderTemplate('Success/index.html', [
                        'success_registration'  => 'true',
                        'success_registration1' => $success_registration1,
                        'success_registration2' => $success_registration2
                   ] );
                }
                else
                {
                    echo "Error. Verification email not sent";
                    exit();
                }
            }
            // failure
            echo "Error adding data to dealers_pending table.";
            exit();
        }
        // failure
        echo "Error inserting data.";
        exit();
    }




    /**
     * new user clicks link in email
     *
     * @return string boolean
     */
    public function verifyCustomerAccount()
    {
        // retrieve token & user_id & pass to verifyNewUserAccount method below
        $token = isset($_REQUEST['token']) ? filter_var($_REQUEST['token'], FILTER_SANITIZE_STRING) : '';
        $id = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // verify match in `users_pending`, if true, set active = 1
        $result = Customerpending::verifyNewCustomerAccount($token, $id);

        // display success in view
        if($result)
        {
            // define message
            $acct_activated1 = "Congratulations! Your account has been activated.";

            // define message
            $acct_activated2 = '';

            // define message
            $acct_activated3 = 'You can now <a href="/admin/customer/login">Log In</a>';

            // show success message
            View::renderTemplate('Success/index.html', [
                'acct_activated'  => 'true',
                'acct_activated1' => $acct_activated1,
                'acct_activated2' => $acct_activated2,
                'acct_activated3' => $acct_activated3
            ]);
        }
        else
        {
            echo "An error occurred while verifying your account. Please try again.";
            exit();
        }
    }




    /**
     * validates new partner account
     *
     * @return string boolean
     */
    public function verifyPartnerAccount()
    {
        // retrieve token & user_id & pass to verifyNewUserAccount method below
        $token = isset($_REQUEST['token']) ? filter_var($_REQUEST['token'], FILTER_SANITIZE_STRING) : '';
        $id = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // verify match in `partners_pending`, if true, set active = 1
        $result = Partnerpending::verifyNewPartnerAccount($token, $id);

        // success
        if($result)
        {
            $acct_activated1 = "Congratulations! Your account has been activated.";
            $acct_activated2 = '';
            $acct_activated3 = 'You can now <a href="/admin/partner/login">Log In</a>';

            // show success message
            View::renderTemplate('Success/index.html', [
                'acct_activated'  => 'true',
                'acct_activated1' => $acct_activated1,
                'acct_activated2' => $acct_activated2,
                'acct_activated3' => $acct_activated3
            ]);
        }
        // failure
        else
        {
            echo "An error occurred while verifying your account. Please try again.";
            exit();
        }
    }




    /**
     * validates new dealer account
     *
     * @return string boolean
     */
    public function verifyDealerAccount()
    {
        // retrieve token & user_id & pass to verifyNewUserAccount method below
        $token = isset($_REQUEST['token']) ? filter_var($_REQUEST['token'], FILTER_SANITIZE_STRING) : '';
        $id = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // verify match in `dealers_pending`, if true, set active = 1
        $result = Dealerpending::verifyNewDealerAccount($token, $id);

        // success
        if($result)
        {
            $acct_activated1 = "Congratulations! Your account has been activated.";
            $acct_activated2 = '';
            $acct_activated3 = 'You can now <a href="/admin/dealer/login">Log In</a>';

            // show success message
            View::renderTemplate('Success/index.html', [
                'acct_activated'  => 'true',
                'acct_activated1' => $acct_activated1,
                'acct_activated2' => $acct_activated2,
                'acct_activated3' => $acct_activated3
            ]);
        }
        // failure
        else
        {
            echo "An error occurred while verifying your account. Please try again.";
            exit();
        }
    }




    public function postCustomerSecurityAnswers()
    {
        // echo "Connected."; exit();

        // retrieve query string variable
        $customerid = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get customer data (type = array)
        $customer = Customer::getCustomer($customerid);

        // store security answers in `customers` table
        $result = Customer::storeSecurityAnswers($customerid);

        // success
        if($result)
        {
            // update first_login status to false (first_login = 0)
            $result = Customer::updateFirstLoginStatus($customerid);

            if($result)
            {
              $register_success1 = "Your registration is complete!";

              $register_success2 = "Click 'Admin' in  the Menu. Then,
                'Dashboard' to access the Dashboard.";

              $register_success3 = 'Click <a href="/admin/customer/login">here</a> to Log In.';

              $register_success4 = "Remember, after you log in, click 'Admin'
                in the Menu.";

              View::renderTemplate('Success/index.html', [
                  'partner_register_success'  => 'true',
                  'register_success1' => $register_success1,
                  'register_success2' => $register_success2,
                  'register_success3' => $register_success3,
                  'register_success4' => $register_success4,
                  'first_name'        => $customer->billing_firstname,
                  'last_name'         => $customer->billing_lastname
              ]);
            }
            else
            {
                echo "Error updating user login status.";
                exit();
            }
        }
        else
        {
            echo "Error inserting security data.";
            exit();
        }
    }




    public function postPartnerSecurityAnswers()
    {
        // echo "Connected."; exit();

        // retrieve query string variable
        $partnerid = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get partner data
        $partner = Customer::getCustomer($partnerid);

        // store security answers in `partners` table
        $result = Customer::storeSecurityAnswers($partnerid);

        // success
        if($result)
        {
            // update first_login status to false (first_login = 0)
            $result = Customer::updateFirstLoginStatus($partnerid);

            if($result)
            {
              $register_success1 = "Your registration is complete!";

              $register_success2 = "Click 'Admin' in  the Menu. Then,
                'Dashboard' to access the Dashboard.";

              $register_success3 = 'Click <a href="/admin/partner/login">here</a> to Log In.';

              $register_success4 = "Remember, on the next screen, click 'Admin'
                in the Menu.";

              View::renderTemplate('Success/index.html', [
                  'partner_register_success'  => 'true',
                  'register_success1' => $register_success1,
                  'register_success2' => $register_success2,
                  'register_success3' => $register_success3,
                  'register_success4' => $register_success4,
                  'first_name'        => $partner->billing_firstname,
                  'last_name'         => $partner->billing_lastname
              ]);
            }
            else
            {
                echo "Error updating user login status.";
                exit();
            }
        }
        else
        {
            echo "Error inserting security data.";
            exit();
        }
    }




    public function postDealerSecurityAnswers()
    {
        // echo "Connected."; exit();

        // retrieve query string variable
        $dealerid = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get dealer
        $dealer = Customer::getCustomer($dealerid);

        // store security answers in `partners` table
        $result = Customer::storeSecurityAnswers($dealerid);

        // success
        if($result)
        {
            // update first_login status to false (first_login = 0)
            $result = Customer::updateFirstLoginStatus($dealerid);

            if($result)
            {
              $register_success1 = "Your registration is complete!";

              $register_success2 = "Click 'Admin' in  the Menu. Then,
                'Dashboard' to access the Dashboard.";

              $register_success3 = 'Click <a href="/admin/dealer/login">here</a> to Log In.';

              $register_success4 = "Remember, on the next screen, click 'Admin'
                in the Menu.";

              View::renderTemplate('Success/index.html', [
                  'partner_register_success'  => 'true',
                  'register_success1' => $register_success1,
                  'register_success2' => $register_success2,
                  'register_success3' => $register_success3,
                  'register_success4' => $register_success4,
                  'first_name'        => $dealer->billing_firstname,
                  'last_name'         => $dealer->billing_lastname
              ]);
            }
            else
            {
                echo "Error updating dealer user login status.";
                exit();
            }
        }
        else
        {
            echo "Error inserting security data.";
            exit();
        }
    }
}
