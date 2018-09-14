<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Holster;
use \App\Models\Pistol;
use \App\Models\Pistolbrand;
use \App\Models\Laser;
use \App\Models\Holstermodel;
use \Core\Router;



class Holsters extends \Core\Controller
{

    /**
     * retrieves data to populate holster page
     * @return View
     */
    public function indexAction()
    {
        // $holsters = Holster::allHolsters();
        $holsters = Holster::getHolstersDataForTable();

        // test
        // echo '<pre>';
        // print_r($holsters);
        // echo '</pre>';
        // exit();

        $table_header_data = Holster::getUniqueModelData();

        // test
        // echo '<pre>';
        // print_r($table_header_data);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Holsters/index.html', [
            'pagetitle'   => '',
            'holsters'    => $holsters,
            'tableHeader' => $table_header_data,
            'activemenu'  => 'holsters',
            'canonURL'    => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }



    /**
     * retrieves holsters by TR Series ID
     * @return [type] [description]
     */
    public function byTrseriesIdAction()
    {
        // echo "Connected to byTrseriesId() method in Holsters controller"; exit();

        // retrieve param
        $pistolMfr = $this->route_params['pistolMfr'];
        $id = $this->route_params['laserId'];
        $laser_model = $this->route_params['laserModel'];

        // get holsters by laser ID
        $holsters = Holster::byTrseries($id);

        // test
        // echo '<pre>';
        // print_r($holsters);
        // echo '</pre>';
        // exit();

        // create title lines
        $holster_title01 = 'LASER-FIT™ HOLSTERS';

        // render view
        View::renderTemplate('Holsters/by-laser.html', [
            'pistol_brand'    => $pistolMfr,
            'brand_name'      => $pistolMfr,
            'pistolMfr'       => $pistolMfr,
            'laser_model'     => $laser_model,
            'holsters'        => $holsters,
            'holster_title01' => $holster_title01
        ]);
    }



    /**
     * display details of one holster
     *
     * @return View  the holster page
     */
    public function showHolster()
    {
        $id = $this->route_params['id'];
        $trseries_model = $this->route_params['laserModel'];

        $holster = Holster::showHolster($id, $trseries_model);

        // test
        // echo '<pre>';
        // print_r($holster);
        // echo '</pre>';
        // exit();

        // render view
        View::renderTemplate('ProductDetails/holster.html', [
           'holster'     => $holster,
           'canonURL'    => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);

    }


    /**
     * retrieves holsters by TR Series ID & holster model
     *
     * @return View
     */
    public function byTrseriesIdAndHolsterModel()
    {
        // echo "Connected to byTrseriesIdAndHolsterModel() method in Holsters controller!<br><br>"; exit();

        // retrieve trseries ID from parameters
        $id = $this->route_params['id'];

        // retrieve route
        $params = $_SERVER['QUERY_STRING'];

        // convert route string to array
        $paramsArr = explode('/', $params);

        // store 3rd element (holster model) in variable
        $model = $paramsArr[2];

        $holsters = Holster::byTrseriesIdAndHolsterModel($id, $model);

        // test
        // echo '<pre>';
        // print_r($holsters);
        // echo '</pre>';
        // exit();

        // get trseries model
        foreach($holsters as $key => $holster) {
            $laser_model = $holster->trseries_model;
            $waist = $holster->waist;
            $pistolMfr = $holster->pistolMfr;
        }

        // test
        // echo $laser_model . '<br>';
        // echo $waist . '<br>';
        // echo $pistolMfr . '<br>';
        // echo '<pre>';
        // print_r($holsters);
        // echo '</pre>';
        // exit();

        // render view
        View::renderTemplate('Holsters/by-laser.html', [
           'holsters'    => $holsters,
           'model'       => $model,
           'waist'       => $waist,
           'pistolMfr'   => $pistolMfr,
           'laser_model' => $laser_model,
           'search'      => true
        ]);
    }




// = = = = = = refactored updates above = = = = = = = = = = = = = = = = = = = //







   /**
    * retrieves holsters by manufacturer/brand
    *
    * @return Object       The matching holsters
    */
   public function getHolsters()
   {
      // retrieve param
      $brand_name = $this->route_params['manufacturer'];

      // get ID
      $id = Pistolbrand::getBrandId($brand_name);

      // get holsters
      $holsters = Holster::getHolsters($id);

      // test
      // echo '<pre>';
      // print_r($holsters);
      // echo '</pre>';
      // exit();

      // render view
      View::renderTemplate('Holsters/bybrand.html', [
         'title' => 'Laser-Fit Holsters ',
         'holsters' => $holsters
      ]);
   }




   /**
    * retrieves holsters by laser model name
    *
    * @return Object       The matching holsters
    */
   public function getHolstersByLaserModel()
   {
      // retrieve param
      // $laser_model = $this->route_params['model'];
      $laser_model = (isset($_REQUEST['model']) ? filter_var($_REQUEST['model'], FILTER_SANITIZE_STRING) : '');

      // get holsters
      $holsters = Holster::getHolstersByLaserModel($laser_model);

      // test
      // echo '<pre>';
      // print_r($holsters);
      // echo '</pre>';
      // exit();

      if($holsters)
      {
         // get pistol brand name
         $result = Holster::getPistolBrandNameByLaserModelName($laser_model);

         // test
         // echo '<pre>';
         // print_r($holsters);
         // echo '</pre>';
         // exit();

         // store pistol brand name in variable
         $pistol_brand = $result->pistol_mfr;

         // create title lines
         $holster_title01 = 'LASER-FIT™ HOLSTERS';
         $holster_title02 = 'for ' . $laser_model . ' / ' . $laser_model . 'G equipped ' . $pistol_brand . '®';

         // render view
         View::renderTemplate('Holsters/by-laser.html', [
            'pistol_brand'    => $pistol_brand,
            'brand_name'      => $pistol_brand,
            'laser_model'     => $laser_model,
            'holsters'        => $holsters,
            'holster_title01' => $holster_title01,
            'holster_title02' => $holster_title02
         ]);
      }
      else
      {
         // create title lines
         $holster_title01 = 'LASER-FIT™ HOLSTERS';

         // render view
         View::renderTemplate('Holsters/by-laser.html', [
            'laser_model'     => $laser_model,
            'holsters'        => $holsters,
            'holster_title01' => $holster_title01
         ]);
      }
   }




   /**
    * retrieves holsters by laser model and holster model
    *
    * @return Object       The matching holsters
    */
   public function find()
   {
      // retrieve param
      $laser = ( isset($_REQUEST['laser']) ) ? filter_var($_REQUEST['laser'], FILTER_SANITIZE_STRING): '';
      $holster_model = ( isset($_REQUEST['model']) ) ? filter_var($_REQUEST['model'], FILTER_SANITIZE_STRING): '';

      // get holsters
      $holsters = Holster::getHolstersByLaserAndHolsterModel($laser, $holster_model);

      // test
      // echo '<pre>';
      // print_r($holsters);
      // echo '</pre>';
      // exit();

      // get pistol brand name
      $result = Holster::getPistolBrandNameByLaserModelName($laser);

      // test
      // echo '<pre>';
      // print_r($holsters);
      // echo '</pre>';
      // exit();

      // store pistol brand name in variable
      $pistol_brand = $result->pistol_mfr;

      switch($holster_model){
         case "SuperTuck®":
            $waist = 'IWB';
            break;
         case "MiniTuck®":
            $waist = 'IWB';
            break;
         case "SnapSlide®":
            $waist = 'OWB';
            break;
         case "MiniSlide®":
           $waist = 'OWB';
           break;
         case "Mini Scabbard":
           $waist = 'OWB';
           break;
         case "Insider":
            $waist = 'IWB';
            break;
         default:
            $waist = 'POCKET';
            break;
      }

      // create title lines
      $holster_title01 = $holster_model . ' ' . $waist . ' Holsters';
      $holster_title02 = 'for ' . $laser . ' / ' . $laser . 'G equipped ' . $pistol_brand . '®';


      // render view
      View::renderTemplate('Holsters/by-laser.html', [
         'laser_model'     => $laser,
         'holsters'        => $holsters,
         'brand_name'      => $result->pistol_mfr,
         'holster_title01' => $holster_title01,
         'holster_title02' => $holster_title02,
         'holster_model'   => $holster_model,
         'laser_model'     => $laser
      ]);
   }



   /**
    * retrieves holsters by laser ID
    *
    * @return Object       The matching holsters
    */
   public function laser()
   {
      // retrieve param
      $laser_id = $this->route_params['id'];

      // get pistol mfr name from lasers table
      // $results = Laser::getMfrNameByLaserId($laser_id);

      // get laser data
      $laser = Laser::getLaserById($laser_id);

      // get holsters by laser ID
      $holsters = Holster::getHolstersByLaserId($laser_id);

      // test
      // echo '<pre>';
      // print_r($holsters);
      // echo '</pre>';
      // exit();

      // create title lines
      $holster_title01 = 'LASER-FIT™ HOLSTERS';

      // change title based on red or green ID
      if($laser->laser_color === 'red')
      {
         $holster_title02 = 'for ' . $laser->laser_model . ' / ' . $laser->laser_model . 'G equipped ' . $laser->mfr_name . '®';
      }
      if($laser->laser_color === 'green')
      {
         $holster_title02 = 'for ' . $laser->laser_model . ' / ' . rtrim($laser->laser_model, "G") . ' equipped ' . $laser->mfr_name . '®';
      }

      // render view
      View::renderTemplate('Holsters/by-laser.html', [
         'title'           => 'Laser-Fit Holsters ',
         'brand_name'      => $laser->mfr_name,
         'holster_title01' => $holster_title01,
         'holster_title02' => $holster_title02,
         'holsters'        => $holsters,
         'laser'           => $laser,
         'laser_series'    => $laser->laser_series,
         'laser_model'     => $laser->laser_model
      ]);
   }




   public function getHolster()
   {
      // retrieve holster ID
      // $id = $this->route_params['id'];
      // retrieve query string data
      $id = ( isset($_REQUEST['holster_id']) ) ? filter_var($_REQUEST['holster_id'], FILTER_SANITIZE_NUMBER_INT): '';
      $pistol_id = ( isset($_REQUEST['pistol_id']) ) ? filter_var($_REQUEST['pistol_id'], FILTER_SANITIZE_NUMBER_INT): '';

      // echo 'holster_id => ' . $id . '<br>';
      // echo 'pistol_id => ' . $pistol_id;
      // exit();

      // get holster details
      $holster = Holster::getHolster($id, $pistol_id);

      // test
      // echo '<pre>';
      // print_r($holster);
      // echo '</pre>';
      // exit();

      // get pistol details
      $pistol = Pistol::getModelDetails($pistol_id);

      // test
      // echo '<pre>';
      // print_r($pistol);
      // echo '</pre>';
      // exit();

      // get laser data
      $lasers = Laser::getLaserByPistolId($pistol_id);

      // test
      // echo '<pre>';
      // print_r($lasers);
      // echo '</pre>';
      // exit();

      // render view
      View::renderTemplate('ProductDetails/index.html', [
         'holster'     => $holster,
         'first_word'  => $holster->laser_series,
         'pistol'      => $pistol,
         'lasers'      => $lasers,
         'canonURL'    => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
         //'second_word' => $holster->pistol_brand_name
      ]);
    }



    /**
     * get holsters for pistols by pistol ID
     *
     * @return Object The holsters
     */
    public function getHolstersByPistol()
    {
        // retrieve form data
        $pistol_brand_id = ( isset($_REQUEST['pistol_brand']) ) ? filter_var($_REQUEST['pistol_brand'], FILTER_SANITIZE_NUMBER_INT): '';
        $pistol_id = ( isset($_REQUEST['pistol_id']) ) ? filter_var($_REQUEST['pistol_id'], FILTER_SANITIZE_NUMBER_INT): '';

        // echo $pistol_brand_id. '<br>';
        // echo $pistol_id . '<br>';
        // exit();

        // get holsters
        $holsters = Holster::getHolstersByPistol($pistol_id);

        // test
        // echo '<pre>';
        // print_r($holsters);
        // echo '</pre>';
        // exit();

        // render view
        View::renderTemplate('Holsters/index.html', [
            'holsters'      => $holsters,
            'searchResults' => 'true'
        ]);
    }





    public function searchHolstersByBrand()
    {
        // retrieve form data
        $name = ( isset($_REQUEST['name']) ) ? filter_var($_REQUEST['name'], FILTER_SANITIZE_STRING) : '';

        // if empty submit
        if($name == '')
        {
            header("Location: /holsters");
            exit();
        }

        // get holsters
        $holsters = Holster::getHolsterByBrand($name);

        // test
        // echo '<pre>';
        // print_r($holsters);
        // echo '</pre>';

        // render view
        View::renderTemplate('Holsters/index.html', [
            'holsters' => $holsters,
            'searched' => $name
        ]);
    }



   /**
    * get medium images for holster by ID
    *
    * @return Object   Object with two properties
    */
   public function getReverseImageMed()
   {
      $id = $this->route_params['id'];

      // get med images
      $holsterMedImages = Holster::getReverseImageMed($id);

      // test
      // echo '<pre>';
      // print_r($holsterMedImages);
      // echo '</pre>';
      // exit();

      // echo to Ajax request
      echo json_encode($holsterMedImages);
   }




   /**
    * retrieves holster details
    *
    * @return Obj   The holster record
    */
   public function holster()
   {
      // get param
      $id = ( isset($_REQUEST['id'])  ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT) : '';
      $laser_id = ( isset($_REQUEST['laser_id'])  ) ? filter_var($_REQUEST['laser_id'], FILTER_SANITIZE_NUMBER_INT) : '';

      // get holster
      $holster = Holster::getHolsterById($id);

      // test
      // echo '<pre>';
      // print_r($holster);
      // echo '</pre>';
      // exit();

      // render view
      View::renderTemplate('ProductDetails/index.html', [
         'holster'  => $holster,
         'laser_id' => $laser_id
      ]);
   }
}
