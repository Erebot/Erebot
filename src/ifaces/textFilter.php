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

include_once('src/ifaces/mainConfig.php');

/**
 * \brief
 *      Interface to filter events based on their content.
 *
 * This interface provides the necessary methods to filter
 * an event based on the contents of the message it contains.
 */
interface iErebotTextFilter
{
    /// The pattern contains a static text to match.
    const TYPE_STATIC   = 0;

    /// The pattern contains wildcards characters ('?', '*' or '&').
    const TYPE_WILDCARD = 1;

    /// The pattern is a regular expression.
    const TYPE_REGEXP   = 2;

    /**
     * Constructs a new text filter using the given configuration.
     * Except for $config, the rest of the arguments is the same as for
     * iErebotTextFilter::addPattern() or iErebotTextFilter::removePattern().
     *
     * \param $config
     *      The main configuration for the bot (used to determine the prefix
     *      to use, if any). Must implement the iErebotMainConfig interface.
     *
     * \param $type
     *      (optional) The type of pattern used, that is, one of:
     *      *   iErebotTextFilter::TYPE_STATIC
     *      *   iErebotTextFilter::TYPE_WILDCARD
     *      *   iErebotTextFilter::TYPE_REGEXP
     *
     * \param $pattern
     *      (optional) The pattern which will be matched against by this filter.
     *
     * \param $require_prefix
     *      (optional) A boolean which indicates whether a prefix is required
     *      (TRUE) or not (FALSE) for the filter to match. You may also set it
     *      to NULL to indicate it doesn't matter whether a prefix is present
     *      or not.
     *
     * \note
     *      It is an error if one of $type or $pattern is set to NULL
     *      and the other is not.
     *
     * \note
     *      If both $type and $pattern are set to NULL (the default),
     *      an empty filter is created to which patterns can be added
     *      later using the iErebotTextFilter::addPattern() method.
     *      Therefore, the following snippets of code are equivalent:
     *      \code
     *      $filter = new ErebotTextFilter(
     *          $mainConfig,
     *          ErebotTextFilter::TYPE_STATIC,
     *          'foo'
     *      );
     *      \endcode
     *      and
     *      \code
     *      $filter = new ErebotTextFilter($mainConfig);
     *      $filter->addPattern(ErebotTextFilter::TYPE_STATIC, 'foo');
     *      \endcode
     */
    public function __construct(
        iErebotMainConfig  &$config,
                            $type           = NULL,
                            $pattern        = NULL,
                            $requirePrefix  = FALSE
    );

    /**
     * Adds a match pattern to this filter.
     *
     * \param $type
     *      The type of pattern to add, that is, one of:
     *      *   iErebotTextFilter::TYPE_STATIC
     *      *   iErebotTextFilter::TYPE_WILDCARD
     *      *   iErebotTextFilter::TYPE_REGEXP
     *
     * \param $pattern
     *      The pattern which will be matched against by this filter.
     *
     * \param $require_prefix
     *      A boolean which indicates whether a prefix is required
     *      (TRUE) or not (FALSE) for the filter to match. You may
     *      also set it to NULL to indicate it doesn't matter whether
     *      a prefix is present or not.
     */
    public function addPattern(
        $type,
        $pattern,
        $requirePrefix = FALSE
    );

    /**
     * Removes a match pattern from this filter.
     * The parameters used to remove a pattern MUST be EXACTLY THE SAME
     * as when it was added (be it at construction time or through the
     * use of iErebotTextFilter::addPattern()).
     *
     * \param $type
     *      The type of pattern to remove, that is, one of:
     *      *   iErebotTextFilter::TYPE_STATIC
     *      *   iErebotTextFilter::TYPE_WILDCARD
     *      *   iErebotTextFilter::TYPE_REGEXP
     *
     * \param $pattern
     *      The pattern which will be matched against by this filter.
     *
     * \param $require_prefix
     *      A boolean which indicates whether a prefix is required
     *      (TRUE) or not (FALSE) for the filter to match. You may
     *      also set it to NULL to indicate it doesn't matter whether
     *      a prefix is present or not.
     */
    public function removePattern(
        $type,
        $pattern,
        $requirePrefix = FALSE
    );

    /**
     * Returns an array of all patterns registered on this filter
     * for the given $type.
     *
     * \param $type
     *      (optional) Only patterns of this type will be returned.
     *      If set to NULL (the default), an array of arrays is
     *      returned instead where the outermost array's keys are
     *      pattern types and the corresponding arrays are the
     *      patterns registered for that particular type.
     *
     * \return
     *      Returns pattern of the given $type or all patterns if
     *      $type is NULL.
     */
    public function getPatterns($type = NULL);

    /**
     * Tests whether this filter matches the text in the given $event.
     *
     * \param $event
     *      An object implementing the iErebotEvent interface
     *      to match against.
     *
     * \return
     *      Returns TRUE if one of this filter's patterns matches
     *      data in the given $event, FALSE otherwise.
     */
    public function match(iErebotEvent &$event);
}

