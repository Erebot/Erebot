<?php

/**
 * \brief
 *      A stream wrapper which performs a glob
 *      on files and returns the content of all
 *      matching files, wrapped in XML boilerplate.
 *
 *  Provides a PHP stream wrapper for files with a glob-like feature.
 */
class ErebotWrapperXGlob
{
    public      $context;
    protected   $position;
    protected   $content;

    const XMLNS = 'http://www.erebot.net/xmlns/xglob';
    const TAG   = 'wrapping';

    public function __construct()
    {
        $this->matches = FALSE;
    }

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
        $this->content  = '';
    }

    public function stream_open($path, $mode, $options, &$opened)
    {
        $this->position = 0;
        $pos        = strpos($path, '://');
        if ($pos === FALSE)
            return FALSE;

        $path   = substr($path, $pos + 3);
        $first  = substr($path, 0, 1);
        $abs    = '';

        if ($first != DIRECTORY_SEPARATOR && (
            strncasecmp(PHP_OS, 'Win', 3) || $first != '/'))
            $abs = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR;

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
        $content   .= '</xglob:'.self::TAG.'>'; 
        $this->content = $content;
        return TRUE;
    }

    public function stream_read($count)
    {
        $ret = substr($this->content, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    function stream_seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->content) && $offset >= 0) {
                     $this->position = $offset;
                     return TRUE;
                }
                return FALSE;

            case SEEK_CUR:
                if ($offset >= 0) {
                     $this->position += $offset;
                     return TRUE;
                }
                return FALSE;

            case SEEK_END:
                $len = strlen($this->content);
                if ($len + $offset >= 0) {
                     $this->position = $len + $offset;
                     return TRUE;
                }
                return FALSE;

            default:
                return FALSE;
        }
    }

    public function url_stat()
    {
        return array();
    }
}

// The name "glob" is already used internally as of PHP 5.3.0.
// Moreover, we need extra stuff (XML validity), hence "xglob".
if (!in_array("xglob", stream_get_wrappers()))
    stream_wrapper_register('xglob',  'ErebotWrapperXGlob', STREAM_IS_URL);

?>
