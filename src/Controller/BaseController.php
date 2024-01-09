<?php

namespace Starfruit\HelperBundle\Controller;

use Starfruit\HelperBundle\Service\IPService;

class BaseController extends \Pimcore\Controller\FrontendController
{
    public function allowAdminer()
    {
        $whitelistIP = isset($_ENV['STARFRUIT_ADMINER_WHITELIST_IPS']) ? $_ENV['STARFRUIT_ADMINER_WHITELIST_IPS'] : null;

        if (!$whitelistIP) {
            return false;
        }

        $myIP = IPService::getIP();
        $whitelistIP = str_replace(' ', '', $whitelistIP);
        $whitelistIPs = explode(',', $whitelistIP);

        return in_array($myIP, $whitelistIPs);
    }
}
