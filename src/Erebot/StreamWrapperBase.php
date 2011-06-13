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
 *      Abstract class for a minimal PHP stream wrapper.
 */
abstract class Erebot_StreamWrapperBase
{
    /// If path is relative, search for the resource using the include_path. 
    const STREAM_USE_PATH = STREAM_USE_PATH;

    /**
     * \brief
     *      Whether the stream wrapper should report errors or not.
     *
     * If this flag is set, the wrapper is responsible for raising errors
     * using trigger_error() during opening of the stream. If this flag
     * is not set, it should not raise any errors. 
     */
    const STREAM_REPORT_ERRORS = STREAM_REPORT_ERRORS;

    /**
     * \brief
     *      For links, whether to return information about the link
     *      itself or the resource it links to.
     *
     * For resources with the ability to link to other resource
     * (such as an HTTP Location: forward, or a filesystem symlink).
     * This flag specified that only information about the link itself
     * should be returned, not the resource pointed to by the link.
     * This flag is set in response to calls to lstat(), is_link(),
     * or filetype().
     */
    const STREAM_URL_STAT_LINK = STREAM_URL_STAT_LINK;

    /**
     * \brief
     *      Whether to report errors in
     *      Erebot_StreamWrapperBase::url_stat() or not.
     *
     * If this flag is set, the wrapper should not raise any errors.
     * If this flag is not set, it is responsible for reporting errors
     * using the trigger_error() function during stating of the path.
     */
    const STREAM_URL_STAT_QUIET = STREAM_URL_STAT_QUIET;

    /// Set position equal to offset bytes.
    const SEEK_SET = SEEK_SET;

    /// Set position to current location plus offset.
    const SEEK_CUR = SEEK_CUR;

    /// Set position to end-of-file plus offset.
    const SEEK_END = SEEK_END;

    /**
     * \brief
     *      Constructs a new instance of this stream wrapper.
     *
     * This method is called when opening the stream wrapper,
     * right before Erebot_StreamWrapperBase::stream_open().
     */
    public function __construct()
    {
    }

    /**
     * \brief
     *      Close a resource.
     *
     * This method is called in response to fclose().
     * All resources that were locked, or allocated, by the wrapper
     * should be released.
     */
    abstract public function stream_close();

    /**
     * \brief
     *      Tests for end-of-file on a file pointer.
     *
     * This method is called in response to feof(). 
     *
     * \retval bool
     *      Returns TRUE if the read/write position is at the end
     *      of the stream and if no more data is available to be read,
     *      or FALSE otherwise.
     */
    abstract public function stream_eof();

    /**
     * \brief
     *      Opens file or URL.
     *
     * This method is called immediately after the wrapper
     * is initialized (f.e. by fopen() and file_get_contents()).
     *
     * \param string $path
     *      Specifies the URL that was passed to the original function.
     *
     * \param string $mode
     *      The mode used to open the file, as detailed for fopen().
     *
     * \param int $options
     *      Holds additional flags set by the streams API. It can hold one
     *      or more of the flags defined in thisinterface, OR'd together.
     *
     * \param string $opened_path
     *      If the path is opened successfully, and
     *      Erebot_StreamWrapperBase::STREAM_USE_PATH
     *      is set in $options, $opened_path should be set to the full
     *      path of the file/resource that was actually opened.
     *
     * \retval bool
     *      Returns TRUE on success or FALSE on failure.
     *
     * \note
     *      The URL can be broken apart with parse_url(). Note that
     *      only URLs delimited by "://"" are supported. ":" and ":/"
     *      while technically valid URLs, are not.
     *
     * \attention
     *      Remember to check if the mode is valid for the path requested.
     */
    abstract public function stream_open($path, $mode, $options, &$openedPath);

    /**
     * \brief
     *      Read from stream.
     *
     * This method is called in response to fread() and fgets().
     *
     * \param int $count
     *      How many bytes of data from the current position should be returned.
     *
     * \retval string
     *      If there are less than count bytes available, returns as many
     *      as are available.
     *
     * \retval FALSE
     *      If no more data is available, FALSE should be returned.
     *
     * \attention
     *      Remember to update the read/write position of the stream
     *      (by the number of bytes that were successfully read).
     *
     * \note
     *      As a special case, an empty string may also be returned when
     *      no more data is available for reading.
     *
     * \note
     *      Erebot_StreamWrapperBase::stream_eof() is called directly
     *      after calling Erebot_StreamWrapperBase::stream_read() to check
     *      if EOF has been reached. If not implemented, EOF is assumed.
     */
    public function stream_read($count)
    {
        return FALSE;
    }

    /**
     * \brief
     *      Seeks to specific location in a stream.
     *
     * This method is called in response to fseek().
     *
     * \param int $offset
     *      The stream offset to seek to.
     *
     * \param int $whence
     *      How the seek is to be interpreted. One of:
     *      -   Erebot_StreamWrapperBase::SEEK_SET
     *      -   Erebot_StreamWrapperBase::SEEK_CUR
     *      -   Erebot_StreamWrapperBase::SEEK_END
     *
     * \retval bool
     *      Returns TRUE if the position was updated, FALSE otherwise.
     *
     * \attention
     *      The read/write position of the stream should be updated
     *      according to the $offset and $whence.
     *
     * \note
     *      Upon success, Erebot_StreamWrapperBase::stream_tell()
     *      is called directly after calling
     *      Erebot_StreamWrapperBase::stream_seek().
     *      If Erebot_StreamWrapperBase::stream_tell() fails,
     *      the return value to the caller function will be set to FALSE.
     */
    public function stream_seek($offset, $whence)
    {
        return FALSE;
    }

    /**
     * \brief
     *      Retrieve information about a file resource.
     *
     * This method is called in response to fstat().
     *
     * \retval array
     *      Same as for http://php.net/manual/en/function.stat.php.
     *
     * \note
     *      When using PHP 5.2.x, fstat() is sometimes called
     *      automatically on the stream. For backward compatibility,
     *      this method should always be implemented.
     */
    public function stream_stat()
    {
        return array();
    }

    /**
     * \brief
     *      Retrieve the current position of a stream.
     *
     * This method is called in response to ftell().
     *
     * \retval int
     *      The current position of the stream.
     */
    abstract public function stream_tell();

    /**
     * \brief
     *      Retrieve information about a file.
     *
     * This method is called in response to all stat() related functions.
     *
     * \param string $path
     *      The file path or URL to stat. Note that in the case
     *      of a URL, it must be a :// delimited URL.
     *      Other URL forms are not supported.
     *
     * \param int $flags
     *      Holds additional flags set by the streams API.
     *      It can hold one or more of the following values OR'd together:
     *      -   Erebot_StreamWrapperBase::STREAM_URL_STAT_LINK
     *      -   Erebot_StreamWrapperBase::STREAM_URL_STAT_QUIET
     *
     * \see
     *      http://php.net/manual/en/streamwrapper.url-stat.php for a
     *      complete list of all stat() related functions.
     */
    public function url_stat($path, $flags)
    {
        return array();
    }
}

