<?php

abstract class E2GProcessor {
    public $modx;
    public $config;
    public $lng;

    public function __construct(DocumentParser $modx, $config = array()) {
        $this->modx =& $modx;
        $this->config = $config;
    }

    abstract function process();
}