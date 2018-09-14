<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\Dealer;
use \App\Models\State;
use \App\Models\Order;
use \App\Mail;


class Dealers extends \Core\Controller
{

    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        // redirect user not logged in to root
        if(!isset($_SESSION['user']))
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
     * retrieves all dealer records
     *
     * @return object The records of all dealers
     */
    public function getDealersAction()
    {

        // get dealers
        $dealers = Dealer::getDealers();

        // test
        // echo '<pre>';
        // print_r($dealers);
        // echo '</pre>';
        // exit();

        // render view
        View::renderTemplate('Admin/Armalaser/Show/dealers.html', [
            'dealers' => $dealers
        ]);
    }


    /**
     * retrieves dealers by state field
     *
     * @return object  The dealers for searched state
     */
    public function searchDealersByStateAction()
    {
        // retrieve data from query string
        $state = ( isset($_REQUEST['state']) ) ? filter_var($_REQUEST['state']): '';

        if($state === '')
        {
            header("Location: /admin/dealers/get-dealers");
            exit();
        }

        // get dealers
        $dealers = Dealer::getDealersByState($state);

        // test
        // echo '<pre>';
        // print_r($dealers);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Armalaser/Show/dealers.html', [
            'dealers'   => $dealers,
            'searched'  => $state
        ]);
    }


    /**
     * displays form to add new dealer record
     */
    public function addDealerAction()
    {
        if ( ($_SESSION['userType'] == 'admin' && $_SESSION['access_level'] == 3) or $_SESSION['userType'] == 'supervisor')
        {
            // get states for drop-down
            $states = State::getStates();

            // render view
            View::renderTemplate('Admin/Armalaser/Add/dealer.html', [
                'states' => $states
            ]);
        }
        else
        {
            {
                echo "Authorization required for access.";
                exit();
            }
        }
    }


    /**
     * inserts new dealer into `dealers` table
     *
     * @return boolean
     */
    public function postDealerAction()
    {
        // add dealer to `dealers`
        $result = Dealer::postDealer();

        if($result)
        {
            echo '<script>alert("Dealer successfully added!")</script>';
            echo '<script>window.location.href="/admin/dealers/get-dealers"</script>';
        }
        else
        {
            View::renderTemplate('Error/index.html', [
                'errorMessage' => 'Incorrect Authorization Code.'
            ]);
            exit();
        }
    }



    /**
     * displays populated form to allow user to edit dealer record
     *
     * @return
     */
    public function editDealerAction()
    {
        // retrieve dealer ID from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get states
        $states = State::getStates();

        // get shows
        $dealer = Dealer::getDealerById($id);

        // test
        // echo '<pre>';
        // print_r($dealer);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Armalaser/Update/dealer.html', [
            'states'  => $states,
            'dealer'  => $dealer
        ]);
    }




    /**
     * posts updated dealer data to dealers table
     *
     * @return boolean
     */
    public function updateDealerAction()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // update dealer
        $result = Dealer::updateDealer($id);

        if($result)
        {
            echo '<script>alert("Successfully updated!")</script>';
            echo '<script>window.location.href="/admin/dealers/get-dealers"</script>';
        }
    }



    /**
     * deletes dealer from dealers table
     *
     * @return boolean
     */
    public function deleteDealerAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // delete laser
        $result = Dealer::deleteDealer($id);

        if($result)
        {
            echo '<script>alert("Dealer successfully deleted!")</script>';
            echo '<script>window.location.href="/admin/dealers/get-dealers"</script>';
            exit();
        }
    }



    /**
     * Retrieves orders by Dealer ID
     *
     * @return Object   The dealer's orders
     */
    public function getOrders()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // get dealer
        $dealer = Dealer::getDealer($id);

        // test
        // echo '<h4>Dealer:</h4>';
        // echo '<pre>';
        // print_r($dealer);
        // echo '</pre>';
        // exit();

        // get orders
        $orders = Order::getDealerOrders($id);

        // test
        // echo '<h4>Orders:</h4>';
        // echo '<pre>';
        // print_r($orders);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Armalaser/Show/dealer-orders.html', [
            'pagetitle' => 'Orders for ' . $dealer->company,
            'customer' => $dealer,
            'orders'   => $orders
        ]);
    }




    /**
     * Dealer functionality - updates dealer account in `dealers`
     *
     * @return boolean
     */
    public function updateAccountAction()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // update dealer account
        $result = Dealer::updateDealerAccount($id);

        if($result)
        {
            echo '<script>alert("Successfully updated!")</script>';
            echo '<script>window.location.href="/admin/dealers/get-account?id='.$id.'"</script>';
        }
    }



    /**
     * Dealer functionality - updates dealer credentials in `dealers`
     *
     * @return boolean
     */
    public function updateAccountPasswordAction()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // retrieve form values
        $password = ( isset($_REQUEST['new_password'])  ) ? filter_var($_REQUEST['new_password'], FILTER_SANITIZE_STRING) : '';
        $confirm_password = ( isset($_REQUEST['confirm_password'])  ) ? filter_var($_REQUEST['confirm_password'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // echo $password . '<br>';
        // echo $confirm_newpassword . '<br>';
        // exit();

        // validation if JavaScript fails or is disabled
        if($password == '' || $confirm_password == '')
        {
            $errorMessage = 'All fields required.';
            View::renderTemplate('Error/index.html', [
                'errorMessage' => $errorMessage
            ]);
           exit();
        }

        if(strlen($password) < 6 )
        {
            $errorMessage = 'Password must be at least 6 characters in length.';
            View::renderTemplate('Error/index.html', [
                'errorMessage' => $errorMessage
            ]);
            exit();
        }

        if($password != $confirm_password)
        {
            $errorMessage = 'Passwords do not match. Please check and try again.';
            View::renderTemplate('Error/index.html', [
                'errorMessage' => $errorMessage
            ]);
            exit();
        }

        // hash new password
        $password = password_hash($password, PASSWORD_DEFAULT);

        // update dealer record
        $result = Dealer::updateAccountPassword($id, $password);

        if ($result)
        {
            echo '<script>alert("Your Password was successfully changed.\n\n\You will now be logged out.\n\n\You can log back in with your new password.")</script>';
            echo '<script>window.location.href="/admin/logout"</script>';
        }
    }




    // = =  NOT LOGGED IN functions ('Action' suffix not required)  = = = = = //

    public function forgotPassword()
    {
        View::renderTemplate('Login/get-new-password.html', [
            'userType' => 'dealer'
        ]);
    }




    public static function getPassword()
    {
        // Verify that email exists in `users` table
        $email = ( isset($_REQUEST['email_address']) ) ? htmlspecialchars($_REQUEST['email_address']) : '';

        // verify dealer exists
        $dealer = Dealer::doesDealerExist($email);

        // test
        // echo '<pre>';
        // print_r($dealer);
        // echo '</pre>';
        // exit();

        // dealer found
        if($dealer)
        {
            // create temp password
            $tmp_pass = bin2hex(openssl_random_pseudo_bytes(4));

            // insert temporary password
            $result = Dealer::insertTempPassword($dealer->id, $tmp_pass);

            // table updated with temp password
            if ($result)
            {
                // send email to user; pass $customer object & $tmp_pass
                $result = Mail::sendTempPassword($dealer, $tmp_pass);

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
        // dealer not found
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

        View::renderTemplate('Login/temp-dealer-password-login.html', [
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

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // echo $email . '<br>';
        // echo $tmp_pass . '<br>';
        // echo $newpassword . '<br>';
        // echo $confirm_newpassword . '<br>';
        // exit();

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

        // check for match; store dealer record
        $dealer = Dealer::matchDealer($email, $tmp_pass);

        // test
        // echo '<h4>Dealer: </h4>';
        // echo '<pre>';
        // print_r($dealer);
        // echo '</pre>';
        // exit();

        // dealer found
        if($dealer)
        {
            $firstYear = date('Y') - 55;
            $thisYear = date('Y');

            // create array of years
            $years = range( $firstYear, $thisYear);

            $instructions = 'You\'re almost done '.$dealer->first_name.'. Please answer the following
                security questions.';

            // test
            // echo '<pre>';
            // print_r($dealer);
            // echo '</pre>';

            // display security questions
            View::renderTemplate('Login/answer-security-questions.html', [
                'user'         => $dealer,
                'years'        => $years,
                'instructions' => $instructions,
                'newpassword'  => $newpassword
            ]);
            exit();
        }
        // dealer not found
        else
        {
           $errorMessage = "Unable to match customer with temporary password.";
           View::renderTemplate('Error/index.html', [
              'errorMessage' => $errorMessage
           ]);
           exit();
        }
    }




    public static function securityAnswers()
    {
        // retrieve user ID from query string
        $id = ( isset($_REQUEST['id']) ) ? strtolower(filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT)) : '';
        $newpassword = ( isset($_REQUEST['newpassword']) ) ? strtolower(filter_var($_REQUEST['newpassword'], FILTER_SANITIZE_STRING)) : '';

        // check values in `dealer_users`
        $dealer = Dealer::checkSecurityAnswers($id);

        // test
        // echo '<pre>';
        // print_r($dealer);
        // echo '</pre>';
        // exit();

        // success
        if (!empty($dealer))
        {
            // update password
            $result = Dealer::resetPassword($id, $newpassword);

            // password reset successfully
            if($result)
            {
                // delete tmp_pass from users table
                $result = Dealer::deleteTempPassword($dealer->id);

                // success! header to login route
                if ($result)
                {
                    header("Location: /admin/dealer/login?action=newpassword");
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
        else
        {
            View::renderTemplate('Error/index.html', [
                'errorMessage' => 'Security answers do not match.'
            ]);
            exit();
        }
    }

}
