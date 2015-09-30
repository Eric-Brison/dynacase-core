<?php


namespace Dcp\Pu;

/**
 * Class TestAffect1
 * @package Dcp\Pu
 */
class TestAffect2 extends \Dcp\Family\Tst_Affect1
{

    protected function preAffect(array & $data, &$more, &$reset) {
        parent::preAffect($data, $more, $reset);
        $this->two++;
    }
    protected function postAffect(array  $data, $more, $reset) {
        parent::postAffect($data, $more, $reset);
        $this->two++;
    }
}