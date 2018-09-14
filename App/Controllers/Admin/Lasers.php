<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\User;
use \App\Models\Laser;
use \App\Models\Trseries;
use \App\Models\Gtoflx;
use \App\Models\Stingray;
use \App\Models\State;
use \App\Models\Show;

class Lasers extends \Core\Controller
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


    // = = = = Admin Functionality = = = = = = = = = = = = = = = //

    /**
     * display all records from trseries table
     *
     * @return Object  All TR SEries records
     */
    public function getTrseriesAction()
    {
        // get lasers data
        $trseries = Trseries::getAllLasers();

        // render view
        View::renderTemplate('Admin/Armalaser/Show/trseries.html', [
            'lasers' => $trseries
        ]);

    }


    /**
     * retrieve laser(s) by search criterion
     *
     * @return object  The matching laser(s)
     */
    public function searchTrseriesByModelAction()
    {
        // get laser model name from form
        $laser_model = ( isset($_REQUEST['laser_model'])  ) ? filter_var($_REQUEST['laser_model']): '';

        if($laser_model == '')
        {
            header("Location: /admin/lasers/get-trseries");
            exit();
        }

        // get laser details
        $lasers = Trseries::getLaser($laser_model);

        // test
        // echo '<pre>';
        // print_r($lasers);
        // echo '</pre>';
        //exit();

        // render view
        View::renderTemplate('Admin/Armalaser/Show/trseries.html', [
            'lasers'    => $lasers,
            'searched'  => $laser_model
        ]);
    }




    /**
     * displays form populated with record data by ID
     *
     * @return object  The record's data
     */
    public function editTrseriesAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // get laser details
        $laser = Trseries::getLaserById($id);

        // echo '<pre>';
        // print_r($laser);
        // echo '</pre>';
        // exit();

        // render view
        View::renderTemplate('Admin/Armalaser/Update/trseries.html', [
            'laser' => $laser
        ]);
    }




    /**
     * updates record in lasers table by ID
     *
     * @return boolean
     */
    public function updateTrseriesAction()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // update laser
        $result = Trseries::updateLaser($id);

        if($result)
        {
            echo '<script>alert("TR Series laser successfully updated!")</script>';
            echo '<script>window.location.href="/admin/lasers/get-trseries"</script>';
            exit();
        }
    }




    //  - - - - - -  new above - - - - - - - - - - - - - - - - - - - - - - - //








    /**
     * display all records from lasers table
     *
     * @return object  All the laser records
     */
    public function getLasersAction()
    {
        // get lasers data
        $lasers = Laser::getAllLasers();

        // render view
        View::renderTemplate('Admin/Show/lasers.html', [
            'lasers' => $lasers
        ]);

    }



    /**
     * retrieve laser(s) by search criterion
     *
     * @return object  The matching laser(s)
     */
    public function searchLasersByModelAction()
    {
        // get laser model name from form
        $laser_model = ( isset($_REQUEST['laser_model'])  ) ? filter_var($_REQUEST['laser_model']): '';

        if($laser_model == '')
        {
            header("Location: /admin/lasers/get-lasers");
            exit();
        }

        // get laser details
        $lasers = Laser::getLaser($laser_model);

        // test
        // echo '<pre>';
        // print_r($lasers);
        // echo '</pre>';
        //exit();

        // render view
        View::renderTemplate('Admin/Show/lasers.html', [
            'lasers'    => $lasers,
            'searched'  => $laser_model
        ]);
    }



    /**
     * displays form to add record to lasers table
     */
    public function addLaserAction()
    {
        // create arrays for drop-downs
        $laser_series = [
            'GTO FLX',
            'TR Series',
            'Stingray'
        ];
        $laser_colors = ['red', 'green'];
        $batteries = [
            'TR Series (battery id = 1)' => 1,
            'GTO/FLX (battery id = 1)'   => 1,
            'Stingray (battery id = 2)'  => 2
        ];
        $toolkits = [
          'TR Series (tool kit id = 9)' => 9,
          'GTO/FLX (tool kit id = 1)'   => 5,
          'Stingray (tool kit id = 2)'  => 6
        ];

        View::renderTemplate('Admin/Add/laser.html', [
            'laser_series'  => $laser_series,
            'laser_colors'  => $laser_colors,
            'batteries'     => $batteries,
            'toolkits'      => $toolkits
        ]);
    }



    /**
     * posts new record to lasers table
     *
     * @return boolean
     */
    public function postNewLaserAction()
    {
        // add new laser to lasers table
        $result = Laser::postNewLaser();

        if($result)
        {
            echo '<script>alert("Laser successfully added!")</script>';
            echo '<script>window.location.href="/admin/lasers/get-lasers"</script>';
            exit();
        }
    }



    /**
     * deletes record from lasers table by ID
     *
     * @return boolean
     */
    public function deleteLaserAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // delete laser
        $result = Laser::deleteLaser($id);

        if($result)
        {
            echo '<script>alert("Laser successfully deleted!")</script>';
            echo '<script>window.location.href="/admin/lasers/get-lasers"</script>';
            exit();
        }
    }



    /**
     * displays form populated with record data by ID
     *
     * @return object  The record's data
     */
    public function editLaserAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // get laser details
        $laser = Laser::getLaserById($id);

        // echo '<pre>';
        // print_r($laser);
        // echo '</pre>';
        // exit();

        // create arrays for drop-downs
        $laser_series = [
            'GTO FLX',
            'TR Series',
            'Stingray'
        ];
        $laser_colors = ['red', 'green'];

        // render view
        View::renderTemplate('Admin/Update/laser.html', [
            'laser'        => $laser,
            'laser_series' => $laser_series,
            'laser_colors' => $laser_colors
        ]);
    }

}
