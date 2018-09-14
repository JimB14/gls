<?php

namespace App\Controllers;

use \Core\View;


class FAQ extends \Core\Controller
{
    public function indexAction()
    {
        View::renderTemplate('FAQ/index.html', [
            'pagetitle' => 'Frequently Asked Questions',
            'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }
}
