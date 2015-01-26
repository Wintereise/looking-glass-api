<?php
/**
 * Created by PhpStorm.
 * User: Tomoyo
 * Date: 1/26/2015
 * Time: 午後 01:15
 */

require_once('../libs/shell.lib.php');

$config = array(
    "dbname" => "../db/db.sqlite"
);

$app = new \Phalcon\Mvc\Micro();
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
            send401($response);
        }
    }
    else
    {
        send401($response);
    }
});

$app->get('/v1/api/{task}/{target}', function($task, $target) use ($app, $response, $shell)
{
    switch ($task)
    {
        case 'traceroute':
            $shell->execute('traceroute', '-w 2 -A', $target);
            $response->setJsonContent(array(
                'state' => 'ok',
                'code' => 200,
                'message' => 'The trace was successfully performed.',
                'data' => $shell->getBufferedOutput(),
            ));
        break;
    }
    return $response;
});

$app->handle();

function send401 ($response)
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

function parse ($data)
{
    echo $data;
}