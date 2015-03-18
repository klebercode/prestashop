<?php

class ProductController extends ProductControllerCore {
	

	/*
	* module: fkcorreioslite
	* date: 2015-02-15 20:16:53
	* version: 160.2.1
	*/
	public function initContent() {
		parent::initContent();

        // Executa se for versao 1.6.x
        if (version_compare(substr(_PS_VERSION_, 0 ,5), '1.6.0', '>=')) {
            $this->context->smarty->assign(array(
                'HOOK_EXTRA_RIGHT' => Hook::exec('displayRightColumnProduct', array('product' => $this->product, 'category' => $this->category)),
            ));
        }

	}
    
}