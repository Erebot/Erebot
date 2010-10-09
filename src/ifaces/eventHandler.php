<?php

/**
 * \brief
 *      Interface for event handlers.
 *
 * This interface provides the necessary methods to represent
 * a structure capable of handling events from an IRC server.
 */
interface iErebotEventHandler
{
    /**
     * Constructs an event handler.
     *
     * \param $callback
     *      The callback function/method which will be called
     *      when an event is received which meets the $constraints,
     *      is part of valid $targets and passed the $filters
     *      successfully.
     *
     * \param $constraints
     *      Either a string of array of strings containing
     *      the names of classes/interfaces which should be
     *      considered acceptable events for this handler
     *      to treat. Therefore, it's a list of constraints
     *      on the event's type.
     *
     * \param $targets
     *      (optional) An object implementing the iErebotEventTargets
     *      interface, which describes which targets this event handler
     *      will consider valid. This is complementary to the $constraints
     *      parameter and can be used to build whitelists/blacklists
     *      (eg. make the bot react to an event only if it comes from
     *      a trusted source like the bot's administrator).
     *      See the documentation on iErebotEventTargets for more information.
     *      If this is set to NULL (the default), any target is
     *      considered valid.
     *
     * \param $filters
     *      (optional) An object implementing the iErebotTextFilter
     *      interface, which can be used to filter events containing
     *      text based on the content of that text.
     *      See the documentation on iErebotTextFilter for more information.
     *      If this is set to NULL (the default), any text is considered
     *      valid (ie: no filtering is done).
     */
    public function __construct(
        $callback,
        $constraints,
        iErebotEventTargets $targets    = NULL,
        iErebotTextFilter   $filters    = NULL
    );

    /**
     * Returns a reference to the callback which was associated
     * with this handler during construction.
     *
     * \return
     *      The callback associated for this handler.
     */
    public function & getCallback();

    /**
     * Returns the constraints on the event's type associated
     * with this handler during construction.
     *
     * \return
     *      Type constraints for this handler, either
     *      as a string or an array of strings (whichever
     *      was used during construction).
     */
    public function getConstraints();

    /**
     * Returns the constraints on the event's target associated
     * with this handler during construction.
     *
     * \return
     *      An object implementing the iErebotEventTargets
     *      interface and expressing constraints on targets.
     */
    public function & getTargets();

    /**
     * Returns the constraints on the event's text associated
     * with this handler during construction.
     *
     * \return
     *      An object implementing the iErebotTextFilter
     *      interface and expressing constraints on an
     *      event's text.
     */
    public function & getFilters();

    /**
     * Given an event, this method does its best to handler it.
     *
     * \param $event
     *      An object implementing the iErebotEvent interface
     *      that this method will try to handle.
     *
     * \note
     *      It is this method's responsability to make appropriate
     *      checks and act upon the result of those checks.
     *      It may for example check that the event matches the
     *      constraints (on type, target and/or content) expressed
     *      by the current handler.
     */
    public function handleEvent(iErebotEvent &$event);
}

?>
