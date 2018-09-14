<?php

namespace App\Models;

use PDO;


class Contactmodel extends \Core\Model
{
    public static function processContactFormData()
    {
      // retrieve form data
      $name = (isset($_REQUEST['name'])) ? filter_var($_REQUEST['name'], FILTER_SANITIZE_STRING): '';
      $email = (isset($_REQUEST['email'])) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL): '';
      $telephone = (isset($_REQUEST['telephone'])) ? filter_var($_REQUEST['telephone'], FILTER_SANITIZE_STRING): '';
      $message = (isset($_REQUEST['message'])) ? filter_var($_REQUEST['message'], FILTER_SANITIZE_STRING): '';
      $catalog_yes = (isset($_REQUEST['catalog_yes'])) ? filter_var($_REQUEST['catalog_yes'], FILTER_SANITIZE_STRING): '';
      $address = (isset($_REQUEST['address'])) ? filter_var($_REQUEST['address'], FILTER_SANITIZE_STRING): '';
      $city = (isset($_REQUEST['city'])) ? filter_var($_REQUEST['city'], FILTER_SANITIZE_STRING): '';
      $state = (isset($_REQUEST['state'])) ? filter_var($_REQUEST['state'], FILTER_SANITIZE_STRING): '';
      $zip = (isset($_REQUEST['zip'])) ? filter_var($_REQUEST['zip'], FILTER_SANITIZE_STRING): '';

      // validate form data
      if($name == '' || $email == '' || $telphone = '' || $message == '')
      {
          echo "Error. All fields required.";
          exit();
      }

      // test
      // echo '<pre>';
      // print_r($_REQUEST);
      // echo '</pre>';

      $data = [
        'name'        => $name,
        'email'       => $email,
        'telephone'   => $telephone,
        'catalog_yes' => $catalog_yes,
        'message'     => $message,
        'address'     => $address,
        'city'        => $city,
        'state'       => $state,
        'zip'         => $zip
      ];

      // return to Contact Controller
      return $data;
    }




    public static function processVideoReviewFormData($laser_model)
    {
      // retrieve & sanitize form data & store in variables
      $name = (isset($_REQUEST['name'])) ? filter_var($_REQUEST['name'], FILTER_SANITIZE_STRING): '';
      $email = (isset($_REQUEST['email'])) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL): '';
      $telephone = (isset($_REQUEST['telephone'])) ? filter_var($_REQUEST['telephone'], FILTER_SANITIZE_STRING): '';
      $youtube_url = (isset($_REQUEST['youtube_url'])) ? filter_var($_REQUEST['youtube_url'], FILTER_SANITIZE_STRING): '';
      $message = (isset($_REQUEST['message'])) ? filter_var($_REQUEST['message'], FILTER_SANITIZE_STRING): '';

      // validate form data if javascript disabled
      if($name == '' || $email == '' || $telphone = '' || $youtube_url == '' || $message == '')
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
        'name'        => $name,
        'email'       => $email,
        'telephone'   => $telephone,
        'youtube_url' => $youtube_url,
        'message'     => $message
      ];

      // return associative array to Contact Controller
      return $data;
    }
}
