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
 *      Interface for a Uniform Resource Identifier
 *      parser/generator compatible with RFC 3986.
 */
interface Erebot_Interface_URI
{
    /**
     * Constructs an URI.
     *
     * \param mixed $uri
     *      Either a string representing the URI or an array
     *      as returned by PHP's parse_url() function.
     *
     * \throw Erebot_InvalidValueException
     *      The given URI is invalid.
     */
    public function __construct($uri);

    /**
     * Returns the current URI as a string.
     *
     * \param bool $raw
     *      (optional) Whether the raw contents of the components
     *      should be used (TRUE) or a normalized alternative (FALSE).
     *      The default is to apply normalization.
     *
     * \param bool $credentials
     *      (optional) Whether the content of the "user information"
     *      component should be part of the returned string (TRUE)
     *      or not (FALSE). The default is for such credentials to
     *      appear in the result.
     *
     * \retval string
     *      The current URI as a string, eventually normalized.
     */
    public function toURI($raw = FALSE, $credentials = TRUE);

    /**
     * Returns the current URI as a string,
     * in its normalized form.
     *
     * \note
     *      This method is a shortcut for Erebot_URI::toURI(FALSE).
     */
    public function __toString();

    /**
     * Returns the current URI's scheme.
     *
     * \param bool $raw
     *      (optional) Whether the value should be normalized
     *      before it's returned (FALSE) or not (TRUE).
     *      The default is to apply normalization.
     *
     * \retval string
     *      The current URI's scheme as a string,
     *      eventually normalized.
     */
    public function getScheme($raw = FALSE);

    /**
     * Sets the current URI's scheme.
     *
     * \param string $scheme
     *      New scheme for this URI, as a string.
     *
     * \throw Erebot_InvalidValueException
     *      The given $scheme is not valid.
     */
    public function setScheme($scheme);

    /**
     * Returns the current URI's user information.
     *
     * \param bool $raw
     *      (optional) Whether the value should be normalized
     *      before it's returned (FALSE) or not (TRUE).
     *      The default is to apply normalization.
     *
     * \retval mixed
     *      The current URI's user information,
     *      eventually normalized or NULL.
     */
    public function getUserInfo($raw = FALSE);

    /**
     * Sets the current URI's user information.
     *
     * \param mixed $userinfo
     *      New user information for this URI
     *      (either a string or NULL).
     *
     * \throw Erebot_InvalidValueException
     *      The given user information is not valid.
     */
    public function setUserInfo($userinfo);

    /**
     * Returns the current URI's host.
     *
     * \param bool $raw
     *      (optional) Whether the value should be normalized
     *      before it's returned (FALSE) or not (TRUE).
     *      The default is to apply normalization.
     *
     * \retval mixed
     *      The current URI's host as a string,
     *      eventually normalized or NULL.
     */
    public function getHost($raw = FALSE);

    /**
     * Sets the current URI's host.
     *
     * \param string $host
     *      New host for this URI (either a string or NULL).
     *
     * \throw Erebot_InvalidValueException
     *      The given $host is not valid.
     */
    public function setHost($host);

    /**
     * Returns the current URI's port.
     *
     * \param bool $raw
     *      (optional) Whether the value should be normalized
     *      before it's returned (FALSE) or not (TRUE).
     *      The default is to apply normalization.
     *
     * \retval mixed
     *      When normalization is in effect, the port for
     *      the current URI will be returned as an integer,
     *      or NULL.
     *      When normalization has been disabled, the port
     *      will be returned as a string or NULL.
     */
    public function getPort($raw = FALSE);

    /**
     * Sets the current URI's port.
     *
     * \param mixed $port
     *      New port for this URI (either a numeric string,
     *      an integer or NULL).
     *
     * \throw Erebot_InvalidValueException
     *      The given $port is not valid.
     */
    public function setPort($port);

    /**
     * Returns the current URI's path.
     *
     * \param bool $raw
     *      (optional) Whether the value should be normalized
     *      before it's returned (FALSE) or not (TRUE).
     *      The default is to apply normalization.
     *
     * \retval string
     *      The current URI's path as a string,
     *      eventually normalized.
     */
    public function getPath($raw = FALSE);

    /**
     * Sets the current URI's path.
     *
     * \param string $path
     *      New path for this URI.
     *
     * \throw Erebot_InvalidValueException
     *      The given $path is not valid.
     *
     * \note
     *      This is a very thin wrapper around the internal
     *      method Erebot_URI::_setPath().
     */
    public function setPath($path);

    /**
     * Returns the current URI's query.
     *
     * \param bool $raw
     *      (optional) Whether the value should be normalized
     *      before it's returned (FALSE) or not (TRUE).
     *      The default is to apply normalization.
     *
     * \retval mixed
     *      The current URI's query as a string,
     *      eventually normalized or NULL.
     */
    public function getQuery($raw = FALSE);

    /**
     * Sets the current URI's query.
     *
     * \param mixed $query
     *      New query for this URI (either a string or NULL).
     *
     * \throw Erebot_InvalidValueException
     *      The given $query is not valid.
     */
    public function setQuery($query);

    /**
     * Returns the current URI's fragment.
     *
     * \param bool $raw
     *      (optional) Whether the value should be normalized
     *      before it's returned (FALSE) or not (TRUE).
     *      The default is to apply normalization.
     *
     * \retval mixed
     *      The current URI's fragment as a string,
     *      eventually normalized or NULL.
     */
    public function getFragment($raw = FALSE);

    /**
     * Sets the current URI's fragment.
     *
     * \param mixed $fragment
     *      New fragment for this URI (either a string or NULL).
     *
     * \throw Erebot_InvalidValueException
     *      The given $fragment is not valid.
     */
    public function setFragment($fragment);

    /**
     * Returns information about the current URI,
     * in the same format as parse_url().
     *
     * \param $component
     *      (optional) A specific component to return.
     *      Read the documentation about parse_url()
     *      for more information.
     *
     * \retval mixed
     *      Either an array, a string, an integer or NULL,
     *      depending on $component and the actual contents
     *      of this URI.
     *      Read the documentation about parse_url()
     *      for more information.
     */
    public function asParsedURL($component = -1);

    /**
     * Given a relative reference, returns a new absolute URI
     * matching that reference.
     *
     * \param string $reference
     *      Some relative reference (can be an absolute
     *      or relative URI). The current absolute URI
     *      is used as the base to dereference it.
     *
     * \retval Erebot_URI
     *      A new absolute URI matching the given $reference.
     *
     * \throw Erebot_InvalidValueException
     *      The given $reference is not valid.
     */
    public function relative($reference);
}

