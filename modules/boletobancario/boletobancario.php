<?php

/*
* ADAPTADO PARA 1.5.X POR PRESTABR
* http://prestabr.com.br 
* @version 2.7
*/

/** MODULO ADAPTADO POR ODLANIER 
 * @author Odlanier de Souza Mendes
 * @copyright Dlani
 **/

##########################################
# Módulo Disponibilizado por Agência Pró #
#     http://www.agenciapro.com.br       #
#      USE MAS DEIXE OS CRÉDITOS!        #
#                                        #
#       MÓDULO CORRIDO PARA VERSÕES      #
#	         1.0 DO PRESTASHOP           #
##########################################

class boletobancario extends PaymentModule
{
    private $_html = '';
    private $_postErrors = array();
    public $currencies;
	public $logo_url; 
	
    public function __construct()
    {
        $this->name = 'boletobancario';
        $this->tab = 'payments_gateways';
        $this->version = '2.7.1';
        $this->author = 'Adaptado por PrestaBR';

        $this->currencies = true;
        $this->currencies_mode 	= 'radio';

        parent::__construct();

        /* The parent construct is required for translations */
        $this->page = basename(__file__, '.php');
        $this->displayName = $this->l('Boleto Bancario');
        $this->description = $this->l('Aceitar pagamentos em Boleto Banc&aacute;rio');
        $this->confirmUninstall = $this->l('Tem certeza de que pretende eliminar os seus dados?');
        $this->textshowemail= $this->l('Caso não tenha impresso o seu Boleto, utilize o link abaixo para gerar uma 2ª. via.');

	/*if (!sizeof(Currency::checkPaymentCurrencies($this->id)))
	$this->warning = $this->l('Por favor defina uma moeda padrão.');*/
    }

    public function install()
    {
		if(!Configuration::get('_PS_OS_BOLETO_')>0)
		{
			$this->criaOS();
			$this->copiaEmail();
		}
        if (!parent::install() or 
			!Configuration::updateValue('BOLETOBANCARIO_BANCO', 'nenhum') or 
			!Configuration::updateValue('BOLETOBANCARIO_PRAZO', 'nenhum') or 
			!Configuration::updateValue('BOLETOBANCARIO_IDENT', 'nenhum') or 
			!Configuration::updateValue('BOLETOBANCARIO_CNPJC', 'nenhum') or 
			!Configuration::updateValue('BOLETOBANCARIO_ENDER', 'nenhum') or 
			!Configuration::updateValue('BOLETOBANCARIO_ESTAD', 'nenhum') or 
			!Configuration::updateValue('BOLETOBANCARIO_CEDEN', 'nenhum') or 
			!Configuration::updateValue('BOLETOBANCARIO_LOGO', (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'img/logo.jpg') or 
			!$this->registerHook('payment') or 
			!$this->registerHook('paymentReturn') or 
			!$this->registerHook('header') or 
			!$this->registerHook('displayOrderDetail')
			)
			return false;
		return true;
    }

	public function criaOS()
	{
		$orderState=new OrderState();
		$orderState->name=array();
		$orderState->template=array();
		foreach(Language::getLanguages() as $language){
			$orderState->name[$language["id_lang"]]="Boleto - Pendente";
			$orderState->template[$language["id_lang"]]="boletobancario";
		}
		$orderState->send_email=true;
		$orderState->color="#0060FF";
		$orderState->unremovable=false;
		$orderState->hidden=false;
		$orderState->delivery=false;
		$orderState->logable=false;
		$orderState->invoice=false;
		if($orderState->add())
			copy(dirname(__FILE__).'/logo.gif',_PS_IMG_DIR_.'os/'.$orderState->id.'.gif');
		Configuration::updateValue("_PS_OS_BOLETO_",(int)$orderState->id);
	}

	public function copiaEmail()
	{
		$languages=Language::getLanguages();
		$mailTxtPath=dirname(__FILE__).'/mails/br/boletobancario.txt';
		$mailHtmlPath=dirname(__FILE__).'/mails/br/boletobancario.html';
		foreach($languages as $lang)
		{
			$mailDestTxt=_PS_MAIL_DIR_.strtolower($lang["iso_code"])."/boletobancario.txt";
			$mailDestHtml=_PS_MAIL_DIR_.strtolower($lang["iso_code"])."/boletobancario.html";
			copy($mailTxtPath,$mailDestTxt);
			copy($mailHtmlPath,$mailDestHtml);
		}
	}

    public function uninstall()
    {
        if (!Configuration::deleteByName('BOLETOBANCARIO_BANCO') or 
			!Configuration::deleteByName('BOLETOBANCARIO_PRAZO') or 
			!Configuration::deleteByName('BOLETOBANCARIO_IDENT') or
			!Configuration::deleteByName('BOLETOBANCARIO_CNPJC') or 
			!Configuration::deleteByName('BOLETOBANCARIO_ENDER') or 
			!Configuration::deleteByName('BOLETOBANCARIO_ESTAD') or
			!Configuration::deleteByName('BOLETOBANCARIO_LOGO') or
			!Configuration::deleteByName('BOLETOBANCARIO_CEDEN') or 
			parent::uninstall())
			return false;
		return true;
    }

    // Gera Botão no Histórico de Pedidos
    public function hookDisplayOrderDetail($params)
    {
	global $smarty;
	$boleto_banco = Configuration::get('BOLETOBANCARIO_BANCO');
	$smarty->assign(array('id_module' => $this->id,'id_banco' => $boleto_banco));
        return $this->display(__file__, 'views/templates/front/botao_gera_boleto.tpl');
    }

	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}

    public function getContent()
    {
        $this->_html = '<h2>Boleto PHP</h2>';
        if ($_POST['change_bank'] == true)
        {
            Configuration::updateValue('BOLETOBANCARIO_BANCO', $_POST['banco']);
            $this->displayConf();
        }

        if (isset($_POST['submitBoletoBancario']))
        {
        	
            //$_FILES['logotipo']['size'] > 0 ? include(dirname(__file__) . "/include_class/upload_logo.inc") : false;
			
			foreach ($_POST as $chave => $valor)
            {
                Configuration::updateValue("BB_$chave", $valor);
            }
            Configuration::updateValue('BOLETOBANCARIO_TAXA',  $_POST['taxa']);
            Configuration::updateValue('BOLETOBANCARIO_PRAZO', $_POST['prazo']);
            Configuration::updateValue('BOLETOBANCARIO_IDENT', $_POST['identificacao']);
            Configuration::updateValue('BOLETOBANCARIO_CNPJC', $_POST['cpf_cnpj']);
            Configuration::updateValue('BOLETOBANCARIO_ENDER', $_POST['endereco']);
            Configuration::updateValue('BOLETOBANCARIO_ESTAD', $_POST['cidade_uf']);
            Configuration::updateValue('BOLETOBANCARIO_LOGO', $_POST['logotipo']);
            Configuration::updateValue('BOLETOBANCARIO_CEDEN', $_POST['cedente']);
            $this->displayConf();
        }

		$this->displayboletobancario();
		$this->displayFormSettingsboletobancario();
		return $this->_html;
    }


    public function displayConf()
    {
        $this->_html.= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmação') . '" />'.$this->l('Configura&ccedil;&otilde;es atualizadas').'</div>';
    }

    public function displayErrors()
    {
             $nbErrors = sizeof($this->_postErrors);
             $this->_html.= '
		<div class="alert error">
			<h3>' . ($nbErrors > 1 ? $this->l('Esse') : $this->l('e esse')) . ' ' .
              $nbErrors . ' ' . ($nbErrors > 1 ? $this->l('erros') : $this->l('erro')) .
            		'</h3><ol>';

        foreach ($this->_postErrors as $error)
            $this->_html.= '<li>' . $error . '</li>';
            $this->_html.= '</ol></div>';
    }

    public function displayboletobancario()
    {
        $this->_html.= '
		<img src="../modules/boletobancario/boletophp.jpg" style="float:left; margin-right:15px;" />
		<a href="http://prestabr.com.br/" title="PrestaBR" target="_blank"><img src="https://prestabr.com.br/logo_mini.png" style="float:right; margin-left:15px;" /></a>
		<b>' . $this->l('Este m&oacute;dulo permite aceitar pagamentos via Boleto Banc&aacute;rio.') .
               '</b><br /><br />
		' . $this->l('Selecione o Banco corresponde a sua ag&ecirc;ncia cobradora e configure com aten&ccedil;ão as informa&ccedil;&otilde;es banc&aacute;rias,') .'
		<br />
		' .$this->l('busque orienta&ccedil;ão do seu gerente. Adaptado para 1.5.x por PRESTABR.').'
		<br /><br /><br />';
    }

    public function displayFormSettingsboletobancario()
    {

        $conf = Configuration::getMultiple(array('BOLETOBANCARIO_BANCO',
            'BOLETOBANCARIO_IDENT', 'BOLETOBANCARIO_CNPJC', 'BOLETOBANCARIO_ENDER',
            'BOLETOBANCARIO_ESTAD', 'BOLETOBANCARIO_CEDEN', 'BOLETOBANCARIO_PRAZO', 'BOLETOBANCARIO_TAXA', 'BOLETOBANCARIO_LOGO'));

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
        $logo_url = array_key_exists('logotipo', $_POST) ? $_POST['logotipo'] : (array_key_exists
            ('BOLETOBANCARIO_LOGO', $conf) ? $conf['BOLETOBANCARIO_LOGO'] : 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT,'UTF-8').__PS_BASE_URI__.'/img/logo.jpg');

        include (dirname(__file__) . "/include_class/form_select_banco.inc");
        if ($boleto_banco != 'nenhum')
        {
            $this->_html.= '
			<form action="" method="post" enctype="multipart/form-data">
			<input type="hidden" name="variavelbanco" value="'.$boleto_banco.'">';

		    $taxa_boleto = $taxa;
            include (dirname(__file__) . "/boletophp/boleto_".$boleto_banco.".php");

            $all = '';
            $dados_boleto = "";
            foreach ($dadosboleto as $chave => $valor)
            {
                //$all .= "'" . "BB_$chave" . "', ";
                $dados_boleto[$chave] = Configuration::get(("BB_".$chave.""));
            }

            //print_r($dados_boleto);

            //include (dirname(__file__) . "/include_class/form_banco_todos.inc");
   
		   $this->_html .= '
			  <br>
			  <fieldset>
				 <legend><img src="../img/admin/money.gif" />'.$this->l('Configurações do Boleto').'</legend>
				 <br />
				 <label>'.$this->l('Logotipo do Boleto').'</label>         
				 <div class="margin-form">
					 <img src="'.$logo_url.'" alt="logo" /><br /><br />
					 <input type="text" size="33" class="text" name="logotipo" id="logotipo" value="'.$logo_url.'">
					 '.$this->l('Requisitos: Altura Máx.:').' 100px '.$this->l('Largura Máx.:').'200px
				</div>
				 <br />
				 
				 <label>'.$this->l('Prazo de Vencimento').'</label>         
				 <div class="margin-form">
				 	<input type="text" size="33" name="prazo" value="'.$prazo.'" /> '.$this->l('Ex. 5').' 
				 </div>
				 <br />
				 
				 <label>'.$this->l('Taxa de desconto para Boleto').'</label>         
				 <div class="margin-form">
				 	<input type="text" size="33" name="taxa" value="'.$taxa.'" /> '.$this->l('Ex. 1,5').' 
				 </div>
				 <br />';
		
			foreach ( $dadosboleto as $a => $b )
			{
				$label = str_replace('_', ' ', ucwords($a));
				$this->_html .=  '   
						 <label>'.$label.'</label>         
						 <div class="margin-form">
						 <input type="text" size="33" name="'.$a.'" value="'.$dados_boleto[$a].'" /> Ex.: '.htmlspecialchars( utf8_encode($b)).'</div>
						 <br />';
			};
			$this->_html .= '</fieldset>';
          //include (dirname(__file__) . "/include_class/form_banco_instrucoes.inc");
            include (dirname(__file__) . "/include_class/form_banco_seusdados.inc");
            $this->_html .= '
			</form>';

        }

    }

    public function hookPayment($params)
    {

        global $smarty;
        $addressPag = new Address(intval($params['cart']->id_address_invoice));
        $customerPag = new Customer(intval($params['cart']->id_customer));
        $boleto_banco = Configuration::get('BOLETOBANCARIO_BANCO');
        $conf = Configuration::getMultiple(array(
			'BOLETOBANCARIO_BANCO',
			'BOLETOBANCARIO_IDENT', 
			'BOLETOBANCARIO_CNPJC', 
			'BOLETOBANCARIO_ENDER',
			'BOLETOBANCARIO_ESTAD', 
			'BOLETOBANCARIO_CEDEN', 
			'BOLETOBANCARIO_PRAZO'
		));
		
        $boleto_banco = array_key_exists('banco', $_POST) ? $_POST['banco'] : (array_key_exists
            ('BOLETOBANCARIO_BANCO', $conf) ? $conf['BOLETOBANCARIO_BANCO'] : '');
        $smarty->assign(array(
			'imgBtn' => "modules/boletobancario/imagens/logo".$boleto_banco.".jpg",
            'this_path' => $this->_path, 
			'desc' => Configuration::get('BOLETOBANCARIO_TAXA'),
			'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/' . $this->name . '/'));
        //return utf8_encode( htmlspecialchars_decode($this->display(__file__, 'views/templates/hook/payment.tpl')) );
		return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookPaymentReturn($params)
    {
        global $smarty, $cart;
		
		$cart = new Cart(Tools::getValue('id_cart'));

		$taxa = Configuration::get('BOLETOBANCARIO_TAXA');
		$prazo = Configuration::get('BOLETOBANCARIO_PRAZO');

        $state 	= $params['objOrder']->getCurrentState();  
        $order = new Order($params['objOrder']->id);
		$boleto_banco = Configuration::get('BOLETOBANCARIO_BANCO');

		$taxa_formatada = $taxa;
		$data_venc = date("d/m/Y", time() + ($prazo * 86400));
	    $valor_produtos = (float)$cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
	    $valor_envio = (float)$cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
		$valor_cupons = (float)$cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
		$valor_embalagem = (float)$cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
		$valor_cobrado = (float)$params['total_to_pay'];
		$totalSemImp = $valor_produtos + $valor_envio + $valor_embalagem + $valor_cupons;
		$valor_impostos = ($valor_cobrado - $totalSemImp);
		$valor_total = (($valor_produtos - ($valor_produtos * $taxa_formatada /100)) + $valor_envio + $valor_embalagem + $valor_cupons);
		$valor_boleto = $valor_total + $valor_impostos;

		if ($state == Configuration::get('_PS_OS_BOLETO_') || $state == Configuration::get('PS_OS_OUTOFSTOCK'))
			$smarty->assign(array(
				'total_to_pay' => Tools::displayPrice(round($valor_boleto, 2), $params['currencyObj'], true),
				'total' =>$valor_cobrado,
				'desc' => Configuration::get('BOLETOBANCARIO_TAXA'),
				'prazo' => Configuration::get('BOLETOBANCARIO_PRAZO'),
				'cart' => Tools::getValue('id_cart'),
				'status' => 'ok',
				'id_order' => $params['objOrder']->id,
				'secure_key'=> $params['objOrder']->secure_key,
				'id_module' => $this->id,
				'url' =>  (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ .'modules/boletobancario/gera_boleto.php',
				'id_banco' => $boleto_banco
			));
        else
       		$smarty->assign('status', 'failed');

        //return utf8_encode( htmlspecialchars_decode($this->display(__file__, 'views/templates/hook/payment_return.tpl')) );
		return $this->display(__FILE__, 'payment_return.tpl');

    }
	
	public function codificacao($string) 
	{
        return mb_detect_encoding($string.'x', 'UTF-8, ISO-8859-1');
    }
	
	/**
	  * Gera o boleto à partir de uma lista de parametros
	  * @param banco
	  * @param id_order
	  * @param id_cart
	  * @return array dadosboleto
	  * Essa função por padrão é chamada em
	  * gera_boleto.php 
	  */

    public static function geraboleto($params)
    {
        $banco 	= isset($params['id_banco']) ? $params['id_banco'] : Tools::getValue('id_banco');
        $id_cart = isset($params['cart']->id) ? $params['cart']->id : Tools::getValue('id_cart');
		
        $cart = new Cart($id_cart);
	$customerPag = new Customer(intval($cart->id_customer));        
        $invoiceAddress = new Address(intval($cart->id_address_invoice));

	include (dirname(__file__) . "/boletophp/boleto_".$banco.".php");

        $all = '';
        $dados_boleto = '';
        foreach ($dadosboleto as $chave => $valor)
        {
            $dados_boleto[$chave] = (Configuration::get("BB_".$chave.""));
        }
        foreach ($dados_boleto as $chave => $valor)
        {   
            $dadosboleto[$chave] = $dados_boleto[$chave];	
        }

        $conf = Configuration::getMultiple(array(
			'BOLETOBANCARIO_BANCO',
            'BOLETOBANCARIO_IDENT', 
			'BOLETOBANCARIO_CNPJC', 
			'BOLETOBANCARIO_ENDER',
            'BOLETOBANCARIO_ESTAD', 
			'BOLETOBANCARIO_CEDEN', 
			'BOLETOBANCARIO_PRAZO', 
			'BOLETOBANCARIO_TAXA')
			);
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

		$boleto_banco = Configuration::get('BOLETOBANCARIO_BANCO');

		$taxa_formatada = (float)$taxa;
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
		
		$numero = (isset($invoiceAddress->company) && is_numeric($invoiceAddress->company) ? $invoiceAddress->company : $invoiceAddress->company); 
		
		$id_order = isset($_POST['id_order']) ? $_POST['id_order'] : Tools::getValue('id_order');

		$dadosboleto['valor_boleto'] = number_format((float)$valor_boleto, 2, ',', '.');
		$dadosboleto['nosso_numero'] = ($id_order);
		$dadosboleto['numero_documento'] = (str_pad($id_order, 7, 0, STR_PAD_LEFT)); 
		$dadosboleto['data_vencimento'] = ($data_venc);
        $dadosboleto['data_documento'] = (date("d/m/Y")); 
		$dadosboleto['data_processamento'] = (date("d/m/Y"));
		$dadosboleto['identificacao'] = ($identificacao);
		$dadosboleto['cpf_cnpj'] = ($cpf_cnpj);
		$dadosboleto['endereco'] = ($endereco_seu);
		$dadosboleto['cidade_uf']= ($cidade_uf);
		$dadosboleto['cedente'] = ($cedente);
		$dadosboleto['sacado'] 	= ($customerPag->firstname . ' ' . $customerPag->lastname).(isset($customerPag->cpf) ? ' (CPF: '.$customerPag->cpf.')' : ''); 
		$dadosboleto['endereco1'] = ($invoiceAddress->address1.(isset($numero) && $numero != '' ? ', '.$numero : '').(isset($invoiceAddress->other) ? ' - '.$invoiceAddress->other : ''));
		$dadosboleto['endereco2'] = ($invoiceAddress->address2 . " CEP: " . $invoiceAddress->postcode . ' - ' . $invoiceAddress->city);
		
        return $dadosboleto;
        
    }
	public function hookHeader($params)
	{
		$this->context->controller->addCSS(($this->_path).'css/boletobancario.css', 'all');
	}

}

?>
