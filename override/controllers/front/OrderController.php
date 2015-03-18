<?php

class OrderController extends OrderControllerCore {
	

	/*
	* module: fkcorreioslite
	* date: 2015-02-15 20:16:53
	* version: 160.2.1
	*/
	public function initContent() {
		parent::initContent();

        if ((int)$this->step == 2) {
            Hook::exec('displayBeforeCarrier');
            $this->setTemplate(_PS_THEME_DIR_.'order-carrier.tpl');
        }
	

	}
}