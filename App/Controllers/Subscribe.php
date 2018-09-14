<?php

namespace App\Controllers;

use \Core\View;


class Subscribe extends \Core\Controller
{
    public function indexAction()
    {
        View::renderTemplate('Subscribe/index.html', [
            'pagetitle' => 'Subscribe',
            'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }
}
