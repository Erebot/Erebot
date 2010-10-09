<?php

/**
 * \brief
 *      A stream wrapper for timers.
 *
 *  Provides asynchronous timers. The timers are created as
 *  subprocess which run the "sleep" command (or equivalent)
 *  for the given duration before exiting.
 *  On the PHP side, the wrapper allows scripts to wait for
 *  the timer to time out using stream_select() on the timer's
 *  descriptor.
 */
class ErebotWrapperTimer
{
    protected   $process;
    protected   $pipes;

    public function stream_cast($cast_as)
    {
        return $this->process;
    }

    public function stream_close()
    {
        if ($this->process === NULL)
            return FALSE;

        pclose($this->process);
        $this->process = NULL;
        return TRUE;
    }

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = @parse_url($path);
        if ($url === FALSE)
            return FALSE;

        if (!isset($url['scheme'], $url['host']))
            return FALSE;

        $delay = (double) $url['host'];

        # "Windows Server 2003 Resource Kit Tools" is needed under Windows.
        # Grab it from: http://www.microsoft.com/downloads/details.aspx
        #               ?FamilyID=9d467a69-57ff-4ae7-96ee-b18c4790cffd
        if (!strncasecmp(PHP_OS, 'WIN', 3))
            $command    = 'start /B sleep.exe -m '.($delay * 1000);
        else
            $command    = 'sleep '.$delay.' &';
        $this->process = popen($command, 'r');

        $opened_path = $url['scheme'].'://'.$url['host'];
        return (is_resource($this->process));
    }
}

if (!in_array("timer", stream_get_wrappers()))
    stream_wrapper_register('timer', 'ErebotWrapperTimer');

?>
