<?php

namespace App\Models;

use PDO;
use \Core\View;


class Customer extends \Core\Model
{
    /**
     * checks if email is in customers table
     *
     * @param  string   $email  The customer's email address
     *
     * @return string           The answer
     */
    public static function checkIfAvailable($email)
    {
        if($email == '' || strlen($email) < 3)
        {
          echo "Invalid email address";
          exit();
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT id FROM customers
                    WHERE email = :email
                    LIMIT 1";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':email' => $email
            ];
            $stmt->execute($parameters);
            $result = $stmt->fetch(PDO::FETCH_OBJ);

            $count = $stmt->rowCount();

            if ($count < 1)
            {
              return 'available';
            }
            else
            {
              return 'not available';
            }
        }
        catch (PDOException $e) {
            echo $e->getMessage();
            exit();
        }
    }




    public static function addNewCustomer()
    {
        // unset SESSION variable
        unset($_SESSION['registererror']);

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        // retrieve form data
        $email        = (isset($_REQUEST['customer_email'])) ? filter_var($_REQUEST['customer_email'], FILTER_SANITIZE_STRING) : '';
        $verify_email = (isset($_REQUEST['confirm_customer_email'])) ? filter_var($_REQUEST['confirm_customer_email'], FILTER_SANITIZE_STRING) : '';
        $first_name   = (isset($_REQUEST['customer_first_name'])) ? filter_var($_REQUEST['customer_first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name    = (isset($_REQUEST['customer_last_name'])) ? filter_var($_REQUEST['customer_last_name'], FILTER_SANITIZE_STRING) : '';
        $password     = (isset($_REQUEST['customer_password'])) ? filter_var($_REQUEST['customer_password'], FILTER_SANITIZE_STRING) : '';
        $verify_password = isset($_REQUEST['confirm_customer_password']) ? filter_var($_REQUEST['confirm_customer_password'], FILTER_SANITIZE_STRING) : '';
        $agree = isset($_REQUEST['customer_agree']) ? filter_var($_REQUEST['customer_agree'], FILTER_SANITIZE_STRING) : '';
        $user_ip = $_SERVER['REMOTE_ADDR'];

        // php validation (if Javascript fails or is turned off by user)
        if ($email == '' || $verify_email == '' || $first_name == '' || $last_name == ''
            || $password == '' || $verify_password == '' || $agree != 'on')
        {
            $_SESSION['registererror'] = '*All fields are required.';
            View::renderTemplate('Register/customer.html', [
                'pagetitle' => 'Customer Registration',
                'customer_email' => $email,
                'confirm_customer_email' => $verify_email,
                'customer_first_name' => $first_name,
                'customer_last_name' => $last_name
            ]);
            exit();
         }

        // check if emails match
        if($verify_email != $email)
        {
            $_SESSION['registererror'] = '*Emails do not match.';
            View::renderTemplate('Register/customer.html', [
                'pagetitle' => 'Customer Registration',
                'customer_email' => $email,
                'confirm_customer_email' => $verify_email,
                'customer_first_name' => $first_name,
                'customer_last_name' => $last_name
            ]);
            exit();
        }

        // check if passwords match
        if($verify_password != $password)
        {
            $_SESSION['registererror'] = '*Passwords do not match';
            View::renderTemplate('Register/customer.html', [
                'pagetitle' => 'Customer Registration',
                'customer_email' => $email,
                'confirm_customer_email' => $verify_email,
                'customer_first_name' => $first_name,
                'customer_last_name' => $last_name
            ]);
            exit();
        }

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        // hash password
        $pass = password_hash($password, PASSWORD_DEFAULT);

        // test
        // echo $first_name . '<br>';
        // echo $last_name . '<br>';
        // echo $email . '<br>';
        // echo $pass . '<br>';
        // exit();

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO customers SET
                    billing_firstname = :billing_firstname,
                    billing_lastname  = :billing_lastname,
                    email             = :email,
                    pass              = :pass,
                    ip                = :ip";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':billing_firstname' => $first_name,
                ':billing_lastname'  => $last_name,
                ':email'             => $email,
                ':pass'              => $pass,
                ':ip'                => $user_ip
            ];
            $result = $stmt->execute($parameters);

            // get new customer's ID
            $customer_id = $db->lastInsertId();

            // create token for validation email
            $token = md5(uniqid(rand(), true)) . md5(uniqid(rand(), true));

            // store result, new user ID & token in array to return to
            // Register controller
            $results = [
                'result' => $result,
                'id'     => $customer_id,
                'token'  => $token
            ];

            // test
            // echo '<pre>';
            // print_r($results);
            // echo '</pre>';
            // exit();

            return $results;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * returns customer record by ID from `customers`
     *
     * @param  Int   $id    The customer ID
     * @return Obj          The customer record
     */
    public static function getCustomer($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM customers
                    WHERE id = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $customer = $stmt->fetch(PDO::FETCH_OBJ);

            return $customer;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }





    public static function getCustomerByEmail($email)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM customers
                    WHERE email = :email";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':email' => $email
            ];
            $stmt->execute($parameters);

            $customer = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $customer;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * validates customer credentials
     *
     * @param  string $email     The customer's email
     * @param  string $password  The customer's password
     *
     * @return boolean
     */
    public static function validateLoginCredentials($email, $password)
    {
        // clear variable for new values
        unset($_SESSION['loginerror']);

        // set gate-keeper to true
        $okay = true;

        // check if fields have length
        if($email == "" || $password == "")
        {
            $_SESSION['loginerror'] = 'Please enter login email and password.';
            $okay = false;
            header("Location: /admin/customer/login");
            exit();
        }

        // validate email
        if(filter_var($email, FILTER_SANITIZE_EMAIL === false))
        {
            $_SESSION['loginerror'] = 'Please enter a valid email address';
            $okay = false;
            header("Location: /admin/customer/login");
            exit();
        }

        if($okay)
        {
            // check if email exists & retrieve password
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "SELECT * FROM customers
                        WHERE email = :email
                        AND active = 1";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':email' => $email
                ];
                $stmt->execute($parameters);
                $customer = $stmt->fetch(PDO::FETCH_OBJ);
            }
            catch (PDOException $e)
            {
                $_SESSION['loginerror'] = "Error checking credentials";
                header("Location: /admin/login/");
                exit();
            }
        }

        // check if email & active match found
        if(empty($customer))
        {
            $_SESSION['loginerror'] = "User not found.";
            header("Location: /admin/customer/login");
            exit();
        }

        // returning user verified
        if( (!empty($customer)) && (password_verify($password, $customer->pass)) )
        {
            // return $customer object to controller
            return $customer;
        }
        else
        {
            $_SESSION['loginerror'] = "Matching credentials not found.
            Please verify and try again.";
            header("Location: /admin/customer/login");
            exit();
        }
    }




    public static function storeSecurityAnswers($partnerid)
    {
        // retrieve post variables
        $security1 = strtolower( (isset($_REQUEST['security1']) ) ? filter_var($_REQUEST['security1'], FILTER_SANITIZE_STRING) : '');
        $security2 = strtolower( (isset($_REQUEST['security2'])) ? filter_var($_REQUEST['security2'], FILTER_SANITIZE_STRING) : '');
        $security3 = (isset($_REQUEST['security3'])) ? filter_var($_REQUEST['security3'], FILTER_SANITIZE_STRING) : '';

        // test
        // var_dump($_REQUEST); exit();
        // echo $security1 . '<br>';
        // echo $security2 . '<br>';
        // echo $security3 . '<br>';
        // exit();

        // backup validation
        if ($security1 == '' || $security2 == '' || $security3 == '')
        {
            echo "All fields required.";
            exit();
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE customers SET
                    security1 = :security1,
                    security2 = :security2,
                    security3 = :security3
                    WHERE id  = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':security1' => $security1,
                ':security2' => $security2,
                ':security3' => $security3,
                ':id'        => $partnerid
            ];
            $result = $stmt->execute($parameters);

            // return boolean to Register Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function updateFirstLoginStatus($id)
    {
        try
        {
            $db = static::getDB();

            $sql = "UPDATE customers SET
                    first_login = 0
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function doesCustomerExist($email)
    {
        // echo "Connected to doesCustomerExist()"; exit();

        // Server side validation (HTML5 validation 'required' on input tag)
        if($email === '' || strlen($email) < 6){
            echo 'Please provide a valid email address';
            exit();
        }

        // check if email is in `customers` table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM customers
                    WHERE email = :email";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':email' => $email
            ];
            $stmt->execute($parameters);
            $customer = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $customer;
        }
        catch (PDOException $e) {
            echo $e->getMessage();
            exit();
        }
    }




    public static function insertTempPassword($id, $tmp_pass)
    {
        try
        {
            $db = static::getDB();

            $sql = "UPDATE customers SET
                    tmp_pass = :tmp_pass
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':tmp_pass' => $tmp_pass,
                ':id'       => $id
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function matchCustomer($email, $tmp_pass)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM customers
                    WHERE email = :email
                    AND tmp_pass = :tmp_pass";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':email'    => $email,
                ':tmp_pass' => $tmp_pass
            ];
            $stmt->execute($parameters);
            $customer = $stmt->fetch(PDO::FETCH_OBJ);

            return $customer;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function checkSecurityAnswers($id)
    {
        // retrieve form data and trim any leading or trailing whitespace
        $security1 = trim(( isset($_REQUEST['security1']) ) ? strtolower(filter_var($_REQUEST['security1'], FILTER_SANITIZE_STRING)) : '');
        $security2 = trim(( isset($_REQUEST['security2']) ) ? strtolower(filter_var($_REQUEST['security2'], FILTER_SANITIZE_STRING)) : '');
        $security3 = trim(( isset($_REQUEST['security3']) ) ? strtolower(filter_var($_REQUEST['security3'], FILTER_SANITIZE_STRING)) : '');

        // test
        // echo $id .'<br>';
        // echo $security1 .'<br>';
        // echo $security2 .'<br>';
        // echo $security3 .'<br>';
        // exit();

        // check for values
        if($security1 == '' || $security2 == '' || $security3 == '')
        {
            echo "All fields required.";
            exit();
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM customers
                    WHERE id = :id
                    AND security1 = :security1
                    AND security2 = :security2
                    AND security3 = :security3";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'        => $id,
                ':security1' => $security1,
                ':security2' => $security2,
                ':security3' => $security3
            ];
            $stmt->execute($parameters);

            $customer = $stmt->fetch(PDO::FETCH_OBJ);

            // test
            // echo '<pre>';
            // print_r($customer);
            // echo '</pre>';
            // exit();

            return $customer;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function resetPassword($id, $newpassword)
    {
        // hash password
        $hashed_password = password_hash($newpassword, PASSWORD_DEFAULT);

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE customers SET
                    pass = :pass
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'   => $id,
                ':pass' => $hashed_password
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function deleteTempPassword($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE customers SET
                    tmp_pass = null
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch(PDOException $e)
        {
            $e->getMessage();
            exit();
        }
    }



    /**
     * updates registered user's billing info in `customers` table
     *
     * @param  Int      $id             The customer ID
     * @param  Array    $billing_data   Data from form
     * @return Boolean                  Success or failure
     */
    public static function updateBillingShippingInfo($id, $billing_data, $shipping_data)
    {
        // echo "Connected!";

        // test
        // echo '<pre>';
        // print_r($billing_data);
        // echo '</pre>';
        // exit();

        try
        {
            $db = static::getDB();

            $sql = "UPDATE customers SET
                    billing_firstname     = :billing_firstname,
                    billing_lastname      = :billing_lastname,
                    billing_company       = :billing_company,
                    billing_phone         = :billing_phone,
                    billing_address       = :billing_address,
                    billing_address2      = :billing_address2,
                    billing_city          = :billing_city,
                    billing_state         = :billing_state,
                    billing_zip           = :billing_zip,
                    shipping_firstname    = :shipping_firstname,
                    shipping_lastname     = :shipping_lastname,
                    shipping_company      = :shipping_company,
                    shipping_phone        = :shipping_phone,
                    shipping_address      = :shipping_address,
                    shipping_address2     = :shipping_address2,
                    shipping_city         = :shipping_city,
                    shipping_state        = :shipping_state,
                    shipping_zip          = :shipping_zip,
                    shipping_instructions = :shipping_instructions
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'                    => $id,
                ':billing_firstname'     => $billing_data['billing_firstname'],
                ':billing_lastname'      => $billing_data['billing_lastname'],
                ':billing_company'       => $billing_data['billing_company'],
                ':billing_phone'         => $billing_data['billing_phone'],
                ':billing_address'       => $billing_data['billing_address'],
                ':billing_address2'      => $billing_data['billing_address2'],
                ':billing_city'          => $billing_data['billing_city'],
                ':billing_state'         => $billing_data['billing_state'],
                ':billing_zip'           => $billing_data['billing_zip'],
                ':shipping_firstname'    => $shipping_data['shipping_firstname'],
                ':shipping_lastname'     => $shipping_data['shipping_lastname'],
                ':shipping_company'      => $shipping_data['shipping_company'],
                ':shipping_phone'        => $shipping_data['shipping_phone'],
                ':shipping_address'      => $shipping_data['shipping_address'],
                ':shipping_address2'     => $shipping_data['shipping_address2'],
                ':shipping_city'         => $shipping_data['shipping_city'],
                ':shipping_state'        => $shipping_data['shipping_state'],
                ':shipping_zip'          => $shipping_data['shipping_zip'],
                ':shipping_instructions' => $shipping_data['shipping_instructions']
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * updates guest's billing info in `customers` table
     *
     * @param  Int      $email          The guest's email
     * @param  Array    $billing_data   Data from form
     * @return Boolean                  Success or failure
     */
    public static function updateBillingShippingInfoForGuest($id, $email, $billing_data, $shipping_data)
    {
        // echo "Connected!";

        // test
        // echo $email;
        // echo '<pre>';
        // print_r($billing_data);
        // echo '</pre>';
        // echo '<pre>';
        // print_r($shipping_data);
        // echo '</pre>';
        // exit();

        try
        {
            $db = static::getDB();

            $sql = "UPDATE customers SET
                    billing_firstname     = :billing_firstname,
                    billing_lastname      = :billing_lastname,
                    billing_company       = :billing_company,
                    billing_phone         = :billing_phone,
                    billing_address       = :billing_address,
                    billing_address2      = :billing_address2,
                    billing_city          = :billing_city,
                    billing_state         = :billing_state,
                    billing_zip           = :billing_zip,
                    shipping_firstname    = :shipping_firstname,
                    shipping_lastname     = :shipping_lastname,
                    shipping_company      = :shipping_company,
                    shipping_phone        = :shipping_phone,
                    shipping_address      = :shipping_address,
                    shipping_address2     = :shipping_address2,
                    shipping_city         = :shipping_city,
                    shipping_zip          = :shipping_zip,
                    shipping_state        = :shipping_state,
                    shipping_instructions = :shipping_instructions
                    WHERE id = :id
                    AND email = :email";
            $stmt = $db->prepare($sql);
            $parameters = [
                'id'                     => $id,
                ':email'                 => $email,
                ':billing_firstname'     => $billing_data['billing_firstname'],
                ':billing_lastname'      => $billing_data['billing_lastname'],
                ':billing_company'       => $billing_data['billing_company'],
                ':billing_phone'         => $billing_data['billing_phone'],
                ':billing_address'       => $billing_data['billing_address'],
                ':billing_address2'      => $billing_data['billing_address2'],
                ':billing_city'          => $billing_data['billing_city'],
                ':billing_state'         => $billing_data['billing_state'],
                ':billing_zip'           => $billing_data['billing_zip'],
                ':shipping_firstname'    => $shipping_data['shipping_firstname'],
                ':shipping_lastname'     => $shipping_data['shipping_lastname'],
                ':shipping_company'      => $shipping_data['shipping_company'],
                ':shipping_phone'        => $shipping_data['shipping_phone'],
                ':shipping_address'      => $shipping_data['shipping_address'],
                ':shipping_address2'     => $shipping_data['shipping_address2'],
                ':shipping_city'         => $shipping_data['shipping_city'],
                ':shipping_state'        => $shipping_data['shipping_state'],
                ':shipping_zip'          => $shipping_data['shipping_zip'],
                ':shipping_instructions' => $shipping_data['shipping_instructions']
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }

    }




    /**
     * retrieves all customers from `customers`
     *
     * @return Object   The customers
     */
    public static function getCustomers()
    {
        try
        {
            $db = static::getDB();

            $sql = "SELECT * FROM customers
                    ORDER BY created_at DESC
                    LIMIT 20";
            $stmt = $db->query($sql);
            $stmt->execute();
            $customers = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $customers;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getCount()
    {
        try
        {
            $db = static::getDB();

            $sql = "SELECT COUNT(*)
                    FROM customers";
            $stmt = $db->query($sql);
            $stmt->execute();

            $count = $stmt->fetchColumn();

            return $count;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }

    }




    public static function getByLastName($last_name)
    {
        try
        {
            $db = static::getDB();

            $sql = "SELECT *
                    FROM customers
                    WHERE billing_lastname
                    LIKE '$last_name%'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $customers = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $customers;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function updateCustomer($id)
    {
        // retrieve form data
        $first_name = ( isset($_REQUEST['customer_firstname']) ) ? filter_var($_REQUEST['customer_firstname'], FILTER_SANITIZE_STRING) : '';
        $last_name = ( isset($_REQUEST['customer_lastname']) ) ? filter_var($_REQUEST['customer_lastname'], FILTER_SANITIZE_STRING) : '';
        $address = ( isset($_REQUEST['customer_address']) ) ? filter_var($_REQUEST['customer_address'], FILTER_SANITIZE_STRING) : '';
        $address2 = ( isset($_REQUEST['customer_address2']) ) ? filter_var($_REQUEST['customer_address2'], FILTER_SANITIZE_STRING) : '';
        $city = ( isset($_REQUEST['customer_city']) ) ? filter_var($_REQUEST['customer_city'], FILTER_SANITIZE_STRING) : '';
        $state = ( isset($_REQUEST['customer_state']) ) ? filter_var($_REQUEST['customer_state'], FILTER_SANITIZE_STRING) : '';
        $zip = ( isset($_REQUEST['customer_zip']) ) ? filter_var($_REQUEST['customer_zip'], FILTER_SANITIZE_STRING) : '';
        $company = ( isset($_REQUEST['customer_company']) ) ? filter_var($_REQUEST['customer_company'], FILTER_SANITIZE_STRING) : '';
        $phone = ( isset($_REQUEST['customer_phone']) ) ? filter_var($_REQUEST['customer_phone'], FILTER_SANITIZE_STRING) : '';
        $email = ( isset($_REQUEST['customer_email']) ) ? filter_var($_REQUEST['customer_email'], FILTER_SANITIZE_STRING) : '';
        $security1 = ( isset($_REQUEST['customer_security1']) ) ? filter_var($_REQUEST['customer_security1'], FILTER_SANITIZE_STRING) : '';
        $security2 = ( isset($_REQUEST['customer_security2']) ) ? filter_var($_REQUEST['customer_security2'], FILTER_SANITIZE_STRING) : '';
        $security3 = ( isset($_REQUEST['customer_security3']) ) ? filter_var($_REQUEST['customer_security3'], FILTER_SANITIZE_STRING) : '';
        $active = ( isset($_REQUEST['customer_active']) ) ? filter_var($_REQUEST['customer_active'], FILTER_SANITIZE_STRING) : '';

        try
        {
            $db = static::getDB();

            $sql = "UPDATE customers SET
                    billing_firstname = :first_name,
                    billing_lastname  = :last_name,
                    billing_address    = :address,
                    billing_address2   = :address2,
                    billing_city       = :city,
                    billing_state      = :state,
                    billing_zip        = :zip,
                    billing_company    = :company,
                    billing_phone      = :telephone,
                    email              = :email,
                    security1          = :security1,
                    security2          = :security2,
                    security3          = :security3,
                    active             = :active
                    WHERE id           = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'         => $id,
                ':first_name' => $first_name,
                ':last_name'  => $last_name,
                ':address'    => $address,
                ':address2'   => $address2,
                ':city'       => $city,
                ':state'      => $state,
                ':zip'        => $zip,
                ':company'    => $company,
                ':telephone'  => $phone,
                ':email'      => $email,
                ':security1'  => $security1,
                ':security2'  => $security2,
                ':security3'  => $security3,
                ':active'     => $active
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * Updates customer data in `customers` - changes by Customer in back-end
     *
     * @param  String  $id      The customer ID
     * @return Boolean
     */
    public static function updateCustomerAccount($id)
    {
        // retrieve form data
        $email = (isset($_REQUEST['customer_email'])) ? filter_var($_REQUEST['customer_email'], FILTER_SANITIZE_EMAIL) : '';
        $first_name = (isset($_REQUEST['customer_first_name'])) ? filter_var($_REQUEST['customer_first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = (isset($_REQUEST['customer_last_name'])) ? filter_var($_REQUEST['customer_last_name'], FILTER_SANITIZE_STRING) : '';
        $company = (isset($_REQUEST['customer_company'])) ? filter_var($_REQUEST['customer_company'], FILTER_SANITIZE_STRING) : '';
        $telephone = (isset($_REQUEST['customer_phone'])) ? filter_var($_REQUEST['customer_phone'], FILTER_SANITIZE_STRING) : '';
        $address = (isset($_REQUEST['customer_address'])) ? filter_var($_REQUEST['customer_address'], FILTER_SANITIZE_STRING) : '';
        $city = (isset($_REQUEST['customer_city'])) ? filter_var($_REQUEST['customer_city'], FILTER_SANITIZE_STRING) : '';
        $state = (isset($_REQUEST['customer_state'])) ? filter_var($_REQUEST['customer_state'], FILTER_SANITIZE_STRING) : '';
        $zip = (isset($_REQUEST['customer_zip'])) ? filter_var($_REQUEST['customer_zip'], FILTER_SANITIZE_STRING) : '';
        $security1 = strtolower( (isset($_REQUEST['security1'])) ? filter_var($_REQUEST['security1'], FILTER_SANITIZE_STRING) : '');
        $security2 = strtolower( (isset($_REQUEST['security2'])) ? filter_var($_REQUEST['security2'], FILTER_SANITIZE_STRING) : '');
        $security3 = (isset($_REQUEST['security3'])) ? filter_var($_REQUEST['security3'], FILTER_SANITIZE_STRING) : '';
        $email_optin = (isset($_REQUEST['email_optin'])) ? filter_var($_REQUEST['email_optin'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE customers SET
                    billing_firstname = :first_name,
                    billing_lastname  = :last_name,
                    billing_company   = :company,
                    billing_phone     = :telephone,
                    billing_address   = :address,
                    billing_city      = :city,
                    billing_state     = :state,
                    billing_zip       = :zip,
                    email             = :email,
                    security1         = :security1,
                    security2         = :security2,
                    security3         = :security3,
                    email_optin       = :email_optin
                    WHERE id          = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'          => $id,
                ':first_name'  => $first_name,
                ':last_name'   => $last_name,
                ':company'     => $company,
                ':telephone'   => $telephone,
                ':address'     => $address,
                ':city'        => $city,
                ':state'       => $state,
                ':zip'         => $zip,
                ':email'       => $email,
                ':security1'   => $security1,
                ':security2'   => $security2,
                ':security3'   => $security3,
                ':email_optin' => $email_optin
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * Updates customer password in `customers` by Customer in back-end
     *
     * @param  String   $id         The customer ID
     * @param  String   $password   The new password hashed
     * @return Boolean
     */
    public static function updateAccountPassword($id, $password)
    {
        try
        {
            $db = static::getDB();

            $sql = "UPDATE customers SET
                    pass = :pass
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
               ':pass' => $password,
               ':id'   => $id
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }
}