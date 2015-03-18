<?php

  /*
  *
  * ADAPTADO PARA 1.5.X POR PRESTABR
  * http://prestabr.com.br 
  *
  */

include (dirname(__file__) . '/../../config/config.inc.php');
include (dirname(__file__) . '/../../header.php');
include (dirname(__file__) . '/boletobancario.php');

$context = Context::getContext();
$cart = $context->cart;
$boletobancario = new boletobancario();
//$currency = $boletobancario->getCurrency();
$conversao_taxa = Tools::convertPrice($cart->getOrderTotal(true, 3), $currency) + $valor_boleto;

if ($cart->id_customer == 0 OR $cart->id_address_delivery == 0 OR $cart->id_address_invoice == 0 OR !$boletobancario->active)
	Tools::redirect('index.php?controller=order&step=1');

$authorized = false;
foreach (Module::getPaymentModules() as $module)
	if ($module['name'] == 'boletobancario')
	{
		$authorized = true;
		break;
	}
if (!$authorized)
	die($boletobancario->l('This payment method is not available.', 'validation'));

$customer = new Customer((int)$cart->id_customer);

if (!Validate::isLoadedObject($customer))
	Tools::redirect('index.php?controller=order&step=1');

$currency = $context->currency;
$total = (float)($cart->getOrderTotal(true, Cart::BOTH));

$boletobancario->validateOrder($cart->id, Configuration::get('PS_OS_BANKWIRE'), $total, $boletobancario->displayName, NULL, array(), (int)$currency->id, false, $customer->secure_key);

$order = new Order($boletobancario->currentOrder);
Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$boletobancario->id.'&id_order='.$boletobancario->currentOrder.'&key='.$customer->secure_key);

?>
