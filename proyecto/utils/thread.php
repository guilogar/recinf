<?php // utils/thread.php

class Threaded
{
    public function __construct() { }
    public function run() { }
    public function synchronized() { }
}

class Volatile
{
    public function __construct() { }
    public function run() { }
    public function synchronized() { }
}

class Pool
{
    private $num_process;
    private $process = array();

    public function __construct($num_process)
    {
        $this->$num_process = $num_process;
    }

    public function submit($p)
    {
        array_push($this->process, $p);
    }

    public function collect()
    {
        foreach ($this->process as $k => $v)
        {
            $v->run();
        }
    }

    public function shutdown()
    {
        $this->process = array();
    }
}

function stemword($word = "", $language = "english", $codification = "UTF_8")
{
    if($word === "") return "";

    return $word;
}
