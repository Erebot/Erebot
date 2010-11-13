<?php
GPLH
/**
 * \brief
 *      Interface for an event which has a source.
 */
interface   iErebotEventSource
extends     iErebotEvent
{
    public function & getSource();
}

