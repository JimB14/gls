<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Accessory;


class Targets extends \Core\Controller
{
    public function index()
    {
        // get target data from accessories table using known IDs
        $targets = Accessory::getTargets();

        // test
        // echo '<pre>';
        // print_r($targets);
        // echo '</pre>';
        // exit();

        // initialize variables
        $bullseyeID;
        $silhouetteID;

        // get IDs for each target
        foreach($targets as $target)
        {
            if($target->id == 6001)
            {
                $bullseyeID = $target->id;
            }
            if($target->id == 6002)
            {
                $silhouetteID = $target->id;
            }
        }

        // test
        // echo '<pre>';
        // print_r($targets);
        // echo '</pre>';

        View::renderTemplate('Targets/index.html', [
            'targets'      => $targets,
            'bullseyeID'   => $bullseyeID,
            'silhouetteID' => $silhouetteID,
            'activemenu'   => 'targets',
            'canonURL'     => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }
}
