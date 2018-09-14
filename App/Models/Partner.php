<?php

namespace App\Models;

use PDO;
use \App\Config;


class Partner extends \Core\Model
{
    /**
     * checks if email is in partners table
     *
     * @param  string   $email  The partner's email address
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

            $sql = "SELECT id FROM partners
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




    /**
     * Inserts new partner into `partners`
     *
     * @return Array    New partner ID, token, boolean
     */
    public static function addNewPartner()
    {
        // retrieve form data
        $email = (isset($_REQUEST['partner_email'])) ? filter_var($_REQUEST['partner_email'], FILTER_SANITIZE_EMAIL) : '';
        $first_name = (isset($_REQUEST['partner_first_name'])) ? filter_var($_REQUEST['partner_first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = (isset($_REQUEST['partner_last_name'])) ? filter_var($_REQUEST['partner_last_name'], FILTER_SANITIZE_STRING) : '';
        $company = (isset($_REQUEST['partner_company'])) ? filter_var($_REQUEST['partner_company'], FILTER_SANITIZE_STRING) : '';
        $telephone = (isset($_REQUEST['partner_phone'])) ? filter_var($_REQUEST['partner_phone'], FILTER_SANITIZE_STRING) : '';
        $address = (isset($_REQUEST['partner_address'])) ? filter_var($_REQUEST['partner_address'], FILTER_SANITIZE_STRING) : '';
        $city = (isset($_REQUEST['partner_city'])) ? filter_var($_REQUEST['partner_city'], FILTER_SANITIZE_STRING) : '';
        $state = (isset($_REQUEST['partner_state'])) ? filter_var($_REQUEST['partner_state'], FILTER_SANITIZE_STRING) : '';
        $zip = (isset($_REQUEST['partner_zip'])) ? filter_var($_REQUEST['partner_zip'], FILTER_SANITIZE_STRING) : '';
        $password   = (isset($_REQUEST['partner_password'])) ? filter_var($_REQUEST['partner_password'], FILTER_SANITIZE_STRING) : '';
        $confirm_password = (isset($_REQUEST['partner_confirm_password'])) ? filter_var($_REQUEST['partner_confirm_password'], FILTER_SANITIZE_STRING) : '';
        $auth_code = (isset($_REQUEST['partner_auth_code'])) ? filter_var($_REQUEST['partner_auth_code'], FILTER_SANITIZE_STRING) : '';
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
        // echo  $password . '<br>';
        // echo  $confirm_password . '<br>';
        // echo  $auth_code . '<br>';
        // echo  $partner_type . '<br>';
        // echo  $ip . '<br>';
        // exit();

        // check Authorization Code
        if ($auth_code != Config::PARTNERAUTHCODE)
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

                $sql = "INSERT INTO partners SET
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
                    ':website'    => null,
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




    public static function getPartner($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM partners
                    WHERE id = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $partner = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $partner;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * validates partner credentials
     *
     * @param  string   $email     The partner's email
     * @param  string   $password  The partnerRegister's password
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
            header("Location: /admin/login/partner");
            exit();
        }

        // validate email
        if(filter_var($email, FILTER_SANITIZE_EMAIL == false))
        {
            $_SESSION['loginerror'] = 'Please enter a valid email address';
            $okay = false;
            header("Location: /admin/login/partner");
            exit();
        }

        if($okay)
        {
            // check if email exists & retrieve password
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "SELECT * FROM partners
                        WHERE email = :email
                        AND active = 1";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':email' => $email
                ];
                $stmt->execute($parameters);
                $partner = $stmt->fetch(PDO::FETCH_OBJ);

                // returning user verified
                if( (!empty($partner)) && (password_verify($password, $partner->pass)) )
                {
                    return $partner;
                }
                else
                {
                    $partner = false;
                    return $partner;
                }
            }
            catch (PDOException $e)
            {
                $_SESSION['loginerror'] = "Error checking credentials";
                header("Location: /admin/login/partner");
                exit();
            }
        }
    }




    public static function updateFirstLogin($id)
    {
        try
        {
            $db = static::getDB();

            $sql = "UPDATE partners SET
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

            $sql = "UPDATE partners SET
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

            $sql = "UPDATE partners SET
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




    public static function getPartners()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM partners";
            $stmt = $db->query($sql);
            $stmt->execute();
            $partners = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $partners;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getPartnersByLastName($last_name)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM partners
                    WHERE last_name LIKE '$last_name%'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $partners = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $partners;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function doesPartnerExist($email)
    {
        // echo "Connected to doesPartnerExist()"; exit();

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

            $sql = "SELECT * FROM partners
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

            $sql = "UPDATE partners SET
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

            $sql = "SELECT * FROM partners
                    WHERE email = :email
                    AND tmp_pass = :tmp_pass";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':email'    => $email,
                ':tmp_pass' => $tmp_pass
            ];
            $stmt->execute($parameters);
            $partner = $stmt->fetch(PDO::FETCH_OBJ);

            return $partner;
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

            $sql = "SELECT * FROM partners
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

            $partner = $stmt->fetch(PDO::FETCH_OBJ);

            // test
            // echo '<pre>';
            // print_r($partner);
            // echo '</pre>';
            // exit();

            return $partner;
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

            $sql = "UPDATE partners SET
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

            $sql = "UPDATE partners SET
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




    public static function updatePartner($id)
    {
        // retrieve form data
        $company = (isset($_REQUEST['partner_company'])) ? filter_var($_REQUEST['partner_company'], FILTER_SANITIZE_STRING) : '';
        $first_name = (isset($_REQUEST['partner_first_name'])) ? filter_var($_REQUEST['partner_first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = (isset($_REQUEST['partner_last_name'])) ? filter_var($_REQUEST['partner_last_name'], FILTER_SANITIZE_STRING) : '';
        $email = (isset($_REQUEST['partner_email'])) ? filter_var($_REQUEST['partner_email'], FILTER_SANITIZE_EMAIL) : '';
        $telephone = (isset($_REQUEST['partner_phone'])) ? filter_var($_REQUEST['partner_phone'], FILTER_SANITIZE_STRING) : '';
        $address = (isset($_REQUEST['partner_address'])) ? filter_var($_REQUEST['partner_address'], FILTER_SANITIZE_STRING) : '';
        $city = (isset($_REQUEST['partner_city'])) ? filter_var($_REQUEST['partner_city'], FILTER_SANITIZE_STRING) : '';
        $state = (isset($_REQUEST['partner_state'])) ? filter_var($_REQUEST['partner_state'], FILTER_SANITIZE_STRING) : '';
        $zip = (isset($_REQUEST['partner_zip'])) ? filter_var($_REQUEST['partner_zip'], FILTER_SANITIZE_STRING) : '';
        $website = (isset($_REQUEST['partner_website'])) ? filter_var($_REQUEST['partner_website'], FILTER_SANITIZE_STRING) : '';
        $map_url = ( isset($_REQUEST['partner_map_url']) ) ? filter_var($_REQUEST['partner_map_url'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE partners SET
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




    public static function deletePartner($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "DELETE FROM partners
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
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * Adds new partner from inside Admin panel
     *
     * @return Array
     */
    public static function postPartner()
    {

        // retrieve form data
        $email = (isset($_REQUEST['partner_email'])) ? filter_var($_REQUEST['partner_email'], FILTER_SANITIZE_EMAIL) : '';
        $first_name = (isset($_REQUEST['partner_first_name'])) ? filter_var($_REQUEST['partner_first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = (isset($_REQUEST['partner_last_name'])) ? filter_var($_REQUEST['partner_last_name'], FILTER_SANITIZE_STRING) : '';
        $company = (isset($_REQUEST['partner_company'])) ? filter_var($_REQUEST['partner_company'], FILTER_SANITIZE_STRING) : '';
        $telephone = (isset($_REQUEST['partner_phone'])) ? filter_var($_REQUEST['partner_phone'], FILTER_SANITIZE_STRING) : '';
        $address = (isset($_REQUEST['partner_address'])) ? filter_var($_REQUEST['partner_address'], FILTER_SANITIZE_STRING) : '';
        $city = (isset($_REQUEST['partner_city'])) ? filter_var($_REQUEST['partner_city'], FILTER_SANITIZE_STRING) : '';
        $state = (isset($_REQUEST['partner_state'])) ? filter_var($_REQUEST['partner_state'], FILTER_SANITIZE_STRING) : '';
        $zip = (isset($_REQUEST['partner_zip'])) ? filter_var($_REQUEST['partner_zip'], FILTER_SANITIZE_STRING) : '';
        $website = (isset($_REQUEST['partner_website'])) ? filter_var($_REQUEST['partner_website'], FILTER_SANITIZE_STRING) : '';
        $map_url = ( isset($_REQUEST['partner_map_url']) ) ? filter_var($_REQUEST['partner_map_url'], FILTER_SANITIZE_STRING) : '';
        $password   = (isset($_REQUEST['partner_password'])) ? filter_var($_REQUEST['partner_password'], FILTER_SANITIZE_STRING) : '';
        $confirm_password = (isset($_REQUEST['partner_confirm_password'])) ? filter_var($_REQUEST['partner_confirm_password'], FILTER_SANITIZE_STRING) : '';
        $auth_code = (isset($_REQUEST['partner_auth_code'])) ? filter_var($_REQUEST['partner_auth_code'], FILTER_SANITIZE_STRING) : '';
        $partner_type = (isset($_REQUEST['type'])) ? filter_var($_REQUEST['type'], FILTER_SANITIZE_STRING) : ''; // hidden

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // echo $email . '<br>';
        // echo $first_name . '<br>';
        // echo $last_name . '<br>';
        // echo $company . '<br>';
        // echo $telephone . '<br>';
        // echo $address . '<br>';
        // echo $city . '<br>';
        // echo $state . '<br>';
        // echo $zip . '<br>';
        // echo $website . '<br>';
        // echo $password . '<br>';
        // echo $email . '<br>';
        // echo $confirm_password . '<br>';
        // echo $auth_code . '<br>';
        // echo $partner_type . '<br>';
        // exit();

        // check Authorization Code
        if ($auth_code != Config::PARTNERAUTHCODE)
        {
            $result = false;

            return $result;
            exit();
        }

        // initialize gate-keeper variable
        $okay = true;

        // validate if JavaScript fails or is disabled
        if ($first_name == ''     || $last_name == '' || $email == '' || $company == ''
            || $telephone == ''   || $address == ''   || $city == ''  || $state == ''
            || $zip == ''         || $password == ''  || $confirm_password == '')
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

                $sql = "INSERT INTO partners SET
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




    /**
     * Updates partner data in `partners` - changes by Partner in back-end
     *
     * @param  String  $id      The partner ID
     * @return Boolean
     */
    public static function updatePartnerAccount($id)
    {
        // retrieve form data
        $email = (isset($_REQUEST['partner_email'])) ? filter_var($_REQUEST['partner_email'], FILTER_SANITIZE_EMAIL) : '';
        $first_name = (isset($_REQUEST['partner_first_name'])) ? filter_var($_REQUEST['partner_first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = (isset($_REQUEST['partner_last_name'])) ? filter_var($_REQUEST['partner_last_name'], FILTER_SANITIZE_STRING) : '';
        $company = (isset($_REQUEST['partner_company'])) ? filter_var($_REQUEST['partner_company'], FILTER_SANITIZE_STRING) : '';
        $telephone = (isset($_REQUEST['partner_phone'])) ? filter_var($_REQUEST['partner_phone'], FILTER_SANITIZE_STRING) : '';
        $address = (isset($_REQUEST['partner_address'])) ? filter_var($_REQUEST['partner_address'], FILTER_SANITIZE_STRING) : '';
        $city = (isset($_REQUEST['partner_city'])) ? filter_var($_REQUEST['partner_city'], FILTER_SANITIZE_STRING) : '';
        $state = (isset($_REQUEST['partner_state'])) ? filter_var($_REQUEST['partner_state'], FILTER_SANITIZE_STRING) : '';
        $zip = (isset($_REQUEST['partner_zip'])) ? filter_var($_REQUEST['partner_zip'], FILTER_SANITIZE_STRING) : '';
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

            $sql = "UPDATE partners SET
                    first_name = :first_name,
                    last_name  = :last_name,
                    company    = :company,
                    telephone  = :telephone,
                    address    = :address,
                    city       = :city,
                    state      = :state,
                    zip        = :zip,
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
     * Updates partner password in `partners` by Partner in back-end
     *
     * @param  String   $id         The partner ID
     * @param  String   $password   The new password hashed
     * @return Boolean
     */
    public static function updateAccountPassword($id, $password)
    {
        try
        {
            $db = static::getDB();

            $sql = "UPDATE partners SET
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
