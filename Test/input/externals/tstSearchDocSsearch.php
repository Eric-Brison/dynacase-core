<?php
function tstSearchDocSsearch($start = "0", $slice = "ALL", $userid = 0)
{
    $s = new \SearchDoc();
    $s->setObjectReturn(false);
    $s->setSlice($slice);
    $s->setStart($start);
    $s->addFilter("name = 'SOMETHING_THAT_DOES_NOT_EXISTS'");
    return $s->search();
}
