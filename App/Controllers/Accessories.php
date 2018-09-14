<?php

namespace App\Controllers;

use \App\Models\Accessory;
use \App\Models\Battery;
use \App\Models\Toolkit;
use \Core\View;


class Accessories extends \Core\Controller
{

    /**
     * retrieves battery record by ID and displays view
     * @return View         the show battery page
     */
    public function showBattery()
    {
        // retrieve params
        $id = $this->route_params['id'];
        // $slug = $this->route_params['slug'];

        // get battery
        $battery = Battery::getBattery($id);

        // test
        // echo '<pre>';
        // print_r($battery);
        // echo '</pre>';
        // exit();

        // render view
        View::renderTemplate('ProductDetails/battery.html', [
            'battery'  => $battery,
            'canonURL' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }



    /**
     * retrieves toolkit record by ID and displays view
     * @return Views    the show toolkit page
     */
    public function showToolkit()
    {
        // retrieve params
        $id = $this->route_params['id'];
        // $slug = $this->route_params['slug'];

        // get battery
        $toolkit = Toolkit::getToolkit($id);

        // test
        // echo '<pre>';
        // print_r($toolkit);
        // echo '</pre>';
        // exit();

        // render view
        View::renderTemplate('ProductDetails/toolkit.html', [
            'toolkit'  => $toolkit,
            'canonURL' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }



    /**
     * retrieves accessory item record by ID and displays view
     * @return Views    the show accessory page
     */
    public function showAccessory()
    {
        // echo "Connected to showAccessory() in Accessories Controller!";

        // retrieve params
        $id = $this->route_params['id'];
        // $slug = $this->route_params['slug'];

        // get accessory item
        $item = Accessory::getAccessory($id);

        // test
        // echo '<pre>';
        // print_r($item);
        // echo '</pre>';
        // exit();

        // render view
        View::renderTemplate('ProductDetails/accessory.html', [
            'item'     => $item,
            'canonURL' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);

    }



    /**
     * retrieves all records from batteries table and displays in view
     * @return [type] [description]
     */
    public function getBatteries()
    {
        $batteries = Battery::getBatteries();

        // render view
        View::renderTemplate('Accessories/batteries.html', [
            'batteries'     => $batteries,
            'canonURL' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }



    /**
     * retrieves all records from batteries table and displays in view
     * @return [type] [description]
     */
    public function getToolkits()
    {
        $toolkits = Toolkit::getToolkits();

        // render view
        View::renderTemplate('Accessories/laser-parts.html', [
            'toolkits' => $toolkits,
            'canonURL' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }



    /**
     * retrieve data and display accessories page
     * @return Views    the accessories page
     */
    public function indexAction()
    {

        // get accessories; filter by brand products not empty
        $accessories = Accessory::getAccessories();

        // test
        // echo '<pre>';
        // print_r($accessories);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Accessories/index.html', [
            'accessories' => $accessories,
            'activemenu'  => 'accessories'
        ]);
    }

// = = = = =  refactored updates above  = = = = = = = = = = = = = = = = = = = //

}
