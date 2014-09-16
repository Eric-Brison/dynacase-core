<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Mail;

interface DataSource {
    /**
     * @return string Mime type
     */
    public function getMimeType();
    /**
     * @return string Data content
     */
    public function getData();
    /**
     * @return string Name of content
     */
    public function getName();
}
