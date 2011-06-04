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
 *      A stream wrapper which performs a glob
 *      on files and returns the content of all
 *      matching files, wrapped in XML boilerplate.
 *
 *  Provides a PHP stream wrapper for files with a glob-like feature.
 */
class   Erebot_XGlobStream
extends Erebot_StreamWrapperBase
{
    public      $context;
    protected   $_position;
    protected   $_content;

    const XMLNS = 'http://www.erebot.net/xmlns/xglob';
    const TAG   = 'wrapping';

    /// \copydoc Erebot_StreamWrapperBase::stream_tell()
    public function stream_tell()
    {
        return $this->_position;
    }

    /// \copydoc Erebot_StreamWrapperBase::stream_eof()
    public function stream_eof()
    {
        return ($this->_position >= strlen($this->_content));
    }

    /// \copydoc Erebot_StreamWrapperBase::stream_close()
    public function stream_close()
    {
        $this->_position    = 0;
        $this->_content     = '';
    }

    /// \copydoc Erebot_StreamWrapperBase::stream_open()
    public function stream_open($path, $mode, $options, &$opened)
    {
        $this->_position = 0;
        $pos        = strpos($path, '://');
        if ($pos === FALSE)
            return FALSE;

        $path   = substr($path, $pos + 3);
        $first  = substr($path, 0, 1);
        $abs    = '';

        if ($first != '/')
            $abs = getcwd().DIRECTORY_SEPARATOR;

        $matches    = glob($abs.$path, 0);
        if ($matches === FALSE)
            $matches = array();

        $content    = '<xglob:'.self::TAG.' xmlns:xglob="'.self::XMLNS.'">';
        foreach ($matches as $absname) {
            if (!is_file($absname))
                continue;
            $cnt = file_get_contents($absname);
            if ($cnt !== FALSE)
                $content .= $cnt;
        }
        $content        .= '</xglob:'.self::TAG.'>'; 
        $this->_content  = $content;
        return TRUE;
    }

    /// \copydoc Erebot_StreamWrapperBase::stream_read()
    public function stream_read($count)
    {
        $ret = substr($this->_content, $this->_position, $count);
        $this->_position += strlen($ret);
        return $ret;
    }

    /// \copydoc Erebot_StreamWrapperBase::stream_seek()
    public function stream_seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->_content) && $offset >= 0) {
                     $this->_position = $offset;
                     return TRUE;
                }
                return FALSE;

            case SEEK_CUR:
                if ($offset >= 0) {
                     $this->_position += $offset;
                     return TRUE;
                }
                return FALSE;

            case SEEK_END:
                $len = strlen($this->_content);
                if ($len + $offset >= 0) {
                     $this->_position = $len + $offset;
                     return TRUE;
                }
                return FALSE;

            default:
                return FALSE;
        }
    }
}

// The name "glob" is already used internally as of PHP 5.3.0.
// Moreover, we need extra stuff (XML validity), hence "xglob".
if (!in_array("xglob", stream_get_wrappers())) {
    stream_wrapper_register('xglob', 'Erebot_XGlobStream', STREAM_IS_URL);
}

