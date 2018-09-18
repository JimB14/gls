<?php

namespace App\Models;

use PDO;
use \App\Config;
use \App\Models\Payflow;

/**
* What follows is a script that partially implements the HTTPS protocol,
* via the PHP cURL extension. The assumption here is that an outside <form>
* is going to submit to this page with (minimally the named parameters
* below) -- a new POST willl be created and submitted to the PayPal servers to
* run the transaction.
*
* Note that that's pretty obviously *not* how you're going to get data to
* this script in real life; this is meant only to illustrate the basic
* operation.

* Use in conjunction with the Payflow Pro Developer's Guide.
* NOTE: The URLs are different from those in the Payflow Pro Devloper's Guide.  They are
* are pilot-payflowpro.paypal.com for testing and payflowpro.paypal.com for production.

* The nice thing about this protocol is that if you *don't* get a
* $response, you can simply re-submit the transaction *using the same
* REQUEST_ID* until you *do* get a response -- every time PayPal gets
* a transaction with the same REQUEST_ID, it will not process a new
* transactions, but simply return the same results, with a DUPLICATE=1
* parameter appended.
*/

/**
* API rebuild by Radu Manole,
* radu@u-zine.com, March 2007
*/

/**
 * Payflow model
 */
class Payflow extends \Core\Model
{
    // properties
    private $email;
    private $pilot_url = 'https://pilot-payflowpro.paypal.com';
    private $live_url  = 'https://payflowpro.paypal.com';
    private $submiturl;
    private $vendor;
    private $user;
    private $partner;
    private $password;
    private $errors = '';
    private $currencies_allowed = ['USD'];
    private $results = [];
    private $shipping_method;
    private $ship_to_zip;

    // set test mode for testing or LIVE
    private $paypal_test_mode = Config::PAYPALTESTMODE;


    /**
     * receives & validates PP credentials, sets submitURL, checks if curl_init() exists
     * @param  String   $vendor     [description]
     * @param  String   $user       [description]
     * @param  String   $partner    [description]
     * @param  String   $password   [description]
     * @return [type]           [description]
     */
    function payflow($vendor, $user, $partner, $password)
    {
        $this->vendor = $vendor;
        $this->user = $user;
        $this->partner = $partner;
        $this->password = $password;

        // validate VENDOR
        if (strlen($vendor) == 0)
        {
            $this->set_errors('Vendor not found');
            return false;
        }
        // validate USER
        if (strlen($this->user) == 0)
        {
            $this->set_errors('User not found');
            return false;
        }
        // validate PARTNER
        if (strlen($this->partner) == 0)
        {
            $this->set_errors('Partner not found');
            return false;
        }
        // validate PWD
        if (strlen($this->password) == 0)
        {
            $this->set_errors('Password not found');
            return false;
        }
        // set submiturl for test (development) or live (production)
        if ($this->paypal_test_mode == true)
        {
            $this->submiturl = $this->pilot_url;
        }
        else
        {
            $this->submiturl = $this->live_url;
        }

        // check for CURL
        // curl_init - initializes a cURL session
        // resource: http://php.net/manual/en/function.curl-init.php
        if (!function_exists('curl_init'))
        {
            // return error message if curl_init() not found
            $this->set_errors('Curl function not found.');
            return;
        }
        else
        {
            return true;
        }
    }



  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
    /**
     * Recurring billing sale transaction; accepts 9 parameters (one is an array)
     *
     * @param  String   $vendor       PP credential
     * @param  String   $user         PP credential
     * @param  String   $partner      PayPal
     * @param  String   $password     Account password
     * @param  Array    $parameters   Additional data
     * @return Array                  API response
     */
    // public function sale_transaction($email, $vendor, $user, $partner, $password, $parameters, $shipping_method)
    public function sale_transaction($email, $ship_to_zip, $vendor, $user, $partner, $password, $parameters)
    {
        // echo "Connected to sale_transaction()"; exit();

        $this->email = $email;
        $this->ship_to_zip = $ship_to_zip;
        $this->vendor = $vendor;
        $this->user = $user;
        $this->partner = $partner;
        $this->password = $password;
        // $this->shipping_method = $shipping_method;

        // set submiturl for test (development) or live (production)
        if ($this->paypal_test_mode == true)
        {
            $this->submiturl = $this->pilot_url;
        }
        else
        {
            $this->submiturl = $this->live_url;
        }

        // test
        // print 'Data to submit to API. In Payflow model.';
        // print '<pre>';
        // print_r($parameters);
        // print '</pre>';
        // print 'Values of variables: <br>';
        // print 'Config::PAYPALTESTMODE: ' . Config::PAYPALTESTMODE . '<br>';
        // print '$this->test_mode: ' . $this->paypal_test_mode . '<br>';
        // print '$this->pilot_url: ' . $this->pilot_url . '<br>';
        // print '$this->live_url: ' . $this->live_url . '<br>';
        // print '$this->submiturl: ' . $this->submiturl . '<br>';
        // exit();

        // validate card number (ACCT)
        if ($this->validate_card_number($parameters['ACCT']) == false)
        {
            $this->set_errors('Card Number not valid');
            return;
        }
        // validate EXPDATE
        if ($this->validate_card_expire($parameters['EXPDATE']) == false)
        {
            $this->set_errors('Card Expiration Date not valid');
            return;
        }
        // validate AMT
        if (!is_numeric($parameters['AMT']) || $parameters['AMT'] <= 0)
        {
            $this->set_errors('Jim, Amount is not valid');
            return;
        }
        // validate currency (if currency drop-down used)
        if (!in_array($parameters['CURRENCY'], $this->currencies_allowed))
        {
            $this->set_errors('Currency not allowed');
            return;
        }

        // create request_id for use in headers
        $tempstr = $parameters['ACCT'] . $parameters['AMT'] . date('YmdGis') . "1";
        $request_id = md5($tempstr);

        // alternative $request_id creation method
        // $request_id = date('YmdGis'); // must be unique ID

        // build query string for recurring billing to pass to PP
        $nvp_string  = 'USER=' . $this->user . '&';
        $nvp_string .= 'VENDOR=' . $this->vendor . '&';
        $nvp_string .= 'PARTNER=' . $this->partner . '&';
        $nvp_string .= 'PWD=' . $this->password . '&';
        $nvp_string .= 'TRXTYPE=' . 'S' . '&'; //  R = Recurring, S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
        $nvp_string .= 'TENDER=' . 'C' . '&'; // C = credit card, P = PayPal
        $nvp_string .= 'ACCT=' . $parameters['ACCT'] . '&';
        $nvp_string .= 'EXPDATE=' . $parameters['EXPDATE'] . '&';
        $nvp_string .= 'AMT=' . $parameters['AMT'] . '&';
        $nvp_string .= 'CURRENCY=' . $parameters['CURRENCY'] . '&';
        $nvp_string .= 'FIRSTNAME=' . $parameters['BILLTOFIRSTNAME'] . '&';
        $nvp_string .= 'LASTNAME=' . $parameters['BILLTOLASTNAME'] . '&';
        // $nvp_string .= 'TAXAMT=' . $parameters['TAXAMT'] . '&';
        // $nvp_string .= 'FREIGHTAMT=' . $parameters['FREIGHTAMT'] . '&';
        if (isset($parameters['CVV2']))
        {
            $nvp_string .= 'CVV2=' . $parameters['CVV2'] . '&';
        }
        $nvp_string .= 'IPADDRESS=' . $parameters['IPADDRESS'] . '&';

        // verbosity
        $nvp_string .= 'VERBOSITY=HIGH';

        // test
        // echo '<br><br>NVP string submitted to API: <br>';
        // echo $nvp_string . '<br><br>';
        // exit();

        // call method for headers
        $headers = $this->get_curl_headers();
        $headers[] = "X-VPS-Request-ID: " . $request_id;

        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->submiturl); // endpoint
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvp_string); //adding POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

        $result = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        $pfpro = $this->get_curl_result($result); // NVP string of result

        // test
        // echo 'API response NVP string: <br>';
        // echo $pfpro; // string

        // parse API response NVP string & store in array $response[]
        // parse_str() parses name-value pairs into array elements
        // resource:  http://php.net/manual/en/function.parse-str.php
        parse_str($pfpro, $response);

        // test
        // echo 'PNREF: ' . $response['PNREF'];
        // echo '<br><br>API response string converted to array';
        // echo '<pre>';
        // print_r($response);
        // echo '</pre>';
        // exit();

        // successful return PP response
        if (isset($response['RESULT']) && $response['RESULT'] == 0)
        {
            $results = [
                'response'      => $response,
                'email'         => $this->email,
                'ship_to_zip'   => $this->ship_to_zip
            ];
            return $results;
            exit();
        }
        // unsuccessful due to exceeding purchase limit set in PayPal Manager
        else if (isset($response['RESULT']) && $response['RESULT'] == 126 && $response['POSTFPSMSG'] == 'Review')
        {
            $results = [
                'response' => $response,
                'email'    => $this->email
            ];
            return $results;
            exit();
        }
        // unsuccessful for other reasons
        else
        {
            $this->set_errors($response['RESPMSG'] . ' ['. $response['RESULT'] . ']');
            return false;
        }
    }




    // resource:  https://developer.paypal.com/docs/classic/payflow/payflow-pro/payflow-pro-transaction-type/#credit-or-refund-transaction

    /**
     * makes request to PP API
     * @param  Array    $params   The transaction parameters
     * @return String             The API response
     */
    public function issueRefund($params)
    {
        // echo "Connected to issueRefund() in Payflow model.<br><br>";

        if (strlen($params['ORIGID']) < 3)
        {
          echo "Invalid PNREF string.";
          exit();
        }

        // set submiturl for test (development) or live (production)
        if ($this->paypal_test_mode == true)
        {
            $this->submiturl = $this->pilot_url;
        }
        else
        {
            $this->submiturl = $this->live_url;
        }

        // build hash
        $tempstr = $params['ORIGID'] . $params['AMT'] . date('YmdGis') . "2";
        $request_id = md5($tempstr);

        // Resource:  https://developer.paypal.com/docs/classic/payflow/payflow-pro/payflow-pro-transaction-type/#credit-or-refund-transaction
        // body
        $plist = 'USER=' . $params['USER'] . '&';
        $plist .= 'VENDOR=' . $params['VENDOR'] . '&';
        $plist .= 'PARTNER=' . $params['PARTNER'] . '&';
        $plist .= 'PWD=' . $params['PWD'] . '&';
        $plist .= 'TRXTYPE=' . $params['TRXTYPE'] . '&'; //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
        $plist .= "ORIGID=" . $params['ORIGID'] . '&'; // ORIGID to the PNREF value returned from the original transaction
        $plist .= 'AMT=' . $params['AMT'] . '&';  // format for PayPal with decimal and no comma
        $plist .= 'VERBOSITY=HIGH';

        // test
        // echo $plist . '<br>';
        // echo $this->submiturl . '<br>';
        // echo $request_id . '<br><br>';
        // exit();

        $headers = $this->get_curl_headers();
        $headers[] = "X-VPS-Request-ID: " . $request_id;

        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->submiturl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, $plist); // adding POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); // verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); // forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

        $result = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        // test
        // echo $result . '<br><br>';
        // exit();

        $pfpro = $this->get_curl_result($result); // result arrray

        // test
        // echo $pfpro . '<br>';
        // echo gettype($pfpro);
        // exit();

        // parse API response NVP string & store in array $response[]
        // parse_str() parses name-value pairs into array elements
        // resource:  http://php.net/manual/en/function.parse-str.php
        parse_str($pfpro, $response);

        // test
        // echo '<pre>';
        // print_r($response);
        // echo '</pre>';
        // exit();

        // return to Paypal model
        return $response;
    }








    /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

    // Modification of recurring billing profile
      /**
       * Recurring billing sale transaction; accepts 9 parameters (one is an array)
       * @param  string   $vendor         PP credential
       * @param  string   $user           PP credential
       * @param  string   $partner        PayPal or merchant
       * @param  string   $password       Account password
       * @param  array    $data_array     Additional data
       * @return String                   Name-value pair string paresed into array
       */
      function processPaymentForNewAgents($vendor, $user, $partner, $password, $data_array)
      {
          $this->vendor = $vendor;
          $this->user = $user;
          $this->partner = $partner;
          $this->password = $password;

          // set submit URL (endpoint)
          if ($this->test_mode == 1)
          {
              $this->submiturl = 'https://pilot-payflowpro.paypal.com';
          }
          else
          {
              $this->submiturl = 'https://payflowpro.paypal.com';
          }

          // create request_id for use in headers - see line #210
          $tempstr = $data_array['ORIGPROFILEID'] . date('YmdGis') . "1";
          $request_id = md5($tempstr);

          // build query string for recurring billing to pass to PP
          $plist  = 'USER=' . $this->user . '&';
          $plist .= 'VENDOR=' . $this->vendor . '&';
          $plist .= 'PARTNER=' . $this->partner . '&';
          $plist .= 'PWD=' . $this->password . '&';
          $plist .= 'ORIGPROFILEID=' . $data_array['ORIGPROFILEID'] . '&';
          $plist .= 'TRXTYPE=' . $data_array['TRXTYPE'] . '&'; //  R = Recurring, S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
          $plist .= 'TENDER=' . $data_array['TENDER'] . '&'; // C = credit card, P = PayPal
          $plist .= 'ACTION=' . $data_array['ACTION'] . '&';
          $plist .= 'AMT=' . $data_array['AMT'] . '&';
          // verbosity
          $plist .= 'VERBOSITY=HIGH';

          // call method for headers
          $headers = $this->get_curl_headers();
          $headers[] = "X-VPS-Request-ID: " . $request_id;

          $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $this->submiturl);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
          curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
          curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
          curl_setopt($ch, CURLOPT_POSTFIELDS, $plist); //adding POST data
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
          curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
          curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

          $result = curl_exec($ch);
          $headers = curl_getinfo($ch);
          curl_close($ch);

          $pfpro = $this->get_curl_result($result); //result arrray

          // parse query string & store name-value pairs in array $response[]
          parse_str($pfpro, $response);

          // test
          // echo $response['TRXPNREF'].'<br><br>';
          // echo 'Response array<br>';
          // echo '<pre>';
          // print_r($response);
          // echo '</pre>';
          // exit();

          // if successful return PP response
          if (isset($response['RESULT']) && $response['RESULT'] == 0)
          {
              // return to Paypal Controller
              return $response;
          }
          else
          {
              $this->set_errors($response['RESPMSG'] . ' ['. $response['RESULT'] . ']');
              return false;
          }
      }


      /**
       * [reduceBill description]
       * @param  String   $vendor       PP vendor credential
       * @param  String   $user         PP user credential
       * @param  String   $partner      PP partner credential
       * @param  String   $password     PP password credential
       * @param  Array    $data_array   Additional parameters
       * @return String                 Name-value pairs of PayPal's stored values
       */
      public function reduceBill($vendor, $user, $partner, $password, $data_array)
      {
          // test
          // echo $vendor . '<br>';
          // echo $user . '<br>';
          // echo $partner . '<br>';
          // echo $password . '<br>';
          // exit();

          $this->vendor = $vendor;
          $this->user = $user;
          $this->partner = $partner;
          $this->password = $password;

          // set submit URL (endpoint)
          if ($this->test_mode == 1)
          {
              $this->submiturl = 'https://pilot-payflowpro.paypal.com';
          }
          else
          {
              $this->submiturl = 'https://payflowpro.paypal.com';
          }

          // create request_id for use in headers
          $tempstr = $vendor . date('YmdGis') . "1";
          $request_id = md5($tempstr);

          // build query string for recurring billing to pass to PP
          $plist  = 'USER=' . $this->user . '&';
          $plist .= 'VENDOR=' . $this->vendor . '&';
          $plist .= 'PARTNER=' . $this->partner . '&';
          $plist .= 'PWD=' . $this->password . '&';
          $plist .= 'TRXTYPE=' . 'R' . '&'; //  R = Recurring, S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
          $plist .= 'ACTION=' . $data_array['ACTION'] . '&';
          $plist .= 'AMT=' . $data_array['AMT'] . '&';
          $plist .= 'TENDER=' . 'C' . '&'; // C = credit card, P = PayPal
          $plist .= 'ORIGPROFILEID=' . $data_array['ORIGPROFILEID'] . '&';
          $plist .= 'COMMENT1=' . $data_array['COMMENT1'] . '&';
          $plist .= 'IPADDRESS=' . $data_array['IPADDRESS'] . '&';
          $plist .= 'VERBOSITY=HIGH';

          // call method for headers
          $headers = $this->get_curl_headers();
          $headers[] = "X-VPS-Request-ID: " . $request_id;

          $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $this->submiturl);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
          curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
          curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
          curl_setopt($ch, CURLOPT_POSTFIELDS, $plist); //adding POST data
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
          curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
          curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

          $result = curl_exec($ch);
          $headers = curl_getinfo($ch);
          curl_close($ch);

          $pfpro = $this->get_curl_result($result); //result arrray

          // parse query string & store name-value pairs in array $response[]
          parse_str($pfpro, $response);

          // test
          // echo 'Response array<br>';
          // echo '<pre>';
          // print_r($response);
          // echo '</pre>';
          // exit();

          // if successful return PP response
          if (isset($response['RESULT']) && $response['RESULT'] == 0)
          {
              // return to Paypal Controller
              return $response;
          }
          else
          {
              $this->set_errors($response['RESPMSG'] . ' ['. $response['RESULT'] . ']');
              return false;
          }
      }



      /**
       * retrieves inquiry status response from PayPal
       *
       * @param  String   $vendor       PP credential
       * @param  String   $user         PP credential
       * @param  String   $partner      PP credential
       * @param  String   $password     PP credential
       * @param  Array    $data_array   Required parameters (includes PROFILEID)
       * @return String                 Name-value pairs of PayPal's stored values
       */
      function profileStatusInquiry($vendor, $user, $partner, $password, $data_array)
      {
          $this->vendor = $vendor;
          $this->user = $user;
          $this->partner = $partner;
          $this->password = $password;

          // set submit URL (endpoint)
          if ($this->test_mode == 1)
          {
              $this->submiturl = 'https://pilot-payflowpro.paypal.com';
          }
          else
          {
              $this->submiturl = 'https://payflowpro.paypal.com';
          }

          // create request_id for use in headers - see line #210
          $tempstr = $data_array['ORIGPROFILEID'] . date('YmdGis') . "1";
          $request_id = md5($tempstr);

          // build query string for recurring billing to pass to PP
          $plist  = 'USER=' . $this->user . '&';
          $plist .= 'VENDOR=' . $this->vendor . '&';
          $plist .= 'PARTNER=' . $this->partner . '&';
          $plist .= 'PWD=' . $this->password . '&';
          $plist .= 'TENDER=' . $data_array['TENDER'] . '&'; // C = credit card, P = PayPal
          $plist .= 'TRXTYPE=' . $data_array['TRXTYPE'] . '&'; //  R = Recurring, S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
          $plist .= 'ACTION=' . $data_array['ACTION'] . '&';
          $plist .= 'ORIGPROFILEID=' . $data_array['ORIGPROFILEID'] . '&';
          $plist .= 'VERBOSITY=HIGH';

          // call method for headers
          $headers = $this->get_curl_headers();
          $headers[] = "X-VPS-Request-ID: " . $request_id;

          $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $this->submiturl);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
          curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
          curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
          curl_setopt($ch, CURLOPT_POSTFIELDS, $plist); //adding POST data
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
          curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
          curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

          $result = curl_exec($ch);
          $headers = curl_getinfo($ch);
          curl_close($ch);

          $pfpro = $this->get_curl_result($result); //result arrray

          // parse query string & store name-value pairs in array $response[]
          parse_str($pfpro, $response);

          // test
          // echo $response['TRXPNREF'].'<br><br>';
          // echo 'Response array<br>';
          // echo '<pre>';
          // print_r($response);
          // echo '</pre>';
          // exit();

          // if successful return PP response
          if (isset($response['RESULT']) && $response['RESULT'] == 0)
          {
              // return to Paypal Controller
              return $response;
          }
          else
          {
              $this->set_errors($response['RESPMSG'] . ' ['. $response['RESULT'] . ']');
              return false;
          }
      }



      /**
       * authorizes credit card for use
       *
       * @param  String   $vendor       PP credential
       * @param  String   $user         PP credential
       * @param  String   $partner      PP credential
       * @param  String   $password     PP credential
       * @param  Array    $data_array   Required parameters (includes PROFILEID)
       * @return String                 Name-value pairs of PayPal's stored values
       */
      function authorizeCreditCard($vendor, $user, $partner, $password, $data_array)
      {
          $this->vendor = $vendor;
          $this->user = $user;
          $this->partner = $partner;
          $this->password = $password;

          // validate credit card data
          if ($this->validate_card_number($data_array['ACCT']) == false)
          {
              $this->set_errors('Card Number not valid');
              return;
          }
          if ($this->validate_card_expire($data_array['EXPDATE']) == false)
          {
              $this->set_errors('Card Expiration Date not valid');
              return;
          }

          // set submit URL (endpoint)
          if ($this->test_mode == 1)
          {
              $this->submiturl = 'https://pilot-payflowpro.paypal.com';
          }
          else
          {
              $this->submiturl = 'https://payflowpro.paypal.com';
          }

          // create request_id for use in headers - see line #210
          $tempstr = $data_array['FIRSTNAME'] . date('YmdGis') . "1";
          $request_id = md5($tempstr);

          // build query string for recurring billing to pass to PP
          $plist  = 'USER=' . $this->user . '&';
          $plist .= 'VENDOR=' . $this->vendor . '&';
          $plist .= 'PARTNER=' . $this->partner . '&';
          $plist .= 'PWD=' . $this->password . '&';
          $plist .= 'FIRSTNAME=' . $data_array['FIRSTNAME'] . '&';
          $plist .= 'LASTNAME=' . $data_array['LASTNAME'] . '&';
          $plist .= 'CARDTYPE=' . $data_array['CARDTYPE'] . '&';
          $plist .= 'ACCT=' . $data_array['ACCT'] . '&';
          $plist .= 'EXPDATE=' . $data_array['EXPDATE'] . '&';
          $plist .= 'TENDER=' . $data_array['TENDER'] . '&';
          $plist .= 'TRXTYPE=' . $data_array['TRXTYPE'] . '&'; //  R = Recurring, S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
          $plist .= 'CURRENCY=' . $data_array['CURRENCY'] . '&';
          $plist .= 'AMT=' . $data_array['AMT'] . '&';
          if (isset($data_array['CVV2']))
          {
              $plist .= 'CVV2=' . $data_array['CVV2'] . '&';
          }
          $plist .= 'IPADDRESS=' . $data_array['IPADDRESS'] . '&';
          $plist .= 'VERBOSITY=HIGH';


          // call method for headers
          $headers = $this->get_curl_headers();
          $headers[] = "X-VPS-Request-ID: " . $request_id;

          $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $this->submiturl);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
          curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
          curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
          curl_setopt($ch, CURLOPT_POSTFIELDS, $plist); //adding POST data
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
          curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
          curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

          $result = curl_exec($ch);
          $headers = curl_getinfo($ch);
          curl_close($ch);

          $pfpro = $this->get_curl_result($result); //result arrray

          // echo $pfpro; exit();

          // parse query string & store name-value pairs in array $response[]
          parse_str($pfpro, $response);

          // test
          // echo 'Response array<br>';
          // echo '<pre>';
          // print_r($response);
          // echo '</pre>';
          // exit();

          // if successful return PP response
          if (isset($response['RESULT']) && $response['RESULT'] == 0)
          {
              // return to Paypal Controller
              return $response;
          }
          else
          {
              $this->set_errors($response['RESPMSG'] . ' ['. $response['RESULT'] . ']');
              return false;
          }
      }



      /**
       * updates user profile with new credit card data
       *
       * @param  String $vendor       PP credential
       * @param  String $user         PP credential
       * @param  String $partner      PP credential
       * @param  String $password     PP credential
       * @param  Array $data_array    Additional required parameters
       * @return String               PP response name-value string
       */
      function updateUserProfileWithNewCardData($vendor, $user, $partner, $password, $data_array)
      {
          $this->vendor = $vendor;
          $this->user = $user;
          $this->partner = $partner;
          $this->password = $password;

          // set submit URL (endpoint)
          if ($this->test_mode == 1)
          {
              $this->submiturl = 'https://pilot-payflowpro.paypal.com';
          }
          else
          {
              $this->submiturl = 'https://payflowpro.paypal.com';
          }

          // create request_id for use in headers - see line #210
          $tempstr = $data_array['ORIGPROFILEID'] . date('YmdGis') . "1";
          $request_id = md5($tempstr);

          // build query string for recurring billing to pass to PP
          $plist  = 'USER=' . $this->user . '&';
          $plist .= 'VENDOR=' . $this->vendor . '&';
          $plist .= 'PARTNER=' . $this->partner . '&';
          $plist .= 'PWD=' . $this->password . '&';
          $plist .= 'TRXTYPE=' . $data_array['TRXTYPE'] . '&'; //  R = Recurring, S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
          $plist .= 'TENDER=' . $data_array['TENDER'] . '&';
          $plist .= 'ACTION=' . $data_array['ACTION'] . '&';
          $plist .= 'ORIGID=' . $data_array['ORIGID'] . '&'; // ORIGID is the PNREF value (length=12) of an original transaction used to update credit card account information.
          $plist .= 'ORIGPROFILEID=' . $data_array['ORIGPROFILEID'] . '&';
          $plist .= 'VERBOSITY=HIGH';

          // call method for headers
          $headers = $this->get_curl_headers();
          $headers[] = "X-VPS-Request-ID: " . $request_id;

          $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $this->submiturl);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
          curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
          curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
          curl_setopt($ch, CURLOPT_POSTFIELDS, $plist); //adding POST data
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
          curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
          curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

          $result = curl_exec($ch);
          $headers = curl_getinfo($ch);
          curl_close($ch);

          $pfpro = $this->get_curl_result($result); //result arrray

          // echo $pfpro; exit();

          // parse query string & store name-value pairs in array $response[]
          parse_str($pfpro, $response);

          // test
          // echo 'Response array<br>';
          // echo '<pre>';
          // print_r($response);
          // echo '</pre>';
          // exit();

          // if successful return PP response
          if (isset($response['RESULT']) && $response['RESULT'] == 0)
          {
              // return to Paypal Controller
              return $response;
          }
          else
          {
              $this->set_errors($response['RESPMSG'] . ' ['. $response['RESULT'] . ']');
              return false;
          }
      }


      /**
       * updates PayPal data for passed PROFILEID
       *
       * @param   String  $vendor       PP credential
       * @param   String  $user         PP credential
       * @param   String  $partner      PP credential
       * @param   String  $password     PP credential
       * @param   Array   $data_array   Additional required parameters
       * @return  Array                 PP response string parsed into array
       */
      public function processReactivation($vendor, $user, $partner, $password, $data_array)
      {
          $this->vendor = $vendor;
          $this->user = $user;
          $this->partner = $partner;
          $this->password = $password;

          // set submit URL (endpoint)
          if ($this->test_mode == 1)
          {
              $this->submiturl = 'https://pilot-payflowpro.paypal.com';
          }
          else
          {
              $this->submiturl = 'https://payflowpro.paypal.com';
          }

          // create request_id for use in headers - see line #210
          $tempstr = $data_array['ORIGPROFILEID'] . date('YmdGis') . "1";
          $request_id = md5($tempstr);

          // build query string for recurring billing to pass to PP
          $plist  = 'USER=' . $this->user . '&';
          $plist .= 'VENDOR=' . $this->vendor . '&';
          $plist .= 'PARTNER=' . $this->partner . '&';
          $plist .= 'PWD=' . $this->password . '&';
          $plist .= 'TRXTYPE=' . $data_array['TRXTYPE'] . '&'; //  R = Recurring, S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
          $plist .= 'TENDER=' . $data_array['TENDER'] . '&';
          $plist .= 'ACTION=' . $data_array['ACTION'] . '&';
          $plist .= 'ORIGPROFILEID=' . $data_array['ORIGPROFILEID'] . '&';
          $plist .= 'FIRSTNAME=' . $data_array['FIRSTNAME'] . '&';
          $plist .= 'LASTNAME=' . $data_array['LASTNAME'] . '&';
          $plist .= 'CARDTYPE=' . $data_array['CARDTYPE'] . '&';
          $plist .= 'ACCT=' . $data_array['ACCT'] . '&';
          $plist .= 'EXPDATE=' . $data_array['EXPDATE'] . '&';
          $plist .= 'CVV2=' . $data_array['CVV2'] . '&';
          $plist .= 'CURRENCY=' . $data_array['CURRENCY'] . '&';
          $plist .= 'AMT=' . $data_array['AMT'] . '&';
          $plist .= 'START=' . $data_array['START'] . '&';
          $plist .= 'VERBOSITY=HIGH';

          // call method for headers
          $headers = $this->get_curl_headers();
          $headers[] = "X-VPS-Request-ID: " . $request_id;

          $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $this->submiturl);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
          curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
          curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
          curl_setopt($ch, CURLOPT_POSTFIELDS, $plist); //adding POST data
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
          curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
          curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

          $result = curl_exec($ch);
          $headers = curl_getinfo($ch);
          curl_close($ch);

          $pfpro = $this->get_curl_result($result); //result arrray

          // echo $pfpro; exit();

          // parse query string & store name-value pairs in array $response[]
          parse_str($pfpro, $response);

          // test
          // echo 'Response array<br>';
          // echo '<pre>';
          // print_r($response);
          // echo '</pre>';
          // exit();

          // if successful return PP response
          if (isset($response['RESULT']) && $response['RESULT'] == 0)
          {
              // return to Paypal Controller
              return $response;
          }
          else
          {
              $this->set_errors($response['RESPMSG'] . ' ['. $response['RESULT'] . ']');
              return false;
          }
      }




  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
  // Cancel recurring billing

    /**
     * cancels payment using paypal's payflow gateway
     *
     * @param  string  $vendor          The vendor credential
     * @param  string  $user            The user credential
     * @param  string  $partner         The partner credential
     * @param  string  $password        The pwd credential
     * @param  string  $trxtype         Recurring = 'R'
     * @param  string  $tender          Tender = 'C' (credit)
     * @param  string  $action          Action = 'C' (credit)
     * @param  string  $origprofileid   The user's profile ID in PayPal's records
     * @return string                   The PayPal response string
     */
    public function cancelPayment($vendor, $user, $partner, $password, $trxtype, $tender, $action, $origprofileid)
    {
        // store credentials & required parameters in payflow class variables
        $this->vendor = $vendor;
        $this->user = $user;
        $this->partner = $partner;
        $this->password = $password;

        // set submit URL (endpoint)
        if ($this->test_mode == 1)
        {
            $this->submiturl = 'https://pilot-payflowpro.paypal.com';
        }
        else
        {
            $this->submiturl = 'https://payflowpro.paypal.com';
        }

        // create request_id for use in headers - see line #210
        $tempstr = $origprofileid . date('YmdGis') . "1";
        $request_id = md5($tempstr);

        // alternative $request_id creation method
        // $request_id = date('YmdGis'); // must be unique ID

        // build query string for recurring billing to pass to PP
        $plist  = 'USER=' . $this->user . '&';
        $plist .= 'VENDOR=' . $this->vendor . '&';
        $plist .= 'PARTNER=' . $this->partner . '&';
        $plist .= 'PWD=' . $this->password . '&';
        $plist .= 'TENDER=' . $tender . '&'; // C = credit card, P = PayPal
        $plist .= 'TRXTYPE=' . $trxtype . '&'; //  R = Recurring, S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
        $plist .= 'ACTION=' . $action . '&'; // C = cancel
        $plist .= 'ORIGPROFILEID=' . $origprofileid . '&';
        //$plist .= 'CLIENTIP=' . $data_array['clientip'] . '&';

        // verbosity
        $plist .= 'VERBOSITY=HIGH';

        // call method for headers
        $headers = $this->get_curl_headers();
        $headers[] = "X-VPS-Request-ID: " . $request_id;

        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->submiturl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, $plist); //adding POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

        $result = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        $pfpro = $this->get_curl_result($result); //result arrray

        // parse query string & store name-value pairs in array $response[]
        parse_str($pfpro, $response);

        // test
        // echo $response['RPREF'].'<br><br>';
        // echo 'Response array<br>';
        // echo '<pre>';
        // print_r($response);
        // echo '</pre>';
        // exit();

        // if successful return PP response
        if (isset($response['RESULT']) && $response['RESULT'] == 0)
        {
            // return to Paypal Model
            return $response;
        }
        else
        {
            $this->set_errors($response['RESPMSG'] . ' ['. $response['RESULT'] . ']');
            return false;
        }

    }









  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

    // Authorization
    function authorization($card_number, $card_expire, $amount, $card_holder_name, $CURRENCY = 'USD')
    {
        if ($this->validate_card_number($card_number) == false)
        {
            $this->set_errors('Card Number not valid');
            return;
        }
        if ($this->validate_card_expire($card_expire) == false)
        {
            $this->set_errors('Card Expiration Date not valid');
            return;
        }
        if (!is_numeric($amount) || $amount <= 0)
        {
            $this->set_errors('Amount is not valid');
            return;
        }
        if (!in_array($currency, $this->currencies_allowed))
        {
            $this->set_errors('Currency not allowed');
            return;
        }

        // build hash
        $tempstr = $card_number . $amount . date('YmdGis') . "1";
        $request_id = md5($tempstr);

        // body of the POST
        $plist = 'USER=' . $this->user . '&';
        $plist .= 'VENDOR=' . $this->vendor . '&';
        $plist .= 'PARTNER=' . $this->partner . '&';
        $plist .= 'PWD=' . $this->password . '&';
        $plist .= 'TENDER=' . 'C' . '&'; // C = credit card, P = PayPal
        $plist .= 'TRXTYPE=' . 'A' . '&'; //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
        $plist .= 'ACCT=' . $card_number . '&';
        $plist .= 'EXPDATE=' . $card_expire . '&';
        $plist .= 'NAME=' . $card_holder_name . '&';
        $plist .= 'AMT=' . $amount . '&';  // amount
        $plist .= 'CURRENCY=' . $CURRENCY . '&';
        $plist .= 'VERBOSITY=HIGH';

        $headers = $this->get_curl_headers();
        $headers[] = "X-VPS-Request-ID: " . $request_id;

        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->submiturl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, $plist); //adding POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

        // $rawHeader = curl_exec($ch); // run the whole process
        // $info = curl_getinfo($ch); //grabbing details of curl connection
        $result = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        $pfpro = $this->get_curl_result($result); //result arrray

        if (isset($pfpro['RESULT']) && $pfpro['RESULT'] == 0)
        {
            return $pfpro;
        }
        else
        {
            $this->set_errors($pfpro['RESPMSG'] . ' ['. $pfpro['RESULT'] . ']');
            return false;
        }
    }



  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

    // Delayed Capture
    function delayed_capture($origid, $card_number = '', $card_expire = '', $amount = '')
    {
        if (strlen($origid) < 3)
        {
            $this->set_errors('OrigID not valid');
            return;
        }

        // build hash
        $tempstr = $card_number . $amount . date('YmdGis') . "2";
        $request_id = md5($tempstr);

        // body
        $plist = 'USER=' . $this->user . '&';
        $plist .= 'VENDOR=' . $this->vendor . '&';
        $plist .= 'PARTNER=' . $this->partner . '&';
        $plist .= 'PWD=' . $this->password . '&';
        $plist .= 'TENDER=' . 'C' . '&'; // C = credit card, P = PayPal
        $plist .= 'TRXTYPE=' . 'D' . '&'; //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
        $plist .= "ORIGID=" . $origid . "&"; // ORIGID to the PNREF value returned from the original transaction
        $plist .= 'VERBOSITY=MEDIUM';

        $headers = $this->get_curl_headers();
        $headers[] = "X-VPS-Request-ID: " . $request_id;

        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->submiturl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, $plist); //adding POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

        $result = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        $pfpro = $this->get_curl_result($result); //result arrray

        if (isset($pfpro['RESULT']) && $pfpro['RESULT'] == 0)
        {
            return $pfpro;
        }
        else
        {
            $this->set_errors($pfpro['RESPMSG'] . ' ['. $pfpro['RESULT'] . ']');
            return false;
        }
    }



  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

    // Authorization followed by Delayed Capture
    function authorization_delayed_capture($card_number, $card_expire, $amount, $card_holder_name, $CURRENCY = 'USD')
    {
        // 1. authorization
        $result = $this->authorization($card_number, $card_expire, $amount, $card_holder_name, $CURRENCY = 'USD');
        if (!$this->get_errors() && isset($result['PNREF']))
        {
            // 2. delayed
            $result_capture = $this->delayed_capture($result['PNREF']);
            if (!$this->get_errors())
            {
                return $result_capture;
            }
        }
        return false;
      }



  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

      // Credit Transaction
      function credit_transaction($origid) {

        if (strlen($origid) < 3)
        {
            $this->set_errors('OrigID not valid');
            return;
        }

        // build hash
        $tempstr = $card_number . $amount . date('YmdGis') . "2";
        $request_id = md5($tempstr);

        // body
        $plist = 'USER=' . $this->user . '&';
        $plist .= 'VENDOR=' . $this->vendor . '&';
        $plist .= 'PARTNER=' . $this->partner . '&';
        $plist .= 'PWD=' . $this->password . '&';
        $plist .= 'TENDER=' . 'C' . '&'; // C = credit card, P = PayPal
        $plist .= 'TRXTYPE=' . 'C' . '&'; //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
        $plist .= "ORIGID=" . $origid . "&"; // ORIGID to the PNREF value returned from the original transaction
        $plist .= 'VERBOSITY=MEDIUM';

        $headers = $this->get_curl_headers();
        $headers[] = "X-VPS-Request-ID: " . $request_id;

        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->submiturl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, $plist); //adding POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

        $result = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        $pfpro = $this->get_curl_result($result); //result arrray

        if (isset($pfpro['RESULT']) && $pfpro['RESULT'] == 0)
        {
            return $pfpro;
        }
        else
        {
            $this->set_errors($pfpro['RESPMSG'] . ' ['. $pfpro['RESULT'] . ']');
            return false;
        }
    }



  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

    // Void Transaction
    function void_transaction($origid)
    {
        if (strlen($origid) < 3)
        {
            $this->set_errors('OrigID not valid');
            return;
        }

        // build hash
        $tempstr = $card_number . $amount . date('YmdGis') . "2";
        $request_id = md5($tempstr);

        // body
        $plist = 'USER=' . $this->user . '&';
        $plist .= 'VENDOR=' . $this->vendor . '&';
        $plist .= 'PARTNER=' . $this->partner . '&';
        $plist .= 'PWD=' . $this->password . '&';
        $plist .= 'TENDER=' . 'C' . '&'; // C = credit card, P = PayPal
        $plist .= 'TRXTYPE=' . 'V' . '&'; //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
        $plist .= "ORIGID=" . $origid . "&"; // ORIGID to the PNREF value returned from the original transaction
        $plist .= 'VERBOSITY=MEDIUM';

        $headers = $this->get_curl_headers();
        $headers[] = "X-VPS-Request-ID: " . $request_id;

        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->submiturl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, $plist); //adding POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

        $result = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        // pass curl result to method
        $pfpro = $this->get_curl_result($result); //result arrray

        if (isset($pfpro['RESULT']) && $pfpro['RESULT'] == 0)
        {
            return $pfpro;
        }
        else
        {
            $this->set_errors($pfpro['RESPMSG'] . ' ['. $pfpro['RESULT'] . ']');
            return false;
        }
    }



  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

    // Curl custom headers; adjust appropriately for your setup:
    function get_curl_headers()
    {
        $headers = [];

        $headers[] = "Content-Type: text/namevalue"; //or maybe text/xml
        $headers[] = "X-VPS-Timeout: 45";
        $headers[] = "X-VPS-VIT-OS-Name: Linux";  // Name of your OS
        $headers[] = "X-VPS-VIT-OS-Version: RHEL 4";  // OS Version
        $headers[] = "X-VPS-VIT-Client-Type: PHP/cURL";  // What you are using
        $headers[] = "X-VPS-VIT-Client-Version: 0.01";  // For your info
        $headers[] = "X-VPS-VIT-Client-Architecture: x86";  // For your info
        $headers[] = "X-VPS-VIT-Integration-Product: MyApplication";  // For your info, would populate with application name
        $headers[] = "X-VPS-VIT-Integration-Version: 0.01"; // Application version

        return $headers;
    }


  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

    // parse result and return an array
    function get_curl_result($result)
    {
        if (empty($result))
        return;

        // parse_str($result, $response);
        // return $response;

        // initialize array
        $pfpro = [];

        // resource: http://php.net/manual/en/function.strstr.php
        // strstr() - finds 1st occurrence of a string (needle) in a larger string (haystack)
        // and returns string starting from and including the first occurrence of "needle"
        // to the end of the "haystack"
        $result = strstr($result, 'RESULT');

        // testing
        // explode array
        // $valArray = explode('&', $result);
        //
        // loop through array
        // foreach($valArray as $val) {
        //    $valArray2 = explode('=', $val);
        //    $pfpro[$valArray2[0]] = $valArray2[1];
        // }

        return $result;
    }


  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

    function validate_card_expire($mmyy)
    {
        if (!is_numeric($mmyy) || strlen($mmyy) != 4)
        {
            return false;
        }
        $mm = substr($mmyy, 0, 2);
        $yy = substr($mmyy, 2, 2);
        if ($mm < 1 || $mm > 12)
        {
            return false;
        }
        $year = date('Y');
        $yy = substr($year, 0, 2) . $yy; // eg 2007
        if (is_numeric($yy) && $yy >= $year && $yy <= ($year + 10))
        {
        }
        else
        {
            return false;
        }
        if ($yy == $year && $mm < date('n'))
        {
          return false;
        }
        return true;
    }


  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

    // luhn algorithm (https://www.rosettacode.org/wiki/Luhn_test_of_credit_card_numbers)
    function validate_card_number($card_number)
    {
        // $card_number = ereg_replace('[^0-9]', '', $card_number);
        // ereg_replace deprecated in PHP 5.3.0, and removed in 7.0.0
        // remove spaces and letters
        $card_number = preg_replace('/[^0-9]/', '', $card_number);
        if ($card_number < 9)
        {
            return false;
        }
        // reverse string with strrev()
        $card_number = strrev($card_number);
        $total = 0;
        for ($i = 0; $i < strlen($card_number); $i++) {
          $current_number = substr($card_number, $i, 1);
          if ($i % 2 == 1)
          {
              $current_number *= 2;
          }
          if ($current_number > 9)
          {
              $first_number = $current_number % 10;
              $second_number = ($current_number - $first_number) / 10;
              $current_number = $first_number + $second_number;
          }
          $total += $current_number;
        }
        return ($total % 10 == 0);
    }


  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

    function get_errors()
    {
        if ($this->errors != '') {
          return $this->errors;
        }
        return false;
    }


  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

    function set_errors($string)
    {
        $this->errors = $string;
    }


  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

    function get_version()
    {
        return '4.03';
    }
}
