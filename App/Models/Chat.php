<?php

namespace App\Models;

use PDO;

/**
 * Chat model
 */
class Chat extends \Core\Model
{
   public static function processChatMessage($chatText, $chatuserId, $chatId, $userId)
   {

      try
      {
         // establish db connection
         $db = static::getDB();

         // execute query
         $sql = "INSERT INTO chats SET
                 chatId      = :chatId,
                 user_id     = :user_id,
                 chatuser_id = :chatuser_id,
                 message     = :message";
         $stmt = $db->prepare($sql);
         $parameters = [
            ':chatId'      => $chatId,
            ':user_id'     => $userId,
            ':chatuser_id' => $chatuserId,
            ':message'     => $chatText
         ];
         $result = $stmt->execute($parameters);

         // get Id
         $id = $db->lastInsertId();

         // create array to store values for controller
         $results = [
            'result' => $result,
            'id'     => $id
         ];

         // return array with boolean result and chats.id value to Controller
         return $results;
      }
       catch(PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }




   public static function getChatData($chatId)
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         // execute query
         $sql = "SELECT chats.*,
                 chatusers.first_name, chatusers.email,
                 users.first_name as csr_firstname
                 FROM chats
                 LEFT JOIN chatusers ON
                 chatusers.id = chats.chatuser_id
                 LEFT JOIN users ON
                 users.id = chats.user_id
                 WHERE chats.chatId = :chatId
                 ORDER BY chats.created_at";

         $stmt = $db->prepare($sql);
         $parameters = [
            ':chatId' => $chatId
         ];
         $stmt->execute($parameters);

         $chatData = $stmt->fetchAll(PDO::FETCH_OBJ);

         // return to Controller
         return $chatData;
      }
       catch(PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }



   public static function checkForChat()
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         // execute query
         $sql = "SELECT * FROM chats";
         $stmt = $db->prepare($sql);
         $stmt->execute();

         $chatData = $stmt->fetchAll(PDO::FETCH_OBJ);

         $count = $stmt->rowCount();

         // return to Controller
         return $count;
      }
       catch(PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }




   // public static function getChatDataByAjax($chatId)
   // {
   //    try
   //    {
   //       // establish db connection
   //       $db = static::getDB();
   //
   //       // execute query
   //       $sql = "SELECT chats.id, chats.chatId, chats.user_id, chats.chatuser_id,
   //               chats.message, chats.created_at, chatusers.first_name,
   //               chatusers.email
   //               FROM chats
   //               INNER JOIN chatusers ON
   //               chatusers.id = chats.chatuser_id
   //               ORDER BY created_at DESC";
   //       $stmt = $db->prepare($sql);
   //       $stmt->execute();
   //
   //       $messages = $stmt->fetchAll(PDO::FETCH_OBJ);
   //
   //       // return to Controller
   //       return $messages;
   //    }
   //     catch(PDOException $e)
   //    {
   //       echo $e->getMessage();
   //       exit();
   //    }
   // }


   public static function deleteAllChatData()
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         // execute query
         $sql = "TRUNCATE chats";
         $stmt = $db->prepare($sql);
         $result = $stmt->execute();

         return $result;
      }
      catch (PDOException $e)
      {
         $e->getMessage();
         exit();
      }

   }



   public static function deleteChat()
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         // execute query
         $sql = "DELETE FROM chats
                 WHERE session_id = :session_id";
         $stmt = $db->prepare($sql);
         $parameters = [
            ':session_id' => $session_id
         ];
         $result = $stmt->execute($parameters);

         return $result;
      }
      catch (PDOException $e)
      {
         $e->getMessage();
         exit();
      }
   }





}
