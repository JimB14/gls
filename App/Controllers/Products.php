<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Pistolbrand;
use \App\Models\Trseries;
use \App\Models\Gtoflx;
use \App\Models\Stingray;
use \App\Models\Holster;
use \App\Models\Accessory;
use \App\Models\Battery;
use \App\Models\Toolkit;


class Products extends \Core\Controller
{

    /**
    * Retrieves products by brand name
    *
    * @return objects  The products by categories for this brand
    */
    public function showProductsByPistolMfr()
    {
        // echo "Connected to showProductsByPistolMfr() in Products controller"; exit();

        // get param from URL (see front controller #77)
        $name = $this->route_params['manufacturer'];

        // test
        // echo $name . '<br>';
        // exit();

        // get pistol brand ID
        $id = Pistolbrand::getBrandId($name);

        // test
        // echo $id;
        // exit();

        // find TR series matches for this brand
        $trseries = Trseries::getLasersByBrand($id);

        // test
        // echo '<pre>';
        // print_r($trseries);
        // echo '</pre>';
        // exit();

        // find GTO-FLX series matches for this brand
        $gtoflx = Gtoflx::getLasersByBrand($id);

        // test
        // echo '<pre>';
        // print_r($gtoflx);
        // echo '</pre>';
        // exit();

        // get stingray record for selected pistol brand
        $stingray = Stingray::getLasersByBrand($id);

        // test
        // echo '<pre>';
        // print_r($stingray);
        // echo '</pre>';
        // exit();

        $holsters = Holster::getHolstersByBrand($id);

        // test
        // echo '<pre>';
        // print_r($holsters);
        // echo '</pre>';
        // exit();

        // get batteries
        $batteries = Battery::getBatteries();

        // test
        // echo '<pre>';
        // print_r($batteries);
        // echo '</pre>';
        // exit();

        // get toolkits
        $toolkits = Toolkit::getToolkits();

        // test
        // echo '<pre>';
        // print_r($toolkits);
        // echo '</pre>';
        // exit();

        // get accessories (targets, hat, glove contacts)
        $accessories = Accessory::getAccessories();

        // test
        // echo '<pre>';
        // print_r($accessories);
        // echo '</pre>';
        // exit();

        $holster_title01 = 'LASER-FIT™ HOLSTERS';

        // render view
        View::renderTemplate('ProductsByBrand/index.html', [
            'pistolMfr'       => $name,
            'trseries'        => $trseries,
            'gtoseries'       => $gtoflx,
            'stingray'        => $stingray,
            'holsters'        => $holsters,
            'batteries'       => $batteries,
            'toolkits'        => $toolkits,
            'accessories'     => $accessories,
            'holster_title01' => $holster_title01,
            'canonURL'        => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }


}
