<?php

/**
 * \brief
 *      Interface to represent a raw numeric message.
 *
 * This interface provides the necessary methods
 * to represent a raw numeric message from an IRC server.
 */
interface iErebotRaw
{
    /**
     * Constructs a raw message.
     *
     * \param $connection
     *      The connection this message came from, as an object
     *      implementing the iErebotConnection interface.
     *
     * \param $raw
     *      The raw numeric code.
     *
     * \param $source
     *      The source of the raw message. This will generally be
     *      the name of an IRC server.
     *
     * \param $target
     *      The target of the raw message. This will generally be
     *      the bot's nickname.
     *
     * \param $text
     *      The raw content of the message.
     *
     * \note
     *      No attempt is made at parsing the content of the message.
     */
    public function __construct(iErebotConnection &$connection, $raw, $source, $target, $text);

    /**
     * Returns the connection this raw message came from.
     * This is the same object as that passed during construction.
     *
     * \return
     *      Returns an object implementing the iErebotConnection
     *      interface.
     */
    public function & getConnection();

    /**
     * Returns the raw numeric code associated with
     * the current message.
     *
     * \return
     *      The raw numeric code of this message.
     *
     * \see
     *      You may compare the value returned by this method
     *      with one of the constants defined in raws.php.
     *
     * \note
     *      Multiple constants may point to the same code
     *      as the same code may have different interpretations
     *      depending on the server (IRCd) where it is used.
     */
    public function getRaw();

    /**
     * Returns the source of the current message.
     * This will generally be the name of an IRC
     * server.
     *
     * \return
     *      The source of this message.
     */
    public function getSource();

    /**
     * Returns the target of the current message.
     * This will generally be the bot's nickname.
     *
     * \return
     *      The target of this message.
     */
    public function getTarget();

    /**
     * Returns the raw content of the current
     * message. No attempt is made at parsing
     * the content.
     *
     * \return
     *      The content of this message.
     */
    public function getText();
}

?>
