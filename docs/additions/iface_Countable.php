<?php

/**
 * \brief
 *      Classes implementing Countable can be used with the count() function.
 *
 * \see
 *      http://php.net/manual/en/class.countable.php
 */
interface Countable {
    /**
     * Count elements of an object.
     *
     * This method is executed when using the count() function
     * on an object implementing the Countable interface. 
     *
     * \retval int
     *      The custom count as an integer.
     *
     * \see
     *      http://php.net/manual/en/countable.count.php
     */
    public function count();
}
