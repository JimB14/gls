<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Chatuser;
use \App\Models\Chat;
use \App\Mail;



class Chats extends \Core\Controller
{
   /**
    * Before filter - applies to methods appended with 'action', e.g. indexaction()
    *
    *
    * @return void
    */
   protected function before()
   {
      // if SESSION for chat user is not set, send to home
      // if ( !isset($_SESSION['chatuser_id'])  )
      // {
      //    header("Location: /");
      //    exit();
      // }
   }



   public function index()
   {

      if ( isset($_SESSION['chatuser_id']) || isset($_SESSION['user_id']) )
      {
         View::renderTemplate('Chat/index.html', [

         ]);
      }
      else
      {
         $errorMessage = 'You must be authorized to access this page.';

         View::renderTemplate('Error/index.html', [
            'errorMessage'    => $errorMessage
         ]);
      }
   }



   public function isEmailAvailable()
   {
      // store POST variable from Ajax script
      $email = (isset($_REQUEST['email_chat'])) ? filter_var($_REQUEST['email_chat'], FILTER_SANITIZE_STRING): '';

      // check for email using User model method
      $response = Chatuser::isEmailAvailable($email);

      // return $response value ('available' or 'not available') to Ajax method for processing
      echo $response;
   }




   public function register()
   {
      // echo "Connected to register() method in Chats controller!"; exit();

      // retrieve & sanitize form data
      $email = (isset($_REQUEST['email_chat_modal'])) ? filter_var($_REQUEST['email_chat_modal'], FILTER_SANITIZE_STRING): '';
      $first_name = (isset($_REQUEST['first_name_chat'])) ? filter_var($_REQUEST['first_name_chat'], FILTER_SANITIZE_STRING): '';
      $user_ip = $_SERVER['REMOTE_ADDR'];

      // check honeypot for robot content
      $honeypot = filter_var($_REQUEST['honeypot'], FILTER_SANITIZE_STRING);

      if($honeypot != '')
      {
         return false;
         exit();
      }

      // test
      // echo $email . '<br>';
      // echo $first_name . '<br>';
      // echo $user_ip . '<br>';
      // exit();

      // validate in case HTML5 and jQuery fail
      if($email == "" || $first_name == '') {
         $errorMessage = 'All fields are required.';

         View::renderTemplate('Error/index.html', [
            'errorMessage' => $errorMessage
         ]);
         exit();
      }

      // check if user already exists
      $chatUser = Chatuser::checkIfUserExists($email);

      if($chatUser)
      {
         $errorMessage = 'It appears that someone with this email address is already registered. <a href="/chatlogin">Please click here to sign in</a>.';
         View::renderTemplate('Error/index.html',[
            'errorMessage' => $errorMessage
         ]);
         exit();
      }

      // register new user
      $results = Chatuser::registerChatUser($email, $first_name, $user_ip);

      // test
      // echo '<pre>';
      // print_r($results);
      // echo '</pre>';
      // exit();

      // get chatuser data
      $chatUser = Chatuser::getChatUser($results['id']);

      // test
      // echo '<pre>';
      // print_r($chatUser);
      // echo '</pre>';
      // exit();

      if($results['result'])
      {

         header("Location: /chatlogin?id=registered");
         exit();
      }

   }



   public function login()
   {
      // check if chat is in use
      $count = Chat::checkForChat();
      if($count > 0)
      {
         $message = "Chat is currently busy. <br>Please try again in a few minutes.<br> Thank you for using ArmaLaser Chat!";

         View::renderTemplate('Chat/busy.html',  [
            'message' => $message
         ]);
         exit();
      }
      else
      {
         // retrieve & sanitize data
         $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';
         $email = (isset($_REQUEST['email'])) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_STRING): '';
         $timeout = (isset($_REQUEST['timeout'])) ? filter_var($_REQUEST['timeout'], FILTER_SANITIZE_STRING): '';
         $ip_address = $_SERVER['REMOTE_ADDR'];

         // create messages & store in variables
         $message = '<h4>You have successfully registered! You can now log in.</h4>';
         $timeout_msg = '<h4>You have been logged out of Live Chat due to inactivity for 30 minutes. You can log back in.</h4>';

         View::renderTemplate('Chat/login.html', [
            'pagetitle'   => 'Chat Log In',
            'id'          => $id,
            'message'     => $message,
            'email'       => $email,
            'timeout'     => $timeout,
            'timeout_msg' => $timeout_msg,
            'ip_address'  => $ip_address,
            'canonURL'    => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
         ]);
      }
   }



   public function loginUser()
   {
      // retrieve & sanitize form data
      $email = (isset($_REQUEST['email_chat'])) ? filter_var($_REQUEST['email_chat'], FILTER_SANITIZE_STRING): '';
      $first_name = (isset($_REQUEST['first_name_chat'])) ? filter_var($_REQUEST['first_name_chat'], FILTER_SANITIZE_STRING): '';
      $ip_address = (isset($_REQUEST['ip_address'])) ? filter_var($_REQUEST['ip_address'], FILTER_SANITIZE_STRING): '';

      // test
      // echo $email . '<br>';
      // echo $first_name . '<br>';
      // exit();

      // validate in case HTML5 and jQuery fail
      if($email == "" || $first_name == '') {
         $errorMessage = 'All fields are required.';

         View::renderTemplate('Error/index.html', [
            'errorMessage' => $errorMessage
         ]);
         exit();
      }

      // $blockedIps = [
      //    '185.92.73.30',
      //    '73.53.170.154'
      // ];

      // check if user exists
      $chatUser = Chatuser::logInUser($email, $first_name);

      // test
      // echo '<pre>';
      // print_r($chatUser);
      // echo '</pre>';
      // exit();

      // user match found
      if ($chatUser)
      {
         // log chat user in
         $_SESSION['chatuserLoggedIn'] = true;

         // create unique ID
         $_SESSION['chatId'] = md5(uniqid($chatUser->id));

         // assign user ID, access_level, first name & email to SESSION variables
         $_SESSION['chatuser_id'] = $chatUser->id;
         $_SESSION['chatuser_access_level'] = 'chat';
         $_SESSION['chatuser_first_name'] = $chatUser->first_name;
         $_SESSION['chatuser_email'] = $chatUser->email;

         // session timeout code in front-controller public/index.php
         $_SESSION['LAST_CHAT_USER_ACTIVITY'] = time(); // store time logged in

         // test
         // echo $_SESSION['chatuserLoggedIn'] . "<br>";
         // echo $_SESSION['chatuser_id'] . "<br>";
         // echo $_SESSION['chatuser_first_name'] . "<br>";
         // echo $_SESSION['chatuser_email'] . "<br>";
         // exit();

         // send email
         $result = Mail::loginNotification($chatUser);

         // redirect to Chat/index.html via Chats/index (above ~#34)
         header("Location: /chat/livechat");
         exit();
      }
      else
      {
         $_SESSION['chatloginerror'] = "Unable to find user.";
         header("Location: /chat/login");
         exit();
      }
   }





   public function processChatMessage()
   {
      // retrieve data
      $chatText   = (isset($_REQUEST['chatText'])) ? filter_var($_REQUEST['chatText'], FILTER_SANITIZE_STRING): '';
      $chatuserId = (isset($_REQUEST['chatuserId'])) ? filter_var($_REQUEST['chatuserId'], FILTER_SANITIZE_STRING): '';
      $chatId     = (isset($_REQUEST['chatId'])) ? filter_var($_REQUEST['chatId'], FILTER_SANITIZE_STRING): '';
      // chat ID on ArmaLaser side
      $theChatId  = (isset($_REQUEST['theChatId'])) ? filter_var($_REQUEST['theChatId'], FILTER_SANITIZE_STRING): '';
      $userId  = (isset($_REQUEST['ArmaLaserUserId'])) ? filter_var($_REQUEST['ArmaLaserUserId'], FILTER_SANITIZE_STRING): '';

      // $chadId will = ' ' in ArmaLaser response
      if($chatId == '') {
         $chatId = $theChatId;
      } else {
         $chatId = $chatId;
      }

      // store message in chats table
      $results = Chat::processChatMessage($chatText, $chatuserId, $chatId, $userId);

      // if successful and the first message of chat session, notify ArmaLaser agent with link to chat
      if($results['result'] == true && $results['id'] == 1)
      {
         // get chatuser data
         $user = Chatuser::getChatUser($chatuserId);

         // store chatuser first name in variable for mail
         $chatuser_first_name = $user->first_name;

         // send notification email
         $response = Mail::chatNotify($chatText, $chatuserId, $chatId, $chatuser_first_name);
      }

      // echo to Ajax request (echo json_encode returns 'true' if successful) echo $result returns 1 if successful
      echo json_encode($results['result']);
   }


   /**
    * retrieves chat data by chat ID
    * @return Object       The chat data
    */
   public function chat()
   {

      if ( isset($_SESSION['chatuser_id']) || isset($_SESSION['user_id']) )
      {
         // retrieve data
         $chatId   = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

         // store chatId in sesssion
         $_SESSION['chatId'] = $chatId;

         // retrieve chat data
         $chatData = Chat::getChatData($chatId);

         // get count
         $messageCount = count($chatData);

         // create/define variable
         $newChat = '';

         // loop thru $chatData for value of id
         foreach($chatData as $item){
            if($item->id == 1){
               $newChat = 'true';
            }
         }

         // create date Object
         $timezone =  new \DateTimeZone("America/New_York");
         $date = new \DateTime("now", $timezone);
         $now = $date->format("Y-m-d H:i:s"); // matches MySQL format

         // assign day of week to variable
         $today = $date->format('D');


         // http://php.net/manual/en/datetime.format.php
         // assign current time to variable
         $current_hour = $date->format('H');

         // test
         // echo $now . '<br>';
         // echo $today . '<br>';
         // echo $current_hour;
         // exit();

         View::renderTemplate('Chat/index.html', [
            'pagetitle'    => '',
            'chatData'     => $chatData,
            'today'        => $today,
            'newChat'      => $newChat,
            'messageCount' => $messageCount,
            'current_hour' => $current_hour
         ]);
      }
      else
      {
         $errorMessage = 'You must be authorized to access this page.';

         View::renderTemplate('Error/index.html', [
            'errorMessage'    => $errorMessage
         ]);
      }
   }


// ==========================================================================

   public function updateChat()
   {
      // retrieve data
      $chatId = (isset($_REQUEST['chatId'])) ? filter_var($_REQUEST['chatId'], FILTER_SANITIZE_STRING): '';

      $chatData = Chat::getChatData($chatId);

      // render view
      View::renderTemplate('Chat/chat-content.html', [
         'chatData' => $chatData
      ]);
   }

// ==========================================================================



   public function deleteAllChatData()
   {
      // retrieve data
      $chatData = (isset($_REQUEST['chatData'])) ? filter_var($_REQUEST['chatData'], FILTER_SANITIZE_STRING): '';

      // delete chat data
      $result = Chat::deleteAllChatData();

      if($result){

         $_SESSION['chatuserLoggedIn'] = false;

         // create unique ID
         unset($_SESSION['chatId']);
         unset($_SESSION['chatuser_id']);
         unset($_SESSION['chatuser_access_level']);
         unset($_SESSION['chatuser_first_name']);
         unset($_SESSION['chatuser_email']);

         // destroy session
         // session_destroy();

         echo 'Success';
      }
   }



   public function deleteChat()
   {
      // retrieve data
      $chatId = (isset($_REQUEST['chatId'])) ? filter_var($_REQUEST['chatId'], FILTER_SANITIZE_STRING): '';

      // delete chat data
      $result = Chat::deleteChat(chatId);

      if($result){

         // destory session
         session_destroy();

         echo 'Success';
      }
   }



   public function checkForChat()
   {
      // retrieve post data (not passed to function)
      $chatId = (isset($_REQUEST['newChatId'])) ? filter_var($_REQUEST['newChatId'], FILTER_SANITIZE_STRING): '';

      // retrieve chat ID for chat
      $count = Chat::checkForChat();

      // return to Ajax request (in Views/base.html)
      echo $count;
   }



}
