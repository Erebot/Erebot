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
 *      A class used to format dates/times.
 */
class       Erebot_Styling_DateTime
implements  Erebot_Interface_Styling_DateTime
{
    /// A value expressing a date/time.
    protected $_value;

    /// Type of rendering to apply to dates.
    protected $_datetype;

    /// Type of rendering to apply to times.
    protected $_timetype;

    /// Timezone to use during the rendering prcess.
    protected $_timezone;

    /**
     * Constructor.
     *
     * \param mixed $value
     *      A value representing a date/time that will
     *      be formatted. This may be a DateTime object,
     *      an integer representing a Unix timestamp
     *      value (seconds since epoch, UTC) or an array
     *      in the format output by localtime().
     *
     * \param opaque $datetype
     *      The type of rendering to apply to dates.
     *      This is one of the constants defined in
     *      http://php.net/manual/en/class.intldateformatter.php
     *
     * \param opaque $timetype
     *      The type of rendering to apply to times.
     *      This is one of the constants defined in
     *      http://php.net/manual/en/class.intldateformatter.php
     *
     * \param string|NULL $timezone
     *      (optional) Timezone to use when rendering dates/times,
     *      eg. "Europe/Paris".
     *      The default is to use the system's default timezone,
     *      as returned by date_default_timezone_get().
     */
    public function __construct($value, $datetype, $timetype, $timezone = NULL)
    {
        $this->_value       = $value;
        $this->_datetype    = $datetype;
        $this->_timetype    = $timetype;
        $this->_timezone    = $timezone;
    }

    /// \copydoc Erebot_Interface_Styling_Variable::render()
    public function render(Erebot_Interface_I18n $translator)
    {
        $timezone   =   ($this->_timezone !== NULL)
                        ? $this->_timezone
                        : date_default_timezone_get();

        $formatter  = new IntlDateFormatter(
            $translator->getLocale(Erebot_Interface_I18n::LC_TIME),
            $this->_datetype,
            $this->_timetype,
            $timezone
        );
        return (string) $formatter->format($this->_value);
    }

    /// \copydoc Erebot_Interface_Styling_Variable::getValue()
    public function getValue()
    {
        return $this->_value;
    }

    /// \copydoc Erebot_Interface_Styling_DateTime::getDateType()
    public function getDateType()
    {
        return $this->_datetype;
    }

    /// \copydoc Erebot_Interface_Styling_DateTime::getTimeType()
    public function getTimeType()
    {
        return $this->_timetype;
    }

    /// \copydoc Erebot_Interface_Styling_DateTime::getTimeZone()
    public function getTimeZone()
    {
        return $this->_timezone;
    }
}

