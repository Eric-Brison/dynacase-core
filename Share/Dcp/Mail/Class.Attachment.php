<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Mail;

class Attachment implements DataSource
{
    public $file = '';
    public $name = '';
    public $type = '';
    public function __construct($file, $name = 'att.dat', $type = 'application/binary')
    {
        $this->file = $file;
        $this->name = $name;
        $this->type = $type;
    }
    public function getMimeType()
    {
        return $this->type;
    }
    public function getData()
    {
        return file_get_contents($this->file);
    }
    public function getName()
    {
        return $this->name;
    }
}
