<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\User;
use \App\Models\Order;
use \App\Models\Endicia;
use \App\Models\Ups;
use \App\Mail;


/**
 * Postage controller
 *
 * PHP version 7.0
 */
class Postage extends \Core\Controller
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
     * Show the Admin Panel index page
     *
     * @return void
     */
    public function indexAction()
    {
        // retrieve GET variable
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';

        // if user not logged in send to login page
        if(!$user_id)
        {
            header("Location: /admin/user/login");
            exit();
        }
        else
        {
            // get user data from User model
            $user = User::getUserData($user_id);

            // test
            // echo '<pre>';
            // print_r($user);
            // echo '</pre>';
            // exit();

            // render view & pass $broker object
            View::renderTemplate('Admin/index.html', [
                'user' => $user
            ]);
        }
    }



    /**
     * retrieves account status from Endicia API
     *
     * @return Object   the account data
     */
    public function getAccountStatusAction()
    {
        // echo "Connected to getAccountStatus() in Postage controller.";

        // create instance of Endicia object/class
        $endicia = new Endicia();

        // get account data from Endicia API
        $account = $endicia->getAccountStatus();

        // test
        // echo '<pre>';
        // print_r($account);
        // echo '</pre>';
        // exit();

        // render view & pass $broker object
        View::renderTemplate('Admin/Armalaser/Show/endicia-account-status.html', [
            'pagetitle' => 'Endicia Account Status',
            'account'   => $account
        ]);
    }




    /**
     * purchases postage via Endicia API
     *
     * @return View
     */
    public function buyPostageAction()
    {
        // retrieve form data
        $amount = (isset($_REQUEST['postage_amount'])) ? filter_var($_REQUEST['postage_amount'], FILTER_SANITIZE_NUMBER_INT) : '';

        // create instance of Endicia object/class
        $endicia = new Endicia();

        // make postage purchase
        $response = $endicia->buyPostage($amount);

        // test
        // echo '<pre>';
        // print_r($response);
        // echo '</pre>';
        // exit();

        if ($response)
        {
            // create array
            $data = [
                'accountId' => $response->RecreditRequestResponse->CertifiedIntermediary->AccountID,
                'postageBalance' => $response->RecreditRequestResponse->CertifiedIntermediary->PostageBalance,
                'transactionID' => $response->RecreditRequestResponse->TransactionID
            ];

            // test
            // echo '<pre>';
            // print_r($data);
            // echo '</pre>';
            // exit();

            // send email
            $result = Mail::notifyPostagePurchase($data);

            if ($result)
            {
                // get updated account data from Endicia API
                $account = $endicia->getAccountStatus();

                // test
                // echo '<pre>';
                // print_r($account);
                // echo '</pre>';
                // exit();

                // render view
                View::renderTemplate('Admin/Armalaser/Show/endicia-account-status.html', [
                    'pagetitle' => 'Endicia Account Status',
                    'account'   => $account,
                    'response'  => $response,
                    'successMsg'  => 'Postage successfully purchased!'
                ]);
            }
        }
        else
        {
            echo "Error attempting to purchase postage.";
            // email webmaster
            exit();
        }
    }




    /**
     * voids USPS label via Endicia API
     *
     * @return [type]    [description]
     */
    public function voidUspsLabelAction()
    {
        // echo "Connected to voidUspsLabel() in Postage controller."; exit();

        // retrieve form data
        $trackingNumber = (isset($_REQUEST['tracking_number'])) ? filter_var($_REQUEST['tracking_number'], FILTER_SANITIZE_STRING) : '';
        $transactionId = (isset($_REQUEST['transaction_id'])) ? filter_var($_REQUEST['transaction_id'], FILTER_SANITIZE_STRING) : '';
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';
        $order_id = (isset($_REQUEST['order_id'])) ? filter_var($_REQUEST['order_id'], FILTER_SANITIZE_STRING) : '';

        // get user
        $user = User::getUserById($user_id);

        // get UNIX timestamp
        $timestamp = time();

        // create instance of Endicia Class / model
        $endicia = new Endicia();

        // void label
        $result = $endicia->voidLabel($user_id, $timestamp, $transactionId, $trackingNumber);

        // cancel label / refund successful
        if ($result)
        {
            
            // update record in `orders`
            $result = Order::updateAfterLabelVoid($order_id);

            // update successful
            if ($result)
            {
                // send mail
                $result = Mail::notifyUspsLabelVoided($order_id, $user);

                // success message
                echo '<script>';
                echo 'alert("The USPS label was successfully cancelled!")';
                echo '</script>';

                //  set URL for current window
                echo '<script>';
                echo 'window.location.href="/admin/orders/get-order?id='.$order_id.'"';
                echo '</script>';
                exit();
            }
            // update failure
            else
            {
                echo "Unable to update order in orders table.";
                // email webmaster
                exit();
            }
        }
    }




    /**
     * voids UPS label via UPS API
     *
     * @return [type]    [description]
     */
    public function voidUpsLabelAction()
    {
        // echo "Connected to voidUpsLabel() in Postage controller."; exit();

        // retrieve form data
        $trackingNumber = (isset($_REQUEST['tracking_number'])) ? filter_var($_REQUEST['tracking_number'], FILTER_SANITIZE_STRING) : '';
        $comment = (isset($_REQUEST['comment'])) ? filter_var($_REQUEST['comment'], FILTER_SANITIZE_STRING) : '';
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';
        $order_id = (isset($_REQUEST['order_id'])) ? filter_var($_REQUEST['order_id'], FILTER_SANITIZE_STRING) : '';

        // get user
        $user = User::getUserById($user_id);

        // UPS requires uppercase
        $trackingNumber = strtoupper($trackingNumber);

        // create instance of Ups Class / model
        $ups = new Ups();

        // void label
        $response = $ups->voidLabel($comment, $trackingNumber);

        // void successful
        if ($response->Response->ResponseStatusDescription == 'Success')
        {
            // send mail
            $result = Mail::notifyUpsLabelVoided($order_id, $user);

            // success message
            echo '<script>';
            echo 'alert("The USPS label was successfully voided!")';
            echo '</script>';

            //  set URL for current window
            echo '<script>';
            echo 'window.location.href="/admin/orders/get-order?id='.$order_id.'"';
            echo '</script>';
            exit();
        }
        // void failure
        else
        {
            echo "Unable to void the UPS label.";
            // email webmaster
            exit();
        }
    }




    /**
     * creates UPS return label via UPS API
     *
     * @return [type]    [description]
     */
    public function createUpsReturnLabelAction()
    {
        // echo "Connected to createUpsReturnLabelAction() in Postage controller."; exit();

        // retrieve form data
        $trackingNumber = (isset($_REQUEST['tracking_number'])) ? filter_var($_REQUEST['tracking_number'], FILTER_SANITIZE_STRING) : '';
        $comment = (isset($_REQUEST['label_recovery_comment'])) ? filter_var($_REQUEST['label_recovery_comment'], FILTER_SANITIZE_STRING) : '';
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';
        $order_id = (isset($_REQUEST['order_id'])) ? filter_var($_REQUEST['order_id'], FILTER_SANITIZE_STRING) : '';

        // get user
        $user = User::getUserById($user_id);

        // UPS requires uppercase
        $trackingNumber = strtoupper($trackingNumber);

        // create instance of Ups Class / model
        $ups = new Ups();

        // void label
        $response = $ups->labelRecovery($comment, $trackingNumber);

        // test
        // echo '<h4>UPS response: LabelRecovery';
        // echo '<pre>';
        // print_r($response);
        // echo '</pre>';
        // exit();

        // void successful
        if ($response->status == 'Success')
        {
            // update `orders` table
            $result = Order::updateAfterReturnLabelCreated($order_id, $response->url);

            // `orders` table update successful
            if ($result)
            {
                // send mail
                $result = Mail::notifyUpsReturnLabelGenerated($order_id, $user, $response);

                // mail successful
                if ($result)
                {
                    // success message
                    echo '<script>';
                    echo 'alert("A UPS return label was successfully created!")';
                    echo '</script>';

                    //  set URL for current window
                    echo '<script>';
                    echo 'window.location.href="/admin/orders/get-order?id='.$order_id.'"';
                    echo '</script>';
                    exit();
                }
                // mail failure
                else
                {
                    echo "Unable to send label created notification email.";
                    exit();
                }
            }
            // `orders` table update failed
            else
            {
                echo "Unable to update orders table.";
                // email webmaster
                exit();
            }
        }
        // void failure
        else
        {
            echo "Unable to create a UPS return label.";
            // email webmaster
            exit();
        }
    }




    // = = = = = = = = = class functions  = = = = = = = = = = = //



}
