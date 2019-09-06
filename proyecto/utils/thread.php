<?php // utils/thread.php

class Threaded
{
    public __construct() { }
    public run() { }
    public synchronized() { }
}

class Volatile
{
    public __construct() { }
    public run() { }
    public synchronized() { }
}

class Pool
{
    private $num_process;
    private $process = array();

    public __construct($num_process)
    {
        $this->$num_process = $num_process;
    }

    public submit($p)
    {
        array_push($this->process, $p);
    }

    public collect()
    {
        foreach ($this->process as $k => $v)
        {
            $v->run();
        }
    }
}
