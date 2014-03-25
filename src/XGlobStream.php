<?php
/*
    This file is part of Erebot, a modular IRC bot written in PHP.

    Copyright © 2010 François Poirotte

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

// @codingStandardsIgnoreFile
namespace Erebot;

/**
 * \brief
 *      A stream wrapper which performs a glob
 *      on files and returns the content of all
 *      matching files, wrapped in XML boilerplate.
 *
 *  Provides a PHP stream wrapper for files with a glob-like feature.
 */
class XGlobStream extends \Erebot\StreamWrapperBase
{
    /// Stream context, set automatically by PHP.
    public $context;

    /// Current position in the stream.
    protected $position;

    /// Content of the stream.
    protected $content;

    /// The XML namespace the content will be wrapped into.
    const XMLNS = 'http://www.erebot.net/xmlns/xglob';
    /// The XML tag used to wrap the content.
    const TAG   = 'wrapping';

    public function stream_tell()
    {
        return $this->position;
    }

    public function stream_eof()
    {
        return ($this->position >= strlen($this->content));
    }

    public function stream_close()
    {
        $this->position = 0;
        $this->content = '';
    }

    public function stream_open($path, $mode, $options, &$openedPath)
    {
        $this->position = 0;
        $pos = strpos($path, '://');
        if ($pos === false) {
            return false;
        }

        $path   = substr($path, $pos + 3);

        if (strlen($path) < 1) {
            return false;
        }

        if (in_array($path[0], array("/", DIRECTORY_SEPARATOR))) {
            $abs = '';
        } elseif (!strncasecmp(PHP_OS, "Win", 3) && strlen($path) > 1 && $path[1] == ':') {
            $abs = '';
        } else {
            $abs = getcwd() . DIRECTORY_SEPARATOR;
        }

        $abs .= str_replace("/", DIRECTORY_SEPARATOR, $path);
        $matches    = glob($abs, 0);
        if ($matches === false) {
            $matches = array();
        }

        $content    = '<xglob:'.self::TAG.' xmlns:xglob="'.self::XMLNS.'">';
        foreach ($matches as $absname) {
            if (!is_file($absname)) {
                continue;
            }
            $cnt = file_get_contents($absname);
            if ($cnt !== false) {
                $content .= $cnt;
            }
        }
        $content       .= '</xglob:'.self::TAG.'>';
        $this->content  = $content;
        return true;
    }

    public function stream_read($count)
    {
        $ret = substr($this->content, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    public function stream_seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->content) && $offset >= 0) {
                     $this->position = $offset;
                     return true;
                }
                return false;

            case SEEK_CUR:
                if ($offset >= 0) {
                     $this->position += $offset;
                     return true;
                }
                return false;

            case SEEK_END:
                $len = strlen($this->content);
                if ($len + $offset >= 0) {
                     $this->position = $len + $offset;
                     return true;
                }
                return false;

            default:
                return false;
        }
    }
}
