<?php
/**
 * Created by PhpStorm.
 * User: Tomoyo
 * Date: 1/26/2015
 * Time: 午後 04:20
 */

class utils
{
    public static $auth = false;

    public static function send401 (\Phalcon\Http\Response $response)
    {
        $response->setStatusCode(401, "Unauthorized");
        $response->setJsonContent(
            array(
                'state' => 'error',
                'code' => 401,
                'message' => 'Your request is missing the API credentials required to authenticate you, or you provided invalid credentials.',
                'data' => false,
            ));
        if(!$response->isSent())
            $response->send();
    }

    public static function send400 (\Phalcon\Http\Response $response)
    {
        $response->setStatusCode(400, "Malformed request");
        $response->setJsonContent(
            array(
                'state' => 'error',
                'code' => 400,
                'message' => 'Your request contains malformed parameters.',
                'data' => false,
            ));
        if(!$response->isSent())
            $response->send();
    }

    public static function send404 (\Phalcon\Http\Response $response)
    {
        $response->setStatusCode(404, "Not found");
        $response->setJsonContent(
            array(
                'state' => 'error',
                'code' => 404,
                'message' => 'Your\'re looking for something that isn\'t here.',
                'data' => false,
            ));
        if(!$response->isSent())
            $response->send();
    }

    public static function send403 (\Phalcon\Http\Response $response)
    {
        $response->setStatusCode(403, "Forbidden");
        $response->setJsonContent(
            array(
                'state' => 'error',
                'code' => 403,
                'message' => 'You\'re trying to access a resource you\'re not permitted to.',
                'data' => false,
            ));
        if(!$response->isSent())
            $response->send();
    }

    public static function authCheck (Phalcon\Db\Adapter\Pdo\Sqlite $db, Phalcon\Http\Request $request, Phalcon\Http\Response $response)
    {
        if($request->getServer('PHP_AUTH_USER'))
        {
            $apiKey = $request->getServer('PHP_AUTH_USER');
            $res = $db->fetchOne('SELECT * FROM `api` WHERE `key` = :key LIMIT 1', Phalcon\Db::FETCH_ASSOC, array('key' => $apiKey));
            if(!$res)
            {
                self::send401($response);
            }
            else
            {
                self::$auth = true;
            }
        }
        else
        {
            self::send401($response);
        }
    }

    public static function validCIDR ($ip, $cidr)
    {
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