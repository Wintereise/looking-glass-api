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
$shell = new shell(false);
$res = false;

$app->before(function() use ($db, $request, $response)
{
    utils::authCheck($db, $request, $response);
});

$app->get('/api/v1/{task}/{target}[/]?{mask}', function($task, $target, $mask = null) use ($app, $db, $response, $request, $shell)
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
                $uuid = uniqid();
                $db->insert("streams", array($uuid, ip2long($target), "ipv4"), array('uuid', 'target', 'type'));
                $response->setJsonContent(array(
                    'state' => 'ok',
                    'code' => 200,
                    'timestamp' => time(),
                    'message' => 'The stream object has been successfully created.',
                    'data' => $uuid,
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
                $uuid = uniqid();
                $db->insert("streams", array($uuid, ip2long($target), "ipv6"), array('uuid', 'target', 'type'));
                $response->setJsonContent(array(
                    'state' => 'ok',
                    'code' => 200,
                    'timestamp' => time(),
                    'message' => 'The stream object has been successfully created.',
                    'data' => $uuid,
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

$app->get('/api/v1/stream/{uuid}', function($uuid) use ($app, $db, $response, $res)
{
    //needed because we're not using Phalcon\HTTP\Request() for this route
    if (utils::$auth)
    {
        $data = $db->fetchOne("SELECT * FROM `streams` WHERE `uuid` = :uuid LIMIT 1", Phalcon\Db::FETCH_ASSOC, array('uuid' => $uuid));
        if (!$data)
            utils::send403($response);
        else {
            $ip = long2ip($data['target']);
            $type = $data['type'];
            $init = new shell(true);
            switch ($type) {
                case 'ipv4':
                    $init->execute('traceroute -w 1', '-A', $ip);
                    break;
                case 'ipv6':
                    $init->execute('traceroute6 -w 1', '-A', $ip);
                    break;
            }
            $db->execute("DELETE FROM `streams` WHERE `uuid` = ?", array($uuid));
        }
    }
});


$app->put('/api/v1/update-key/{key}', function($key) use ($app, $response, $db)
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

$app->notFound(function() use ($response)
{
    utils::send404($response);
});

function parse($data)
{
    echo $data;
}
$app->handle();