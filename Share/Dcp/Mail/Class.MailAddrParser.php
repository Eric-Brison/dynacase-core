<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Mail;

class MailAddrParserException extends \Exception
{
}
/**
 * Class MailAddrParser
 *
 * Try to parse a UTF8 string containing multiple mail addresses in
 * RFC5322/RFC2822 notation, and extract them as a list of
 * \Dcp\MailAddress objects.
 *
 *     Jöhn Dôé <john.doe@example.net>, "Foo, Bar <ACME Corp.>" <foo.bar@acme.corp>, xyz@example.net
 *
 * And returns a list of \Dcp\Mail\Address objects with display names
 * and mail adresses :
 *
 *     array(
 *         new \Dcp\Mail\Address("john.doe@example.net", "Jöhn Dôé"),
 *         new \Dcp\Mail\Address("foo.bar@acme.corp", "Foo, Bar <ACME Corp.>"),
 *         new \Dcp\Mail\Address("xyz@example.net", "")
 *     )
 *
 * Note:
 * - It will not validate the mail addresses.
 *
 * @package Dcp\Mail
 */
class MailAddrParser
{
    private $debug = false;
    private $lax = false;
    private $state = self::ST_lookingForStartOfMail;
    private $esc = false;
    private $encoding = 'UTF-8';
    private $s = '';
    private $p = 0;
    /*
     * Parser's states
    */
    const ST_lookingForStartOfMail = 'ST_lookingForStartOfMail';
    const ST_inAngleMail = 'ST_inAngleMail';
    const ST_inMail = 'ST_inMail';
    const ST_inQuotedDisplayName = 'ST_inQuotedDisplayName';
    const ST_lookingForAngleMail = 'ST_lookingForAngleMail';
    const ST_lookingForSeparator = 'ST_lookingForSeparator';
    const ST_end = 'ST_end';
    /**
     * Set laxist mode and do not throw exceptions. Misunderstood
     * elements will be skipped.
     *
     * Default is bool(false).
     *
     * @param bool $lax
     */
    public function setLax($lax = true)
    {
        $this->lax = ($lax === true);
    }
    /**
     * Print debugging information with PHP's error_log()
     *
     * Default is bool(false)
     *
     * @param bool $debug
     */
    public function setDebug($debug = true)
    {
        $this->debug = ($debug === true);
    }
    /**
     * Set Multi-Byte string encoding.
     *
     * Default is 'UTF-8'.
     *
     * @param string $encoding
     */
    public function setEncoding($encoding = 'UTF-8')
    {
        $this->encoding = $encoding;
    }
    /**
     * Check if we are after the end of the string.
     *
     * @return bool
     */
    private function eos()
    {
        return ($this->p >= mb_strlen($this->s, $this->encoding));
    }
    /**
     * Check if next char would be after the end of the string.
     *
     * @return bool
     */
    private function eosNext()
    {
        return (($this->p + 1) >= mb_strlen($this->s, $this->encoding));
    }
    /**
     * Peek char at current position
     *
     * @return string
     */
    private function peek()
    {
        return mb_substr($this->s, $this->p, 1, $this->encoding);
    }
    /**
     * Advance the position in the string by 1 char.
     */
    private function next()
    {
        $this->p++;
    }
    /**
     * Check if the given char is a space char.
     *
     * @param $c
     * @return int
     */
    private function isSpace($c)
    {
        return preg_match('/^\s*$/u', $c);
    }
    /**
     * Parse the given string and extract \Dcp\Mail\Address objects
     *
     * @param $s
     * @return \Dcp\Mail\Address[]
     * @throws MailAddrParserException
     */
    function parse($s)
    {
        $this->s = $s;
        $this->p = 0;
        $this->state = self::ST_lookingForStartOfMail;
        $this->esc = false;
        $addresses = array();
        $mail = '';
        $name = '';
        if ($this->eos()) {
            // Empty string
            return $addresses;
        }
        while (!$this->eos()) {
            $c = $this->peek();
            if ($this->debug) {
                error_log(__METHOD__ . " " . sprintf("(p=%s, state=%s) char='%s' {'%s', '%s'}%s", $this->p, $this->state, $c, $mail, $name, ($this->esc) ? ' (ESC)' : ''));
            }
            if ($c == '\\') {
                if (!$this->esc) {
                    $this->esc = true;
                    $this->next();
                    continue;
                }
            }
            switch ($this->state) {
                case self::ST_lookingForStartOfMail:
                    if ($this->esc) {
                        $name.= $c;
                        $this->esc = false;
                        $this->next();
                        if ($this->eos()) {
                            $this->state = self::ST_end;
                        } else {
                            $this->state = self::ST_inMail;
                        }
                    } elseif ($this->isSpace($c) || $c == ",") {
                        $this->next();
                        if ($this->eos()) {
                            $this->state = self::ST_end;
                        }
                    } else {
                        $name = '';
                        $mail = '';
                        $this->state = self::ST_inMail;
                    }
                    break;

                case self::ST_lookingForSeparator:
                    if ($this->esc) {
                        $this->esc = false;
                    } elseif ($this->isSpace($c)) {
                        // Discard spaces
                        
                    } elseif ($c == ",") {
                        $this->state = self::ST_lookingForStartOfMail;
                    } else {
                        if ($this->lax) {
                            // Reset state to lookup next mail address
                            $mail = '';
                            $name = '';
                        } else {
                            throw new MailAddrParserException(sprintf("Unexpected char '%s' at position %d: '%s'\n", $c, $this->p, $s));
                        }
                    }
                    $this->next();
                    if ($this->eos()) {
                        $this->state = self::ST_end;
                    }
                    break;

                case self::ST_inMail:
                    if ($this->esc) {
                        $name.= $c;
                        $this->esc = false;
                    } elseif ($c == '"') {
                        $this->state = self::ST_inQuotedDisplayName;
                    } elseif ($c == '<') {
                        $this->state = self::ST_inAngleMail;
                    } elseif ($c == ',' || $this->eosNext()) {
                        if ($c != ',') {
                            // Append the last char and flush the mail
                            $name.= $c;
                        }
                        $mail = trim($name);
                        if ($this->debug) {
                            error_log(__METHOD__ . " " . sprintf("Got {'%s'}", $mail));
                        }
                        $addresses[] = new Address($mail);
                        $mail = '';
                        $name = '';
                        if ($this->eosNext()) {
                            $this->state = self::ST_end;
                        } else {
                            $this->state = self::ST_lookingForStartOfMail;
                        }
                    } else {
                        $name.= $c;
                    }
                    $this->next();
                    break;

                case self::ST_inQuotedDisplayName:
                    if ($this->esc) {
                        $this->esc = false;
                        $name.= $c;
                    } elseif ($c == '"') {
                        $this->state = self::ST_inMail;
                    } else {
                        $name.= $c;
                    }
                    $this->next();
                    break;

                case self::ST_lookingForAngleMail:
                    if ($this->esc) {
                        $this->esc = false;
                    } elseif ($this->isSpace($c)) {
                        // Discard leading spaces
                        
                    } elseif ($c == '<') {
                        $this->state = self::ST_inAngleMail;
                    } else {
                        if ($this->lax) {
                            // Reset state to lookup next mail address
                            $mail = '';
                            $name = '';
                            $this->state = self::ST_lookingForSeparator;
                        } else {
                            throw new MailAddrParserException(sprintf("Unexpected char '%s' at position %d: '%s'", $c, $this->p, $s));
                        }
                    }
                    $this->next();
                    break;

                case self::ST_inAngleMail:
                    if ($c == '>') {
                        $mail = trim($mail);
                        $name = trim($name);
                        if ($this->debug) {
                            error_log(__METHOD__ . " " . sprintf("Got {'%s', '%s'}", $mail, $name));
                        }
                        $addresses[] = new Address($mail, $name);
                        $mail = '';
                        $name = '';
                        if ($this->eosNext()) {
                            $this->state = self::ST_end;
                        } else {
                            $this->state = self::ST_lookingForSeparator;
                        }
                    } elseif ($c == '<') {
                        if ($this->lax) {
                            // Reset state to lookup next mail address
                            $mail = '';
                            $name = '';
                            $this->state = self::ST_lookingForSeparator;
                        } else {
                            throw new MailAddrParserException(sprintf("Unnexpected char '%s' at position %d: '%s'", $c, $this->p, $s));
                        }
                    } else {
                        $mail.= $c;
                    }
                    $this->next();
                    break;

                case self::ST_end:
                    break;

                default:
                    throw new MailAddrParserException(sprintf("Unknown state '%s'.", $this->state));
            }
        }
        if (!$this->lax && $this->state != self::ST_end) {
            throw new MailAddrParserException(sprintf("Unterminated string in state '%s': '%s'", $this->state, $s));
        }
        return $addresses;
    }
}
