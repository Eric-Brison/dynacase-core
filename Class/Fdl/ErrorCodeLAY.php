<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
