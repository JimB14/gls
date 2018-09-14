<?php

namespace App\Models;

use PDO;
use \App\Config;


class Dealer extends \Core\Model
{

    // = = =  Dealer Registration functionality   = = = = = //

    /**
     * checks if email is in dealers table
     *
     * @param  string   $email  The dealer's email address
     *
     * @return string           The answer
     */
    public static function checkIfAvailable($email)
    {
        if($email == '' || strlen($email) < 3 || strpos($email, '@') == false)
        {
          echo "Invalid email address";
          exit();
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT id FROM dealers
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




    public static function addNewDealer()
    {
        // retrieve form data
        $email = (isset($_REQUEST['dealer_email'])) ? filter_var($_REQUEST['dealer_email'], FILTER_SANITIZE_EMAIL) : '';
        $first_name = (isset($_REQUEST['dealer_first_name'])) ? filter_var($_REQUEST['dealer_first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = (isset($_REQUEST['dealer_last_name'])) ? filter_var($_REQUEST['dealer_last_name'], FILTER_SANITIZE_STRING) : '';
        $company = (isset($_REQUEST['dealer_company'])) ? filter_var($_REQUEST['dealer_company'], FILTER_SANITIZE_STRING) : '';
        $telephone = (isset($_REQUEST['dealer_phone'])) ? filter_var($_REQUEST['dealer_phone'], FILTER_SANITIZE_STRING) : '';
        $address = (isset($_REQUEST['dealer_address'])) ? filter_var($_REQUEST['dealer_address'], FILTER_SANITIZE_STRING) : '';
        $city = (isset($_REQUEST['dealer_city'])) ? filter_var($_REQUEST['dealer_city'], FILTER_SANITIZE_STRING) : '';
        $state = (isset($_REQUEST['dealer_state'])) ? filter_var($_REQUEST['dealer_state'], FILTER_SANITIZE_STRING) : '';
        $zip = (isset($_REQUEST['dealer_zip'])) ? filter_var($_REQUEST['dealer_zip'], FILTER_SANITIZE_STRING) : '';
        $website = (isset($_REQUEST['dealer_website'])) ? filter_var($_REQUEST['dealer_website'], FILTER_SANITIZE_STRING) : '';
        $password   = (isset($_REQUEST['dealer_password'])) ? filter_var($_REQUEST['dealer_password'], FILTER_SANITIZE_STRING) : '';
        $confirm_password = (isset($_REQUEST['dealer_confirm_password'])) ? filter_var($_REQUEST['dealer_confirm_password'], FILTER_SANITIZE_STRING) : '';
        $auth_code = (isset($_REQUEST['dealer_auth_code'])) ? filter_var($_REQUEST['dealer_auth_code'], FILTER_SANITIZE_STRING) : '';
        $partner_type = (isset($_REQUEST['type'])) ? filter_var($_REQUEST['type'], FILTER_SANITIZE_STRING) : ''; // hidden
        $ip = $_SERVER['REMOTE_ADDR'];

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // echo  $email . '<br>';
        // echo  $first_name . '<br>';
        // echo  $last_name . '<br>';
        // echo  $company . '<br>';
        // echo  $telephone . '<br>';
        // echo  $address . '<br>';
        // echo  $city . '<br>';
        // echo  $state . '<br>';
        // echo  $zip . '<br>';
        // echo  $website . '<br>';
        // echo  $password . '<br>';
        // echo  $confirm_password . '<br>';
        // echo  $auth_code . '<br>';
        // echo  $partner_type . '<br>';
        // echo  $ip . '<br>';
        // exit();

        // check Authorization Code
        if ($auth_code != Config::DEALERAUTHCODE)
        {
            // create array
            $results = [
                'result' => false,
            ];

            return $results;
            exit();
        }

        // initialize gate-keeper variable
        $okay = true;

        // validate if JavaScript fails or is disabled
        if ($first_name == '' || $last_name == '' || $email == '' || $company == ''
            || $telephone == ''   || $address == ''   || $city == ''  || $state == ''
            || $zip == ''     || $password == ''  || $confirm_password == '')
        {
            echo "All fields required";
            $okay = false;
            exit();
        }
        if (strlen($password) < 6)
        {
            echo "Password must be at least 6 characters in length";
            $okay = false;
            exit();
        }
        if ($password != $confirm_password)
        {
            echo "Passwords do not match";
            $okay = false;
            exit();
        }

        if ($okay)
        {
            // hash new password
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);

            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "INSERT INTO dealers SET
                        type       = :type,
                        first_name = :first_name,
                        last_name  = :last_name,
                        company    = :company,
                        telephone  = :telephone,
                        address    = :address,
                        city       = :city,
                        state      = :state,
                        zip        = :zip,
                        website    = :website,
                        map_url    = :map_url,
                        email      = :email,
                        pass       = :pass,
                        ip         = :ip";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':type'       => $partner_type,
                    ':first_name' => $first_name,
                    ':last_name'  => $last_name,
                    ':company'    => $company,
                    ':telephone'  => $telephone,
                    ':address'    => $address,
                    ':city'       => $city,
                    ':state'      => $state,
                    ':zip'        => $zip,
                    ':website'    => $website,
                    ':map_url'    => null,
                    ':email'      => $email,
                    ':pass'       => $password_hashed,
                    ':ip'         => $ip
                ];
                $result = $stmt->execute($parameters);

                // get new partner's id
                $id = $db->lastInsertId();

                // create token for validation email
                $token = md5(uniqid(rand(), true)) . md5(uniqid(rand(), true));

                // store result, new user ID & token in array to return to
                // Register controller
                $results = [
                    'result' => $result,
                    'id'     => $id,
                    'token'  => $token
                ];

                // return to Controller
                return $results;
            }
            catch(PDOException $e)
            {
                echo $e->getMessage();
                exit();
            }
        }
    }



    /**
     * retrieves dealer by ID
     *
     * @param  Int  $id     The dealer ID
     * @return Array        The dealer record
     */
    public static function getDealer($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT *
                    FROM dealers
                    WHERE id = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $dealer = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $dealer;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }





    /**
     * validates Dealer credentials
     *
     * @param  String   $email     The dealer's email
     * @param  String   $password  The dealer's password
     *
     * @return Boolean
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
            header("Location: /admin/dealer/login");
            exit();
        }

        // validate email
        if(filter_var($email, FILTER_SANITIZE_EMAIL == false))
        {
            $_SESSION['loginerror'] = 'Please enter a valid email address';
            $okay = false;
            header("Location: /admin/dealer/login");
            exit();
        }

        if($okay)
        {
            // check if email exists & retrieve password
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "SELECT * FROM dealers
                        WHERE email = :email
                        AND active = 1";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':email' => $email
                ];
                $stmt->execute($parameters);
                $dealer = $stmt->fetch(PDO::FETCH_OBJ);

                // returning user verified
                if( (!empty($dealer)) && (password_verify($password, $dealer->pass)) )
                {
                    return $dealer;
                }
                else
                {
                    $dealer = false;
                    return $dealer;
                }
            }
            catch (PDOException $e)
            {
                $_SESSION['loginerror'] = "Error checking credentials";
                header("Location: /admin/dealer/login");
                exit();
            }
        }
    }




    public static function updateFirstLogin($id)
    {
        try
        {
            $db = static::getDB();

            $sql = "UPDATE dealers SET
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




    public static function storeSecurityAnswers($partnerid)
    {
        // retrieve post variables
        $security1 = strtolower( (isset($_REQUEST['security1'])) ? filter_var($_REQUEST['security1'], FILTER_SANITIZE_STRING) : '');
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

            $sql = "UPDATE dealers SET
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

            $sql = "UPDATE dealers SET
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

    //  = =  end Dealer Registration, security questions and first login  = = //




    public static function getDealers()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM dealers
                    ORDER BY company";
            $stmt = $db->query($sql);
            $stmt->execute();
            $dealers = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Dealers Controller
            return $dealers;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function getDealersByState($state)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM dealers
                    WHERE state = :state
                    ORDER BY city";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':state' => $state
            ];
            $stmt->execute($parameters);
            $dealers = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Dealers Controller
            return $dealers;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function getDealersByCity($city)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM dealers
                    WHERE city = :city";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':city' => $city
            ];
            $stmt->execute($parameters);
            $dealers = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Dealers Controller
            return $dealers;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * Adds new dealer from inside Admin panel
     *
     * @return Array
     */
    public static function postDealer()
    {

        // retrieve form data
        $email = (isset($_REQUEST['dealer_email'])) ? filter_var($_REQUEST['dealer_email'], FILTER_SANITIZE_EMAIL) : '';
        $first_name = (isset($_REQUEST['dealer_first_name'])) ? filter_var($_REQUEST['dealer_first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = (isset($_REQUEST['dealer_last_name'])) ? filter_var($_REQUEST['dealer_last_name'], FILTER_SANITIZE_STRING) : '';
        $company = (isset($_REQUEST['dealer_company'])) ? filter_var($_REQUEST['dealer_company'], FILTER_SANITIZE_STRING) : '';
        $telephone = (isset($_REQUEST['dealer_phone'])) ? filter_var($_REQUEST['dealer_phone'], FILTER_SANITIZE_STRING) : '';
        $address = (isset($_REQUEST['dealer_address'])) ? filter_var($_REQUEST['dealer_address'], FILTER_SANITIZE_STRING) : '';
        $city = (isset($_REQUEST['dealer_city'])) ? filter_var($_REQUEST['dealer_city'], FILTER_SANITIZE_STRING) : '';
        $state = (isset($_REQUEST['dealer_state'])) ? filter_var($_REQUEST['dealer_state'], FILTER_SANITIZE_STRING) : '';
        $zip = (isset($_REQUEST['dealer_zip'])) ? filter_var($_REQUEST['dealer_zip'], FILTER_SANITIZE_STRING) : '';
        $website = (isset($_REQUEST['dealer_website'])) ? filter_var($_REQUEST['dealer_website'], FILTER_SANITIZE_STRING) : '';
        $map_url = ( isset($_REQUEST['dealer_map_url']) ) ? filter_var($_REQUEST['dealer_map_url'], FILTER_SANITIZE_STRING) : '';
        $password   = (isset($_REQUEST['dealer_password'])) ? filter_var($_REQUEST['dealer_password'], FILTER_SANITIZE_STRING) : '';
        $confirm_password = (isset($_REQUEST['dealer_confirm_password'])) ? filter_var($_REQUEST['dealer_confirm_password'], FILTER_SANITIZE_STRING) : '';
        $auth_code = (isset($_REQUEST['dealer_auth_code'])) ? filter_var($_REQUEST['dealer_auth_code'], FILTER_SANITIZE_STRING) : '';
        $partner_type = (isset($_REQUEST['type'])) ? filter_var($_REQUEST['type'], FILTER_SANITIZE_STRING) : ''; // hidden

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        // check Authorization Code
        if ($auth_code != Config::DEALERAUTHCODE)
        {
            // create array
            $result = false;

            return $result;
            exit();
        }

        // initialize gate-keeper variable
        $okay = true;

        // validate if JavaScript fails or is disabled
        if ($first_name == '' || $last_name == '' || $email == '' || $company == ''
            || $telephone == ''   || $address == ''   || $city == ''  || $state == ''
            || $zip == ''     || $password == ''  || $confirm_password == '')
        {
            echo "All fields required";
            $okay = false;
            exit();
        }
        if (strlen($password) < 6)
        {
            echo "Password must be at least 6 characters in length";
            $okay = false;
            exit();
        }
        if ($password != $confirm_password)
        {
            echo "Passwords do not match";
            $okay = false;
            exit();
        }

        if ($okay)
        {
            // hash new password
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);

            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "INSERT INTO dealers SET
                        type       = :type,
                        first_name = :first_name,
                        last_name  = :last_name,
                        company    = :company,
                        telephone  = :telephone,
                        address    = :address,
                        city       = :city,
                        state      = :state,
                        zip        = :zip,
                        website    = :website,
                        map_url    = :map_url,
                        email      = :email,
                        pass       = :pass,
                        active     = :active";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':type'       => $partner_type,
                    ':first_name' => $first_name,
                    ':last_name'  => $last_name,
                    ':company'    => $company,
                    ':telephone'  => $telephone,
                    ':address'    => $address,
                    ':city'       => $city,
                    ':state'      => $state,
                    ':zip'        => $zip,
                    ':website'    => $website,
                    ':map_url'    => $map_url,
                    ':email'      => $email,
                    ':pass'       => $password_hashed,
                    ':active'     => 1 // activate
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
    }



    public static function getDealerById($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM dealers
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $dealer = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Admin/Dealers Controller
            return $dealer;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function updateDealer($id)
    {
        // retrieve form data
        $email = (isset($_REQUEST['dealer_email'])) ? filter_var($_REQUEST['dealer_email'], FILTER_SANITIZE_EMAIL) : '';
        $first_name = (isset($_REQUEST['dealer_first_name'])) ? filter_var($_REQUEST['dealer_first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = (isset($_REQUEST['dealer_last_name'])) ? filter_var($_REQUEST['dealer_last_name'], FILTER_SANITIZE_STRING) : '';
        $company = (isset($_REQUEST['dealer_company'])) ? filter_var($_REQUEST['dealer_company'], FILTER_SANITIZE_STRING) : '';
        $telephone = (isset($_REQUEST['dealer_phone'])) ? filter_var($_REQUEST['dealer_phone'], FILTER_SANITIZE_STRING) : '';
        $address = (isset($_REQUEST['dealer_address'])) ? filter_var($_REQUEST['dealer_address'], FILTER_SANITIZE_STRING) : '';
        $city = (isset($_REQUEST['dealer_city'])) ? filter_var($_REQUEST['dealer_city'], FILTER_SANITIZE_STRING) : '';
        $state = (isset($_REQUEST['dealer_state'])) ? filter_var($_REQUEST['dealer_state'], FILTER_SANITIZE_STRING) : '';
        $zip = (isset($_REQUEST['dealer_zip'])) ? filter_var($_REQUEST['dealer_zip'], FILTER_SANITIZE_STRING) : '';
        $website = (isset($_REQUEST['dealer_website'])) ? filter_var($_REQUEST['dealer_website'], FILTER_SANITIZE_STRING) : '';
        $map_url = ( isset($_REQUEST['dealer_map_url']) ) ? filter_var($_REQUEST['dealer_map_url'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE dealers SET
                    first_name = :first_name,
                    last_name  = :last_name,
                    company    = :company,
                    telephone  = :telephone,
                    address    = :address,
                    city       = :city,
                    state      = :state,
                    zip        = :zip,
                    website    = :website,
                    map_url    = :map_url,
                    email      = :email
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'         => $id,
                ':first_name' => $first_name,
                ':last_name'  => $last_name,
                ':company'    => $company,
                ':telephone'  => $telephone,
                ':address'    => $address,
                ':city'       => $city,
                ':state'      => $state,
                ':zip'        => $zip,
                ':website'    => $website,
                ':map_url'    => $map_url,
                ':email'      => $email
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



    public static function deleteDealer($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "DELETE FROM dealers
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $result = $stmt->execute($parameters);

            // return to Admin/Dealers Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function doesDealerExist($email)
    {
        // echo "Connected to doesDealerExist()"; exit();

        // Server side validation (HTML5 validation 'required' on input tag)
        if($email === '' || strlen($email) < 6){
            echo 'Please provide a valid email address';
            exit();
        }

        // check if email is in `dealer_users` table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM dealers
                    WHERE email = :email";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':email' => $email
            ];
            $stmt->execute($parameters);
            $partner = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $partner;
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
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE dealers SET
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




    public static function matchDealer($email, $tmp_pass)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM dealers
                    WHERE email  = :email
                    AND tmp_pass = :tmp_pass";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':email'    => $email,
                ':tmp_pass' => $tmp_pass
            ];
            $stmt->execute($parameters);
            $dealer = $stmt->fetch(PDO::FETCH_OBJ);

            return $dealer;
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

            $sql = "SELECT * FROM dealers
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

            $dealer = $stmt->fetch(PDO::FETCH_OBJ);

            // test
            // echo '<pre>';
            // print_r($dealer);
            // echo '</pre>';
            // exit();

            return $dealer;
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

            $sql = "UPDATE dealers SET
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

            $sql = "UPDATE dealers SET
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



    public static function processFormData()
    {
        // start session if not started
        if (session_status() == PHP_SESSION_NONE)
        {
            session_start();
        }

        // retrieve post data, sanitize & store in variables
        $name         = (isset($_REQUEST['name'])) ? filter_var($_REQUEST['name'], FILTER_SANITIZE_STRING) : '';
        $email        = (isset($_REQUEST['email'])) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL) : '';
        $telephone    = (isset($_REQUEST['telephone'])) ? filter_var($_REQUEST['telephone'], FILTER_SANITIZE_STRING) : '';
        $company      = (isset($_REQUEST['company'])) ? filter_var($_REQUEST['company'], FILTER_SANITIZE_STRING) : '';
        $radio_choice = (isset($_REQUEST['ffl_radio'])) ? filter_var($_REQUEST['ffl_radio'], FILTER_SANITIZE_STRING) : '';
        $spam_fighter = (isset($_REQUEST['spam_fighter'])) ? filter_var($_REQUEST['spam_fighter'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        // check honeypot for robot-filled data
        if($spam_fighter != '')
        {
            return false;
            exit();
        }

        if($name == '' || $email == '' || $telephone == '' || $company == '')
        {
            echo "All fields required.";
            exit();
        }

        // if exists, upload file
        // Assign name value to session variable
        $_SESSION['name'] = $name;

        // test
        // echo $_SESSION['name'] . '<br>';
        // exit();

        if(!empty($_FILES['image']['tmp_name']))
        {

            // Assign target directory to variable
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_dealerffl/';

            if($_SERVER['SERVER_NAME'] != 'localhost')
            {
              // path for live server
              // UPLOAD_PATH = '/home/armalase/public_html/armalaser.com/public'
              $target_dir = Config::UPLOAD_PATH . '/assets/images/uploaded_dealerffl/';
            }
            else
            {
              // path for local machine
              $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_dealerffl/';
            }

            // test
            // echo '$target_dir: ' . $target_dir . '<br>';
            // exit();

            // Access $_FILES global array for uploaded file
            $file_name = $_FILES['image']['name'];
            $file_tmp_loc = $_FILES['image']['tmp_name'];
            $file_type = $_FILES['image']['type'];
            $file_size = $_FILES['image']['size'];
            $file_err_msg = $_FILES['image']['error'];

            // Separate file name into an array by the dot
            $kaboom = explode(".", $file_name);

            // Assign last element of array to file_extension variable (in case file has more than one dot)
            $file_extension = end($kaboom);

            // Assign value to prefix for broker & listing specific image identification
            $prefix = time().'-';


            /* - - - - -  Error handling  - - - - - - */
            $upload_ok = 1;

            // Check if file size < 2 MB
            if($file_size > 2097152)
            {
                $upload_ok = 0;
                unlink($file_tmp_loc);
                echo 'File must be less than 2 Megabytes to upload.';
                exit();
            }
            // Check if file is gif, jpg, png or pdf
            if(!preg_match("/\.(gif|jpg|png|pdf)$/i", $file_name))
            {
                $upload_ok = 0;
                unlink($file_tmp_loc);
                echo 'Image must be gif, jpg, png or pdf to upload.';
                exit();
            }
            // Check for any errors
            if($file_err_msg == 1)
            {
                $upload_ok = 0;
                echo 'Error uploading file. Please try again.';
                exit();
            }

            if($upload_ok = 1)
            {
                // Attach prefix to file name to assure unique name
                $file_name = $prefix . $file_name;

                // Upload file to server into designated folder
                $move_result = move_uploaded_file($file_tmp_loc, $target_dir . $file_name);

                // Check for boolean result of move_uploaded_file()
                if ($move_result != true)
                {
                    unlink($file_tmp_loc);
                    echo 'File not uploaded. Please try again.';
                    exit();
                }
            }
            else
            {
                echo 'File not uploaded. Please try again.';
                exit();
            }
        }

        // store form data in array & return to Dealers Controller
        $results = [
          'name'          => $name,
          'email'         => $email,
          'telephone'     => $telephone,
          'company'       => $company,
          'radio_choice'  => $radio_choice
        ];

        // return $results array to Dealers Controller
        return $results;
    }



    /**
     * Updates dealer data in `dealers` - changes by Dealer in back-end
     *
     * @param  String  $id      The dealer ID
     * @return Boolean
     */
    public static function updateDealerAccount($id)
    {
        // retrieve form data
        $email = (isset($_REQUEST['dealer_email'])) ? filter_var($_REQUEST['dealer_email'], FILTER_SANITIZE_EMAIL) : '';
        $first_name = (isset($_REQUEST['dealer_first_name'])) ? filter_var($_REQUEST['dealer_first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = (isset($_REQUEST['dealer_last_name'])) ? filter_var($_REQUEST['dealer_last_name'], FILTER_SANITIZE_STRING) : '';
        $company = (isset($_REQUEST['dealer_company'])) ? filter_var($_REQUEST['dealer_company'], FILTER_SANITIZE_STRING) : '';
        $telephone = (isset($_REQUEST['dealer_phone'])) ? filter_var($_REQUEST['dealer_phone'], FILTER_SANITIZE_STRING) : '';
        $address = (isset($_REQUEST['dealer_address'])) ? filter_var($_REQUEST['dealer_address'], FILTER_SANITIZE_STRING) : '';
        $city = (isset($_REQUEST['dealer_city'])) ? filter_var($_REQUEST['dealer_city'], FILTER_SANITIZE_STRING) : '';
        $state = (isset($_REQUEST['dealer_state'])) ? filter_var($_REQUEST['dealer_state'], FILTER_SANITIZE_STRING) : '';
        $zip = (isset($_REQUEST['dealer_zip'])) ? filter_var($_REQUEST['dealer_zip'], FILTER_SANITIZE_STRING) : '';
        $website = (isset($_REQUEST['dealer_website'])) ? filter_var($_REQUEST['dealer_website'], FILTER_SANITIZE_STRING) : '';
        $map_url = ( isset($_REQUEST['dealer_map_url']) ) ? filter_var($_REQUEST['dealer_map_url'], FILTER_SANITIZE_STRING) : '';
        $security1 = strtolower( (isset($_REQUEST['security1'])) ? filter_var($_REQUEST['security1'], FILTER_SANITIZE_STRING) : '');
        $security2 = strtolower( (isset($_REQUEST['security2'])) ? filter_var($_REQUEST['security2'], FILTER_SANITIZE_STRING) : '');
        $security3 = (isset($_REQUEST['security3'])) ? filter_var($_REQUEST['security3'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE dealers SET
                    first_name = :first_name,
                    last_name  = :last_name,
                    company    = :company,
                    telephone  = :telephone,
                    address    = :address,
                    city       = :city,
                    state      = :state,
                    zip        = :zip,
                    website    = :website,
                    map_url    = :map_url,
                    email      = :email,
                    security1  = :security1,
                    security2  = :security2,
                    security3  = :security3
                    WHERE id   = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'         => $id,
                ':first_name' => $first_name,
                ':last_name'  => $last_name,
                ':company'    => $company,
                ':telephone'  => $telephone,
                ':address'    => $address,
                ':city'       => $city,
                ':state'      => $state,
                ':zip'        => $zip,
                ':website'    => $website,
                ':map_url'    => $map_url,
                ':email'      => $email,
                ':security1'  => $security1,
                ':security2'  => $security2,
                ':security3'  => $security3
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
     * Updates dealer password in `dealers` by Dealer in back-end
     *
     * @param  String   $id         The dealer ID
     * @param  String   $password   The new password hashed
     * @return Boolean
     */
    public static function updateAccountPassword($id, $password)
    {
        try
        {
            $db = static::getDB();

            $sql = "UPDATE dealers SET
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
