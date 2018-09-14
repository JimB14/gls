<?php

namespace App\Models;

use PDO;


class User extends \Core\Model
{
    public static function getUserData($user_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM users
                    WHERE id = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $user_id
            ];
            $stmt->execute($parameters);

            $user = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Main Controller
            return $user;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * validates user credentials (users are admin users, not customers)
     *
     * @param  string $email     The user's email
     * @param  string $password  The user's password
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
            header("Location: /admin/user/login");
            exit();
        }

        // validate email
        if(filter_var($email, FILTER_SANITIZE_EMAIL === false))
        {
            $_SESSION['loginerror'] = 'Please enter a valid email address';
            $okay = false;
            header("Location: /admin/user/login");
            exit();
        }

        if($okay)
        {
            // check if email exists & retrieve password
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "SELECT * FROM users WHERE
                        email = :email
                        AND active = 1";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':email' => $email
                ];
                $stmt->execute($parameters);
                $user = $stmt->fetch(PDO::FETCH_OBJ);
            }
            catch (PDOException $e)
            {
                $_SESSION['loginerror'] = "Error checking credentials";
                header("Location: /admin/user/login");
                exit();
            }
        }

        // check if email & active match found
        if(empty($user))
        {
            $_SESSION['loginerror'] = "User not found";
            header("Location: /admin/user/login");
            exit();
        }

        // returning user verified
        if( (!empty($user)) && (password_verify($password, $user->pass)) )
        {
            // return $user object to Login controller
            return $user;
        }
        else
        {
            $_SESSION['loginerror'] = "Matching credentials not found.
            Please verify and try again.";
            header("Location: /admin/user/login");
            exit();
        }
    }




    public static function getUsers()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM users";
            $stmt = $db->query($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Dealers Controller
            return $users;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function getUsersByLastName($last_name)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM users
                    WHERE last_name LIKE '$last_name%'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Dealers Controller
            return $users;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function getUserById($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM users
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $user = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Admin/Users Controller
            return $user;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function updateUserPassword($id)
    {
        // retrieve form data
        $new_password = ( isset($_REQUEST['new_password'] ) ) ? filter_var($_REQUEST['new_password'], FILTER_SANITIZE_STRING): '';
        $confirm_password = ( isset($_REQUEST['confirm_password'] ) ) ? filter_var($_REQUEST['confirm_password'], FILTER_SANITIZE_STRING): '';

        // initialize gate-keeper variable
        $okay = true;

        if($new_password == '' || $confirm_password == '')
        {
            echo "Both fields required";
            $okay = false;
            exit();
        }
        if(strlen($new_password) < 6)
        {
            echo "Password must be at least 6 characters in length";
            $okay = false;
            exit();
        }
        if($new_password != $confirm_password)
        {
            echo "Passwords do not match";
            $okay = false;
            exit();
        }

        if($okay)
        {
            // hash new password
            $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "UPDATE users SET
                        pass = :pass
                        WHERE id = :id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':pass' => $new_password_hashed,
                    ':id'   => $id
                ];
                $result = $stmt->execute($parameters);

                // return $result to Admin/Users Controller
                return $result;

            }
            catch(PDOException $e)
            {
                echo $e->getMessage();
                exit();
            }
        }
    }



    public static function updateUser($id)
    {
        // retrieve form data
        $first_name = ( isset($_REQUEST['first_name']) ) ? filter_var($_REQUEST['first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = ( isset($_REQUEST['last_name']) ) ? filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING) : '';
        $email = ( isset($_REQUEST['email']) ) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_STRING) : '';
        $access_level = ( isset($_REQUEST['access_level']) ) ? filter_var($_REQUEST['access_level'], FILTER_SANITIZE_STRING) : '';

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE users SET
                    first_name    = :first_name,
                    last_name     = :last_name,
                    email         = :email,
                    access_level  = :access_level
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'            => $id,
                ':first_name'   => $first_name,
                ':last_name'    => $last_name,
                ':email'        => $email,
                ':access_level' => $access_level
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



    public static function postUser()
    {
        // retrieve form data
        $first_name = ( isset($_REQUEST['first_name']) ) ? filter_var($_REQUEST['first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = ( isset($_REQUEST['last_name']) ) ? filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING) : '';
        $email = ( isset($_REQUEST['email']) ) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_STRING) : '';
        $password = ( isset($_REQUEST['password']) ) ? filter_var($_REQUEST['password'], FILTER_SANITIZE_STRING) : '';
        $confirm_password = ( isset($_REQUEST['confirm_password']) ) ? filter_var($_REQUEST['confirm_password'], FILTER_SANITIZE_STRING) : '';
        // $security1 = ( isset($_REQUEST['security1']) ) ? filter_var($_REQUEST['security1'], FILTER_SANITIZE_STRING) : '';
        // $security2 = ( isset($_REQUEST['security2']) ) ? filter_var($_REQUEST['security2'], FILTER_SANITIZE_STRING) : '';
        // $security3 = ( isset($_REQUEST['security3']) ) ? filter_var($_REQUEST['security3'], FILTER_SANITIZE_STRING) : '';
        // $active = ( isset($_REQUEST['active']) ) ? filter_var($_REQUEST['active'], FILTER_SANITIZE_STRING) : '';
        // $first_login = ( isset($_REQUEST['first_login']) ) ? filter_var($_REQUEST['first_login'], FILTER_SANITIZE_STRING) : '';
        $access_level = ( isset($_REQUEST['access_level']) ) ? filter_var($_REQUEST['access_level'], FILTER_SANITIZE_STRING) : '';

        // initialize gate-keeper variable
        $okay = true;

        if($first_name == '' || $last_name == '' || $email == '' || $password == ''
          || $confirm_password == '' || $access_level == '')
        {
            echo "All fields required";
            $okay = false;
            exit();
        }
        if(strlen($password) < 6)
        {
            echo "Password must be at least 6 characters in length";
            $okay = false;
            exit();
        }
        if($password != $confirm_password)
        {
            echo "Passwords do not match";
            $okay = false;
            exit();
        }

        if($okay)
        {
            // hash new password
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);

            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "INSERT INTO users SET
                        first_name    = :first_name,
                        last_name     = :last_name,
                        email         = :email,
                        pass          = :pass,
                        security1     = :security1,
                        security2     = :security2,
                        security3     = :security3,
                        active        = :active,
                        first_login   = :first_login,
                        access_level  = :access_level";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':first_name'   => $first_name,
                    ':last_name'    => $last_name,
                    ':email'        => $email,
                    ':pass'         => $password_hashed,
                    ':security1'    => null,
                    ':security2'    => null,
                    ':security3'    => null,
                    ':active'       => 1,
                    ':first_login'  => 0,
                    ':access_level' => $access_level
                ];
                $result = $stmt->execute($parameters);

                // return result to Admin/Users Controller
                return $result;
            }
            catch(PDOException $e)
            {
                echo $e->getMessage();
                exit();
            }
        }
    }



    public static function deleteUser($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "DELETE FROM users
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

}
