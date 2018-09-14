<?php

namespace App\Controllers\Api;

use \Core\View;
use \App\Models\Laser;
use \App\Models\Specification;


/**
 * Api controller
 *
 * PHP version 7.0
 */
class Main extends \Core\Controller
{
   /**
   * Before filter
   *
   * @return void
   */
   protected function before()
   {
      // if SESSION is not set, send to login page
      // if(!isset($_SESSION['user']))
      // {
      //     header("Location: /login");
      //     exit();
      // }
   }


   public function lasers()
   {
      $apiKey = $this->route_params['key'];

      // api keys array
      $apiKeysArray = ['123abc456def789ghi', 'Bluefish1', 'budsgunshop'];

      // test
      // echo "$apiKey<br>";
      // echo '<pre>';
      // print_r($apiKeysArray);
      // echo '</pre>';
      // exit();

      // validate api key in db
      // $result = Api::isValid($apiKey);

      if (in_array($apiKey, $apiKeysArray))
      {
         $isAuthorized = true;
      }
      else
      {
         $isAuthorized = false;
      }

      if ($isAuthorized == false)
      {
         echo "Unauthorized key. Please verify and try again.";
         exit();
      }
      else
      {
         // fetch lasers
         $lasers = Laser::getLasersApi();

         // test
         // echo '<pre>';
         // print_r($lasers);
         // echo '</pre>';

         // add prefix to image file names
         $prefix = 'https://armalaser.com/assets/images/laser-pistol/';
         foreach($lasers as $key => $value)
         {
            $lasers[$key]['image_med'] = $prefix . $value['image_med'];
            $lasers[$key]['image_small'] = $prefix . $value['image_small'];
            $lasers[$key]['image_thumb'] = $prefix . $value['image_thumb'];
         }

         // test
         // echo '<br>Lasers AFTER foreach loop';
         // echo '<pre>';
         // print_r($lasers);
         // echo '</pre>';
         // exit();

         // convert to JSON string & echo
         echo json_encode($lasers);
      }
   }



   public function laserspecs()
   {
      $apiKey = $this->route_params['key'];

      // api keys array
      $apiKeysArray = ['123abc456def789ghi', 'Bluefish1', 'budsgunshop'];

      // validate api key in db
      // $result = Api::isValid($apiKey);

      if (in_array($apiKey, $apiKeysArray)) {
         $isAuthorized = true;
      } else {
         $isAuthorized = false;
      }

      if ($isAuthorized == false)
      {
         echo "Unauthorized key. Please verify and try again.";
         exit();
      }
      else
      {
         // fetch specifications
         $laserSpecs = Specification::getLaserSpecsApi();

         // convert to JSON string & echo
         echo json_encode($laserSpecs);
      }
   }




}
