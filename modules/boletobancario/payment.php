<?php

  /*
  *
  * ADAPTADO PARA 1.5.X POR PRESTABR
  * http://prestabr.com.br 
  *
  */

$useSSL = true;

require('../../config/config.inc.php');
Tools::displayFileAsDeprecated();

// init front controller in order to use Tools::redirect
$controller = new FrontController();
$controller->init();

Tools::redirect(Context::getContext()->link->getModuleLink('boletobancario', 'payment'));

?>
