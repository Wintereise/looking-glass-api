<?php
/**
 * Created by: Tomoyo
 * Date: 14/07/17
 * Time: 21:17
 */

class shell
{
    private $output = "";
    private $stream = false;
    private $closure;

    public function __construct ($enableStreaming = false, $closure = null)
    {
        if($enableStreaming == true)
        {
            $this->stream = true;
        }
        if(function_exists($closure))
        {
            $this->closure = $closure;
        }
    }

    public function getBufferedOutput ()
    {
        if($this->stream == true)
        {
            return false;
        }
        else
        {
            return substr($this->output, 0, -1);
        }
    }

    public function handleOutput ($data)
    {
        return call_user_func($this->closure, $data);
    }

    public function execute ($cmd, $args, $host, $failCount = 3)
    {
        $runnableCommand = $this->assembleCmd($cmd, $args);

        // define output pipes
        $spec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        // sanitize + remove single quotes
        $host = str_replace('\'', '', filter_var($host, FILTER_SANITIZE_URL));


        // execute command
        $process = proc_open("{$runnableCommand} '{$host}'", $spec, $pipes, null);

        // check pipe exists
        if (!is_resource($process))
        {
            return false;
        }

        // check for known apps
        $type = $this->determineType($cmd);


        $fail = 0;
        $traceCount = 0;
        $lastFail = 'start';

        // iterate stdout
        while (($str = fgets($pipes[1], 1024)) != null)
        {
            $str = trim($str);

            // correct output for traceroute
            if ($type === 'traceroute')
            {
                // check for consecutive failed hops
                if (strpos($str, '* * *') !== false)
                {
                    $fail++;
                    if ($lastFail !== 'start'
                        && ($traceCount - 1) === $lastFail
                        && $fail >= $failCount)
                    {
                        if($this->stream == true)
                        {
                            echo "--- Traceroute timed out. ---\n";
                        }
                        else
                        {
                            $this->output .= "--- Traceroute timed out. ---\n";
                        }
                        break;
                    }
                    $lastFail = $traceCount;
                }
                $traceCount++;
            }

            // pad string for live output
            if($this->stream == true)
            {
                $this->handleOutput(sprintf("%s\n", $str));
            }
            else
            {
                $this->output .= sprintf("%s\n", $str);
            }
        }
        $status = proc_get_status($process);
        if ($status['running'] == true)
        {
            // close pipes that are still open
            foreach ($pipes as $pipe)
            {
                fclose($pipe);
            }

            // retrieve parent pid
            $ppid = $status['pid'];

            // use ps to get all the children of this process
            $pids = preg_split('/\s+/', `ps -o pid --no-heading --ppid $ppid`);

            // kill remaining processes
            foreach($pids as $pid)
            {
                if (is_numeric($pid))
                {
                    posix_kill($pid, 9);
                }
            }
            proc_close($process);
        }
        return true;
    }

    public function assembleCmd ($cmd, $args)
    {
        if(!is_null($args))
        {
            return sprintf("%s %s", escapeshellcmd($cmd), escapeshellarg($args));
        }
        else
        {
            return $cmd;
        }
    }

    public function determineType ($cmd)
    {
        switch ($cmd)
        {
            case strpos($cmd, 'traceroute'):
                $type = 'traceroute';
                break;
            case strpos($cmd, 'ping'):
                $type = 'ping';
                break;
            case strpos($cmd, 'sflowtool'):
                $type = 'sflow';
                break;
            case strpos($cmd, 'netflow'):
                $type = 'netflow';
                break;
            default:
                $type = 'generic';
        }
        return $type;
    }

}