<?php

namespace App\Models;

use PDO;
use \App\Config;


/**
 * Ordoro model
 */
class Ordoro extends \Core\Model
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
    private $errors   = '';
    private $currencies_allowed = ['USD'];
    private $results = [];
    private $shipping_method;

    // set test mode for testing or LIVE
    private $paypal_test_mode = Config::PAYPALTESTMODE;


    /**
     * receives & validates PP credentials, sets submitURL, checks if curl_init() exists
     * @param  String   $vendor     [description]
     * @param  String   $user       [description]
     * @param  String   $partner    [description]
     * @param  String   $password   [description]
     * @return [type]               [description]
     */
    public static function callOrdoroApi()
    {
        // access Ordoro API
        $ch = curl_init();

        $orderId = '1-23216';

        curl_setopt($ch, CURLOPT_URL, "https://api.ordoro.com/order/$orderId/"); // must have trailing slash
        curl_setopt($ch, CURLOPT_USERPWD, "marylou@armalaser.com:Bluefish1"); // client's email:password
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        // get, post, put, delete
        // curl_setopt($ch, CURLOPT_POST, TRUE);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
