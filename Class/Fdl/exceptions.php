<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Exception class
 * @package FDL
 * @author Anakeen
 *
 */
namespace Dcp {
    /**
     * @brief Exception class
     * use exceptionCode to identifiy correctly exception
     * @class Exception
     * @author Anakeen
     */
    class Exception extends \Exception
    {
        private $dcpCode = '';
        private $httpHeader = array();
        /**
         * Redefined exception : message text is mandatory now
         * @param string $message error message or code error
         */
        public function __construct($message, $argCode = 0)
        {
            $code = $message;
            if ($code && (preg_match('/^([A-Z]+)([0-9]+)$/u', $code, $reg))) {
                $tArgs = array(
                    $code
                );
                $nargs = func_num_args();
                for ($ip = 1; $ip < $nargs; $ip++) {
                    $tArgs[] = func_get_arg($ip);
                }
                $msg = call_user_func_array("ErrorCode::getError", $tArgs);
                if ($msg) $message = $msg;
                $this->dcpCode = $code;
            }
            if ($argCode && is_numeric($argCode)) $intcode = intval($argCode);
            else $intcode = 0;
            
            parent::__construct($message, $intcode);
        }
        /**
         * return code error from constant of ErrorCode Class
         * @return string
         */
        public function getDcpCode()
        {
            if ($this->dcpCode) return $this->dcpCode;
            if (preg_match("/^{([^}]+)/u", $this->message, $reg)) {
                return $reg[1];
            }
            return '';
        }
        /**
         * return code error from constant of ErrorCode Class
         * @return string
         */
        public function getDcpMessage()
        {
            
            if (preg_match("/^{([^}]+)} *(.*)$/ums", $this->message, $reg)) {
                
                return $reg[2];
            }
            return $this->message;
        }
        /**
         */
        public function __toString()
        {
            return __CLASS__ . ": {$this->message}";
        }
        
        public function addHttpHeader($header)
        {
            $this->httpHeader[] = $header;
        }
        public function getHttpHeader()
        {
            return implode("\n", $this->httpHeader);
        }
    }
}

