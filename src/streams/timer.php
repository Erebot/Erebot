<?php
/*
    This file is part of Erebot.

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

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
    protected   $_process;

    public function stream_cast($castAs)
    {
        return $this->_process;
    }

    public function stream_close()
    {
        if ($this->_process === NULL)
            return FALSE;

        pclose($this->_process);
        $this->_process = NULL;
        return TRUE;
    }

    public function stream_open($path, $mode, $options, &$openedPath)
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
        $this->_process = popen($command, 'r');

        $openedPath = $url['scheme'].'://'.$url['host'];
        return (is_resource($this->_process));
    }
}

if (!in_array("timer", stream_get_wrappers())) {
    stream_wrapper_register('timer', 'ErebotWrapperTimer');
}

