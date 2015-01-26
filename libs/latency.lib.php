<?php
/**
 * Created by: Tomoyo
 * Date: 14/07/17
 * Time: 18:25
 */

require_once('vendor/autoload.php');

class latency
{

    private $input, $platform, $host, $packetSize, $interface;

    public function __construct ($host, $packetSize = null, $interface = null)
    {
        $this->platform = php_uname('r');
        $this->host = $host;
        $this->packetSize = $packetSize;
        $this->interface = $interface;
    }

    public function executePing ()
    {
        try
        {
            $command = $this->generateCommandArray($this->host, $this->packetSize, $this->interface);
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
            exit();
        }
        $shell = new shell(false, false);
        if($shell->execute($this->getCommand($command), $this->getArguments($command), $this->host))
        {
            $this->input = $shell->getBufferedOutput();
        }
        else
        {
            throw new Exception("Failed to run the actual command.");
        }
        return $this->parseInetUtilsPing();
    }

    public function parseInetUtilsPing ()
    {
        $array = explode("\n", $this->input);
        $raw = explode("/", explode("=", $array[count($array) - 1])[1]);
        for ($i = 0; $i < count($raw); $i++)
        {
            $raw[$i] = floatval($raw[$i]);
        }
        // 0 is min, 1 is avg, 2 is max, 3 is stdev
        return json_encode($raw);
    }

    public function getCommand ($array)
    {
        return $array['command'];
    }

    public function getArguments ($array)
    {
        $args = null;
        for($i = 0; $i < (count($array) -1); $i++)
        {
            if ($i == 0)
            {
                $args = $array[$i];
            }
            else
            {
                $args .= sprintf(" %s", $array[$i]);
            }
        }
        return $args;
    }

    public function generateCommandArray ($host, $packetSize = null, $interface = null)
    {
        $host = gethostbyname($host);
        $argCount = 0;
        $storage = array();
        if(filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE))
        {
            $storage['command'] = 'ping';
        }
        elseif(filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
        {
            $storage['command'] = 'ping -6';
        }
        else
        {
            throw new Exception("Invalid host");
        }
        if(!is_null($packetSize) || !is_null($interface))
        {
            if(is_numeric($packetSize) && $packetSize < 1501)
            {
                $storage[$argCount] = sprintf('-s %d', $packetSize);
                $argCount++;
            }
            if(strlen($interface >= 2))
            {
                $storage[$argCount] = sprintf('-I %s', $interface);
                $argCount++;
            }
        }
        $storage[$argCount] = '-c 10';
        return $storage;
    }
}