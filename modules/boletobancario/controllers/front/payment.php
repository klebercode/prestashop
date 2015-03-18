<?php
/*
 * ADAPTADO PARA 1.5.X POR PRESTABR
 * http://prestabr.com.br 
 */
class BoletoBancarioPaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		global $cookie, $smarty, $cart;

		$this->display_column_left = false;
		parent::initContent();

	$cart = $this->context->cart;
        
	$invoiceAddress = new Address(intval($cart->id_address_invoice));
        //$customerPag = new Customer(intval($params['cart']->id_customer));
        $customerPag = new Customer(intval($cart->id_customer));


        $boleto_banco = Configuration::get('BOLETOBANCARIO_BANCO');
        $conf = Configuration::getMultiple(array('BOLETOBANCARIO_BANCO', 'BOLETOBANCARIO_IDENT', 'BOLETOBANCARIO_CNPJC', 'BOLETOBANCARIO_ENDER', 'BOLETOBANCARIO_ESTAD', 'BOLETOBANCARIO_CEDEN', 'BOLETOBANCARIO_PRAZO', 'BOLETOBANCARIO_TAXA'));
        $boleto_banco = array_key_exists('banco', $_POST) ? $_POST['banco'] : (array_key_exists
            ('BOLETOBANCARIO_BANCO', $conf) ? $conf['BOLETOBANCARIO_BANCO'] : '');
        $identificacao = array_key_exists('identificacao', $_POST) ? $_POST['identificacao'] : (array_key_exists
            ('BOLETOBANCARIO_IDENT', $conf) ? $conf['BOLETOBANCARIO_IDENT'] : '');
        $cpf_cnpj = array_key_exists('cpf_cnpj', $_POST) ? $_POST['cpf_cnpj'] : (array_key_exists
            ('BOLETOBANCARIO_CNPJC', $conf) ? $conf['BOLETOBANCARIO_CNPJC'] : '');
        $endereco_seu = array_key_exists('endereco', $_POST) ? $_POST['endereco'] : (array_key_exists
            ('BOLETOBANCARIO_ENDER', $conf) ? $conf['BOLETOBANCARIO_ENDER'] : '');
        $cidade_uf = array_key_exists('cidade_uf', $_POST) ? $_POST['cidade_uf'] : (array_key_exists
            ('BOLETOBANCARIO_ESTAD', $conf) ? $conf['BOLETOBANCARIO_ESTAD'] : '');
        $cedente = array_key_exists('cedente', $_POST) ? $_POST['cedente'] : (array_key_exists
            ('BOLETOBANCARIO_CEDEN', $conf) ? $conf['BOLETOBANCARIO_CEDEN'] : '');
        $prazo = array_key_exists('prazo', $_POST) ? $_POST['prazo'] : (array_key_exists
            ('BOLETOBANCARIO_PRAZO', $conf) ? $conf['BOLETOBANCARIO_PRAZO'] : '');
		$taxa = array_key_exists('taxa', $_POST) ? $_POST['taxa'] : (array_key_exists
            ('BOLETOBANCARIO_TAXA', $conf) ? $conf['BOLETOBANCARIO_TAXA'] : '');

        include (dirname(__file__) . "/../../boletophp/boleto_".$boleto_banco.".php");

            $all = '';
            $dados_boleto = '';
        foreach ($dadosboleto as $chave => $valor)
        {
            //$all .= "'" . "BB_$chave" . "', ";
            $dados_boleto[$chave] = Configuration::getMultiple(array("BB_$chave"));
        }
            $dadosdoboleto = '';
        foreach ($dados_boleto as $chave => $valor)
        {
            $dadosdoboleto .= '<input type="hidden" name="' . $chave . '" value="' . $dados_boleto[$chave]["BB_$chave"] .'" />';
        }

		$taxa_formatada = str_replace('.',',',$taxa);
		$data_venc = date("d/m/Y", time() + ($prazo * 86400));
	    	$valor_produtos = (float)$cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
	    	$valor_envio = (float)$cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
		$valor_cupons = (float)$cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
		$valor_embalagem = (float)$cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
		$valor_cobrado = (float)$cart->getOrderTotal(true);
		$totalSemImp = $valor_produtos + $valor_envio + $valor_embalagem + $valor_cupons;
		$valor_impostos = ($valor_cobrado - $totalSemImp);
		$valor_total = (($valor_produtos - ($valor_produtos * $taxa_formatada /100)) + $valor_envio + $valor_embalagem + $valor_cupons);
		$valor_boleto = $valor_total + $valor_impostos;

		$currency = $this->module->getCurrency((int)$cart->id_currency);
		$conversao_taxa = Tools::convertPrice($cart->getOrderTotal(true, 3), $currency) + $valor_boleto;
		$conv_tax_format = number_format($conversao_taxa, 2, ',', '.');
		
		$total = Tools::displayPrice($valor_boleto, $currency);

		$this->context->smarty->assign(array(
			'sign' => $currency->sign,
			'currencies' => $currency,
			'total' => $total,
			'imposto' => $valor_impostos,
			'desc' => $taxa,
			'isoCode' => Language::getIsoById(intval($cookie->id_lang)), 
			//'boletobancarioDetails' => $this->module->details,
		        //'boletobancarioAddress' => $this->module->address, 			
			'identificacao' => $identificacao, 
			'cpf_cnpj' => $cpf_cnpj, 
			'endereco_seu' => $endereco_seu, 
			'cidade_uf' => $cidade_uf, 
			'cedente' => $cedente, 
			'prazo' => $prazo, 
			//'nosso_numero'	=> $nosso_numero, 
			//'numero_documento' => $numero_documento, 
			'data_vencimento' => $data_venc,
            		'data_documento' => date("d/m/Y"), 
			'data_processamento' => date("d/m/Y"),
            		'banco' => $boleto_banco, 
			'sacado' => $cookie->customer_firstname . ' ' . $cookie-> customer_lastname, 
			'endereco1' => $invoiceAddress-> address1, 
			'endereco2' => $invoiceAddress-> address2 . " CEP: " . $invoiceAddress->postcode . ' - ' . $invoiceAddress->city,
           		//'email' => $customer-> email, 
			'url' => dirname(__file__), 
			'dadosdoboleto' => $dadosdoboleto,
		        'banco' => $boleto_banco, 
			'imgBtn' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/imagens/logo'.$boleto_banco.'.jpg',
			//'boletobancarioOwner' => $this->owner, 
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cart->id_currency,
			'currencies' => $this->module->getCurrency((int)$cart->id_currency),
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
		));
		$this->setTemplate('payment_execution.tpl');
	}
}
