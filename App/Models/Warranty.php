<?php

namespace App\Models;

use PDO;


class Warranty extends \Core\Model
{

    /**
     * Insert warranty registration form data into DB armalaser.warranty_registrations
     * @param  Array  $data  The form data
     * @return Boolean       Success or failure
     */
    public static function insertData($lasers, $serial_numbers, $laserids)
    {
        // echo "Connected to insertData() in Warranty model.<br>";
        // echo '<pre>';
        // print_r($_REQUEST);
        // exit();

        // declare array
        $laser_serial = [];

        // combine lasers and serial numbers in new array
        foreach ($lasers as $key => $laser)
        {
            $laser_serial[$key]['serial'] = $serial_numbers[$key];
            $laser_serial[$key]['laser'] = $laser;
            $laser_serial[$key]['laserid'] = $laserids[$key];
        }

        // test
        // echo '<pre>';
        // print_r($laser_serial);
        // echo '</pre>';
        // exit();

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO warranty SET
                    orderid       = :orderid,
                    laserid       = :laserid,
                    laser         = :laser,
                    serial_number = :serial_number,
                    seller        = :seller";
            $stmt = $db->prepare($sql);
            foreach ($laser_serial as $value)
            {
                $stmt->bindParam(':orderid', $_REQUEST['order_id']);
                $stmt->bindParam(':laser', $value['laser']);
                $stmt->bindParam(':laserid', $value['laserid']);
                $stmt->bindParam(':serial_number', $value['serial']);
                $stmt->bindParam(':seller', $_REQUEST['seller']);
                $result = $stmt->execute();
            }

            // return to Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * processes registered user form data
     *
     * @return Array  The form data
     */
    public static function processWarrantyRegistrationData()
    {
        // retrieve form data
        $first_name = (isset($_REQUEST['first_name'])) ? filter_var($_REQUEST['first_name'], FILTER_SANITIZE_STRING): '';
        $last_name =  (isset($_REQUEST['last_name'])) ? filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING): '';
        $address   =  (isset($_REQUEST['address'])) ? filter_var($_REQUEST['address'], FILTER_SANITIZE_STRING): '';
        $city      =  (isset($_REQUEST['city'])) ? filter_var($_REQUEST['city'], FILTER_SANITIZE_STRING): '';
        $state     =  (isset($_REQUEST['state'])) ? filter_var($_REQUEST['state'], FILTER_SANITIZE_STRING): '';
        $zipcode   =  (isset($_REQUEST['zipcode'])) ? filter_var($_REQUEST['zipcode'], FILTER_SANITIZE_STRING): '';
        $seller    =  (isset($_REQUEST['seller'])) ? filter_var($_REQUEST['seller'], FILTER_SANITIZE_STRING): '';
        $dealer    = (isset($_REQUEST['dealer'])) ? filter_var($_REQUEST['dealer'], FILTER_SANITIZE_STRING): '';
        $gunshow       = (isset($_REQUEST['gunshow'])) ? filter_var($_REQUEST['gunshow'], FILTER_SANITIZE_STRING): '';
        $purchase_date = (isset($_REQUEST['purchase_date'])) ? filter_var($_REQUEST['purchase_date'], FILTER_SANITIZE_STRING): '';
        $laser         = (isset($_REQUEST['laser'])) ? filter_var($_REQUEST['laser'], FILTER_SANITIZE_STRING): '';
        $serial        = (isset($_REQUEST['serial'])) ? filter_var($_REQUEST['serial'], FILTER_SANITIZE_STRING): '';
        $email         = (isset($_REQUEST['email'])) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL): '';

        // validate form data
        if($first_name == '' || $last_name == '' || $address == '' || $city == '' || $state == ''
          || $zipcode == '' || $seller == '' || $purchase_date == '' || $laser == ''
          || $serial == '' || $email == '')
        {
            echo "Error. All fields required.";
            exit();
        }

        if ($seller == '')

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        $data = [
          'first_name'    => $first_name,
          'last_name'     => $last_name,
          'address'       => $address,
          'city'          => $city,
          'state'         => $state,
          'zipcode'       => $zipcode,
          'seller'        => $seller,
          'dealer_name'   => $dealer_name,
          'purchase_date' => $purchase_date,
          'model'         => $model,
          'serial'        => $serial,
          'email'         => $email
        ];

        // test
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // exit();

        // return to Warranty Controller
        return $data;
    }



    /**
     * Insert warranty registration form data into DB armalaser.warranty_registrations
     * @param  Array  $data  The form data
     * @return Boolean       Success or failure
     */
    public static function storeWarrantyRegistration($data)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO warranty_registrations SET
                    first_name    = :first_name,
                    last_name     = :last_name,
                    address       = :address,
                    city          = :city,
                    state         = :state,
                    zipcode       = :zipcode,
                    seller        = :seller,
                    dealer_name   = :dealer_name,
                    purchase_date = :purchase_date,
                    model         = :model,
                    serial        = :serial,
                    email         = :email";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':first_name'    => $data['first_name'],
                ':last_name'     => $data['last_name'],
                ':address'       => $data['address'],
                ':city'          => $data['city'],
                ':state'         => $data['state'],
                ':zipcode'       => $data['zipcode'],
                ':seller'        => $data['seller'],
                ':dealer_name'   => $data['dealer_name'],
                ':purchase_date' => $data['purchase_date'],
                ':model'         => $data['model'],
                ':serial'        => $data['serial'],
                ':email'         => $data['email']
            ];
            $result = $stmt->execute($parameters);

            // return to Warranty Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




}
