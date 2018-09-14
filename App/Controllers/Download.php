<?php

namespace App\Controllers;

use \Core\View;


class Download extends \Core\Controller
{
    public function armalaserCatalog()
    {
        View::renderTemplate('Download/2018-catalog.html', [
            'pagetitle' => '2018 ArmaLaser Catalog'
        ]);
    }
}
