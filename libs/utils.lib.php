<?php
/**
 * Created by PhpStorm.
 * User: Tomoyo
 * Date: 1/26/2015
 * Time: 午後 04:20
 */

class utils
{
    public static function send401 (\Phalcon\Http\Response $response)
    {
        $response->setJsonContent(
            array(
                'state' => 'error',
                'code' => 401,
                'message' => 'Your request is missing the API credentials required to authenticate you, or you provided invalid credentials.',
                'data' => false,
            ));
        $response->send();
    }

    public static function send400 (\Phalcon\Http\Response $response)
    {
        $response->setJsonContent(
            array(
                'state' => 'error',
                'code' => 400,
                'message' => 'Your request contains malformed parameters.',
                'data' => false,
            ));
        $response->send();
    }

    public static function validCIDR ($cidr)
    {
        list($ip, $mask) = explode('/', $cidr);
        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
        {
            if((int) $cidr > 0 && (int) $cidr <= 32)
                return true;
        }
        elseif(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
        {
            if((int) $cidr > 0 && (int) $cidr <= 128)
                return true;
        }
        else
            return false;
    }
}