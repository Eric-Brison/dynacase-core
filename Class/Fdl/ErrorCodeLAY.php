<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace {
    /**
     * Error codes used when generate template
     * @class ErrorCodeLAY
     * @brief List all error code for layout generation
     * @see ErrorCode
     * @see Layout
     * @see OooLayout
     */
    class ErrorCodeLAY
    {
        /**
         * @errorCode the template file is not found
         */
        const LAY0001 = 'Ooo Layout not found : %s';
        /**
         * the attribute HTMLTEXT must be placed in a single paragraph in OOo Layout
         * @errorCode the attribute HTMLTEXT must be placed in a single paragraph
         *
         * It can be placed in table cell or in frame if it is only
         */
        const LAY0002 = 'HTML attribute %s misplaced';
        /**
         * the file used for template seems to not be an openDocument Text file
         * @errorCode template file type error
         *
         */
        const LAY0003 = 'The file "%s" is not an openDocumentText';
        /**
         * An XML parse error occurs during content XML generation
         * the corrupted file contains the XML which has error
         * @errorCode open Document XML error
         *
         */
        const LAY0004 = 'Cannot produce open Document Text file from %s template';
        /**
         * the attribute HTMLTEXT must be placed in a single paragraph in OOo Layout
         * @errorCode the attribute HTMLTEXT must be placed in a single paragraph
         *
         * Thes section must be under 'office:text', 'text:text-content' or 'text:office-text-content-main'
         */
        const LAY0005 = 'HTML attribute %s misplaced';
        /**
         * The PHP exec() call failed
         * @errorCode The PHP exec() call failed (internal PHP dysfunction, kernel resource exhaustion, etc.)
         */
        const LAY0006 = "Error in exec() call: %s";
        /**
         * The decompression of the template failed
         * @errorCode The decompression of the template failed (missing 'unzip' command, invalid Zip file format, insufficient disk space, etc.)
         */
        const LAY0007 = "Decompression of '%s' failed with error: %s";
        /**
         * The <automatic-styles> tag was not found in the XSLT output
         */
        const LAY0008 = "Missing automatic-styles tag in '%s'";
        /**
         * The <automatic-styles> tag was not found in the ODT template
         */
        const LAY0009 = "Missing automatic-styles tag in ODT template '%s'";
        /**
         * for beautifier
         */
        private function _bo()
        {
            if (true) return;
        }
    }
}
namespace Dcp\Layout {
    class Exception extends \Dcp\Exception
    {
        /**
         * @var string corrupted file which content generation
         */
        protected $corruptedFile = '';
        /**
         * get Corrupted file : partial layout generation
         * @return string
         */
        public function getCorruptedFile()
        {
            return $this->corruptedFile;
        }
        /**
         * @param string $cf Corrupted file to set
         * @return mixed
         */
        public function setCorruptedFile($cf)
        {
            return $this->corruptedFile = $cf;
        }
        public function __construct($code, $message, $corruptedFile = '')
        {
            
            $this->corruptedFile = $corruptedFile;
            parent::__construct($code, $message);
        }
        /**
         * for beautifier
         */
        private function _bo()
        {
            if (true) return;
        }
    }
}
