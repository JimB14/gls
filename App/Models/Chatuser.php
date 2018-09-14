<?php

namespace App\Models;

use PDO;


class Chatuser extends \Core\Model
{


   public static function getChatUser($chatuserId)
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT * FROM chatusers
                 WHERE id = :id";
         $stmt = $db->prepare($sql);
         $parameters = [
            ':id' => $chatuserId
         ];
         $stmt->execute($parameters);

         $user = $stmt->fetch(PDO::FETCH_OBJ);

         // return to Controller
         return $user;
      }
      catch(PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }



   /**
   * checks if email is in chatusers table
   *
   * @param  string   $email  The user's email address
   * @return string           The answer
   */
   public static function isEmailAvailable($email)
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

         $sql = "SELECT email FROM chatusers
                 WHERE email = :email
                 LIMIT 1";
         $stmt = $db->prepare($sql);
         $parameters = [
             ':email' => $email
         ];
         $stmt->execute($parameters);
         $result = $stmt->fetchAll(PDO::FETCH_OBJ);

         // $count = $stmt->rowCount();
         $count = count($result);

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




   public static function checkIfUserExists($email)
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT * FROM chatusers
                 WHERE email = :email";
         $stmt = $db->prepare($sql);
         $parameters = [
            ':email' => $email
         ];
         $stmt->execute($parameters);

         $chatUser = $stmt->fetch(PDO::FETCH_OBJ);

         // return to Controller
         return $chatUser;
      }
      catch(PDOException $e)
      {
          echo $e->getMessage();
          exit();
      }
   }



   public static function registerChatUser($email, $first_name, $user_ip)
   {

      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "INSERT INTO chatusers SET
                  email      = :email,
                  first_name = :first_name,
                  user_ip    = :user_ip";
         $stmt = $db->prepare($sql);
         $parameters = [
            ':email'      => $email,
            ':first_name' => $first_name,
            ':user_ip'    => $user_ip
         ];
         $result = $stmt->execute($parameters);

         // get new chat user ID
         $id = $db->lastInsertId();

         // store values in Array
         $results = [
            'result' => $result,
            'id'     => $id
         ];

         // return Array to Controller
         return $results;
      }
      catch(PDOException $e)
      {
          echo $e->getMessage();
          exit();
      }

   }




   public static function logInUser($email, $first_name)
   {
      // check if user in chatusers
      try
      {
          // establish db connection
          $db = static::getDB();

          $sql = "SELECT * FROM chatusers WHERE
                  email = :email
                  AND first_name = :first_name";
          $stmt = $db->prepare($sql);
          $parameters = [
              ':email' => $email,
              ':first_name' => $first_name
          ];
          $stmt->execute($parameters);

          $user = $stmt->fetch(PDO::FETCH_OBJ);

          return $user;
      }
      catch (PDOException $e)
      {
          echo $e->getMessage();
          exit();
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
