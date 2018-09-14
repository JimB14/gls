<?php

namespace App\Models;

use PDO;


class Dealeruser extends \Core\Model
{

    /**
     * checks if email is in dealer_users table
     *
     * @param  string   $email  The dealer's email address
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

            $sql = "SELECT id FROM dealer_users
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
        $first_name = (isset($_REQUEST['dealer_first_name'])) ? filter_var($_REQUEST['dealer_first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name  = (isset($_REQUEST['dealer_last_name'])) ? filter_var($_REQUEST['dealer_last_name'], FILTER_SANITIZE_STRING) : '';
        $company  = (isset($_REQUEST['dealer_company'])) ? filter_var($_REQUEST['dealer_company'], FILTER_SANITIZE_STRING) : '';
        $email      = (isset($_REQUEST['dealer_email'])) ? filter_var($_REQUEST['dealer_email'], FILTER_SANITIZE_EMAIL) : '';
        $password   = (isset($_REQUEST['dealer_password'])) ? filter_var($_REQUEST['dealer_password'], FILTER_SANITIZE_STRING) : '';
        $confirm_password = (isset($_REQUEST['dealer_confirm_password'])) ? filter_var($_REQUEST['dealer_confirm_password'], FILTER_SANITIZE_STRING) : '';
        $partner_type = (isset($_REQUEST['type'])) ? filter_var($_REQUEST['type'], FILTER_SANITIZE_STRING) : ''; // hidden
        $ip = $_SERVER['REMOTE_ADDR'];

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // echo  $first_name . '<br>';
        // echo  $last_name . '<br>';
        // echo  $email . '<br>';
        // echo  $password . '<br>';
        // echo  $confirm_password . '<br>';
        // echo  $partner_type . '<br>';
        // echo  $ip . '<br>';
        // exit();

        // initialize gate-keeper variable
        $okay = true;

        // validate if JavaScript fails or is disabled
        if ($first_name == '' || $last_name == '' || $email == '' || $password == ''
            || $confirm_password == '')
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

                $sql = "INSERT INTO dealer_users SET
                        type       = :type,
                        company    = :company,
                        first_name = :first_name,
                        last_name  = :last_name,
                        email      = :email,
                        pass       = :pass,
                        ip         = :ip";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':type'       => $partner_type,
                    ':company'    => $company,
                    ':first_name' => $first_name,
                    ':last_name'  => $last_name,
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




    public static function getDealer($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT dealers.dealeruserid, dealers.name, dealers.address1, dealers.address2,
                    dealers.city, dealers.state, dealers.zip, dealers.telephone,
                    dealer_users.*
                    FROM dealers
                    LEFT JOIN dealer_users
                        ON dealer_users.id = dealers.dealeruserid
                    WHERE dealer_users.id = :id";
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

                $sql = "SELECT * FROM dealer_users
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

            $sql = "UPDATE dealer_users SET
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

            $sql = "UPDATE dealer_users SET
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

            $sql = "UPDATE dealer_users SET
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




    public static function getDealers()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM dealer_users";
            $stmt = $db->query($sql);
            $stmt->execute();
            $dealers = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $dealers;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getDealersByLastName($last_name)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM dealer_users
                    WHERE last_name LIKE '$last_name%'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $dealers = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $dealers;
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

            $sql = "SELECT * FROM dealer_users
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

            $sql = "UPDATE dealer_users SET
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

            $sql = "SELECT * FROM dealer_users
                    WHERE email  = :email
                    AND tmp_pass = :tmp_pass";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':email'    => $email,
                ':tmp_pass' => $tmp_pass
            ];
            $stmt->execute($parameters);
            $dealer = $stmt->fetchAll(PDO::FETCH_OBJ);

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

            $sql = "SELECT * FROM dealer_users
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

            $sql = "UPDATE dealer_users SET
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

            $sql = "UPDATE dealer_users SET
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


    // = = = = = = = = = = new above = = = = = = = = = = = = = = //

}
