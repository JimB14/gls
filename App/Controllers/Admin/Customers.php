<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\Dealer;
use \App\Models\State;
use \App\Models\Partner;
use \App\Models\Customer;
use \App\Models\Order;
use \App\Mail;


class Customers extends \Core\Controller
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
        // if (isset($_SESSION['user']) && $_SESSION['userType'] != 'dealer'
        //     || $_SESSION['userType'] != 'partner'
        //     || $_SESSION['userType'] != 'customer')
        // {
        //     header("Location: /");
        //     exit();
        //
        // }
    }



// = = = = = = = NON ADMIN functions  = = = = = = = = = = = = = = = //
// = = = = = = suffix 'Action' not required = = = = = = = = = = = = //
    public function forgotPassword()
    {
        View::renderTemplate('Login/get-new-password.html', [
            'userType'  => 'customer'
        ]);
    }




    public static function getPassword()
    {
        // Verify that email exists in `users` table
        $email = ( isset($_REQUEST['email_address']) ) ? htmlspecialchars($_REQUEST['email_address']) : '';

        // verify user exists
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

        View::renderTemplate('Login/temp-customer-password-login.html', [
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

        // user found
        if($customer)
        {
            $firstYear = date('Y') - 55;
            $thisYear = date('Y');

            // create array of years
            $years = range( $firstYear, $thisYear);

            $instructions = 'You\'re almost done. Please answer the following
                security questions.';

            // test
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';

            // display security questions
            View::renderTemplate('Login/answer-security-questions.html', [
                'user'         => $customer,
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

        // check values in `customers`
        $customer = Customer::checkSecurityAnswers($id);

        // test
        // echo '<pre>';
        // print_r($customer);
        // echo '</pre>';
        // exit();

        // success
        if (!empty($customer))
        {
            // update password
            $result = Customer::resetPassword($id, $newpassword);

            // password reset successfully
            if($result)
            {
                // delete tmp_pass from `customers` table
                $result = Customer::deleteTempPassword($customer->id);

                //  success! header to login route
                if ($result)
                {
                    header("Location: /admin/customer/login");
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


    //  = = = = = =  Admin functions ('Action' suffix required)  = = = = = = //

    public function getCustomersAction()
    {
        // get customers
        $customers = Customer::getCustomers();

        // test
        // echo '<pre>';
        // print_r($customers);
        // echo '</pre>';
        // exit();

        // get count
        $count = Customer::getCount();

        View::renderTemplate('Admin/Armalaser/Show/customers.html', [
            'customers' => $customers,
            'count'     => $count
        ]);
    }



    public function getCustomersByType()
    {
        // retrieve type from query string
        $type = ( isset($_REQUEST['type'] ) ) ? filter_var($_REQUEST['type'], FILTER_SANITIZE_STRING): '';

        // get customers
        $customers = Customer::getCustomersByType($type);

        // get count
        $count = Customer::getCount();

        View::renderTemplate('Admin/Armalaser/Show/customers.html', [
            // 'pagetitle' => 'Customers - ' . ucwords($type),
            'customers' => $customers,
            'type'      => $type,
            'count'     => $count
        ]);
    }



    /**
     * displays populated form to allow user to edit customer record
     *
     * @return
     */
    public function editcustomerAction()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // get user
        $customer = Customer::getCustomer($id);

        // test
        // echo '<pre>';
        // print_r($customer);
        // echo '</pre>';
        // exit();

        // get states
        $states = State::getStates();

        View::renderTemplate('Admin/Armalaser/Update/customer.html', [
            'customer' => $customer,
            'states'   => $states
        ]);
    }



    /**
     * displays populated form to allow user to edit customer record
     *
     * @return
     */
    public function editAction()
    {
        // retrieve id from query string
        $id = $this->route_params['id'];

        // get user
        $customer = Customer::getCustomer($id);

        // echo '<pre>'; print_r($customer);echo '</pre>';

        // get states
        $states = State::getStates();

        View::renderTemplate('Admin/Armalaser/Update/customer.html', [
            'customer' => $customer,
            'states'   => $states
        ]);
    }



    public function searchByLastNameAction()
    {
        // retrieve last name from form
        $last_name = ( isset($_REQUEST['last_name'] ) ) ? filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING): '';

        $customers = Customer::getByLastName($last_name);

        // test
        // echo '<pre>';
        // print_r($customers);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Armalaser/Show/customers.html', [
            'searched'  => $last_name,
            'customers' => $customers
        ]);
    }




    /**
     * posts updated customer data to `customers` table
     *
     * @return boolean
     */
    public function updateCustomerAction()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // update customer
        $result = Customer::updateCustomer($id);

        if($result)
        {
            echo '<script>alert("Customer successfully updated!")</script>';
            echo '<script>window.location.href="/admin/customers/get-customers"</script>';
        }
    }



    /**
     * posts updated customer data to `customers` table
     *
     * @return boolean
     */
    public function updateMyAccountAction()
    {
        $referer = $_SERVER['HTTP_REFERER'];

        // retrieve id from query string
        $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // update customer
        $result = Customer::updateCustomer($id);

        header("Location: $referer");
        exit();
    }



    /**
     * Updates customer password in `customers`
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



    /**
     * Retrieves orders by customer ID
     *
     * @return Object       The customer's orders
     */
    public function getOrdersAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // get customer
        $customer = Customer::getCustomer($id);

        // test
        // echo '<h4>Customer:</h4>';
        // echo '<pre>';
        // print_r($customer);
        // echo '</pre>';
        // exit();

        // get orders
        $orders = Order::getCustomerOrders($id);

        // test
        // echo '<h4>Orders:</h4>';
        // echo '<pre>';
        // print_r($orders);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Armalaser/Show/customer-orders.html', [
            'pagetitle' => 'Orders for ' . $customer->billing_firstname . ' ' . $customer->billing_lastname,
            'customer' => $customer,
            'orders'   => $orders
        ]);
    }



    //   = = = = = delete customer function not being used = = = = = = = = //
    //   Cannot delete customer without deleting orders, which diminishes and
    //   compromises value of store data. It will not compromise data to delete
    //   a customer who never placed an order, but doing so decreases the number
    //   of recipients of email marketing campaigns.
    /**
     * deletes customer from `customers` table & CASCADES to delete orders
     *
     * @return boolean
     */
    public function deleteCustomerAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // delete customer
        $result = Customer::deleteCustomer($id);

        if($result)
        {
            echo '<script>alert("Customer successfully deleted!")</script>';
            echo '<script>window.location.href="/admin/customers/get-customers"</script>';
            exit();
        }
    }






    /**
     *  Adds new customer to `customers`
     *
     * @return View
     */
    public function addAction()
    {
        // echo "Connected to add() in Customers Controller!";

        // get states
        $states = State::getStates();

        View::renderTemplate('Admin/Armalaser/Add/customer.html', [
            'pagetitle' => 'Create customer',
            'states' => $states
        ]);
    }



    public function createAction()
    {
        // echo "Connected to create() in Customers controller!";
        // echo '<pre>';
        // print_r($_REQUEST);

        // create new customer
        $results = Customer::create();

        if ($results['result'])
        {
            header("Location: /admin/customers/get-customers?type=all");
            exit();
        }
        else
        {
            echo "Error in Customers Controller createAction: unable to create customer.";
            exit();
        }
    }



    // public function getAction()
    // {
    //     // echo "Connected.";

    //     $id = $this->route_params['id'];

    //     // get customer
    //     $customer = Customer::getCustomer($id);

    //     // echo '<pre>'; print_r($customer); echo '</pre>';

    //     View::renderTemplate('Admin/Armalaser/Show/phone-order.html', [
    //         'pagetitle' => 'Phone Order',
    //         'customer' => $customer
    //     ]);
    // }



    /**
     * Ajax request for customers & template
     *
     * @return View
     */
    public function ajaxGetCustomersAction()
    {
        // echo "Connected to ajaxGetCustomers() in Customers controlller."; exit();

        // get customers
        $customers = Customer::getCustomers();


        View::renderTemplate('Admin/Armalaser/Show/phone-order-customers-template.html', [
            'customers' => $customers
        ]);
    }



    /**
     * Ajax request for customer search
     *
     * @return View
     */
    public function ajaxSearchAction()
    {
        // echo 'Inside ajaxSearchAction(): ' . $searchword; exit();

        // retrieve form data
        $searchword = (isset($_REQUEST['searchword'])) ? filter_var($_REQUEST['searchword'], FILTER_SANITIZE_STRING) : '';

        // get customers
        $customers = Customer::searchCustomers($searchword);

        View::renderTemplate('Admin/Armalaser/Show/phone-order-customers-template.html', [
            'customers' => $customers,
            'searchword' => $searchword
        ]);
    }

}
