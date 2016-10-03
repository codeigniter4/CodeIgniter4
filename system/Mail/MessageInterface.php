<?php namespace Mail;

interface MessageInterface
{
    /**
     * The method called by the MailServices that allows this email
     * message to be built. Within this method, the developer will typically
     * set the HTMLContent and/or textContent variables, as well
     * as overriding any default to/from/reply-to/etc.
     *
     * @return mixed
     */
    public function build();

    //--------------------------------------------------------------------

    /**
     * Sets a custom header value to be sent along with the email.
     *
     * @param string $name
     * @param null   $value
     *
     * @return $this
     */
    public function setHeader(string $name, $value = null);

    //--------------------------------------------------------------------

    /**
     * A generic method to set one or more emails/names to our various
     * address fields. Used by the Mailer class.
     *
     * @param             $emails
     * @param string|null $name
     * @param string      $type
     */
    public function setEmails($emails, string $name = null, string $type);

    //--------------------------------------------------------------------
}
