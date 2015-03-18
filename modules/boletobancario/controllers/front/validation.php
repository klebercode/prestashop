<?php

  /*
   * ADAPTADO PARA 1.5.X POR PRESTABR
   * http://prestabr.com.br 
   */

//include (dirname(__file__) . '/boletobancario.php');

class boletobancarioValidationModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
		$boletobancario = new boletobancario();
		$cart = $this->context->cart;
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');

		// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'boletobancario')
			{
				$authorized = true;
				break;
			}
		if (!$authorized)
			die($this->module->l('This payment method is not available.', 'validation'));

		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		$currency = $this->context->currency;
		
		$total = Tools::convertPrice($valor_boleto, $currency);
		//$total = $conversao_taxa;
		
		$boleto_banco = Configuration::get('BOLETOBANCARIO_BANCO');
		
		$link_boleto = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->module->name.'/gera_boleto.php?id_cart='.$cart->id.'&id_banco='.$boleto_banco.'&id_module='.$boletobancario->id;
		
		//$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
		$mailVars = array(
			'{texto_boleto}'	=> $boletobancario->textshowemail,
			'{link_boleto}' 	=> '<a href="'.$link_boleto.'" target="_blank">GERAR BOLETO</a>',
			'{total}'			=> $valor_boleto
		);

		$this->module->validateOrder($cart->id, Configuration::get('_PS_OS_BOLETO_'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
		Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
	}
}
