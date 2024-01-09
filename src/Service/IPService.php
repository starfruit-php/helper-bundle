<?php

namespace Starfruit\HelperBundle\Service;

class IPService
{
    public static function getIP()
    {
        try {
            $url = "ipv4.icanhazip.com";

            $client = new \GuzzleHttp\Client();
            $call = $client->request("GET", $url, []);

            $ip_address = $call->getBody()->getContents();
            $ip_address = str_replace("\n", "", $ip_address);

            return $ip_address;
        } catch (\Throwable $e) {
        }
        
        return '';
    }
}
