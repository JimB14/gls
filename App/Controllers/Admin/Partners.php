<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\State;
use \App\Models\Partner;
use \App\Models\Order;
use \App\Mail;


class Partners extends \Core\Controller
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
     * retrieves all records from users table
     *
     * @return object The records of all users
     */
    public function getPartnersAction()
    {
        // get partners
        $partners = Partner::getPartners();

        // test
        // echo '<pre>';
        // print_r($partners);
        // echo '</pre>';
        // exit();

        // render view
        View::renderTemplate('Admin/Armalaser/Show/partners.html', [
            'pagetitle' => 'Manage Partners',
            'users'     => $partners
        ]);
    }


    /**
     * retrieves partners by last_name field
     *
     * @return object  The user(s) for searched last_name
     */
    public function searchPartnersByLastNameAction()
    {
        // retrieve data from query string
        $last_name = ( isset($_REQUEST['last_name']) ) ? filter_var($_REQUEST['last_name']): '';

        if($last_name === '')
        {
            header("Location: /admin/partners/get-partners");
            exit();
        }

        // get partners
        $partners = Partner::getPartnersByLastName($last_name);

        // test
        // echo '<pre>';
        // print_r($partners);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Show/partners.html', [
            'pagetitle' => 'Manage Partners',
            'users'     => $partners,
            'searched'  => $last_name
        ]);
    }



    // = = = = =  Admin functionality (requires 'Action' suffix) = = = = = = //

    /**
     * displays populated form to allow user to edit partner record
     *
     * @return
     */
    public function editPartnerAction()
    {
        // retrieve partner ID from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get states
        $states = State::getStates();

        // get shows
        $partner = Partner::getPartner($id);

        // test
        // echo '<pre>';
        // print_r($partner);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Armalaser/Update/partner.html', [
            'states'  => $states,
            'partner'  => $partner
        ]);
    }




    /**
     * posts updated partner data to `partners` table
     *
     * @return boolean
     */
    public function updatePartnerAction()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // update partner
        $result = Partner::updatePartner($id);

        if($result)
        {
            echo '<script>alert("Partner successfully updated!")</script>';
            echo '<script>window.location.href="/admin/partners/get-partners"</script>';
        }
    }




    /**
     * deletes partner from `partners` table
     *
     * @return boolean
     */
    public function deletePartnerAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // delete partner
        $result = Partner::deletePartner($id);

        if($result)
        {
            echo '<script>alert("Partner successfully deleted!")</script>';
            echo '<script>window.location.href="/admin/partners/get-partners"</script>';
            exit();
        }
    }




    /**
     * displays form to add new partner record
     */
    public function addPartnerAction()
    {
        // get states for drop-down
        $states = State::getStates();

        // render view
        View::renderTemplate('Admin/Armalaser/Add/partner.html', [
            'states' => $states
        ]);
    }




    /**
     * inserts new parnter into `partners` table
     *
     * @return boolean
     */
    public function postPartnerAction()
    {
        // add partner to `partners`
        $result = Partner::postPartner();

        if($result)
        {
            echo '<script>alert("Partner successfully added!")</script>';
            echo '<script>window.location.href="/admin/partners/get-partners"</script>';
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
     * Retrieves orders by partner ID
     *
     * @return Object       The customer's orders
     */
    public function getOrdersAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // get partner
        $partner = Partner::getPartner($id);

        // test
        // echo '<h4>Partner:</h4>';
        // echo '<pre>';
        // print_r($partner);
        // echo '</pre>';
        // exit();

        // get orders
        $orders = Order::getPartnerOrders($id);

        // test
        // echo '<h4>Orders:</h4>';
        // echo '<pre>';
        // print_r($orders);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Armalaser/Show/partner-orders.html', [
            'pagetitle' => 'Orders for ' . $partner->company,
            'customer' => $partner,
            'orders'   => $orders
        ]);
    }




    /**
     * Partner functionality - updates partner account in `partners`
     *
     * @return boolean
     */
    public function updateAccountAction()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // update partner account
        $result = Partner::updatePartnerAccount($id);

        if($result)
        {
            echo '<script>alert("Successfully updated!")</script>';
            echo '<script>window.location.href="/admin/partners/get-account?id='.$id.'"</script>';
        }
    }




    /**
     * Partner functionality - updates partner credentials in `partners`
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
        // echo $confirm_password . '<br>';
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

        // update partner record
        $result = Partner::updateAccountPassword($id, $password);

        if ($result)
        {
            echo '<script>alert("Your Password was successfully changed.\n\n\You will now be logged out.\n\n\You can log back in with your new password.")</script>';
            echo '<script>window.location.href="/admin/logout"</script>';
        }
    }


    // = = =  NON-ADMIN functions ('Action' suffix NOT required) = = = = = = //

    public function forgotPassword()
    {
        View::renderTemplate('Login/get-new-password.html', [
            'userType'  => 'partner'
        ]);
    }




    public static function getPassword()
    {
        // Verify that email exists in `users` table
        $email = ( isset($_REQUEST['email_address']) ) ? htmlspecialchars($_REQUEST['email_address']) : '';

        // verify user exists
        $partner = Partner::doesPartnerExist($email);

        // test
        // echo '<pre>';
        // print_r($partner);
        // echo '</pre>';
        // exit();

        // customer found
        if($partner)
        {
            // create temp password
            $tmp_pass = bin2hex(openssl_random_pseudo_bytes(4));

            // insert temporary password
            $result = Partner::insertTempPassword($partner->id, $tmp_pass);

            // table updated with temp password
            if ($result)
            {
                // send email to user; pass $customer object & $tmp_pass
                $result = Mail::sendTempPassword($partner, $tmp_pass);

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
                    echo "Unable to send a temporary password. Please try again";
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

        View::renderTemplate('Login/temp-partner-password-login.html', [
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
        $partner = Partner::matchCustomer($email, $tmp_pass);

        // user found
        if($partner)
        {
            $firstYear = date('Y') - 55;
            $thisYear = date('Y');

            // create array of years
            $years = range( $firstYear, $thisYear);

            $instructions = 'You\'re almost done. Please answer the following
                security questions.';

            // test
            // echo '<pre>';
            // print_r($partner);
            // echo '</pre>';

            // display security questions
            View::renderTemplate('Login/answer-security-questions.html', [
                'user'         => $partner,
                'years'        => $years,
                'instructions' => $instructions,
                'newpassword'  => $newpassword
            ]);
            exit();
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




    public static function securityAnswers()
    {
        // retrieve user ID from query string
        $id = ( isset($_REQUEST['id']) ) ? strtolower(filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT)) : '';
        $newpassword = ( isset($_REQUEST['newpassword']) ) ? strtolower(filter_var($_REQUEST['newpassword'], FILTER_SANITIZE_STRING)) : '';

        // check values in `partners`
        $partner = Partner::checkSecurityAnswers($id);

        // test
        // echo '<pre>';
        // print_r($partner);
        // echo '</pre>';
        // exit();

        // success
        if (!empty($partner))
        {
            // update password
            $result = Partner::resetPassword($id, $newpassword);

            // password reset successfully
            if($result)
            {
                // delete tmp_pass from users table
                $result = Partner::deleteTempPassword($partner->id);

                //  success! header to login route
                if ($result)
                {
                    header("Location: /admin/partner/login?action=newpassword");
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
