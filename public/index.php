<?php
/**
 * Created by PhpStorm.
 * User: Tomoyo
 * Date: 1/26/2015
 * Time: 午後 01:15
 */

require_once('../libs/shell.lib.php');
require_once('../libs/utils.lib.php');

$config = array(
    "dbname" => "../db/db.sqlite"
);

$app = new Phalcon\Mvc\Micro();
$db = new Phalcon\Db\Adapter\Pdo\Sqlite($config);
$request = new Phalcon\Http\Request();
$response = new Phalcon\Http\Response();
$shell = new shell(false, $f = 'parse');

$app->before(function() use ($app, $db, $request, $response)
{
    if($request->getServer('PHP_AUTH_USER'))
    {
        $apiKey = $request->getServer('PHP_AUTH_USER');
        $res = $db->fetchOne('SELECT * FROM `api` WHERE `key` = :key LIMIT 1', Phalcon\Db::FETCH_ASSOC, array('key' => $apiKey));
        if(!$res)
        {
            utils::send401($response);
        }
    }
    else
    {
        utils::send401($response);
    }
});

$app->get('/v1/api/{task}/{target}[/]?{mask}', function($task, $target, $mask = null) use ($app, $response, $shell)
{
    $target = trim($target);
    switch ($task)
    {
        case 'ping':
            if(filter_var($target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
            {
                $shell->execute('ping', '-c 3', $target);
                $response->setJsonContent(array(
                    'state' => 'ok',
                    'code' => 200,
                    'timestamp' => time(),
                    'message' => 'The ping was successfully performed.',
                    'data' => $shell->getBufferedOutput(),
                ));
            }
            else
            {
                utils::send400($response);
            }
        break;

        case 'traceroute':
            if(filter_var($target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
            {
                $shell->execute('traceroute', '-w 2 -A', $target);
                $response->setJsonContent(array(
                    'state' => 'ok',
                    'code' => 200,
                    'timestamp' => time(),
                    'message' => 'The trace was successfully performed.',
                    'data' => $shell->getBufferedOutput(),
                ));
            }
            else
            {
                utils::send400($response);
            }
        break;

        case 'ping6':
            if(filter_var($target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
            {
                $shell->execute('ping6', '-c 3', $target);
                $response->setJsonContent(array(
                    'state' => 'ok',
                    'code' => 200,
                    'timestamp' => time(),
                    'message' => 'The ping was successfully performed.',
                    'data' => $shell->getBufferedOutput(),
                ));
            }
            else
            {
                utils::send400($response);
            }
        break;

        case 'traceroute6':
            if(filter_var($target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
            {
                $shell->execute('traceroute6', '-w 2 -A', $target);
                $response->setJsonContent(array(
                    'state' => 'ok',
                    'code' => 200,
                    'timestamp' => time(),
                    'message' => 'The trace was successfully performed.',
                    'data' => $shell->getBufferedOutput(),
                ));
            }
            else
            {
                utils::send400($response);
            }
        break;

        case 'bgp':
            if(utils::validCIDR($target, $mask))
            {
                $shell->execute('bgpctl', 'show ip bgp', sprintf("%s/%s", $target, $mask));
                $response->setJsonContent(array(
                    'state' => 'ok',
                    'code' => 200,
                    'timestamp' => time(),
                    'message' => 'The BGP table lookup was successfully performed.',
                    'data' => $shell->getBufferedOutput(),
                ));
            }
            else
            {
                utils::send400($response);
            }
        break;

        default:
            utils::send404($response);
        break;
    }
    return $response;
});

$app->put('/v1/api/update-key/{key}', function($key) use ($app, $response, $db)
{
    if(strlen($key) != 64)
    {
        utils::send400($response);
    }
    else
    {
        $db->update("api", array("key"), array($key));
        $response->setJsonContent(array(
            'state' => 'ok',
            'code' => 200,
            'timestamp' => time(),
            'message' => 'The API key was successfully updated.',
            'data' => $key,
        ));
    }
    return $response;
});

$app->notFound(function() use ($response){
    utils::send404($response);
});

$app->handle();