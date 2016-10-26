<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code related to mails
 * @class ErrorCodeMAIL
 * @brief List all error code related to mails
 * It is triggered by 'MAIL' keyword
 * @see ErrorCode
 */
class ErrorCodeMAIL
{
    /**
     * @errorCode notifySendmail has a restricted list of values
     * @see \Dcp\Core\MailTemplate
     */
    const MAIL0001 = "'%s' is an invalid notifySendMail value. valid values are '%s' (see \\Dcp\\Core\\MailTemplate::NOTIFY_SENDMAIL_ consts";
}