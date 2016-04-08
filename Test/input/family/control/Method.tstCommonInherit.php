<?php
/*
 * @author Anakeen
 * @package FDL
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _TSTCOMMONINHERIT extends Doc
{
    /*
     * @end-method-ignore
    */
    /**
     * all document's folder are archieved
     * @apiExpose
     * @return string error message empty message if no error
     */
    
    protected function tstA()
    {
        return "Z";
    }
    
    protected function getANumber()
    {
        return "456";
    }
    
    public function preRefresh()
    {
        return $this->tstA();
    }
    
    public function getAReference()
    {
        return $this->title . '/' . $this->getANumber();
    }
    /**
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}
/*
 * @end-method-ignore
*/
?>