<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\Show;
use \App\Models\State;


class Shows extends \Core\Controller
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
     * displays all shows
     *
     * @return
     */
    public function getShowsAction()
    {
        // get shows
        $shows = Show::getAllShowsAdmin();

        // test
        // echo '<pre>';
        // print_r($shows);
        // echo '</pre>';
        // exit();

        $pagetitle = "Manage gun shows";

        View::renderTemplate('Admin/Armalaser/Show/gunshows.html', [
            'shows'     => $shows,
            'pagetitle' => $pagetitle
        ]);
    }



   /**
   * displays form to add show
   */
   public function addShowAction()
   {
      // get states
      $states = State::getStates();

      // get cities
      $cities = Show::getCitiesAdmin();

      // test
      // echo '<pre>';
      // print_r($cities);
      // echo '</pre>';
      // exit();

      // get ArmaLaser rep names
      $reps = Show::getRepNamesAdmin();

      // get show production company names
      $producers = Show::getShowProductionCompaniesAdmin();

      // get show production company URLs
      $urls = Show::getShowProductionCompanyUrlsAdmin();

      // get map urls
      $map_urls = Show::getMapUrlsForEventLocationsAdmin();

      // get locations
      $locations = Show::getLocationsAdmin();

      // get event names
      $names = Show::getEventNamesAdmin();

      View::renderTemplate('Admin/Armalaser/Add/gunshow.html', [
         'states'    => $states,
         'cities'    => $cities,
         'reps'      => $reps,
         'producers' => $producers,
         'urls'      => $urls,
         'map_urls'  => $map_urls,
         'locations' => $locations,
         'names'     => $names
      ]);
   }



    /**
     * posts new show data into database
     *
     * @return boolean
     */
    public function postGunShowAction()
    {
        // add show to shows table
        $result = Show::postGunShow();

        if($result)
        {
            echo '<script>alert("Gun show successfully added!")</script>';
            echo '<script>window.location.href="/admin/shows/get-shows"</script>';
        }
    }



    /**
     * displays populated form to edit show record
     *
     * @return
     */
    public function editShowAction()
    {
        // get states
        $states = State::getStates();

        // get show
        $show = Show::getShowByIdAdmin();

        // test
        // echo '<pre>';
        // print_r($show);
        // echo '</pre>';

        // get cities
       $cities = Show::getCitiesAdmin();

       // test
       // echo '<pre>';
       // print_r($cities);
       // echo '</pre>';
       // exit();

       // get ArmaLaser rep names
       $reps = Show::getRepNamesAdmin();

       // get show production company names
       $producers = Show::getShowProductionCompaniesAdmin();

       // get show production company URLs
       $urls = Show::getShowProductionCompanyUrlsAdmin();

       // get map urls
       $map_urls = Show::getMapUrlsForEventLocationsAdmin();

       // get locations
       $locations = Show::getLocationsAdmin();

       // get event names
       $names = Show::getEventNamesAdmin();

        View::renderTemplate('Admin/Armalaser/Update/gunshow.html', [
            'states'    => $states,
            'show'      => $show,
            'cities'    => $cities,
            'reps'      => $reps,
            'producers' => $producers,
            'urls'      => $urls,
            'map_urls'  => $map_urls,
            'locations' => $locations,
            'names'     => $names,
            'display_status' => $show->display
        ]);
    }



    /**
     * deletes show from database
     *
     * @return boolean
     */
    public function deleteShowAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // delete laser
        $result = Show::deleteShow($id);

        if($result)
        {
            echo '<script>alert("Show successfully deleted!")</script>';
            echo '<script>window.location.href="/admin/shows/get-shows"</script>';
            exit();
        }
    }



   /**
   * posts updated data to shows table
   *
   * @return boolean
   */
   public function updateGunShowAction()
   {
      // retrieve id from query string
      $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

      // update gun show
      $result = Show::updateGunShow($id);

      if($result)
      {
         echo '<script>alert("Gun show successfully updated!")</script>';
         echo '<script>window.location.href="/admin/shows/get-shows"</script>';
      }
   }



    /**
     * returns shows by searched city
     *
     * @return object The shows records
     */
    public function searchShowsByCityAction()
    {
        // retrieve form data
        $city = ( isset($_REQUEST['city']) ) ? filter_var($_REQUEST['city'], FILTER_SANITIZE_STRING) : '';

        // echo $city;
        // exit();

        if($city == '')
        {
            header("Location: /admin/shows/get-shows");
            exit();
        }

        // get show data
        $shows = Show::getShowsByCity($city);

        // test
        // echo '<pre>';
        // print_r($shows);
        // echo '</pre>';
        // exit();

        // get cities for drop-down
        $cities = Show::getCities();

        // create pagetitle
        $pagetitle = "Manage gun shows";

        // render view and pass data
        View::renderTemplate('Admin/Armalaser/Show/gunshows.html', [
            'shows'     => $shows,
            'cities'    => $cities,
            'searched'  => $city,
            'pagetitle' => $pagetitle
        ]);
    }


   public function doesNameExistAction()
   {
      // check if name is in `shows` table
      $count = Show::doesNameExistAdmin();

      // set value of $result
      if($count > 0){
         $result = true;
      } else {
         $result = false;
      }

      // return to Ajax request
      echo $result;
   }


   public function doesLocationExistAction()
   {
      // check if location is in `shows` table
      $count = Show::doesLocationExistAdmin();

      // set value of $result
      if($count > 0){
         $result = true;
      } else {
         $result = false;
      }

      // return to Ajax request
      echo $result;
   }



   public function doesCityExistAction()
   {
      // check if city in shows table
      $count = Show::doesCityExistAdmin();

      // set value of $result
      if($count > 0){
         $result = true;
      } else {
         $result = false;
      }

      // return to Ajax request
      echo $result;
   }


   public function doesRepExistAction()
   {
      // check if city in shows table
      $count = Show::doesRepExistAdmin();

      // set value of $result
      if($count > 0){
         $result = true;
      } else {
         $result = false;
      }

      // return to Ajax request
      echo $result;
   }


   public function doesShowProducerExistAction()
   {
      // check if city in shows table
      $count = Show::doesShowProducerExistAdmin();

      // set value of $result
      if($count > 0){
         $result = true;
      } else {
         $result = false;
      }

      // return to Ajax request
      echo $result;
   }
}
