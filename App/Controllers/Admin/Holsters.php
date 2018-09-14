<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\Flx;
use \App\Models\Holster;
use \App\Models\Pistolbrand;
use \App\Models\Laser;


class Holsters extends \Core\Controller
{
    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        // redirect user not logged in to root
        if(!isset($_SESSION['user']))
        {
            header("Location: /");
            exit();
        }

        // redirect logged in user that is not supervisor or admin
        if (isset($_SESSION['user']) && $_SESSION['userType'] == 'dealer'
            || $_SESSION['userType'] == 'partner'
            || $_SESSION['userType'] == 'customer')
        {
            header("Location: /");
            exit();

        }
    }


    /**
     * displays all holsters
     *
     * @return
     */
    public function getHolstersAction()
    {
        // get holsters
        $holsters = Holster::getHolstersTableData();

        // test
        // echo '<pre>';
        // print_r($holsters);
        // echo '</pre>';
        // exit();


        View::renderTemplate('Admin/Show/holsters.html', [
            'holsters'  => $holsters,
            'pagetitle' => 'Manage holsters'
        ]);
    }






    /**
     * retrieves holsters for product list for Partners
     *
     * @return
     */
    public function partnerGetHolstersAction()
    {
        // get holsters
        $holsters = Holster::getHolstersForPartners();

        // test
        // echo '<pre>';
        // print_r($holsters);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Partners/Show/holsters.html', [
            'holsters'  => $holsters,
            'pagetitle' => 'Holsters - Partners'
        ]);
    }




    public function searchHolstersByNameAction()
    {
        // get laser model name from form
        $name = ( isset($_REQUEST['name'])  ) ? filter_var($_REQUEST['name']): '';

        if($name == '')
        {
            header("Location: /admin/holsters/get-holsters");
            exit();
        }

        // get holster details
        $holsters = Holster::getHolstersByName($name);

        // test
        // echo '<pre>';
        // print_r($holsters);
        // echo '</pre>';
        //exit();

        // render view
        View::renderTemplate('Admin/Show/holsters.html', [
            'holsters' => $holsters,
            'searched'    => $name
        ]);
    }




    public function addHolsterAction()
    {
        // create array for drop-down
        $laser_series = [
            'TR Series',
            'GTO FLX',
            'Stingray'
        ];

        // holster mfrs
        $mfrs = [
          "CrossBreed®",
          "DeSantis"
        ];

        // hands
        $hands = [
          "OWB RIGHT HAND",
          "OWB LEFT HAND",
          "IWB RIGHT HAND",
          "IWB LEFT HAND"
        ];

        // get pistol brand names
        $pistol_mfrs = Pistolbrand::getBrands();

        // get lasers
        // $lasers = Laser::getRedLasersModelsAdmin();

        View::renderTemplate('Admin/Add/holster.html', [
            'laser_series' => $laser_series,
            'mfrs'         => $mfrs,
            'hands'        => $hands,
            'pistol_mfrs'  => $pistol_mfrs
        ]);
    }




    public function postNewHolsterAction()
    {
        // add new flx to flx table
        $result = Holster::postNewHolster();

        if($result)
        {
            echo '<script>alert("Holster successfully added!")</script>';
            echo '<script>window.location.href="/admin/holsters/get-holsters"</script>';
            exit();
        }
    }




    public function deleteHolsterAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // delete laser
        $result = Holster::deleteHolster($id);

        if($result)
        {
            echo '<script>alert("Holster successfully deleted!")</script>';
            echo '<script>window.location.href="/admin/holsters/get-holsters"</script>';
            exit();
        }
    }




    public function editHolsterAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // get holster details
        $holster = Holster::getHolsterById($id);

        // test
        // echo '<pre>';
        // print_r($holster);
        // echo '</pre>';
        // exit();

        // get pistol brand names
        $pistol_mfrs = Pistolbrand::getBrands();

        // create array (for accessories.name) for drop-down
        $laser_series = [
            'TR Series',
            'GTO FLX',
            'Stingray'
        ];

        // holster mfrs
        $mfrs = [
          "CrossBreed®",
          "DeSantis"
        ];

        // hands
        $hands = [
          "OWB RIGHT HAND",
          "OWB LEFT HAND",
          "IWB RIGHT HAND",
          "IWB LEFT HAND"
        ];

        // render view
        View::renderTemplate('Admin/Update/holster.html', [
            'holster'      => $holster,
            'laser_series' => $laser_series,
            'pistol_mfrs'  => $pistol_mfrs,
            'mfrs'         => $mfrs,
            'hands'        => $hands
        ]);
    }




    public function updateHolsterAction()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // update holster
        $result = Holster::updateHolster($id);

        if($result)
        {
            echo '<script>alert("Holster successfully updated!")</script>';
            echo '<script>window.location.href="/admin/holsters/get-holsters"</script>';
            exit();
        }
    }


   /**
    * retrieve laser series models by laser series (Ajax request)
    *
    * @return [type] [description]
    */
   public function getLaserSeriesRedModelsAction()
   {
      // echoing sends the data back and it's logged in the console!!!
      // echo "connected to getLaserSeriesRedModels() \n";

      // get red lasers of selected series
      $laser_models = Laser::getLaserModelsByColorAndSeriesAdmin();

      // echo to Ajax request; return JSON
      // header('Content-Type: application/json');
      echo json_encode($laser_models);
   }
}
