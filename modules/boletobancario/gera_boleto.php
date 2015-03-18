<?php

  /*
  *
  * ADAPTADO PARA 1.5.X POR PRESTABR
  * http://prestabr.com.br 
  *
  */

$useSSL = true;

include(dirname(__FILE__).'/../../config/config.inc.php');
//include(dirname(__FILE__).'/../../header.php');
//include(dirname(__FILE__).'/boletobancario.php');
require_once (dirname(__FILE__).'/boletobancario.php');

$context = Context::getContext();
//$cart = new Cart(Tools::getValue('id_cart'));

$id_cart = isset($_POST['id_cart']) ? $_POST['id_cart'] : Tools::getValue('id_cart');
$id_order = isset($_POST['id_order']) ? $_POST['id_order'] : Tools::getValue('id_order');
$id_banco = isset($_POST['id_banco']) ? $_POST['id_banco'] : Tools::getValue('id_banco');
$id_module = isset($_POST['id_module']) ? $_POST['id_module'] : Tools::getValue('id_module');

$module = Module::getInstanceById(intval($id_module));
$total = Tools::getValue('total');

$logo_url = Configuration::get('BOLETOBANCARIO_LOGO');

$order = new Order($id_order);
$secure_key = isset($_POST['secure_key']) ? $_POST['secure_key'] : $order->secure_key;

$valores = array(
		'id_cart'=>$id_cart,
		'id_order'=>$id_order,
		'id_module'=>$id_module,
		'id_banco'=>$id_banco,
		'secure_key'=>$secure_key
		);

$boleto = new boletobancario();
$dadosboleto = boletobancario::geraboleto($valores);
$taxa_boleto = '';

$context->smarty->assign('logo_url',Configuration::get('BOLETOBANCARIO_LOGO'));

foreach ($dadosboleto as $chave => $value)
{
	$dadosboleto[$chave] = ($value);
}

include  ("include/funcoes_$id_banco.php");
include  ("include/layout_$id_banco.php") ;

?>
