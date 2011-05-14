<?php

/**
 * \brief
 *      Interface to provide accessing objects as arrays.
 *
 * \see
 *      http://php.net/manual/en/class.arrayaccess.php
 */
interface ArrayAccess {
    /**
     * Whether or not an offset exists.
     *
     * This method is executed when using isset() or empty()
     * on objects implementing ArrayAccess.
     *
     * \param mixed $offset
     *      An offset to check for.    
     *
     * \retval bool
     *      TRUE is returned when the offset exists,
     *      FALSE when it doesn't.
     *
     * \see
     *      http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * \note
     *      When using empty() ArrayAccess::offsetGet() will be called
     *      and checked if empty only if ArrayAccess::offsetExists()
     *      returns TRUE.
     */
    public function offsetExists($offset);

    /**
     * Returns the value at specified offset. 
     *
     * This method is executed when checking if offset is empty().
     *
     * \param mixed $offset
     *      The offset to retrieve.
     *
     * \retval mixed
     *      Value at the specified offset.
     *
     * \see
     *      http://php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset);

    /**
     * Assigns a value to the specified offset.
     *
     * \param mixed $offset
     *      The offset to assign the value to.
     *
     * \param mixed $value
     *      The value to set.
     *
     * \see
     *      http://php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value);

    /**
     * Unsets an offset.
     *
     * \param mixed $offset
     *      The offset to unset.
     *
     * \see
     *      http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * \note
     *      This method will not be called when type-casting to (unset).
     */
    public function offsetUnset($offset);
}
