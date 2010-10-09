<?php

ErebotUtils::incl('streams/timer.php');
ErebotUtils::incl('ifaces/timer.php');

class       ErebotTimer
implements  iErebotTimer
{
    protected $stream;
    protected $callback;
    protected $delay;
    protected $repeat;

    // Documented in the interface.
    public function __construct($callback, $delay, $repeat)
    {
        if (!is_callable($callback))
            throw new EErebotInvalidValue('Invalid callback');

        $this->callback = $callback;
        $this->delay    = strval($delay);
        $this->isRepeated($repeat);
        $this->stream   = FALSE;
    }

    public function __destruct()
    {
        if ($this->stream !== FALSE)
            fclose($this->stream);
    }

    // Documented in the interface.
    public function & getCallback()
    {
        return $this->callback;
    }

    // Documented in the interface.
    public function getDelay()
    {
        return $this->delay;
    }

    // Documented in the interface.
    public function isRepeated($repeat = NULL)
    {
        // If repeat = FALSE, then repeat = 1 (once)
        // If repeat = TRUE, then repeat = -1 (forever)
        if (is_bool($repeat))
            $repeat = (-intval($repeat)) * 2 + 1;
        // If repeat = NULL, return current value with no modification.
        // If repeat > 0, the timer will be triggered 'repeat' times.
        if (!is_int($repeat) && $repeat !== NULL)
            throw new EErebotInvalidValue('Invalid repetition');

        $res = $this->repeat;
        if ($repeat !== NULL)
            $this->repeat = $repeat;
        return $res;
    }

    // Documented in the interface.
    public function & getStream()
    {
        return $this->stream;
    }

    // Documented in the interface.
    public function reset()
    {
        if ($this->repeat > 0)
            $this->repeat--;
        else if (!$this->repeat)
            return;

        if ($this->stream !== FALSE)
            fclose($this->stream);
        $this->stream = fopen('timer://'.$this->delay, 'r');
    }

    // Documented in the interface.
    public function activate()
    {
        return (bool) call_user_func_array($this->callback, array(&$this));
    }

    public function __toString()
    {
        return (string) $this->stream;
    }
}

?>
