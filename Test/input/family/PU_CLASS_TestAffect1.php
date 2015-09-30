<?php


namespace Dcp\Pu;


class TestAffect1 extends \Dcp\Family\Document
{
    protected $one=0;
    protected $two=0;

    protected function preAffect(array & $data, &$more, &$reset) {
        $this->one++;
    }
    public function getOne() {
        return $this->one;
    }
    public function getTwo() {
        return $this->two;
    }
}