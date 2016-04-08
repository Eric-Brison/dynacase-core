<?php
/*
 * @author Anakeen
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
