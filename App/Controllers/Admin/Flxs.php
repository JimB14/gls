<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\Flx;


class Flxs extends \Core\Controller
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
     * retrieves all flxs
     *
     * @return
     */
    public function getFlxsAction()
    {
        // get flx
        $flxs = Flx::getAllFlxs();

        View::renderTemplate('Admin/Show/flxs.html', [
            'flxs'      => $flxs,
            'pagetitle' => "Manage FLXs"
        ]);
    }





    /**
     * retrieves all flxs for product list for logged in Partner
     *
     * @return View
     */
    public function partnerGetFlxsAction()
    {
        // get flx
        $flxs = Flx::partnerGetAllFlx();

        // test
        // echo '<pre>';
        // print_r($flxs);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Partners/Show/flxs.html', [
            'flxs'      => $flxs,
            'pagetitle' => "FLXs - Partners"
        ]);
    }



    /**
     * retrieves flx records by search criterion
     *
     * @return object  The flx record(s)
     */
    public function searchFlxsByNameAction()
    {
        // get laser model name from form
        $laser_model = ( isset($_REQUEST['flx_model'])  ) ? filter_var($_REQUEST['flx_model']): '';

        if($laser_model == '')
        {
            header("Location: /admin/flxs/get-flxs");
            exit();
        }

        // get laser details
        $flxs = Flx::getFlxByModel($laser_model);

        // test
        // echo '<pre>';
        // print_r($flxs);
        // echo '</pre>';
        // exit();

        // render view
        View::renderTemplate('Admin/Show/flxs.html', [
            'flxs'      => $flxs,
            'searched'  => $laser_model
        ]);
    }



    /**
     * displays form to add flx to flx table
     */
    public function addFlxAction()
    {
        // create arrays for drop-downs
        $laser_series = [
            'GTO FLX',
            'TR Series',
            'Stingray'
        ];

        View::renderTemplate('Admin/Add/flx.html', [
            'laser_series' => $laser_series
        ]);
    }



    /**
     * adds new flx record to flx table
     *
     * @return boolean
     */
    public function postNewFlxAction()
    {
        // add new flx to flx table
        $result = Flx::postNewFlx();

        if($result)
        {
            echo '<script>alert("FLX successfully added!")</script>';
            echo '<script>window.location.href="/admin/flxs/get-flxs"</script>';
            exit();
        }
    }



    /**
     * deletes flx record from flx table by ID
     *
     * @return boolean
     */
    public function deleteFlxAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // delete laser
        $result = Flx::deleteFlx($id);

        if($result)
        {
            echo '<script>alert("FLX successfully deleted!")</script>';
            echo '<script>window.location.href="/admin/flxs/get-flxs"</script>';
            exit();
        }
    }



    /**
     * displays form populated with flx record to allow user to edit
     *
     * @return void
     */
    public function editFlxAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // get laser details
        $flx = Flx::getFlxById($id);

        // echo '<pre>';
        // print_r($flx);
        // echo '</pre>';
        // exit();

        // create arrays for drop-downs
        $laser_series = [
            'GTO FLX',
            'TR Series',
            'Stingray'
        ];

        // render view
        View::renderTemplate('Admin/Update/flx.html', [
            'flx'          => $flx,
            'laser_series' => $laser_series
        ]);
    }



    /**
     * updates flx record in flx table by ID
     *
     * @return boolean
     */
    public function updateFlxAction()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // update flx
        $result = Flx::updateFlx($id);

        if($result)
        {
            echo '<script>alert("FLX successfully updated!")</script>';
            echo '<script>window.location.href="/admin/flxs/get-flxs"</script>';
            exit();
        }
    }
}
