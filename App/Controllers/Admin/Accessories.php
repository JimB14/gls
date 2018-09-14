<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\Flx;
use \App\Models\Accessory;
use \App\Models\Battery;
use \App\Models\Toolkit;


class Accessories extends \Core\Controller
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
     * Displays all accessories
     *
     * @return
     */
    public function getAccessoriesAction()
    {
        // get accessories
        $accessories = Accessory::getAllAccessories();

        $pagetitle = "Manage accessories";

        View::renderTemplate('Admin/Show/accessories.html', [
            'accessories' => $accessories,
            'pagetitle'   => $pagetitle
        ]);
    }



    /**
     * retrieves accessory records by search criterion
     *
     * @return object The matching accessories records
     */
    public function searchAccessoriesByNameAction()
    {
        // get laser model name from form
        $name = ( isset($_REQUEST['name'])  ) ? filter_var($_REQUEST['name']): '';

        if($name == '')
        {
            header("Location: /admin/accessories/get-accessories");
            exit();
        }

        // get accessory details
        $accessories = Accessory::getAccessoryByName($name);

        // test
        // echo '<pre>';
        // print_r($accessory);
        // echo '</pre>';
        //exit();

        // render view
        View::renderTemplate('Admin/Show/accessories.html', [
            'accessories' => $accessories,
            'searched'    => $name
        ]);
    }



    /**
     * displays form to add accessory item to accessories table
     */
    public function addAccessoryAction()
    {
        // create array (for accessories.name) for drop-down
        $laser_series = [
            'GTO FLX'     => 'gto',
            'TR Series'   => 'tr',
            'Stingray'    => 'stingray',
            'TR & GTO'    => 'trgto',
            'All series'  => 'all'
        ];

        View::renderTemplate('Admin/Add/accessory.html', [
            'laser_series' => $laser_series
        ]);
    }



    /**
     * posts new record to accessories table
     *
     * @return boolean
     */
    public function postNewAccessoryAction()
    {
        // add new flx to flx table
        $result = Accessory::postNewAccessory();

        if($result)
        {
            echo '<script>alert("Accessory item successfully added!")</script>';
            echo '<script>window.location.href="/admin/accessories/get-accessories"</script>';
            exit();
        }
    }



    /**
     * deletes accessory record by ID
     *
     * @return boolean
     */
    public function deleteAccessoryAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // delete laser
        $result = Accessory::deleteAccessory($id);

        if($result)
        {
            echo '<script>alert("Accessory item successfully deleted!")</script>';
            echo '<script>window.location.href="/admin/accessories/get-accessories"</script>';
            exit();
        }
    }



    /**
     * displays populated form to allow user to edit accessory record
     *
     * @return object The accessory record
     */
    public function editAccessoryAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // get laser details
        $accessory = Accessory::getAccessoryById($id);

        // test
        // echo '<pre>';
        // print_r($accessory);
        // echo '</pre>';
        // exit();

        // create array (for accessories.name) for drop-down
        $laser_series = [
            'GTO FLX'     => 'gto',
            'TR Series'   => 'tr',
            'Stingray'    => 'stingray',
            'TR & GTO'    => 'trgto',
            'All series'  => 'all'
        ];

        // render view
        View::renderTemplate('Admin/Update/accessory.html', [
            'accessory'    => $accessory,
            'laser_series' => $laser_series
        ]);
    }



    /**
     * updates accessory record in accessories table
     *
     * @return boolean
     */
    public function updateAccessoryAction()
    {
        // update accessory
        $result = Accessory::updateAccessory();

        if($result)
        {
            echo '<script>alert("Accessory item successfully updated!")</script>';
            echo '<script>window.location.href="/admin/accessories/get-accessories"</script>';
            exit();
        }
    }




    // = = = = =  Logged in Dealer  = = = = = = = = //


    // = = = = =  Logged in Partner  = = = = = = = = //
    /**
     * retrieves batteries for product list for Dealers
     *
     * @return
     */
    public function partnerGetBatteriesAction()
    {
        // get batteries
        $batteries = Battery::getBatteries();

        // test
        // echo '<pre>';
        // print_r($batteries);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Partners/Show/batteries.html', [
            'batteries' => $batteries,
            'pagetitle' => 'Batteries - Partners'
        ]);
    }


    /**
     * retrieves tool kits for product list for Partners
     *
     * @return
     */
    public function partnerGetToolkitsAction()
    {
        // get tool kits
        $toolkits = Toolkit::getToolkits();

        // test
        // echo '<pre>';
        // print_r($toolkits);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Partners/Show/toolkits.html', [
            'toolkits'  => $toolkits,
            'pagetitle' => 'Tool Kits - Partners'
        ]);
    }




    /**
     * retrieves accessories for product list for Partners
     *
     * @return
     */
    public function partnerGetAccessoriesAction()
    {
        // get accessories
        $accessories = Accessory::getAccessories();

        // test
        // echo '<pre>';
        // print_r($accessories);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Partners/Show/accessories.html', [
            'accessories' => $accessories,
            'pagetitle'   => 'Accessories - Partners'
        ]);
    }



}
