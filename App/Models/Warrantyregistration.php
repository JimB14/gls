<?php

namespace App\Models;

use PDO;


class Warrantyregistration extends \Core\Model
{

    /**
     * From back-end: inserts warranty registration data for one or more lasers
     *
     * @param  Array  $data  The form data
     * @return Boolean       Success or failure
     */
    public static function insertData($data, $lasers, $serial_numbers, $laserids)
    {
        // echo "Connected to insertData() in Warrantyregistration model.<br>";
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
                    type          = :type,
                    customerid    = :customerid,
                    first_name    = :first_name,
                    last_name     = :last_name,
                    address       = :address,
                    city          = :city,
                    state         = :state,
                    zipcode       = :zipcode,
                    laserid       = :laserid,
                    serial_number = :serial_number,
                    seller        = :seller,
                    purchase_date = :purchase_date,
                    email         = :email";
            $stmt = $db->prepare($sql);
            foreach ($laser_serial as $value)
            {
                $stmt->bindParam(':orderid', $_REQUEST['order_id']);
                $stmt->bindParam(':type', $data['type']);
                $stmt->bindParam(':customerid', $data['customerid']);
                $stmt->bindParam(':first_name', $data['first_name']);
                $stmt->bindParam(':last_name', $data['last_name']);
                $stmt->bindParam(':address', $data['address']);
                $stmt->bindParam(':city', $data['city']);
                $stmt->bindParam(':state', $data['state']);
                $stmt->bindParam(':zipcode', $data['zipcode']);
                $stmt->bindParam(':laserid', $value['laserid']);
                $stmt->bindParam(':serial_number', $value['serial']);
                $stmt->bindParam(':seller', $_REQUEST['seller']);
                $stmt->bindParam(':purchase_date', $data['purchase_date']);
                $stmt->bindParam(':email', $data['email']);
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
     * Processes and validates form data
     *
     * @return Array    The validated data
     */
    public static function processWarrantyRegistrationData()
    {
        // retrieve form data
        $first_name    = (isset($_REQUEST['first_name'])) ? filter_var($_REQUEST['first_name'], FILTER_SANITIZE_STRING): '';
        $last_name     = (isset($_REQUEST['last_name'])) ? filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING): '';
        $address       = (isset($_REQUEST['address'])) ? filter_var($_REQUEST['address'], FILTER_SANITIZE_STRING): '';
        $city          = (isset($_REQUEST['city'])) ? filter_var($_REQUEST['city'], FILTER_SANITIZE_STRING): '';
        $state         = (isset($_REQUEST['state'])) ? filter_var($_REQUEST['state'], FILTER_SANITIZE_STRING): '';
        $zipcode       = (isset($_REQUEST['zipcode'])) ? filter_var($_REQUEST['zipcode'], FILTER_SANITIZE_STRING): '';
        $seller        = (isset($_REQUEST['seller'])) ? filter_var($_REQUEST['seller'], FILTER_SANITIZE_STRING): '';
        $dealerid      = (isset($_REQUEST['dealer'])) ? filter_var($_REQUEST['dealer'], FILTER_SANITIZE_NUMBER_INT): '';
        $gunshowid     = (isset($_REQUEST['gunshow'])) ? filter_var($_REQUEST['gunshow'], FILTER_SANITIZE_NUMBER_INT): '';
        $purchase_date = (isset($_REQUEST['purchase_date'])) ? filter_var($_REQUEST['purchase_date'], FILTER_SANITIZE_STRING): '';
        $laser_series  = (isset($_REQUEST['laser_series'])) ? filter_var($_REQUEST['laser_series'], FILTER_SANITIZE_STRING): '';
        $laserid       = (isset($_REQUEST['laser'])) ? filter_var($_REQUEST['laser'], FILTER_SANITIZE_NUMBER_INT): '';
        $serial        = (isset($_REQUEST['serial'])) ? filter_var($_REQUEST['serial'], FILTER_SANITIZE_STRING): '';
        $email         = (isset($_REQUEST['email'])) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL): '';
        $userid        = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_NUMBER_INT): ''; // hidden input
        // $userid = `customers`.`id`

        // validate form data
        if($first_name == '' || $last_name == '' || $address == '' || $city == ''
            || $state == '' || $zipcode == '' || $seller == '' || $purchase_date == ''
            || $laser_series == '' || $laserid == ''
            || $serial == '' || $email == '')
        {
            echo "Error. All fields required.";
            exit();
        }

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
          'dealerid'      => $dealerid,
          'gunshowid'     => $gunshowid,
          'purchase_date' => $purchase_date,
          'laser_series'  => $laser_series,
          'laserid'       => $laserid,
          'serial_number' => $serial,
          'email'         => $email,
          'userid'        => $userid  // == `customers`.`id`
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
     *
     * @param  Array  $data  The form data
     * @return Boolean       Success or failure
     */
    public static function storeWarrantyRegistration($data)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO warranty SET
                    first_name    = :first_name,
                    last_name     = :last_name,
                    address       = :address,
                    city          = :city,
                    state         = :state,
                    zipcode       = :zipcode,
                    seller        = :seller,
                    dealerid      = :dealerid,
                    gunshowid     = :gunshowid,
                    purchase_date = :purchase_date,
                    laser_series  = :laser_series,
                    laserid       = :laserid,
                    serial_number = :serial_number,
                    email         = :email,
                    customerid    = :customerid";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':first_name'    => $data['first_name'],
                ':last_name'     => $data['last_name'],
                ':address'       => $data['address'],
                ':city'          => $data['city'],
                ':state'         => $data['state'],
                ':zipcode'       => $data['zipcode'],
                ':seller'        => $data['seller'],
                ':dealerid'      => $data['dealerid'],
                ':gunshowid'     => $data['gunshowid'],
                ':purchase_date' => $data['purchase_date'],
                ':laser_series'  => $data['laser_series'],
                ':laserid'       => $data['laserid'],
                ':serial_number' => $data['serial_number'],
                ':email'         => $data['email'],
                ':customerid'    => $data['userid']
            ];
            $result = $stmt->execute($parameters);

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
     * gets all registrations from armalaser.warranty_registrations
     *
     * @return Object  The registration records
     */
    public static function getRegistrations()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM warranty_registrations
                     ORDER BY created_at DESC
                     LIMIT 20";
            $stmt  = $db->query($sql);
            $stmt->execute();

            $registrations = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Warrantyregistrations controller
            return $registrations;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function getRegistrationsByLastName($last_name)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM warranty_registrations
                    WHERE last_name LIKE '%$last_name%'";
            $stmt = $db->query($sql);
            $stmt->execute();
            $registrations = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Warrantyregistrations Controller
            return $registrations;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * gets registration record by ID
     *
     * @return Object  The registration record
     */
    public static function getRegistration($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM warranty_registrations
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $registration = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Warrantyregistrations controller
            return $registration;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function updateRegistration($id)
    {
        // retrieve form data
        $first_name = ( isset($_REQUEST['first_name']) ) ? filter_var($_REQUEST['first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = ( isset($_REQUEST['last_name']) ) ? filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING) : '';
        $address = ( isset($_REQUEST['address']) ) ? filter_var($_REQUEST['address'], FILTER_SANITIZE_STRING) : '';
        $city = ( isset($_REQUEST['city']) ) ? filter_var($_REQUEST['city'], FILTER_SANITIZE_STRING) : '';
        $state = ( isset($_REQUEST['state']) ) ? filter_var($_REQUEST['state'], FILTER_SANITIZE_STRING) : '';
        $zipcode = ( isset($_REQUEST['zipcode']) ) ? filter_var($_REQUEST['zipcode'], FILTER_SANITIZE_STRING) : '';
        $email = ( isset($_REQUEST['email']) ) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_STRING) : '';
        $seller = ( isset($_REQUEST['dealer']) ) ? filter_var($_REQUEST['dealer'], FILTER_SANITIZE_STRING) : '';
        $dealer_name = ( isset($_REQUEST['dealer_name']) ) ? filter_var($_REQUEST['dealer_name'], FILTER_SANITIZE_STRING) : '';
        $purchase_date = ( isset($_REQUEST['purchase_date']) ) ? filter_var($_REQUEST['purchase_date'], FILTER_SANITIZE_STRING) : '';
        $model = ( isset($_REQUEST['model']) ) ? filter_var($_REQUEST['model'], FILTER_SANITIZE_STRING) : '';
        $serial = ( isset($_REQUEST['serial']) ) ? filter_var($_REQUEST['serial'], FILTER_SANITIZE_STRING) : '';

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE warranty_registrations SET
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
                ':first_name'    => $first_name,
                ':last_name'     => $last_name,
                ':address'       => $address,
                ':city'          => $city,
                ':state'         => $state,
                ':zipcode'       => $zipcode,
                ':seller'        => $seller,
                ':dealer_name'   => $dealer_name,
                ':purchase_date' => $purchase_date,
                ':model'         => $model,
                ':serial'        => $serial,
                ':email'         => $email
            ];
            $result = $stmt->execute($parameters);

            // return result to Admin/Warrantyregistrations Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * delete Registration record by ID
     * @param  Int  $id   The record ID
     * @return boolean
     */
    public static function deleteRegistration($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "DELETE FROM warranty_registrations
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $result = $stmt->execute($parameters);

            // return to Admin/Warrantyregistrations.php Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


}
