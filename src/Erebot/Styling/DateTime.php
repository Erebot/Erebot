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

class       Erebot_Styling_DateTime
implements  Erebot_Interface_Styling_Variable
{
    protected $_value;
    protected $_datetype;
    protected $_timetype;
    protected $_timezone;

    public function __construct($value, $datetype, $timetype, $timezone = NULL)
    {
        $this->_value       = $value;
        $this->_datetype    = $datetype;
        $this->_timetype    = $timetype;
        $this->_timezone    = $timezone;
    }

    public function render(Erebot_Interface_I18n $translator)
    {
        $locale     = $translator->getLocale(Erebot_Interface_I18n::LC_TIME);
        $timezone   = ($this->_timezone !== NULL)
                        ? $this->_timezone
                        : date_default_timezone_get();
        $formatter  = new IntlDateFormatter(
            $locale,
            $this->_datetype,
            $this->_timetype,
            $timezone
        );
        return (string) $formatter->format($this->_value);
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function getDateType()
    {
        return $this->_datetype;
    }

    public function getTimeType()
    {
        return $this->_timetype;
    }

    public function getTimeZone()
    {
        return $this->_timezone;
    }
}

