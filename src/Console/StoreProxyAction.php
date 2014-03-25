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

namespace Erebot\Console;

/**
 * \brief
 *      Custom action which acts as a proxy.
 *
 * This class can be used in conjunction with
 * Console_CommandLine_ParallelOption to have
 * multiple options that act on the same variable.
 */
class StoreProxyAction extends \Console_CommandLine_Action
{
    /**
     * Sets the result of this action.
     * In our case, this actually changes
     * the value of another option.
     *
     * \param mixed $result
     *      Result to assign to the other option.
     *
     * \param mixed $option
     *      (optional) Name of the option this proxy
     *      will operate on. The default is \b null.
     */
    public function setResult($result, $option = null)
    {
        $this->result->options[$option] = $result;
    }

    /**
     * Executes this action whenever the associated
     * option has been passed on the command-line.
     *
     * \param mixed $value
     *      (optional) Value given to this option.
     *      The default is \b false.
     *
     * \param array $params
     *      (optional) Parameters associated with
     *      this action. The default is an empty
     *      set of parameters (empty array).
     */
    public function execute($value = false, $params = array())
    {
        $this->setResult(false, $params['option']);
    }
}
