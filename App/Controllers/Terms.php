<?php

namespace App\Controllers;

use \Core\View;


class Terms extends \Core\Controller
{
    /**
     * Displays terms of use
     *
     * @return void
     */
    public function termsOfUse()
    {
        View::renderTemplate('Terms/terms-of-use.html', [
            'pagetitle' => 'Terms of Use',
            'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }



    /**
     * Displays privacy policy
     *
     * @return void
     */
    public function privacyPolicy()
    {
        View::renderTemplate('Terms/privacy-policy.html', [
            'pagetitle' => 'Privacy Policy',
            'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }
}
