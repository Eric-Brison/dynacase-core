<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

include_once ("Class.DbObj.php");
class DocTag extends DbObj
{
    public $fields = array(
        "initid", // doc initid
        "date", // date of entry
        "tag", // tag code
        "fromuid", // user what id of the user which has set the tag
        
    );
    /**
     * @var int $initid Document's identificator
     */
    public $initid;
    /**
     * @var string $date date record
     */
    public $date;
    /**
     * @var string $tag tag value
     */
    public $tag;
    /**
     * @var int $fromuid id of user wich has set the tag
     */
    public $fromuid;
    
    public $id_fields = array(
        "initid",
        "tag"
    );
    
    public $dbtable = "doctag";
    
    public $sqlcreate = "
create table doctag ( initid int not null,
                   date timestamp,
                   tag text,
                   fromuid int);
create index in_doctag on doctag(initid);
";
}
