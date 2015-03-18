<?php

require_once('FKcorreiosLiteCorreios.php');
require_once('FKcorreiosLiteFuncoes.php');

class fkcorreioslite extends CarrierModule {

    // Contem o id do Carrier em execucao
	public $id_carrier;
    
	private $_urlWs = 'http://www.fokusfirst.com/fokusfirst/loja/modules/fkcontrol/fkservices.wsdl';
    private $_erroWs = '';
 	private $_idProduto = '76';

 	private $_path_logo;
    private $_url_funcoes;
    private $_prazo_entrega = array();

	private $_html = '';
	private $_postErrors = array();
	
	public function __construct() {
	
		$this->name = 'fkcorreioslite';
		$this->tab = 'shipping_logistics';
		$this->version = '160.2.1';
		$this->author = 'FokusFirst';
	
		parent::__construct();
	
		$this->displayName = $this->l('Módulo FKcorreios Lite');
		$this->description = $this->l('Oferece aos seus clientes os serviços de entregas PAC e SEDEX dos Correios do Brasil.');
	
		// Path da pasta com logos dos carrier
		$this->_path_logo = _PS_MODULE_DIR_.$this->name.'/upload/';

        // Path de funcoes.php para ser passado ao js
        $this->_url_funcoes = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'. $this -> name.'/FKcorreiosLiteFuncoes.php';
	}
	
	public function install() {
		
		if (!parent::install()
			Or !$this->criaTabelas()
            Or !$this->instalaRegioes()
			Or !$this->registerHook('displayBackOfficeHeader')
			Or !$this->registerHook('actionCarrierUpdate')
            Or !$this->registerHook('displayBeforeCarrier')
            Or !$this->registerHook('displayFooterProduct')
            Or !$this->registerHook('displayShoppingCartFooter')
            Or !$this->registerHook('displayHeader')
            Or !$this->registerHook('displayRightColumnProduct')
			Or !Configuration::updateValue('FKCORREIOS_REFERENCIA', '')
            Or !Configuration::updateValue('FKCORREIOS_DOMINIO', '')
            Or !Configuration::updateValue('FKCORREIOS_PROPRIETARIO', '')
			Or !Configuration::updateValue('FKCORREIOS_MEU_CEP', '')
			Or !Configuration::updateValue('FKCORREIOS_BLOCO_PRODUTO', 'on')
            Or !Configuration::updateValue('FKCORREIOS_BLOCO_POSICAO', '0')
			Or !Configuration::updateValue('FKCORREIOS_BLOCO_CARRINHO', 'on')
			Or !Configuration::updateValue('FKCORREIOS_EMBALAGEM', '1')) {
			
			return false;
		}
		
		return true;
		
	}
	
	public function uninstall() {
		
		if (!parent::uninstall()
			Or !$this->excluiCarrier()
			Or !$this->excluiTabelas()
			Or !$this->unregisterHook('displayBackOfficeHeader')
			Or !$this->unregisterHook('actionCarrierUpdate')
            Or !$this->unregisterHook('displayBeforeCarrier')
            Or !$this->unregisterHook('displayFooterProduct')
            Or !$this->unregisterHook('displayShoppingCartFooter')
            Or !$this->unregisterHook('displayHeader')
            Or !$this->unregisterHook('displayRightColumnProduct')) {
			
			return false;
		}
        
        // Exclui dados de Configuração
        if (!Db::getInstance()->delete("configuration", "name LIKE 'FKCORREIOS_%'")) {
            return false;
        }
		
		return true;
		
	}
	
	public function getContent() {

		$this->_html = '';
		$this->_html .= '<h2>'.$this->l('Módulo FKcorreios Lite').'</h2>';
	
		if (!empty($_POST)) {
			
			$this->postValidation();
			
			if (!sizeof($this ->_postErrors)) {
				$this->_html .= $this->displayConfirmation($this->l('Configuração atualizada'));
			}else {
				foreach ($this->_postErrors AS $erro) {
					$this->_html .= '<div class="alert error"><img src="'._PS_IMG_.'admin/forbbiden.gif" alt="nok" />&nbsp;'.$erro.'</div>';
				}
			
				$this->_html .= $this->displayError($this->l('Configuração falhou'));
			}
		}
	
		return $this->displayForm();
	}
	
	private function postValidation() {
		
		$sessao = Tools::getValue('section');
		
		switch($sessao) {
			
			case 'configRegistro_1':
				 
				if (Tools::isSubmit('submitSave')) {
					$this->validaRegistro_1($sessao);
				}
				
				break;
				 
			case 'configRegistro_2':
				 
				if (Tools::isSubmit('submitSave')) {
					if (!$this->wsAlteraLicenca(Configuration::get('FKCORREIOS_REFERENCIA'), Tools::getShopDomain(false,true))) {
						$this->_postErrors[] = $this->_erroWs;
					}
					
					if (!$this->_postErrors) {
						$this->postProcess($sessao);
					}
				}
				 
				break;
				
			case 'configGeral':

                // Exclusao do cache
                if (Tools::isSubmit('submitCache')) {
                    $this->postProcess($sessao);
                    break;
                }

				if (Tools::isSubmit('submitSave')) {
					$this->validaGeral($sessao);
				}
				
				break;
				
			case 'configCadastroCep':
			
				if (Tools::isSubmit('submitSave')) {
					$this->validaCadastroCep($sessao);
				}
				
				break;
				
			case 'configEmbalagens':
				
				// Inclui/Exclui embalagem
				if (Tools::isSubmit('submitAdd') Or Tools::isSubmit('submitDel')) {
					$this->postProcess($sessao);
					break;
				}
				
				// Verifica configuracoes das embalagens
				if (Tools::isSubmit('submitSave')) {
					$this->validaEmbalagens($sessao);
				}
					
				break;
				
			case 'configEspecifCorreios':				
				
				if (Tools::isSubmit('submitSave')) {
					$this->validaEspecifCorreios($sessao);
				}
				
				break;
				
				
			case 'configServicosCorreios':
				
				// Inclui/Exclui servico
				if (Tools::isSubmit('submitAdd') Or Tools::isSubmit('submitDel')) {
					$this->postProcess($sessao);
					break;
				}
				
				// Verifica configuracoes dos servicos dos Correios
				if (Tools::isSubmit('submitSave')) {
					$this->validaServicosCorreios($sessao);
				}
					
				break;

            case 'configFreteGratis':

                if (Tools::isSubmit('submitAdd') Or Tools::isSubmit('submitDel')) {
                    $this->postProcess($sessao);
                }

                // Verifica configuracoes do frete gratis
                if (Tools::isSubmit('submitSave')) {
                    $this->validaFreteGratis($sessao);
                }

                break;

        }
	}

	private function validaRegistro_1($sessao) {
		
		// Recupera valores
		$referencia = Trim(Tools::getValue('fkcorreios_referencia'));
		$dominio = Trim(Tools::getShopDomain(false,true));
		$proprietario = Trim(Tools::getValue('fkcorreios_proprietario'));
        $aceite = Trim(Tools::getValue('fkcorreios_aceite'));
		 
		if ($referencia == '' or $dominio == '' or $proprietario == '') {
			$this->_postErrors[] = $this->l('Todos os campos são necessários para Registro da Licença.');
		}
        
        if (!$aceite) {
            $this->_postErrors[] = $this->l('Favor aceitar os Termos de Suporte.');
            return false;
        }
		 
		if (!$this->wsRegistraLicenca($referencia, $dominio, $proprietario)) {
			$this->_postErrors[] = $this->_erroWs;
		}
		 
		if (!$this->_postErrors) {
			$this->postProcess($sessao);
		}
	}

	private function validaGeral($sessao) {
	
		if (Tools::getValue('fkcorreios_meu_cep') == NULL) {
			$this->_postErrors[] = $this->l('Meu CEP não preenchido');
		}

		if (!$this->_postErrors) {
			$this->postProcess($sessao);
		}
	}
	
	private function validaCadastroCep($sessao) {
	
		$estados_capitais = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'fkcorreios_cadastro_cep');
	
		foreach ($estados_capitais as $reg) {
				
			$intervalos = explode("/", Tools::getValue('fkcorreios_cep_estado_'.$reg['id']));
				
			foreach ($intervalos as $intervalo) {
                if ($intervalo == ''){
                    continue;
                }

				if (strlen($intervalo) < 17) {
					$this->_postErrors[] = $this->l('"Intervalo de CEP dos Estados" com erro. Estado').': '.$reg['estado'];
				}
			}
				
			$intervalos = explode("/", Tools::getValue('fkcorreios_cep_capital_'.$reg['id']));
	
			foreach ($intervalos as $intervalo) {
                if ($intervalo == ''){
                    continue;
                }

				if (strlen($intervalo) < 17) {
					$this->_postErrors[] = $this->l('"Intervalo de CEP das Capitais" com erro. Estado').': '.$reg['estado'];
				}
			}

            if (Tools::getValue('fkcorreios_cep_base_capital_'.$reg['id']) == NULL) {
                $this->_postErrors[] = $this->l('CEP base - Capital não preenchido. Estado').': '.$reg['estado'];
            }else {
                $valor = str_replace('-', '', Tools::getValue('fkcorreios_cep_base_capital_'.$reg['id']));

                if (!is_numeric($valor)) {
                    $this->_postErrors[] = $this->l('O campo "CEP base - Capital é inválido. Estado').': '.$reg['estado'];
                }
            }

            if (Tools::getValue('fkcorreios_cep_base_interior_'.$reg['id']) == NULL) {
                $this->_postErrors[] = $this->l('CEP base - Interior não preenchido. Estado').': '.$reg['estado'];
            }else {
                $valor = str_replace('-', '', Tools::getValue('fkcorreios_cep_base_interior_'.$reg['id']));

                if (!is_numeric($valor)) {
                    $this->_postErrors[] = $this->l('O campo "CEP base - Interior é inválido. Estado').': '.$reg['estado'];
                }
            }

        }
	
		if (!$this->_postErrors) {
			$this->postProcess($sessao);
		}
	}
	
	private function validaEmbalagens($sessao) {
	
		$embalagens = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'fkcorreios_embalagens` Where `id_shop` = '.$this->context->shop->id);
	
		foreach ($embalagens as $reg) {
				
			if (Tools::getValue('fkcorreios_descricao_'.$reg['id']) == NULL) {
				$this->_postErrors[] = $this->l('Descrição não preenchida');
			}
				
			if (Tools::getValue('fkcorreios_comprimento_'.$reg['id']) == NULL) {
				$this->_postErrors[] = $this->l('Comprimento não preenchido');
			}else {
				$valor = str_replace(',', '.', Tools::getValue('fkcorreios_comprimento_'.$reg['id']));
	
				if (!is_numeric($valor)) {
					$this->_postErrors[] = $this->l('O campo "Comprimento" não é numérico');
				}else {
                    if ($valor < 0) {
                        $this->_postErrors[] = $this->l('O campo "Comprimento" não pode ser menor que 0 (zero)');
                    }
                }
			}
				
			if (Tools::getValue('fkcorreios_altura_'.$reg['id']) == NULL) {
				$this->_postErrors[] = $this->l('Altura não preenchida');
			}else {
				$valor = str_replace(',', '.', Tools::getValue('fkcorreios_altura_'.$reg['id']));
	
				if (!is_numeric($valor)) {
					$this->_postErrors[] = $this->l('O campo "Altura" não é numérico');
				}else {
                    if ($valor < 0) {
                        $this->_postErrors[] = $this->l('O campo "Altura" não pode ser menor que 0 (zero)');
                    }
                }
			}
				
			if (Tools::getValue('fkcorreios_largura_'.$reg['id']) == NULL) {
				$this->_postErrors[] = $this->l('Largura não preenchida');
			}else {
				$valor = str_replace(',', '.', Tools::getValue('fkcorreios_largura_'.$reg['id']));
	
				if (!is_numeric($valor)) {
					$this->_postErrors[] = $this->l('O campo "Largura" não é numérico');
				}else {
                    if ($valor < 0) {
                        $this->_postErrors[] = $this->l('O campo "Largura" não pode ser menor que 0 (zero)');
                    }
                }
			}
				
			if (Tools::getValue('fkcorreios_peso_'.$reg['id']) == NULL) {
				$this->_postErrors[] = $this->l('Peso não preenchido');
			}else {
				$valor = str_replace(',', '.', Tools::getValue('fkcorreios_peso_'.$reg['id']));
	
				if (!is_numeric($valor)) {
					$this->_postErrors[] = $this->l('O campo "Peso" não é numérico');
				}else {
                    if ($valor < 0) {
                        $this->_postErrors[] = $this->l('O campo "Peso" não pode ser menor que 0 (zero)');
                    }
                }
			}
				
			if (Tools::getValue('fkcorreios_custo_'.$reg['id']) == NULL) {
				$this->_postErrors[] = $this->l('Custo não preenchido');
			}else {
				$valor = str_replace(',', '.', Tools::getValue('fkcorreios_custo_'.$reg['id']));
	
				if (!is_numeric($valor)) {
					$this->_postErrors[] = $this->l('O campo "Custo" não é numérico');
				}else {
                    if ($valor < 0) {
                        $this->_postErrors[] = $this->l('O campo "Custo" não pode ser menor que 0 (zero)');
                    }
                }
			}
		}
	
		if (!$this->_postErrors) {
			$this->postProcess($sessao);
		}
	}
	
	private function validaEspecifCorreios($sessao) {
		
		// Recupera id das especificacoes dos Correios
		$id_esp_correios = Tools::getValue('id_esp_correios');
			
		if (Tools::getValue('fkcorreios_comprimento_min_'.$id_esp_correios) == NULL) {
			$this->_postErrors[] = $this->l('Comprimento mínimo não preenchido');
		}else {
			$valor = str_replace(',', '.', Tools::getValue('fkcorreios_comprimento_min_'.$id_esp_correios));
				
			if (!is_numeric($valor)) {
				$this->_postErrors[] = $this->l('O campo "Comprimento mínimo" não é numérico');
			}else {
                if ($valor < 0) {
                    $this->_postErrors[] = $this->l('O campo "Comprimento mínimo" não pode ser menor que 0 (zero)');
                }
            }
		}
			
		if (Tools::getValue('fkcorreios_comprimento_max_'.$id_esp_correios) == NULL) {
			$this->_postErrors[] = $this->l('Comprimento máximo não preenchido');
		}else {
			$valor = str_replace(',', '.', Tools::getValue('fkcorreios_comprimento_max_'.$id_esp_correios));
				
			if (!is_numeric($valor)) {
				$this->_postErrors[] = $this->l('O campo "Comprimento máximo" não é numérico');
			}else {
                if ($valor < 0) {
                    $this->_postErrors[] = $this->l('O campo "Comprimento máximo" não pode ser menor que 0 (zero)');
                }
            }
		}
			
		if (Tools::getValue('fkcorreios_largura_min_'.$id_esp_correios) == NULL) {
			$this->_postErrors[] = $this->l('Largura mínima não preenchida');
		}else {
			$valor = str_replace(',', '.', Tools::getValue('fkcorreios_largura_min_'.$id_esp_correios));
				
			if (!is_numeric($valor)) {
				$this->_postErrors[] = $this->l('O campo "Largura mínima" não é numérico');
			}else {
                if ($valor < 0) {
                    $this->_postErrors[] = $this->l('O campo "Largura mínima" não pode ser menor que 0 (zero)');
                }
            }
		}
			
		if (Tools::getValue('fkcorreios_largura_max_'.$id_esp_correios) == NULL) {
			$this->_postErrors[] = $this->l('Largura máxima não preenchida');
		}else {
			$valor = str_replace(',', '.', Tools::getValue('fkcorreios_largura_max_'.$id_esp_correios));
				
			if (!is_numeric($valor)) {
				$this->_postErrors[] = $this->l('O campo "Largura máxima" não é numérico');
			}else {
                if ($valor < 0) {
                    $this->_postErrors[] = $this->l('O campo "Largura máxima" não pode ser menor que 0 (zero)');
                }
            }
		}
			
		if (Tools::getValue('fkcorreios_altura_min_'.$id_esp_correios) == NULL) {
			$this->_postErrors[] = $this->l('Altura mínima não preenchida');
		}else {
			$valor = str_replace(',', '.', Tools::getValue('fkcorreios_altura_min_'.$id_esp_correios));
				
			if (!is_numeric($valor)) {
				$this->_postErrors[] = $this->l('O campo "Altura mínima" não é numérico');
			}else {
                if ($valor < 0) {
                    $this->_postErrors[] = $this->l('O campo "Altura mínima" não pode ser menor que 0 (zero)');
                }
            }
		}
			
		if (Tools::getValue('fkcorreios_altura_max_'.$id_esp_correios) == NULL) {
			$this->_postErrors[] = $this->l('Altura máxima não preenchida');
		}else {
			$valor = str_replace(',', '.', Tools::getValue('fkcorreios_altura_max_'.$id_esp_correios));
				
			if (!is_numeric($valor)) {
				$this->_postErrors[] = $this->l('O campo "Altura máxima" não é numérico');
			}else {
                if ($valor < 0) {
                    $this->_postErrors[] = $this->l('O campo "Altura máxima" não pode ser menor que 0 (zero)');
                }
            }
		}
			
		if (Tools::getValue('fkcorreios_somatoria_dimensoes_max_'.$id_esp_correios) == NULL) {
			$this->_postErrors[] = $this->l('Somatória dimensões não preenchida');
		}else {
			$valor = str_replace(',', '.', Tools::getValue('fkcorreios_somatoria_dimensoes_max_'.$id_esp_correios));
				
			if (!is_numeric($valor)) {
				$this->_postErrors[] = $this->l('O campo "Somatória dimensões" não é numérico');
			}else {
                if ($valor < 0) {
                    $this->_postErrors[] = $this->l('O campo "Somatória dimensões" não pode ser menor que 0 (zero)');
                }
            }
		}
        
		if (Tools::getValue('fkcorreios_peso_estadual_max_'.$id_esp_correios) == NULL) {
			$this->_postErrors[] = $this->l('Peso máximo - Estadual não preenchido');
		}else {
			$valor = str_replace(',', '.', Tools::getValue('fkcorreios_peso_estadual_max_'.$id_esp_correios));
				
			if (!is_numeric($valor)) {
				$this->_postErrors[] = $this->l('O campo "Peso máximo - Estadual" não é numérico');
			}else {
                if ($valor < 0) {
                    $this->_postErrors[] = $this->l('O campo "Peso máximo - Estadual" não pode ser menor que 0 (zero)');
                }
            }
		}
			
		if (Tools::getValue('fkcorreios_peso_nacional_max_'.$id_esp_correios) == NULL) {
			$this->_postErrors[] = $this->l('Peso máximo - Nacional não preenchido');
		}else {
			$valor = str_replace(',', '.', Tools::getValue('fkcorreios_peso_nacional_max_'.$id_esp_correios));
				
			if (!is_numeric($valor)) {
				$this->_postErrors[] = $this->l('O campo "Peso máximo - Nacional" não é numérico');
			}else {
                if ($valor < 0) {
                    $this->_postErrors[] = $this->l('O campo "Peso máximo - Nacional" não pode ser menor que 0 (zero)');
                }
            }
		}
	
		if (!$this->_postErrors) {
			$this->postProcess($sessao);
		}
	}
	
	private function validaServicosCorreios($sessao) {
	
		// Recupera id do servico
		$id_correios_transp = Tools::getValue('id_correios_transp');
	
		// Verifica se o servico esta ativo
		if (Tools::getValue('fkcorreios_correios_ativo_'.$id_correios_transp)) {
		
			// Verifica o campo "Grade"
			$valor = Tools::getValue('fkcorreios_correios_grade_'.$id_correios_transp);
		
			if (!is_numeric($valor)) {
				$this->_postErrors[] = $this->l('O campo "Grade" não é numérico');
			}else {
				if ($valor < 0 or $valor > 9) {
					$this->_postErrors[] = $this->l('O valor do campo "Grade" deve estar entre 0 e 9');
				}
			}
		
		}
		
		if (!$this->_postErrors) {
			$this->postProcess($sessao);
		}
		
	}

    private function validaFreteGratis($sessao) {

        // Recupera id da regiao frete gratis
        $id_frete_gratis = Tools::getValue('id_frete_gratis');

        // Verifica se a regiao esta ativa
        if (Tools::getValue('fkcorreios_frete_gratis_ativo_'.$id_frete_gratis)) {

            // Verifica nome da regiao
            $nome_regiao = Tools::getValue('fkcorreios_frete_gratis_nome_regiao_'.$id_frete_gratis);

            if (!$nome_regiao) {
                $this->_postErrors[] = $this->l('O campo "Nome da região" não preenchido.');
            }

            // Verifica valor do pedido
            if (Tools::getValue('fkcorreios_frete_gratis_valor_pedido_'.$id_frete_gratis) == NULL) {
                $this->_postErrors[] = $this->l('O campo "Valor pedido" não preenchido');
            }else {
                $valor = str_replace(',', '.', Tools::getValue('fkcorreios_frete_gratis_valor_pedido_'.$id_frete_gratis));

                if (!is_numeric($valor)) {
                    $this->_postErrors[] = $this->l('O campo "Valor pedido" não é numérico');
                }else {
                    if ($valor <= 0) {
                        $this->_postErrors[] = $this->l('O campo "Valor pedido" não pode ser menor ou igual a 0 (zero)');
                    }
                }
            }

            // Verifica transportadora
            if (!Tools::getValue('fkcorreios_frete_gratis_servico_'.$id_frete_gratis)) {
                $this->_postErrors[] = $this->l('Transportadora não selecionada');
            }

        }

        if (!$this->_postErrors) {
            $this->postProcess($sessao);
        }
    }
	
	private function postProcess($sessao) {
		
		switch($sessao) {
			
			case 'configRegistro_1':
				 
				$this->salvaRegistro_1();
				break;
				 
			case 'configRegistro_2':
				 
				$this->salvaRegistro_2();
				break;
				
			case 'configGeral':

                if (Tools::isSubmit('submitCache')) {
                    $this->excluiCache();
                    break;
                }

                if (Tools::isSubmit('submitSave')) {
                    $this->salvaGeral();
                    break;
                }

                break;

			case 'configCadastroCep':
				
				$this->salvaCadastroCep();
				break;
				
			case 'configEmbalagens':
				
				if (Tools::isSubmit('submitAdd')) {
					$this->incluiEmbalagem();
					break;
				}
				
				if (Tools::isSubmit('submitDel')) {
					$this->excluiEmbalagem();
					break;
				}
				
				if (Tools::isSubmit('submitSave')) {
					$this->salvaEmbalagens ();
					break;
				}

                break;
				
			case 'configEspecifCorreios':
				
				$this->salvaEspecifCorreios ();
				break;
				
			case 'configServicosCorreios':
				
				if (Tools::isSubmit('submitAdd')) {
					$this->incluiServicoCorreios();
					break;
				}
				
				// Exclui servicos
				if (Tools::isSubmit('submitDel')) {
					$this->excluiServicosCorreios();
					break;
				}
				
				// Salva as configuracoes dos servicos dos Correios
				if (Tools::isSubmit('submitSave')) {
					$this->salvaServicosCorreios();
					break;
				}

                break;

            case 'configFreteGratis':

                if (Tools::isSubmit('submitAdd')) {
                    $this->incluiFreteGratis();
                    break;
                }

                if (Tools::isSubmit('submitDel')) {
                    $this->excluiFreteGratis();
                    break;
                }

                if (Tools::isSubmit('submitSave')) {
                    $this->salvaFreteGratis();
                    break;
                }

                break;
            
		}
	}
	
	private function salvaRegistro_1() {
		
		Configuration::updateValue('FKCORREIOS_REFERENCIA', Trim(Tools::getValue('fkcorreios_referencia')));
		Configuration::updateValue('FKCORREIOS_DOMINIO', Trim(Tools::getShopDomain(false,true)));
		Configuration::updateValue('FKCORREIOS_PROPRIETARIO', Trim(Tools::getValue('fkcorreios_proprietario')));
	}
	
	private function salvaRegistro_2() {
		
		Configuration::updateValue('FKCORREIOS_REFERENCIA', '');
		Configuration::updateValue('FKCORREIOS_DOMINIO', '');
		Configuration::updateValue('FKCORREIOS_PROPRIETARIO', '');
	}

    private function excluiCache() {
        Db::getInstance()->delete('fkcorreios_cache');
    }

	private function salvaGeral() {
		
		Configuration::updateValue('FKCORREIOS_MEU_CEP', Tools::getValue('fkcorreios_meu_cep'));
		Configuration::updateValue('FKCORREIOS_BLOCO_PRODUTO', Tools::getValue('fkcorreios_bloco_produto'));
        Configuration::updateValue('FKCORREIOS_BLOCO_POSICAO', Tools::getValue('fkcorreios_bloco_posicao'));
		Configuration::updateValue('FKCORREIOS_BLOCO_CARRINHO', Tools::getValue('fkcorreios_bloco_carrinho'));
		Configuration::updateValue('FKCORREIOS_EMBALAGEM', Tools::getValue('fkcorreios_embalagem'));
	}
	
	private function salvaCadastroCep() {
		
		$estados_capitais = db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'fkcorreios_cadastro_cep`');
	
		foreach ($estados_capitais as $reg) {

            $dados = array(
                'cep_estado'             => Tools::getValue('fkcorreios_cep_estado_'.$reg['id']),
                'cep_capital'           => Tools::getValue('fkcorreios_cep_capital_'.$reg['id']),
                'cep_base_capital'      => Tools::getValue('fkcorreios_cep_base_capital_'.$reg['id']),
                'cep_base_interior'     => Tools::getValue('fkcorreios_cep_base_interior_'.$reg['id'])
            );

			Db::getInstance()->update('fkcorreios_cadastro_cep', $dados,'`id` = '.(int)$reg['id']);
		}
	}
	
	private function incluiEmbalagem() {
		
		$dados = array(
				'id_shop'		=> $this->context->shop->id,
				'descricao' 	=> 'Nova Caixa',
				'comprimento' 	=> '0',
				'altura'    	=> '0',
				'largura'   	=> '0',
				'peso'      	=> '0',
				'cubagem'   	=> '0',
				'custo'     	=> '0',
				'ativo' 		=> '1'
		);
	
		Db::getInstance()->insert('fkcorreios_embalagens', $dados);
	}
	
	private function excluiEmbalagem() {
		
		// Array com as embalagens a ser excluidas
		$regioes_excluidas = Tools::getValue('fkcorreios_excluir');
	
		if ($regioes_excluidas) {
			foreach ($regioes_excluidas as $servicos) {
				Db::getInstance()->delete('fkcorreios_embalagens', '`id` = '.(int)$servicos);
			}
		}
	}
	
	private function salvaEmbalagens() {
		
		// Array com as embalagens ativas
		$embalagens_ativas = Tools::getValue('fkcorreios_ativo');
			
		// Atualiza os dados das embalagens
		$embalagens = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'fkcorreios_embalagens`');
	
		foreach ($embalagens as $reg) {
	
			$comprimento = str_replace(',', '.', Tools::getValue('fkcorreios_comprimento_'.$reg['id']));
			$altura = str_replace(',', '.', Tools::getValue('fkcorreios_altura_'.$reg['id']));
			$largura = str_replace(',', '.', Tools::getValue('fkcorreios_largura_'.$reg['id']));
			$peso = str_replace(',', '.', Tools::getValue('fkcorreios_peso_'.$reg['id']));
			$custo = str_replace(',', '.', Tools::getValue('fkcorreios_custo_'.$reg['id']));
				
			// Calcula cubagem da caixa
			$cubagem = ($comprimento * $altura * $largura);
				
			// Verifica se a embalagem esta ativa
			$ativo = 0;

            if ($embalagens_ativas){
                if (in_array($reg['id'], $embalagens_ativas)) {
                    $ativo = 1;
                }
            }

			$dados = array(
					'descricao' 	=> Tools::getValue('fkcorreios_descricao_'.$reg['id']),
					'comprimento' 	=> $comprimento,
					'altura'   		=> $altura,
					'largura'   	=> $largura,
					'peso'      	=> $peso,
					'cubagem'   	=> $cubagem,
					'custo'     	=> $custo,
					'ativo'			=> $ativo
			);
	
			Db::getInstance()->update('fkcorreios_embalagens', $dados, '`id` = '.(int)$reg['id']);
		}
	}
	
	private function salvaEspecifCorreios() {
		
		// Recupera id das especificacoes dos Correios
		$id_esp_correios = Tools::getValue('id_esp_correios');
		
		$dados = array(
				'comprimento_min'				=> Tools::getValue('fkcorreios_comprimento_min_'.$id_esp_correios),
				'comprimento_max' 				=> Tools::getValue('fkcorreios_comprimento_max_'.$id_esp_correios),
				'largura_min' 					=> Tools::getValue('fkcorreios_largura_min_'.$id_esp_correios),
				'largura_max' 					=> Tools::getValue('fkcorreios_largura_max_'.$id_esp_correios),
				'altura_min' 					=> Tools::getValue('fkcorreios_altura_min_'.$id_esp_correios),
				'somatoria_dimensoes_max' 		=> Tools::getValue('fkcorreios_somatoria_dimensoes_max_'.$id_esp_correios),
				'peso_estadual_max' 			=> Tools::getValue('fkcorreios_peso_estadual_max_'.$id_esp_correios),
				'peso_nacional_max' 			=> Tools::getValue('fkcorreios_peso_nacional_max_'.$id_esp_correios),
		);
			
		Db::getInstance()->update('fkcorreios_especificacoes_correios', $dados,'`id` = '.(int)$id_esp_correios);

        // Exclui cache
        $this->excluiCache();

	}
	
	private function incluiServicoCorreios() {
		
		$servicos_correios = Tools::getValue('fkcorreios_servicos_correios');
	
		if ($servicos_correios) {
	
			foreach ($servicos_correios as $servico) {
	
				// Recupera o nome do servico selecionado
				$especif_correios = Db::getInstance()->getRow('SELECT `servico` FROM `'._DB_PREFIX_.'fkcorreios_especificacoes_correios` WHERE `id` = '.(int)$servico);
	
				// Insere registro em fkcorreios_correios_transp
				if ($especif_correios) {
					$dados = array(
							'id_shop' 		=> $this->context->shop->id,
							'id_carrier' 	=> 0,
							'id_correios' 	=> $servico,
							'nome_carrier' 	=> $especif_correios['servico'],
							'grade' 		=> 0,
							'ativo' 		=> 0
					);
	
					Db::getInstance()->insert('fkcorreios_correios_transp', $dados);
				}
			}
		}
	}
	
	private function excluiServicosCorreios() {
		
		// Recupera id do servico
		$id_correios_transp = Tools::getValue('id_correios_transp');
		
		$correios_transp = Db::getInstance()->getRow('SELECT `id_carrier` FROM `'._DB_PREFIX_.'fkcorreios_correios_transp` WHERE `id` = '.(int)$id_correios_transp);

        // Exclui registro das tabelas do Fkcorreios
        Db::getInstance()->delete('fkcorreios_correios_transp', '`id` = '.(int)$id_correios_transp);

        // Exclui logos
        if (file_exists(_PS_SHIP_IMG_DIR_.'/'.$correios_transp['id_carrier'].'.jpg')) {
            unlink(_PS_SHIP_IMG_DIR_.'/'.$correios_transp['id_carrier'].'.jpg');
        }

        if (file_exists($this->_path_logo.$correios_transp['id_carrier'].'.jpg')) {
            unlink($this->_path_logo.$correios_transp['id_carrier'].'.jpg');
        }

        // Marca excluido no carrier do Prestashop
		$carrier = new Carrier($correios_transp['id_carrier']);

        if ($carrier->id) {
            $carrier->deleted = true;
            $carrier->update();
        }
	}
	
	private function salvaServicosCorreios() {
		
		// Recupera id do servico
		$id_correios_transp = Tools::getValue('id_correios_transp');
		
		// Altera fkcorreios_correios_transp
		$dados = array(
				'grade' => Tools::getValue('fkcorreios_correios_grade_'.$id_correios_transp),
				'ativo' => (!Tools::getValue('fkcorreios_correios_ativo_'.$id_correios_transp) ? '0' : '1')
		);
		
		Db::getInstance()->update('fkcorreios_correios_transp', $dados, 'id = '.(int)$id_correios_transp);
		
		// Recupera dados do servico
		$correios_transp = Db::getInstance()->getRow('SELECT `id_carrier`, `nome_carrier` FROM `'._DB_PREFIX_.'fkcorreios_correios_transp` WHERE `id` = '.(int)$id_correios_transp);
		
		// Upload logo
		$this->uploadLogo($id_correios_transp, $correios_transp['nome_carrier']);
		
		// Cria ou alterar carrier do Prestashop
		if ($correios_transp['id_carrier'] == 0) {
		
			$configCarrier = array(
					'name' 					=> $correios_transp['nome_carrier'],
					'id_tax_rules_group' 	=> 0,
					'active' 				=> (!Tools::getValue('fkcorreios_correios_ativo_'.$id_correios_transp) ? '0' : '1'),
					'deleted' 				=> false,
					'shipping_handling' 	=> false,
					'range_behavior' 		=> true,
					'is_module' 			=> true,
					'shipping_external' 	=> true,
					'shipping_method' 		=> 0,
					'external_module_name' 	=> $this->name,
					'need_range' 			=> true,
					'url' 					=> 'http://websro.correios.com.br/sro_bin/txect01%24.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI=@',
					'is_free' 				=> false,
					'grade' 				=> Tools::getValue('fkcorreios_correios_grade_'.$id_correios_transp),
					'id_shop'				=> $this->context->shop->id,
					'id_correios_transp'	=> $id_correios_transp
			);
		
			// Atualiza o campo id_carrier
			$id_carrier = $this->instalaCarrier($configCarrier);
			Db::getInstance()->update('fkcorreios_correios_transp', array('id_carrier' => $id_carrier), '`id` = '.(int)$id_correios_transp);
		}else {
			$configCarrier = array(
					'id_carrier' 			=> $correios_transp['id_carrier'],
					'id_correios_transp'	=> $id_correios_transp,
					'name' 					=> $correios_transp['nome_carrier'],
					'active'				=> (!Tools::getValue('fkcorreios_correios_ativo_'.$id_correios_transp) ? '0' : '1'),
					'grade' 				=> Tools::getValue('fkcorreios_correios_grade_'.$id_correios_transp),
					'id_shop'				=> $this->context->shop->id
			);
		
			$this->alteraCarrier($configCarrier);
		}

	}

    private function incluiFreteGratis() {

        $frete_gratis = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'fkcorreios_frete_gratis` WHERE `id_shop` = '.$this->context->shop->id);

        // Insere registro em fkcorreios_frete_gratis
        if (!$frete_gratis) {
            $dados = array(
                'id_shop'               => $this->context->shop->id,
                'id_correios_transp'    => 0,
                'nome_regiao'            => 'Nova Região',
                'regiao_uf'             => '',
                'regiao_cep'            => '',
                'valor_pedido'            => '0',
                'id_produtos'            => '',
                'ativo'                    => '1'
            );

            Db::getInstance()->insert('fkcorreios_frete_gratis', $dados);
        }else {
            $this->_postErrors[] = $this->l('Na versão Lite é possível incluir somente 1 região de frete grátis.');
        }
    }

    private function excluiFreteGratis() {

        // Recupera id da regiao frete gratis a ser excluida
        $id_frete_gratis = Tools::getValue('id_frete_gratis');
        Db::getInstance()->delete('fkcorreios_frete_gratis', '`id` = '.(int)$id_frete_gratis);
    }

    private function salvaFreteGratis() {

        // Recupera id da regiao frete gratis salva
        $id_frete_gratis = Tools::getValue('id_frete_gratis');

        $dados = array(
            'id_correios_transp'    => Tools::getValue('fkcorreios_frete_gratis_servico_'.$id_frete_gratis),
            'nome_regiao'           => Tools::getValue('fkcorreios_frete_gratis_nome_regiao_'.$id_frete_gratis),
            'regiao_uf'             => '',
            'regiao_cep'            => '',
            'valor_pedido'          => Tools::getValue('fkcorreios_frete_gratis_valor_pedido_'.$id_frete_gratis),
            'id_produtos'           => '',
            'ativo'                 => (!Tools::getValue('fkcorreios_frete_gratis_ativo_'.$id_frete_gratis) ? '0' : '1')
        );

        Db::getInstance()->update('fkcorreios_frete_gratis', $dados, 'id = '.(int)$id_frete_gratis);

    }

	private function uploadLogo($id, $nome_carrier) {
		
		$extensoes_permitidas = array('0' => 'jpg');

        if(!empty($_FILES['fkcorreios_correios_imagem_'.$id]['name'])) {

            // Verifica se houve algum erro com o upload
            if ($_FILES['fkcorreios_correios_imagem_'.$id]['error'] != 0) {
                $this->_postErrors[] = $nome_carrier.': '.$this->l('Erro durante upload da imagem.');
                return;
            }

            // Verifica extensão do arquivo
            $array = explode('.', $_FILES['fkcorreios_correios_imagem_'.$id]['name']);
            $extensao = end($array);
            $extensao = strtolower($extensao);

            if (array_search($extensao, $extensoes_permitidas) === false) {
                $this->_postErrors[] = $nome_carrier.': '.$this->l('Permitido somente arquivos com extensões jpg.');
                return;
            }

            // Move o logo para a pasta upload dando rename
            if (!move_uploaded_file($_FILES['fkcorreios_correios_imagem_'.$id]['tmp_name'], $this->_path_logo.$id.'.'.$extensao)) {
                $this->_postErrors[] = $nome_carrier.': '.$this->l('Não foi possível mover o arquivo para a pasta img.');
                return;
            }
        }
	}
	
	private function instalaCarrier($configCarrier) {
		
		$carrier = new Carrier();
		$carrier->name 					= $configCarrier['name'];
		$carrier->id_tax_rules_group 	= $configCarrier['id_tax_rules_group'];
		$carrier->active 				= $configCarrier['active'];
		$carrier->deleted 				= $configCarrier['deleted'];
		$carrier->shipping_handling 	= $configCarrier['shipping_handling'];
		$carrier->range_behavior 		= $configCarrier['range_behavior'];
		$carrier->is_module 			= $configCarrier['is_module'];
		$carrier->shipping_external 	= $configCarrier['shipping_external'];
		$carrier->shipping_method 		= $configCarrier['shipping_method'];
		$carrier->external_module_name 	= $configCarrier['external_module_name'];
		$carrier->need_range 			= $configCarrier['need_range'];
		$carrier->url 					= $configCarrier['url'];
		$carrier->is_free 				= $configCarrier['is_free'];
		$carrier->grade 				= $configCarrier['grade'];
		
		$languages = Language::getLanguages(true);
		foreach ($languages as $language) {
			$carrier->delay[(int)$language['id_lang']] = 'Prazo de Entrega';
		}
		
		if ($carrier->add()) {
		
			// Liga carrier ao shop
			$carrier->associateTo($configCarrier['id_shop']);
			
			// Liga carrier aos grupos de clientes
            $grupos = Group::getGroups(true);

            if (version_compare(_PS_VERSION_, '1.5.5.0', '<')) {

                foreach ($grupos as $grupo) {

                    $dados = array(
                        'id_carrier'    => $carrier->id,
                        'id_group'      => $grupo['id_group']
                    );

                    Db::getInstance()->insert('carrier_group', $dados);
                }
            }else {
                $grupos_clientes = array();

                foreach ($grupos as $grupo) {
                    $grupos_clientes[] = $grupo['id_group'];
                }

                $carrier->setGroups($grupos_clientes);
            }

			// Define intervalo de precos
			$intervalo_preco = new RangePrice();
			
			if (!$intervalo_preco->rangeExist($carrier->id, '0', '100000')) {
				$intervalo_preco->id_carrier = $carrier->id;
				$intervalo_preco->delimiter1 = '0';
				$intervalo_preco->delimiter2 = '100000';
				$intervalo_preco->add();
			}
		
			// Define intervalo de pesos
			$intervalo_peso = new RangeWeight();
			
			if (!$intervalo_peso->rangeExist($carrier->id, '0', '10000')) {
				$intervalo_peso->id_carrier = $carrier->id;
				$intervalo_peso->delimiter1 = '0';
				$intervalo_peso->delimiter2 = '10000';
				$intervalo_peso->add();;
			}
		
			// Liga carrier as regioes
			$regioes = Zone::getZones(true);
			foreach ($regioes as $regiao) {
				
				if (!$carrier->checkCarrierZone($carrier->id, $regiao['id_zone'])) {
					$carrier->addZone($regiao['id_zone']);
				}
				
			}
		
			// Copia logo
			$logo = $this->_path_logo.$configCarrier['id_correios_transp'].'.jpg';
			
			if (file_exists($logo)) {
				
				// Exclui logo da pasta tmp
				if (file_exists(_PS_TMP_IMG_DIR_.'carrier_mini_'.$carrier->id.'_'.$configCarrier['id_shop'].'.jpg')) {
					unlink(_PS_TMP_IMG_DIR_.'carrier_mini_'.$carrier->id.'_'.$configCarrier['id_shop'].'.jpg');
				}
				
				copy($logo, _PS_SHIP_IMG_DIR_.$carrier->id.'.jpg');
			}
			
			// Retorna o ID Carrier
			return $carrier->id;
		}

        return false;
	}
	
	private function alteraCarrier($configCarrier) {
		
		$carrier = new Carrier($configCarrier['id_carrier']);
		$carrier->name 		= $configCarrier['name'];
		$carrier->active 	= $configCarrier['active'];
		$carrier->grade		= $configCarrier['grade'];
		$carrier->update();
		
		// Copia logo
		$logo = $this->_path_logo.$configCarrier['id_correios_transp'].'.jpg';
			
		if (file_exists($logo)) {

			// Exclui logo da pasta tmp
			if (file_exists(_PS_TMP_IMG_DIR_.'carrier_mini_'.$carrier->id.'_'.$configCarrier['id_shop'].'.jpg')) {
				unlink(_PS_TMP_IMG_DIR_.'carrier_mini_'.$carrier->id.'_'.$configCarrier['id_shop'].'.jpg');
			}
		
			copy($logo, _PS_SHIP_IMG_DIR_.$carrier->id.'.jpg');
		}
		
	}
	
	private function excluiCarrier() {
		
		$correios_transp = Db::getInstance()->executeS('SELECT `id_carrier` FROM `'._DB_PREFIX_.'fkcorreios_correios_transp`');
		
		foreach ($correios_transp as $reg) {

            // Exclui logos
            if (file_exists(_PS_SHIP_IMG_DIR_.'/'.$reg['id_carrier'].'.jpg')) {
                unlink(_PS_SHIP_IMG_DIR_.'/'.$reg['id_carrier'].'.jpg');
            }

            // Marca excluido no carrier do Prestashop
			$carrier = new Carrier($reg['id_carrier']);

            if ($carrier->id) {
                $carrier->deleted = true;
                $carrier->update();
            }
		}
		
		return true;
	}
	
	private function displayForm() {
		
		$this->_html .= '<fieldset>';
		$this->_html .= '<legend><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->l('Status do Módulo FKcorreios Lite').'</legend>';
		
		$alert = array();
		$enviarAlert = false;
		
		// Verifica registro da licenca
		if (Configuration::get('FKCORREIOS_REFERENCIA') == '' Or Configuration::get('FKCORREIOS_DOMINIO') == '' Or Configuration::get('FKCORREIOS_PROPRIETARIO') == '') {
			$this->_html .= '<img src="'._PS_IMG_.'admin/warn2.png" />Registre sua Licença de Uso.';
		}else {
            // Verifica instalacao do SOAP
            if (!extension_loaded('soap')) {
                $enviarAlert = true;
                $alert['soapMsg'] = $this->l('Ative a função SOAP em seu PHP.');
                $alert['soapImg'] = '<img src="'._PS_IMG_.'admin/warn2.png" />';
            }

			// Verifica Configuracoes Gerais
			if (!Configuration::get('FKCORREIOS_MEU_CEP')) {
				$enviarAlert = true;
				$alert['confGeralMsg'] = $this->l('Configurações Gerais não preenchidas.');
				$alert['confGeralImg'] = '<img src="'._PS_IMG_.'admin/warn2.png" />';
			}
			
			// Verifica embalagens
			if (Configuration::get('FKCORREIOS_EMBALAGEM') == '1') {
				
				$embalagens = Db::getInstance()->ExecuteS('SELECT `id` FROM `'._DB_PREFIX_.'fkcorreios_embalagens` Where `ativo` = 1 And `id_shop` = '.$this->context->shop->id);
				
				if (!$embalagens) {
					$enviarAlert = true;
					$alert['embMsg'] = $this->l('Embalagens Padrão não definidas.');
					$alert['embImg'] = '<img src="'._PS_IMG_.'admin/warn2.png" />';
				}
			}

            // Verifica ativacao dos servicos dos Correios
            $correios_transp = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'fkcorreios_correios_transp` WHERE `ativo` = 1 AND `id_shop` = '.$this->context->shop->id);

            if (!$correios_transp) {
                $enviarAlert = true;
                $alert['correiosTranspMsg'] = $this->l('Serviços dos Correios não definidos.');
                $alert['correiosTranspImg'] = '<img src="'._PS_IMG_.'admin/warn2.png" />';
            }

			// Mensagens
			if ($enviarAlert == false) {
				$this->_html .= '<img src="'._PS_IMG_ .'admin/module_install.png" /><strong>'.$this->l('FKcorreios Lite está configurado e online!').'</strong>';
			}else {
				$this->_html .= '<strong>'.$this->l('FKcorreios Lite ainda não configurado, por favor verifique os alertas abaixo:').'</strong>';
				$this->_html .= '<br><br>';

                if (isset($alert['soapMsg'])) {
                    $this->_html .= $alert['soapImg'].$alert['soapMsg'];
                    $this->_html .= '<br>';
                }

				if (isset($alert['confGeralMsg'])) {
					$this->_html .= $alert['confGeralImg'].$alert['confGeralMsg'];
					$this->_html .= '<br>';
				}
				
				if (isset($alert['embMsg'])) {
					$this->_html .= $alert['embImg'].$alert['embMsg'];
					$this->_html .= '<br>';
				}

                if (isset($alert['correiosTranspMsg'])) {
                    $this->_html .= $alert['correiosTranspImg'].$alert['correiosTranspMsg'];
                    $this->_html .= '<br>';
                }
                
			}
		}
		
		$this->_html .= '</fieldset>';
		$this->displayFormConfig();
		
		return $this->_html;
		
	}
	
	private function displayFormConfig() {
		
		//Identificacao das abas
		$id_licenca = $this->l('Registro da Licença');
		$id_config_geral = $this->l('Configurações Gerais');
		$id_cadastro_cep = $this->l('Cadastro CEP');
		$id_embalagens = $this->l('Embalagens Padrão');
		$id_especificacoes_correios = $this->l('Especificações Correios');
		$id_servicos_correios = $this->l('Serviços Correios');
        $id_frete_gratis = $this->l('Frete Grátis');
		
		
		if (Configuration::get('FKCORREIOS_REFERENCIA') != '' and Configuration::get('FKCORREIOS_DOMINIO') != '' and Configuration::get('FKCORREIOS_PROPRIETARIO') != '') {
			 
			if (!$this->wsVerificaLicenca(Configuration::get('FKCORREIOS_REFERENCIA'), Tools::getShopDomain(false,true))) {
		
				$this->_html .= '<div class="alert error"><img src="'._PS_IMG_.'admin/forbbiden.gif" alt="nok" />&nbsp;'.$this->_erroWs.'</div>';
		
				$this->_html .= '<ul id="fkcorreios_menuTab">';
				$this->_html .= '   <li id="menuTab1" class="menuTabButton selected">1. '.$id_licenca.'</li>';
				$this->_html .= '   <li id="menuTab2" class="menuTabButton">2. '.$id_config_geral.'</li>';
				$this->_html .= '   <li id="menuTab3" class="menuTabButton">3. '.$id_cadastro_cep.'</li>';
				$this->_html .= '   <li id="menuTab4" class="menuTabButton">4. '.$id_embalagens.'</li>';
				$this->_html .= '   <li id="menuTab5" class="menuTabButton">5. '.$id_especificacoes_correios.'</li>';
				$this->_html .= '   <li id="menuTab6" class="menuTabButton">6. '.$id_servicos_correios.'</li>';
                $this->_html .= '   <li id="menuTab7" class="menuTabButton">7. '.$id_frete_gratis.'</li>';
				$this->_html .= '</ul>';
		
				$this->_html .= '<div id="fkcorreios_tabList">';
		
				$this->_html .= '   <div id="menuTab1Sheet" class="fkcorreios_tabItem selected">';
				ob_start();
				include_once dirname(__FILE__).'/config/displayRegistro_1.php';
				$this->_html .= ob_get_contents();
				ob_end_clean();
				$this->_html .= '   </div>';
		
				$this->_html .= '</div>';
		
			}else {
				$this->_html .= '<ul id="fkcorreios_menuTab">';
				$this->_html .= '   <li id="menuTab1" class="menuTabButton">1. '.$id_licenca.'</li>';
				$this->_html .= '   <li id="menuTab2" class="menuTabButton selected">2. '.$id_config_geral.'</li>';
				$this->_html .= '   <li id="menuTab3" class="menuTabButton">3. '.$id_cadastro_cep.'</li>';
				$this->_html .= '   <li id="menuTab4" class="menuTabButton">4. '.$id_embalagens.'</li>';
				$this->_html .= '   <li id="menuTab5" class="menuTabButton">5. '.$id_especificacoes_correios.'</li>';
				$this->_html .= '   <li id="menuTab6" class="menuTabButton">6. '.$id_servicos_correios.'</li>';
                $this->_html .= '   <li id="menuTab7" class="menuTabButton">7. '.$id_frete_gratis.'</li>';
				$this->_html .= '</ul>';
		
				$this->_html .= '<div id="fkcorreios_tabList">';
		
				$this->_html .= '   <div id="menuTab1Sheet" class="fkcorreios_tabItem">';
				ob_start();
				include_once dirname(__FILE__).'/config/displayRegistro_2.php';
				$this->_html .= ob_get_contents();
				ob_end_clean();
				$this->_html .= '   </div>';
		
				$this->_html .= '   <div id="menuTab2Sheet" class="fkcorreios_tabItem selected">';
				ob_start();
				include_once dirname(__FILE__).'/config/displayConfigGeral.php';
				$this->_html .= ob_get_contents();
				ob_end_clean();
				$this->_html .= '   </div>';
				
				$this->_html .= '   <div id="menuTab3Sheet" class="fkcorreios_tabItem">';
				ob_start();
				include_once dirname(__FILE__).'/config/displayCadastroCep.php';
				$this->_html .= ob_get_contents();
				ob_end_clean();
				$this->_html .= '   </div>';
				
				$this->_html .= '   <div id="menuTab4Sheet" class="fkcorreios_tabItem">';
				ob_start();
				include_once dirname(__FILE__).'/config/displayEmbalagens.php';
				$this->_html .= ob_get_contents();
				ob_end_clean();
				$this->_html .= '   </div>';
				
				$this->_html .= '   <div id="menuTab5Sheet" class="fkcorreios_tabItem">';
				ob_start();
				include_once dirname(__FILE__).'/config/displayEspecificacoesCorreios.php';
				$this->_html .= ob_get_contents();
				ob_end_clean();
				$this->_html .= '   </div>';
				
				$this->_html .= '   <div id="menuTab6Sheet" class="fkcorreios_tabItem">';
				ob_start();
				include_once dirname(__FILE__).'/config/displayServicosCorreios.php';
				$this->_html .= ob_get_contents();
				ob_end_clean();
				$this->_html .= '   </div>';
				
                $this->_html .= '   <div id="menuTab7Sheet" class="fkcorreios_tabItem">';
                ob_start();
                include_once dirname(__FILE__).'/config/displayFreteGratis.php';
                $this->_html .= ob_get_contents();
                ob_end_clean();
                $this->_html .= '   </div>';
				
				$this->_html .= '</div>';
		
			}
		}else {
			 
			$this->_html .= '<ul id="fkcorreios_menuTab">';
			$this->_html .= '   <li id="menuTab1" class="menuTabButton selected">1. '.$id_licenca.'</li>';
			$this->_html .= '   <li id="menuTab2" class="menuTabButton">2. '.$id_config_geral.'</li>';
			$this->_html .= '   <li id="menuTab3" class="menuTabButton">3. '.$id_cadastro_cep.'</li>';
			$this->_html .= '   <li id="menuTab4" class="menuTabButton">4. '.$id_embalagens.'</li>';
			$this->_html .= '   <li id="menuTab5" class="menuTabButton">5. '.$id_especificacoes_correios.'</li>';
			$this->_html .= '   <li id="menuTab6" class="menuTabButton">6. '.$id_servicos_correios.'</li>';
            $this->_html .= '   <li id="menuTab7" class="menuTabButton">7. '.$id_frete_gratis.'</li>';
			$this->_html .= '</ul>';
		
			$this->_html .= '<div id="fkcorreios_tabList">';
			 
			$this->_html .= '   <div id="menuTab1Sheet" class="fkcorreios_tabItem selected">';
			ob_start();
			include_once dirname(__FILE__).'/config/displayRegistro_1.php';
			$this->_html .= ob_get_contents();
			ob_end_clean();
			$this->_html .= '   </div>';
			 
			$this->_html .= '</div>';
		}
		
		$this->_html .= '<script>';
		$this->_html .= '   $(".menuTabButton").click(function () {';
		$this->_html .= '       $(".menuTabButton.selected").removeClass("selected");';
		$this->_html .= '       $(this).addClass("selected");';
		$this->_html .= '       $(".fkcorreios_tabItem.selected").removeClass("selected");';
		$this->_html .= '       $("#" + this.id + "Sheet").addClass("selected");';
		$this->_html .= '   });';
		$this->_html .= '</script>';
		
		if (isset($_GET['id_tab'])) {
			$this->_html .= '<script>';
			$this->_html .= '   $(".menuTabButton.selected").removeClass("selected");';
			$this->_html .= '   $("#menuTab'.Tools::safeOutput(Tools::getValue('id_tab')).'").addClass("selected");';
			$this->_html .= '   $(".fkcorreios_tabItem.selected").removeClass("selected");';
			$this->_html .= '   $("#menuTab'.Tools::safeOutput(Tools::getValue('id_tab')).'Sheet").addClass("selected");';
			$this->_html .= '</script>';
		}
		
		return $this->_html;
		
	}
	
	public function getOrderShippingCost($params, $shipping_cost) {

        $cep_destino = '';
        $uf_destino = '';

        // Inicializa a classe funcoes
        $funcoes = new FKcorreiosLiteFuncoes();

        // Verifica se o cliente está logado e recupera os dados do endereco de entrega
        if ($this->context->customer->isLogged()) {

            $address = new Address($params->id_address_delivery);

            // Recupera CEP destino
            if ($address->postcode) {
                $cep_destino = $address->postcode;
            }
        }else {
            // Recupera dados do CEP informado
            if ($this->context->cookie->fkcorreios_cep) {
                $cep_destino = $this->context->cookie->fkcorreios_cep;
            }
        }

        // Para pedidos efetuados via Admin
        if (!$cep_destino) {
            $address = new Address($params->id_address_delivery);

            // Ignora Carrier se não existir CEP
            if (!$address->postcode) {
                return false;
            }

            $cep_destino = $address->postcode;
        }

        // Valida CEP destino
        $cep_destino = trim(preg_replace("/[^0-9]/", "", $cep_destino));

        // Ignora Carrier se o CEP for invalido
        if (strlen($cep_destino) <> 8) {
            return false;
        }

        // Recupera UF
        $uf_destino = $funcoes->retornaUF($cep_destino);

        if ($uf_destino == 'erro') {
            return false;
        }

        // Recupera UF origem
        $uf_origem = $funcoes->retornaUF(trim(preg_replace("/[^0-9]/", "", Configuration::get('FKCORREIOS_MEU_CEP'))));

        // Recupera dados do servico
        $dados_carrier = $this->recuperaDadosServico($this->id_carrier);

        // Recupera valor do pedido
        if (isset($this->context->cart)) {
            $valor_pedido = $this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
        }else {
            // Para pedidos efetuados via Admin
            $cart = new cart($params->id);
            $valor_pedido = $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
        }

        // Verifica se e frete gratis considerando valor do pedido
        $pedido_frete_gratis = $this->verificaPedidoFreteGratis($valor_pedido);

        // Instancia Carrier
        $carrier = new Carrier($this->id_carrier);
        
        // Recupera produtos
        $produtos = array();

        foreach ($params->getProducts() as $prod) {

            // Ignora o produto se for virtual
            if ($prod['is_virtual'] == 1) {
                continue;
            }
            
            // Calcula cubagem
            $cubagem = $prod['height'] * $prod['width'] * $prod['depth'];

            for ($qty = 0; $qty < $prod['quantity']; $qty++) {

                $produtos[] = array(
                    'id'            => $prod['id_product'],
                    'altura'        => $prod['height'],
                    'largura'       => $prod['width'],
                    'comprimento'   => $prod['depth'],
                    'peso'          => $prod['weight'],
                    'cubagem'       => $cubagem,
                    'valor_produto' => $prod['price_wt'],
                    'frete_gratis'  => false
                );
            }
        }

        // Processa embalagens
        if (Configuration::get('FKCORREIOS_EMBALAGEM') == '0') {
            $embalagens = $this->processaEmbalagemIndividual($this->id_carrier, $produtos, $uf_origem, $uf_destino);
        }else {
            $embalagens = $this->processaEmbalagemPadrao($this->id_carrier, $produtos, $uf_origem, $uf_destino);
        }

        // Ignora carrier se nao existirem embalagens (ou seja as dimensoes estao fora do permitido)
        if (!$embalagens) {
            return false;
        }

        $fkcorreios = array(
            'cep_origem'                        => trim(preg_replace("/[^0-9]/", "", Configuration::get('FKCORREIOS_MEU_CEP'))),
            'uf_origem'                         => $uf_origem,
            'cep_destino'                       => $cep_destino,
            'uf_destino'                        => $uf_destino,
            'tempo_preparacao'                  => 0,
            'custos_envio'                      => 0,
            'cod_servico'                       => $dados_carrier['cod_servico'],
            'cod_administrativo'                => $dados_carrier['cod_administrativo'],
            'senha'                             => $dados_carrier['senha'],
            'valor_pedido'                      => $valor_pedido,
            'pedido_frete_gratis'               => $pedido_frete_gratis['frete_gratis'],
            'produto_frete_gratis'              => false,
            'mostrar_todos_carrier'             => false,
            'carrier_atual'                     => $this->id_carrier,
            'carrier_frete_gratis'              => $pedido_frete_gratis['carrier_frete_gratis'],
            'produtos'                          => $produtos,
            'embalagens'                        => $embalagens
        );

        // Ignora Carrier se o Pedido for Frete Gratis e configurado para mostrar somente o Carrier de Frete Gratis
        if ($fkcorreios['pedido_frete_gratis'] == true And $fkcorreios['mostrar_todos_carrier'] == false And $fkcorreios['carrier_atual'] != $fkcorreios['carrier_frete_gratis']) {
            return false;
        }

        // Calcula valor do frete dos Correios
        return $this->processaCorreiosOnline($fkcorreios);

	}
	
	public function getOrderShippingCostExternal($params) {
		return $this->getOrderShippingCost($params, 0);
	}
	
	public function hookDisplayBackOfficeHeader() {
        // CSS
        if (version_compare(substr(_PS_VERSION_, 0, 5), '1.6.0', '<')) {
            $this->context->controller->addCSS($this->_path.'css/fkcorreios_admin_15x.css');
        }else {
            $this->context->controller->addCSS($this->_path.'css/fkcorreios_admin_16x.css');
        }

        $this->context->controller->addCSS($this->_path.'css/fkcorreios_tab.css');

		// JS
		$this->context->controller->addJS($this->_path.'js/fkcorreios_admin.js');
		$this->context->controller->addJS($this->_path.'js/maskedinput.js');
	}

    public function hookdisplayHeader($params) {
        // CSS
        if (version_compare(substr(_PS_VERSION_, 0, 5), '1.6.0', '<')) {
            $this->context->controller->addCSS($this->_path.'css/fkcorreios_front_15x.css');
        }else {
            $this->context->controller->addCSS($this->_path.'css/fkcorreios_front_16x.css');
        }

        // JS
        $this->context->controller->addJS($this->_path.'js/fkcorreios_front.js');
        $this->context->controller->addJS($this->_path.'js/maskedinput.js');
    }
	
	public function hookactionCarrierUpdate($params) {

        $atualizado = false;

        $correios_transp = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'fkcorreios_correios_transp` WHERE `id_carrier` = '.(int)$params['id_carrier']);

        // Verifica se houve alteracao no id
        if ((int)$correios_transp['id_carrier'] != (int)$params['carrier']->id) {
            $novo_id = $params['carrier']->id;
            $atualizado = true;
        }else {
            $novo_id = $correios_transp['id_carrier'];
        }

        // Verifica se houve alteracao no nome
        if ($correios_transp['nome_carrier'] != $params['carrier']->name) {
            $novo_nome = $params['carrier']->name;
            $atualizado = true;
        }else {
            $novo_nome = $correios_transp['nome_carrier'];
        }

        // Verifica se houve alteracao na grade
        if ((int)$correios_transp['grade'] != (int)$params['carrier']->grade) {
            $nova_grade = $params['carrier']->grade;
            $atualizado = true;
        }else {
            $nova_grade = $correios_transp['grade'];
        }

        // Verifica se houve alteracao no campo ativo
        if ($correios_transp['ativo'] != $params['carrier']->active) {
            $novo_ativo = $params['carrier']->active;
            $atualizado = true;
        }else {
            $novo_ativo = $correios_transp['ativo'];
        }

        if ($atualizado == true) {

            $dados = array(
                'id_carrier' => $novo_id,
                'nome_carrier' => $novo_nome,
                'grade' => $nova_grade,
                'ativo' => $novo_ativo
            );

            Db::getInstance()->update('fkcorreios_correios_transp', $dados, '`id_carrier` = '.(int)$correios_transp['id_carrier']);
        }

	}

    public function hookdisplayBeforeCarrier($params) {

        if (!isset($this->context->smarty->tpl_vars['delivery_option_list'])) {
            return;
        }

        $delivery_option_list = $this->context->smarty->tpl_vars['delivery_option_list'];

        foreach ($delivery_option_list->value as $id_address) {

            foreach ($id_address as $key) {

                foreach ($key['carrier_list'] as $id_carrier) {

                    if (isset($this->_prazo_entrega[$id_carrier['instance']->id])) {

                        if (is_numeric($this->_prazo_entrega[$id_carrier['instance']->id])) {

                            if ($this->_prazo_entrega[$id_carrier['instance']->id] == 0) {
                                $msg = $this->l(' no mesmo dia');
                            }else {
                                if ($this->_prazo_entrega[$id_carrier['instance']->id] > 1) {
                                    $msg = $this->_prazo_entrega[$id_carrier['instance']->id].$this->l(' dias úteis');
                                }else {
                                    $msg = $this->_prazo_entrega[$id_carrier['instance']->id].$this->l(' dia útil');
                                }
                            }
                        }else {
                            $msg = $this->_prazo_entrega[$id_carrier['instance']->id];
                        }

                        $id_carrier['instance']->delay[$this->context->cart->id_lang] = $this->l('Prazo de entrega:').' '.$msg;
                    }
                }
            }
        }

    }

    public function hookdisplayShoppingCartFooter($params) {

        // Retorna se nao for para mostrar no carrinho
        if (Configuration::get('FKCORREIOS_BLOCO_CARRINHO') != 'on') {
            return false;
        }

        // Retorna se o carrinho estiver vazio
        if (!$params['products']) {
            return false;
        }
        
        // Retorna se carrinho contiver somente produtos virtuais
        $virtual = true;
        
        foreach ($this->context->cart->getProducts() as $prod) {
            if ($prod['is_virtual'] == 0) {
                $virtual = false;
            }    
        }
        
        if ($virtual == true) {
            return false;
        }
        
        $this->processaFrete($params, 'carrinho');
        return $this->display(__FILE__, 'views/carrinho.tpl');
    }
    
    public function hookDisplayRightColumnProduct($params) {
        
        // Retorna se for versao 1.5.x
        if (version_compare(substr(_PS_VERSION_, 0 ,5), '1.6.0', '<')) {
            return false;
        }
        
        // Retorna se nao for para mostrar em produtos
        if (Configuration::get('FKCORREIOS_BLOCO_PRODUTO') != 'on') {
            return false;
        }
        
        // Retorna se nao for para mostrar no resumo
        if (Configuration::get('FKCORREIOS_BLOCO_POSICAO') != '0') {
            return false;
        }
        
        // Retorna se $params nao contem dados do produto (override ainda nao executado)
        if (!isset($params['product'])) {
            return false;
        }

        // Retorna se for produto virtual
        if ($params['product']->is_virtual == 1) {
            return false;
        }
        
        $this->processaFrete($params, 'produto');
        
        // Informa id do produto - smarty
        $this->smarty->assign(array('fkcorreios_id_produto' => $params['product']->id));
        
        return $this->display(__FILE__, 'views/produto_resumo.tpl'); 
    }
    
    public function hookdisplayFooterProduct($params) {
        
        // Retorna se nao for para mostrar em produtos
        if (Configuration::get('FKCORREIOS_BLOCO_PRODUTO') != 'on') {
            return false;
        }
        
        // Retorna se nao for para mostrar no resumo (somente Prestashop 1.6.x)
        if (version_compare(substr(_PS_VERSION_, 0 ,5), '1.6.0', '>=')) {
            if (Configuration::get('FKCORREIOS_BLOCO_POSICAO') != '1') {
                return false;
            }
        }
        
        // Retorna se for produto virtual
        if ($params['product']->is_virtual == 1) {
            return false;
        }
        
        $this->processaFrete($params, 'produto');
        
        // Informa id do produto - smarty
        $this->smarty->assign(array('fkcorreios_id_produto' => $params['product']->id));
        
        return $this->display(__FILE__, 'views/produto.tpl');           
    }
    
    private function processaFrete($params, $origem) {
        
        // Inicializa variaveis
        $cep_destino = '';
        $uf_destino = '';
        $valor_frete = 0;
        
        // Inicializa CEP - smarty
        $this->smarty->assign(array('fkcorreios_cep' => ''));
        if ($this->context->cookie->fkcorreios_cep) {
            $this->smarty->assign(array('fkcorreios_cep' => $this->context->cookie->fkcorreios_cep));
        }
        
        // Inicializa foco - smarty
        $this->smarty->assign(array('fkcorreios_foco' => false));
        
        // Inicializa variavel de mensagem
        $fkcorreios_msg = $this->l('Aguardando CEP');

        // Inicializa variavel do valor frete
        $fkcorreios_frete = array();
        
        // Inicializa a classe funcoes
        $funcoes = new FKcorreiosLiteFuncoes();
        
        // Inicializa variavel que indica se deve ou não processar o frete
        $processar = false;
        
        // Recupera CEP Destino
        $validar_cep = false;
        
        if ($origem == 'produto' and Tools::isSubmit('submitProd')) {
            
            $processar = true;
            $validar_cep = true;
            $cep_destino = $_POST['fkcorreios_cep_prod'];
            
            // Grava cookie do foco
            $this->context->cookie->fkcorreios_foco = true;
            
        }elseif ($origem == 'carrinho' and (Tools::isSubmit('submitCarrinho') Or $this->context->customer->isLogged() Or isset($this->context->cookie->fkcorreios_cep))) {
             
            $processar = true;
            $validar_cep = true;
             
            // Se enviado CEP via submit
            if (Tools::isSubmit('submitCarrinho')){
                $cep_destino = $_POST['fkcorreios_cep_carrinho'];
                
                // Grava cookie do foco
                $this->context->cookie->fkcorreios_foco = true;
            }else {
                // Se o cliente esta logado
                if ($this->context->customer->isLogged()) {
                    $cep_destino = $params['delivery']->postcode;
                }else {
                    $cep_destino = $this->context->cookie->fkcorreios_cep;
                }
            }
        }
        
        // Valida CEP
        if ($validar_cep) {
            
            // Valida CEP destino
            $cep_destino = trim(preg_replace("/[^0-9]/", "", $cep_destino));

            // Envia mensagem de erro se o CEP for invalido
            if (strlen($cep_destino) <> 8) {

                $processar = false;
                $fkcorreios_msg = $this->l('CEP inválido');
            }else {
                // Recupera UF
                $uf_destino = $funcoes->retornaUF($cep_destino);

                // Envia mensagem de erro se UF não localizada
                if ($uf_destino == 'erro') {
                    $processar = false;
                    $fkcorreios_msg = $this->l('CEP inválido');
                }    
            }
        }
        
        // Processa o frete
        if ($processar) {
            
            // Recupera dados dos servicos dos Correios
            $sql = "SELECT
                        "._DB_PREFIX_."fkcorreios_correios_transp.id_carrier,
                        "._DB_PREFIX_."carrier.id_reference,
                        "._DB_PREFIX_."fkcorreios_correios_transp.nome_carrier
                    FROM "._DB_PREFIX_."fkcorreios_correios_transp
                        INNER JOIN "._DB_PREFIX_."carrier
                            ON "._DB_PREFIX_."fkcorreios_correios_transp.id_carrier = "._DB_PREFIX_."carrier.id_carrier
                    WHERE "._DB_PREFIX_."fkcorreios_correios_transp.ativo = 1";
            
            $correios_transp = Db::getInstance()->executeS($sql);
            
            foreach ($correios_transp as $reg) {
                
                // Recupera UF origem
                $uf_origem = $funcoes->retornaUF(trim(preg_replace("/[^0-9]/", "", Configuration::get('FKCORREIOS_MEU_CEP'))));

                // Recupera dados do servico
                $dados_carrier = $this->recuperaDadosServico($reg['id_carrier']);
                
                // Recupera valor do pedido, verifica frete gratis e grava dados dos produtos
                if ($origem == 'produto') {
                    
                    $preco = $params['product']->price;
                    $impostos = $params['product']->tax_rate;
                    $valor_pedido = $preco * (1+($impostos/100));
                    
                    // Verifica se e frete gratis considerando valor do produto
                    $pedido_frete_gratis = $this->verificaPedidoFreteGratis($valor_pedido);

                    // Calcula cubagem
                    $cubagem = $params['product']->height * $params['product']->width * $params['product']->depth;

                    // Grava array com dados do produto
                    $produtos = array();
                    $produtos[] = array(
                        'id'            => $params['product']->id,
                        'altura'        => $params['product']->height,
                        'largura'       => $params['product']->width,
                        'comprimento'   => $params['product']->depth,
                        'peso'          => $params['product']->weight,
                        'cubagem'       => $cubagem,
                        'valor_produto' => $valor_pedido,
                        'frete_gratis'  => false
                    );    
                }elseif ($origem == 'carrinho') {
                    $valor_pedido = $this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
                    
                    // Verifica se e frete gratis considerando valor do pedido
                    $pedido_frete_gratis = $this->verificaPedidoFreteGratis($valor_pedido);

                    // Recupera produtos
                    $produtos = array();

                    foreach ($this->context->cart->getProducts() as $prod) {

                        // Ignora o produto se for virtual
                        if ($prod['is_virtual'] == 1) {
                            continue;
                        }
                        
                        // Calcula cubagem
                        $cubagem = $prod['height'] * $prod['width'] * $prod['depth'];

                        for ($qty = 0; $qty < $prod['quantity']; $qty++) {

                            $produtos[] = array(
                                'id'            => $prod['id_product'],
                                'altura'        => $prod['height'],
                                'largura'       => $prod['width'],
                                'comprimento'   => $prod['depth'],
                                'peso'          => $prod['weight'],
                                'cubagem'       => $cubagem,
                                'valor_produto' => $prod['price_wt'],
                                'frete_gratis'  => false
                            );
                        }
                    }    
                }
                
                // Processa embalagens
                if (Configuration::get('FKCORREIOS_EMBALAGEM') == '0') {
                    $embalagens = $this->processaEmbalagemIndividual($reg['id_carrier'], $produtos, $uf_origem, $uf_destino);
                }else {
                    $embalagens = $this->processaEmbalagemPadrao($reg['id_carrier'], $produtos, $uf_origem, $uf_destino);
                }

                // Ignora carrier se nao existirem embalagens (ou seja as dimensoes estao fora do permitido
                if (!$embalagens) {
                    continue;
                }

                $fkcorreios = array(
                    'cep_origem'                        => trim(preg_replace("/[^0-9]/", "", Configuration::get('FKCORREIOS_MEU_CEP'))),
                    'uf_origem'                         => $uf_origem,
                    'cep_destino'                       => $cep_destino,
                    'uf_destino'                        => $uf_destino,
                    'tempo_preparacao'                  => 0,
                    'custos_envio'                      => 0,
                    'cod_servico'                       => $dados_carrier['cod_servico'],
                    'cod_administrativo'                => $dados_carrier['cod_administrativo'],
                    'senha'                             => $dados_carrier['senha'],
                    'valor_pedido'                      => $valor_pedido,
                    'pedido_frete_gratis'               => $pedido_frete_gratis['frete_gratis'],
                    'produto_frete_gratis'              => false,
                    'mostrar_todos_carrier'             => false,
                    'carrier_atual'                     => $reg['id_carrier'],
                    'carrier_frete_gratis'              => $pedido_frete_gratis['carrier_frete_gratis'],
                    'produtos'                          => $produtos,
                    'embalagens'                        => $embalagens
                );
                
                // Ignora Carrier se o Pedido for Frete Gratis e configurado para mostrar somente o Carrier de Frete Gratis
                if ($fkcorreios['pedido_frete_gratis'] == true And $fkcorreios['mostrar_todos_carrier'] == false And $fkcorreios['carrier_atual'] != $fkcorreios['carrier_frete_gratis']) {
                    continue;
                }

                // Calcula valor do frete dos Correios
                $valor_frete = $this->processaCorreiosOnline($fkcorreios);

                // Ignora Carrier se valor do frete for false
                if ($valor_frete === false) {
                    continue;
                }
                
                // Path do logotipo
                $path_logo = Tools::getShopDomainSsl(true, true)._PS_IMG_.'s/'.$reg['id_carrier'].'.jpg';

                if (!file_exists(_PS_IMG_DIR_.'s/'.$reg['id_carrier'].'.jpg')) {
                    $path_logo = '';
                }

                $fkcorreios_msg = $this->l('Frete calculado');

                // Formata prazo de entrega
                if (is_numeric($this->_prazo_entrega[$reg['id_carrier']])) {
                    if ($this->_prazo_entrega[$reg['id_carrier']] == 0) {
                        $prazo_entrega = $this->l(' no mesmo dia');
                    }else {
                        if ($this->_prazo_entrega[$reg['id_carrier']] > 1) {
                            $prazo_entrega = $this->_prazo_entrega[$reg['id_carrier']].$this->l(' dias úteis');
                        }else {
                            $prazo_entrega = $this->_prazo_entrega[$reg['id_carrier']].$this->l(' dia útil');
                        }
                    }
                }else {
                    $prazo_entrega = $this->_prazo_entrega[$reg['id_carrier']];
                }

                $fkcorreios_frete[] = array(
                    'url_imagem'    => $path_logo,
                    'nome_carrier'  => $reg['nome_carrier'],
                    'prazo_entrega' => $prazo_entrega,
                    'valor_frete'   => $valor_frete
                );
            }
            
            // Grava cookie do CEP
            $this->context->cookie->fkcorreios_cep = $cep_destino;
            $this->smarty->assign(array('fkcorreios_cep' => $cep_destino));

            // Classifica array por valor do frete
            usort($fkcorreios_frete, array($this, 'ordenaValorFrete'));

            $this->context->smarty->assign(array('fkcorreios_frete' => $fkcorreios_frete));
            
            if ($origem == 'carrinho') {
                // Recarrega a pagina para atualizar o valor do frete do carrinho se o cliente não estiver logado
                if (Tools::isSubmit('submitCarrinho') And !$this->context->customer->isLogged()){
                    if (Configuration::get('PS_REWRITING_SETTINGS') == 0) {
                        $atualPage = $_SERVER['REQUEST_URI'];
                    }else {
                        $atualPage = Tools::getShopDomainSsl(true,true).$_SERVER['REQUEST_URI'];
                    }

                    Tools::redirect($atualPage);
                }
            }    
        }
        
        // Grava foco no smarty
        if ($this->context->cookie->fkcorreios_foco) {
            $this->smarty->assign(array('fkcorreios_foco' => true));
            $this->context->cookie->fkcorreios_foco = '';   
        }
        
        // Grava mensagem no Smarty
        if ($processar and count($fkcorreios_frete) == 0) {
            $fkcorreios_msg = $this->l('Não foi possível selecionar transportadora para a localidade. Favor entrar em contato com o Atendimento ao Cliente.');
            $this->context->smarty->assign(array('fkcorreios_msg' => $fkcorreios_msg));
        }else {
            $this->context->smarty->assign(array('fkcorreios_msg' => $fkcorreios_msg));
        }
        
        return true;
    }

    private function recuperaDadosServico($id_carrier) {

        $retorno = array(
            'regiao_atendida'                   => true,
            'cod_servico'                       => '',
            'cod_administrativo'                => '',
            'senha'                             => '',
        );
        
        $sql = 'SELECT cod_servico
                FROM '._DB_PREFIX_.'fkcorreios_especificacoes_correios
                    INNER JOIN '._DB_PREFIX_.'fkcorreios_correios_transp
                        ON '._DB_PREFIX_.'fkcorreios_especificacoes_correios.id = '._DB_PREFIX_.'fkcorreios_correios_transp.id_correios
                WHERE '._DB_PREFIX_.'fkcorreios_correios_transp.ativo = 1 AND 
                      '._DB_PREFIX_.'fkcorreios_correios_transp.id_shop = '.(int)$this->context->shop->id.' AND 
                      '._DB_PREFIX_.'fkcorreios_correios_transp.id_carrier = '.(int)$id_carrier;

        $especif_correios = Db::getInstance()->getRow($sql);

        if ($especif_correios) {
            $retorno['cod_servico'] = $especif_correios['cod_servico'];
            $retorno['cod_administrativo'] = '';
            $retorno['senha'] = '';
        }

        return $retorno;
    }

    private function verificaPedidoFreteGratis($valor_pedido) {

        $sql = 'SELECT  '._DB_PREFIX_.'fkcorreios_correios_transp.id_carrier
                FROM '._DB_PREFIX_.'fkcorreios_frete_gratis
                    INNER JOIN '._DB_PREFIX_.'fkcorreios_correios_transp
                    ON '._DB_PREFIX_.'fkcorreios_frete_gratis.id_correios_transp = '._DB_PREFIX_.'fkcorreios_correios_transp.id
                WHERE   '._DB_PREFIX_.'fkcorreios_frete_gratis.ativo = 1 AND
                        '._DB_PREFIX_.'fkcorreios_frete_gratis.valor_pedido > 0 AND
                        '._DB_PREFIX_.'fkcorreios_frete_gratis.valor_pedido <= '.$valor_pedido.' AND
                        '._DB_PREFIX_.'fkcorreios_frete_gratis.id_shop = '.$this->context->shop->id;

        $frete_gratis = Db::getInstance()->executeS($sql);

        if ($frete_gratis) {

            foreach ($frete_gratis as $reg) {
                return array('frete_gratis' => true, 'carrier_frete_gratis' => $reg['id_carrier']);
            }
        }

        return array('frete_gratis' => false, 'carrier_frete_gratis' => '0');
    }

    private function processaEmbalagemIndividual($id_carrier, $produtos, $uf_origem, $uf_destino) {

        $embalagens = array();

        // Recupera as dimensoes permitidas
        $dimensoes = $this->recuperaDimensoes($id_carrier, $uf_origem, $uf_destino);

        foreach ($produtos as $prod) {

            // Retorna vazio (ignora carrier) se as dimensoes e peso estiverem fora do permitido
            if ($prod['altura'] > $dimensoes['altura_max'] Or $prod['largura'] > $dimensoes['largura_max'] Or $prod['comprimento'] > $dimensoes['comprimento_max'] Or
                $prod['peso']  > $dimensoes['peso_maximo'] Or
                $prod['altura'] + $prod['largura'] + $prod['comprimento'] > $dimensoes['somatoria_dimensoes_max']) {

                return array();
            }

            $embalagens[] = array(
                'altura'            => ($prod['altura'] < $dimensoes['altura_min'] ? $dimensoes['altura_min'] : $prod['altura']),
                'largura'           => ($prod['largura'] < $dimensoes['largura_min'] ? $dimensoes['largura_min'] : $prod['largura']),
                'comprimento'       => ($prod['comprimento'] < $dimensoes['comprimento_min'] ? $dimensoes['comprimento_min'] : $prod['comprimento']),
                'peso_embalagem'    => '0',
                'custo_embalagem'   => '0',
                'cubagem'           => $prod['cubagem'],
                'peso_produtos'     => $prod['peso'],
                'valor_produtos'    => $prod['valor_produto'],
                'frete_gratis'      => $prod['frete_gratis']
            );
        }

        return $embalagens;
    }

    private function processaEmbalagemPadrao($id_carrier, $produtos, $uf_origem, $uf_destino) {

        // Recupera as dimensoes permitidas
        $dimensoes = $this->recuperaDimensoes($id_carrier, $uf_origem, $uf_destino);

        // Seleciona as embalagens validas para os Correios
        $sql = 'SELECT *
                FROM `'._DB_PREFIX_.'fkcorreios_embalagens`
                WHERE   `ativo` = 1 AND `id_shop` = '.$this->context->shop->id.' AND
                        `comprimento` >= '.$dimensoes['comprimento_min'].' AND `comprimento` <= '.$dimensoes['comprimento_max'].' AND
                        `altura` >= '.$dimensoes['altura_min'].' AND `altura` <= '.$dimensoes['altura_max'].' AND
                        `largura` >= '.$dimensoes['largura_min'].' AND `largura` <= '.$dimensoes['largura_max'].' AND
                        `comprimento` + `altura` + `largura` <= '.$dimensoes['somatoria_dimensoes_max'].'
                ORDER BY `cubagem`';

        $caixas = Db::getInstance()->executeS($sql);

        // Classifica produtos por cubagem
        usort($produtos, array($this, 'ordenaCubagem'));

        // Inicializa variaveis das embalagens
        $embalagens = array();

        $altura_embalagem = 0;
        $largura_embalagem = 0;
        $comprimento_embalagem = 0;
        $peso_embalagem = 0;
        $custo_embalagem = 0;
        $cubagem_embalagem = 0;

        $peso_acumulado_produtos = 0;
        $valor_acumulado_produtos = 0;
        $cubagem_acumulada_produtos = 0;

        // Adiciona os produtos em suas embalagens
        foreach ($produtos as $prod) {

            // Se peso do produto for igual a zero assume valor minimo
            if ($prod['peso'] > 0) {
                $peso_produto = $prod['peso'];
            }else {
                $peso_produto = 0.01;
            }

            // Retorna vazio (ignora carrier) se as dimensoes e peso estiverem fora do permitido
            $embalagem_selecionada = $this->selecionaEmbalagem($caixas, $prod['cubagem']);
            
            if ($prod['altura'] > $dimensoes['altura_max'] Or 
                $prod['largura'] > $dimensoes['largura_max'] Or 
                $prod['comprimento'] > $dimensoes['comprimento_max'] Or
                $peso_produto  > $dimensoes['peso_maximo'] Or
                $prod['altura'] + $prod['largura'] + $prod['comprimento'] > $dimensoes['somatoria_dimensoes_max']) {

                return array();
            }

            if ($embalagem_selecionada) {
                if (($peso_produto + $embalagem_selecionada['peso']) > $dimensoes['peso_maximo']) {
                    return array();
                }
            }

            // Grava embalagem se produto for frete gratis
            if ($prod['frete_gratis'] == true) {

                // Grava dados considerando as dimensoes minimas
                $embalagens[] = array(
                    'altura'            => ($prod['altura'] < $dimensoes['altura_min'] ? $dimensoes['altura_min'] : $prod['altura']),
                    'largura'           => ($prod['largura'] < $dimensoes['largura_min'] ? $dimensoes['largura_min'] : $prod['largura']),
                    'comprimento'       => ($prod['comprimento'] < $dimensoes['comprimento_min'] ? $dimensoes['comprimento_min'] : $prod['comprimento']),
                    'peso_embalagem'    => 0,
                    'custo_embalagem'   => 0,
                    'cubagem'           => $prod['cubagem'],
                    'peso_produtos'     => $peso_produto,
                    'valor_produtos'    => $prod['valor_produto'],
                    'frete_gratis'      => true
                );

                continue;
            }

            // Grava embalagem se nao existe embalagem para o produto
            if (!$embalagem_selecionada) {
                $embalagens[] = array(
                    'altura'            => ($prod['altura'] < $dimensoes['altura_min'] ? $dimensoes['altura_min'] : $prod['altura']),
                    'largura'           => ($prod['largura'] < $dimensoes['largura_min'] ? $dimensoes['largura_min'] : $prod['largura']),
                    'comprimento'       => ($prod['comprimento'] < $dimensoes['comprimento_min'] ? $dimensoes['comprimento_min'] : $prod['comprimento']),
                    'peso_embalagem'    => 0,
                    'custo_embalagem'   => 0,
                    'cubagem'           => $prod['cubagem'],
                    'peso_produtos'     => $peso_produto,
                    'valor_produtos'    => $prod['valor_produto'],
                    'frete_gratis'      => false
                );

                continue;
            }

            // Verifica se existe caixa para a cubagem acumulada somada a cubagem do produto atual
            $embalagem_selecionada = $this->selecionaEmbalagem($caixas, ($prod['cubagem'] + $cubagem_acumulada_produtos));

            // Se embalagem nao localizada
            if (!$embalagem_selecionada Or (($peso_acumulado_produtos + $peso_produto + $peso_embalagem) > $dimensoes['peso_maximo'] And $dimensoes['peso_maximo'] > 0)) {

                // Grava dados acumulados
                $embalagens[] = array(
                    'altura'            => $altura_embalagem,
                    'largura'           => $largura_embalagem,
                    'comprimento'       => $comprimento_embalagem,
                    'peso_embalagem'    => $peso_embalagem,
                    'custo_embalagem'   => $custo_embalagem,
                    'cubagem'           => $cubagem_embalagem,
                    'peso_produtos'     => $peso_acumulado_produtos,
                    'valor_produtos'    => $valor_acumulado_produtos,
                    'frete_gratis'      => false
                );

                // Seleciona embalagem para o produto
                $embalagem_selecionada = $this->selecionaEmbalagem($caixas, $prod['cubagem']);

                // Inicializa variaveis
                $peso_acumulado_produtos = 0;
                $valor_acumulado_produtos = 0;
                $cubagem_acumulada_produtos = 0;
            }

            // Guarda os campos da embalagem
            $altura_embalagem = $embalagem_selecionada['altura'];
            $largura_embalagem = $embalagem_selecionada['largura'];
            $comprimento_embalagem = $embalagem_selecionada['comprimento'];
            $peso_embalagem = $embalagem_selecionada['peso'];
            $custo_embalagem = $embalagem_selecionada['custo'];
            $cubagem_embalagem = $embalagem_selecionada['cubagem'];

            // Acumula valores
            $peso_acumulado_produtos += $peso_produto;
            $valor_acumulado_produtos += $prod['valor_produto'];
            $cubagem_acumulada_produtos += $prod['cubagem'];
        }

        // Grava a ultima embalagem
        if ($peso_acumulado_produtos > 0) {

            $embalagens[] = array(
                'altura'            => $altura_embalagem,
                'largura'           => $largura_embalagem,
                'comprimento'       => $comprimento_embalagem,
                'peso_embalagem'    => $peso_embalagem,
                'custo_embalagem'   => $custo_embalagem,
                'cubagem'           => $cubagem_embalagem,
                'peso_produtos'     => $peso_acumulado_produtos,
                'valor_produtos'    => $valor_acumulado_produtos,
                'frete_gratis'      => false
            );
        }

        return $embalagens;
    }

    private function recuperaDimensoes($id_carrier, $uf_origem, $uf_destino) {

        // Recupera as dimensoes mínimas/maximas e pesos permitidos para os Correios
        $sql = 'SELECT  '._DB_PREFIX_.'fkcorreios_especificacoes_correios.comprimento_min,
                        '._DB_PREFIX_.'fkcorreios_especificacoes_correios.comprimento_max,
                        '._DB_PREFIX_.'fkcorreios_especificacoes_correios.largura_min,
                        '._DB_PREFIX_.'fkcorreios_especificacoes_correios.largura_max,
                        '._DB_PREFIX_.'fkcorreios_especificacoes_correios.altura_min,
                        '._DB_PREFIX_.'fkcorreios_especificacoes_correios.altura_max,
                        '._DB_PREFIX_.'fkcorreios_especificacoes_correios.somatoria_dimensoes_max,
                        '._DB_PREFIX_.'fkcorreios_especificacoes_correios.volume_max,
                        '._DB_PREFIX_.'fkcorreios_especificacoes_correios.peso_estadual_max,
                        '._DB_PREFIX_.'fkcorreios_especificacoes_correios.peso_nacional_max
                FROM '._DB_PREFIX_.'fkcorreios_correios_transp
                    INNER JOIN '._DB_PREFIX_.'fkcorreios_especificacoes_correios
                    ON '._DB_PREFIX_.'fkcorreios_correios_transp.id_correios = '._DB_PREFIX_.'fkcorreios_especificacoes_correios.id
                WHERE   '._DB_PREFIX_.'fkcorreios_correios_transp.id_shop = '.$this->context->shop->id.' AND
                        '._DB_PREFIX_.'fkcorreios_correios_transp.id_carrier = '.(int)$id_carrier;

        $especif_correios = Db::getInstance()->getRow($sql);

        if ($especif_correios) {

            if ($uf_origem == $uf_destino) {
                $peso_maximo = $especif_correios['peso_estadual_max'];
            }else {
                $peso_maximo = $especif_correios['peso_nacional_max'];
            }

            return array(
                'comprimento_min'           => $especif_correios['comprimento_min'],
                'comprimento_max'           => $especif_correios['comprimento_max'],
                'largura_min'               => $especif_correios['largura_min'],
                'largura_max'               => $especif_correios['largura_max'],
                'altura_min'                => $especif_correios['altura_min'],
                'altura_max'                => $especif_correios['altura_max'],
                'somatoria_dimensoes_max'   => $especif_correios['somatoria_dimensoes_max'],
                'volume_max'                => $especif_correios['volume_max'],
                'peso_maximo'               => $peso_maximo
            );
        }

        return array(
            'comprimento_min'           => 0,
            'comprimento_max'           => 0,
            'largura_min'               => 0,
            'largura_max'               => 0,
            'altura_min'                => 0,
            'altura_max'                => 0,
            'somatoria_dimensoes_max'   => 0,
            'volume_max'                => 0,
            'peso_maximo'               => 0
        );
    }

    private function selecionaEmbalagem($caixas, $cubagem) {

        foreach ($caixas as $caixa) {

            if ($cubagem <= $caixa['cubagem']) {

                return array(
                    'altura'        => $caixa['altura'],
                    'largura'       => $caixa['largura'],
                    'comprimento'   => $caixa['comprimento'],
                    'peso'          => $caixa['peso'],
                    'custo'         => $caixa['custo'],
                    'cubagem'       => $caixa['cubagem']
                );
            }
        }

        return array();
    }

    private function montaHash($fkcorreios, $embalagem) {
	
	// if criado por kleber para conseguir gerar boleto sem erro
	if (isset($this->context->cart->id) > 0) {
        	$id_cart = $this->context->cart->id; //.':'.;
	} else {
		$id_cart = 0;
	}

        $hash = $this->context->shop->id.':'.
	$id_cart.':'.
        $fkcorreios['carrier_atual'].':'.
        $fkcorreios['cep_origem'].':'.
        $fkcorreios['cep_destino'].':'.
        Configuration::get('FKCORREIOS_MAO_PROPRIA').':'.
        Configuration::get('FKCORREIOS_VALOR_DECLARADO').':'.
        Configuration::get('FKCORREIOS_AVISO_RECEBIMENTO').':'.
        $embalagem['altura'].'/'.
        $embalagem['largura'].'/'.
        $embalagem['comprimento'].'/'.
        $embalagem['cubagem'].'/'.
        $embalagem['valor_produtos'].'/'.
        $embalagem['peso_produtos'];

        return md5($hash);
    }

    private function gravaCache($hash, $valor_frete, $prazo_entrega) {

        $dados = array(
            'hash' => $hash,
            'valor_frete' => $valor_frete,
            'prazo_entrega' => $prazo_entrega
        );

        Db::getInstance()->insert('fkcorreios_cache', $dados);

    }

    private function verificaCache($hash) {

        $cache = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'fkcorreios_cache` WHERE `hash` = "'.$hash.'"');

        if ($cache) {
            return array('localizado' => true, 'valor_frete' => $cache['valor_frete'], 'prazo_entrega' => $cache['prazo_entrega']);
        }else {
            return array('localizado' => false, 'valor_frete' => '0', 'prazo_entrega' => '0');
        }
    }

    private function processaCorreiosOnline($fkcorreios) {

        // Inicializa variaveis
        $prazo_entrega = 0;
        $valor_frete = 0;
        $total_frete = 0;

        // Recupera dados das especificacoes dos Correios
        $sql = 'SELECT  '._DB_PREFIX_.'fkcorreios_especificacoes_correios.mao_propria_valor,
                        '._DB_PREFIX_.'fkcorreios_especificacoes_correios.aviso_recebimento_valor,
                        '._DB_PREFIX_.'fkcorreios_especificacoes_correios.valor_declarado_percentual,
                        '._DB_PREFIX_.'fkcorreios_especificacoes_correios.seguro_automatico_valor
                FROM '._DB_PREFIX_.'fkcorreios_correios_transp
                    INNER JOIN '._DB_PREFIX_.'fkcorreios_especificacoes_correios
                    ON '._DB_PREFIX_.'fkcorreios_correios_transp.id_correios = '._DB_PREFIX_.'fkcorreios_especificacoes_correios.id
                WHERE   '._DB_PREFIX_.'fkcorreios_correios_transp.id_shop = '.$this->context->shop->id .' AND
                        '._DB_PREFIX_.'fkcorreios_correios_transp.id_carrier = '.(int)$fkcorreios['carrier_atual'];

        $especif_correios = Db::getInstance()->getRow($sql);

        // Instancia a classe correios
        $ws = new FKcorreiosLiteCorreios();

        foreach ($fkcorreios['embalagens'] as $embalagem) {

            // Verifica se existe no cache
            $hash = $this->montaHash($fkcorreios, $embalagem);
            $cache = $this->verificaCache($hash);

            if ($cache['localizado'] == true) {
                // Recupera valores
                $prazo_entrega = $cache['prazo_entrega'];
                $valor_frete = $cache['valor_frete'];
            }else {
                // Consome web services dos Correios
                $ws->setEmpresa($fkcorreios['cod_administrativo']);
                $ws->setSenha($fkcorreios['senha']);
                $ws->setCodServico($fkcorreios['cod_servico']);
                $ws->setCepOrigem($fkcorreios['cep_origem']);
                $ws->setCepDestino($fkcorreios['cep_destino']);
                $ws->setPeso($embalagem['peso_produtos'] + $embalagem['peso_embalagem']);
                $ws->setFormato('1');
                $ws->setComprimento($embalagem['comprimento']);
                $ws->setAltura($embalagem['altura']);
                $ws->setLargura($embalagem['largura']);
                $ws->setDiametro('0');
                $ws->setCubagem($embalagem['cubagem']);
                $ws->setMaoPropria('N');
                $ws->setValorDeclarado('0');
                $ws->setAvisoRecebimento('N');

                // Se ocorreu erro na consulta aos Correios
                if (!$ws->Calcular()) {
                    return false;
                }

                // Recupera valores
                $prazo_entrega = $ws->getPrazoEntrega();
                $valor_frete = $ws->getValor();

                // Grava cache
                $hash = $this->montaHash($fkcorreios, $embalagem);
                $this->gravaCache($hash, $ws->getValor(), $ws->getPrazoEntrega());
            }

            // Grava prazo de entrega
            $this->_prazo_entrega[$fkcorreios['carrier_atual']] = $prazo_entrega;

            // Verifica se o pedido e frete gratis e o Carrier e o Carrier Frete Gratis
            if ($fkcorreios['pedido_frete_gratis'] == true And $fkcorreios['carrier_atual'] == $fkcorreios['carrier_frete_gratis']) {
                return 0;
            }

            // Acumula valor do frete
            $total_frete += $valor_frete + $embalagem['custo_embalagem'];

        }
        
        return $total_frete;

    }

    static function ordenaCubagem($a, $b) {

        if ($a['cubagem'] == $b['cubagem']) {
            return 0;
        }
        return ($a['cubagem'] < $b['cubagem']) ? -1 : 1;
    }

    static function ordenaValorFrete($a, $b) {

        if ($a['valor_frete'] == $b['valor_frete']) {
            return 0;
        }
        return ($a['valor_frete'] < $b['valor_frete']) ? -1 : 1;
    }

    private function criaTabelas() {
		
		// Exclui tabelas anteriores, se existirem
		$this->excluiTabelas();
		
		$db = Db::getInstance();
		
		// Cria a tabela de cadastro de cep
		$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fkcorreios_cadastro_cep` (
            	`id` 			    int(10)     NOT NULL AUTO_INCREMENT,
            	`estado` 		    varchar(2),
            	`capital` 		    varchar(50),
            	`cep_estado` 	    varchar(150),
           	 	`cep_capital` 	    varchar(150),
           	 	`cep_base_capital` 	varchar(9),
           	 	`cep_base_interior`	varchar(9),
            	PRIMARY KEY  (`id`)
            	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
		$db-> Execute($sql);
		
		// Insere intervalo de cep dos estados e capitais
		$sql = "INSERT INTO `"._DB_PREFIX_."fkcorreios_cadastro_cep` (`estado`, `capital`, `cep_estado`, `cep_capital`, `cep_base_capital`, `cep_base_interior`) VALUES
            ('AC', 'Rio Branco', 		'69900000:69999999', 						'69900001:69923999',                    '69900-001', '69985-000'),
            ('AL', 'Maceió', 			'57000000:57999999', 						'57000001:57099999',                    '57000-001', '57770-000'),
            ('AM', 'Manaus', 			'69000000:69299999/69400000:69899999', 		'69000001:69099999',                    '69000-001', '69158-000'),
            ('AP', 'Macapá', 			'68900000:68999999', 						'68900001:68911999',                    '68900-001', '68950-000'),
            ('BA', 'Salvador', 			'40000000:48999999', 						'40000001:42499999',                    '40000-001', '44500-000'),
            ('CE', 'Fortaleza', 		'60000000:63999999', 						'60000001:61599999',                    '60000-001', '62750-000'),
            ('DF', 'Brasília', 			'70000000:72799999/73000000:73699999', 		'70000001:72799999/73000001:73699999',  '70000-001', '70000-001'),
            ('ES', 'Vitória', 			'29000000:29999999', 						'29000001:29099999',                    '29000-001', '29700-001'),
            ('GO', 'Goiãnia', 			'72800000:72999999/73700000:76799999', 		'74000001:74899999',                    '74000-001', '75000-001'),
            ('MA', 'São Luiz', 			'65000000:65999999', 						'65000001:65109999',                    '65000-001', '65250-000'),
            ('MG', 'Belo Horizonte', 	'30000000:39999999', 						'30000001:31999999',                    '30000-001', '37130-000'),
            ('MS', 'Campo Grande', 		'79000000:79999999', 						'79000001:79124999',                    '79000-001', '79300-001'),
            ('MT', 'Cuiabá', 			'78000000:78899999', 						'78000001:78099999',                    '78000-001', '78200-000'),
            ('PA', 'Belém', 			'66000000:68899999', 						'66000001:66999999',                    '66000-001', '68370-001'),
            ('PB', 'João Pessoa', 		'58000000:58999999', 						'58000001:58099999',                    '58000-001', '58930-000'),
            ('PE', 'Recife', 			'50000000:56999999', 						'50000001:52999999',                    '50000-001', '53690-000'),
            ('PI', 'Teresina', 			'64000000:64999999', 						'64000001:64099999',                    '64000-001', '64235-000'),
            ('PR', 'Curitiba', 			'80000000:87999999', 						'80000001:82999999',                    '80000-001', '86800-001'),
            ('RJ', 'Rio de Janeiro', 	'20000000:28999999', 						'20000001:23799999',                    '20000-001', '27300-001'),
            ('RN', 'Natal', 			'59000000:59999999', 						'59000001:59139999',                    '59000-001', '59780-000'),
            ('RO', 'Porto Velho', 		'76800000:76999999', 						'76800001:76834999',                    '76800-001', '76870-001'),
            ('RR', 'Boa Vista', 		'69300000:69399999', 						'69300001:69339999',                    '69300-001', '69343-000'),
            ('RS', 'Porto Alegre', 		'90000000:99999999', 						'90000001:91999999',                    '90000-001', '97540-001'),
            ('SC', 'Florianópolis', 	'88000000:89999999', 						'88000001:88099999',                    '88000-001', '89245-000'),
            ('SE', 'Aracajú', 			'49000000:49999999', 						'49000001:49098999',                    '49000-001', '49500-000'),
            ('SP', 'São Paulo', 		'01000000:19999999', 						'01000001:05999999/08000000:08499999',  '01000-001', '17800-000'),
            ('TO', 'Palmas', 			'77000000:77999999', 						'77000001:77249999',                    '77000-001', '77645-000');";
		$db-> Execute($sql);
		
		// Cria tabela com as medidas e peso de caixas
		$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fkcorreios_embalagens` (
	            `id` 			int(10) 		NOT NULL AUTO_INCREMENT,
				`id_shop`		int(10),
	            `descricao` 	varchar(50),
	            `comprimento` 	decimal(20,2),
	            `altura` 		decimal(20,2),
	            `largura` 		decimal(20,2),
	            `peso` 			decimal(20,2),
	            `cubagem` 		decimal(20,6),
	            `custo` 		decimal(20,2),
	            `ativo` 		tinyint(1),
	            PRIMARY KEY (`id`)
	            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
		$db-> Execute($sql);
        
        $sql = "INSERT INTO "._DB_PREFIX_."fkcorreios_embalagens(id_shop, descricao, comprimento, altura, largura, peso, cubagem, custo, ativo) VALUES
            (".$this->context->shop->id.", 'Caixa 1', 16.00, 2.00,  11.00, 0.20, 352.000000,  0.00, 1),
            (".$this->context->shop->id.", 'Caixa 2', 16.00, 4.00,  11.00, 0.25, 704.000000,  0.00, 1),
            (".$this->context->shop->id.", 'Caixa 3', 16.00, 6.00,  11.00, 0.30, 1056.000000, 0.00, 1),
            (".$this->context->shop->id.", 'Caixa 4', 16.00, 8.00,  11.00, 0.35, 1408.000000, 0.00, 1),
            (".$this->context->shop->id.", 'Caixa 5', 16.00, 10.00, 11.00, 0.40, 1760.000000, 0.00, 1);";
        $db-> Execute($sql);
		
		// Cria tabela com as Especificacoes dos Correios
        // id_interno: 1= E-SEDEX, 2= PAC, 3= SEDEX, 4= SEDEX 10, 5= SEDEX 12, 6= SEDEX HOJE
		$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fkcorreios_especificacoes_correios` (
	            `id` 							int(10) 		NOT NULL AUTO_INCREMENT,
	            `id_shop`		                int(10),
	            `id_interno`			        int(10),
	            `servico` 						varchar(50),
				`cod_servico`					varchar(50),
				`cod_administrativo` 			varchar(50),
            	`senha` 						varchar(10),
	            `comprimento_min` 				decimal(20,2),
				`comprimento_max` 				decimal(20,2),
				`largura_min` 					decimal(20,2),
				`largura_max` 					decimal(20,2),
	            `altura_min` 					decimal(20,2),
				`altura_max` 					decimal(20,2),
				`somatoria_dimensoes_max` 		decimal(20,2),
                `volume_max`                    decimal(20,2),
	            `peso_estadual_max`				decimal(20,2),
				`peso_nacional_max`				decimal(20,2),
				`intervalo_pesos_estadual`		varchar(250),
				`intervalo_pesos_nacional`		varchar(250),
				`cubagem_max_isenta`			decimal(20,5),
				`cubagem_base_calculo`			decimal(20,5),
				`mao_propria_valor`				decimal(20,2),
				`aviso_recebimento_valor`		decimal(20,2),
				`valor_declarado_percentual`	decimal(20,2),
				`valor_declarado_max`			decimal(20,2),
				`seguro_automatico_valor`       decimal(20,2),
	            PRIMARY KEY (`id`)
	            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
		$db-> Execute($sql);

		// Cria tabela com os servicos dos correios e transportadoras controladas pelo Fkcorreios
		$sql = 'CREATE TABLE IF NOT EXISTS `' ._DB_PREFIX_. 'fkcorreios_correios_transp` (
            	`id` 				int(10) 	NOT NULL AUTO_INCREMENT,
				`id_shop`			int(10),
            	`id_carrier` 		int(10),
				`id_correios` 		int(10),
				`nome_carrier`  	varchar(64),
            	`grade` 			int(10),
            	`ativo` 			tinyint(1),
            	PRIMARY KEY (`id`),
				INDEX (`id_carrier`, `id_shop`)
            	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
		$db-> Execute($sql);
		
        // Cria tabela com as configuracoes do frete gratis
        $sql = 'CREATE TABLE IF NOT EXISTS `' ._DB_PREFIX_. 'fkcorreios_frete_gratis` (
            	`id` 					int(10) 		NOT NULL AUTO_INCREMENT,
            	`id_shop`			    int(10),
				`id_correios_transp`	int(10),
				`nome_regiao`  			varchar(100),
				`regiao_uf`				varchar(100),
				`regiao_cep`			text,
				`valor_pedido`			decimal(20,2),
				`id_produtos`			text,
				`ativo` 			    tinyint(1),
				INDEX (`id_correios_transp`),
            	PRIMARY KEY (`id`)
            	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        $db-> Execute($sql);

        // Cria a tabela de cache
        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fkcorreios_cache` (
                `id`            int(10)     NOT NULL AUTO_INCREMENT,
                `hash`          varchar(32),
                `valor_frete`   decimal(20,2),
                `prazo_entrega` int(10),
                INDEX (`hash`),
                PRIMARY KEY  (`id`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        $db-> Execute($sql);

		return true;
	}
	
	private function excluiTabelas() {
		
		$db = Db::getInstance();
		
		$sql = "DROP TABLE IF EXISTS `"._DB_PREFIX_."fkcorreios_cadastro_cep`;";
		$db-> Execute($sql);
		
		$sql = "DROP TABLE IF EXISTS `"._DB_PREFIX_."fkcorreios_embalagens`;";
		$db-> Execute($sql);
		
		$sql = "DROP TABLE IF EXISTS `"._DB_PREFIX_."fkcorreios_especificacoes_correios`;";
		$db-> Execute($sql);
		
		$sql = "DROP TABLE IF EXISTS `"._DB_PREFIX_."fkcorreios_correios_transp`;";
		$db-> Execute($sql);
		
        $sql = "DROP TABLE IF EXISTS `"._DB_PREFIX_."fkcorreios_frete_gratis`;";
        $db-> Execute($sql);

        $sql = "DROP TABLE IF EXISTS `"._DB_PREFIX_."fkcorreios_cache`;";
        $db-> Execute($sql);
		
		return true;
	}

    private function instalaRegioes(){

        $regioes = new Zone();

        // Cria regiao Brasil
        $nome = 'Brasil';

        if (!$regioes->getIdByName($nome)) {
            $regioes->name = $nome;
            $regioes->active = true;
            $regioes->add();

            // Liga a regiao aos Shops
            $regioes->associateTo($regioes->id_shop_list);

            // Liga country padrao a regiao
            $country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
            $country->id_zone = $regioes->id;
            $country->save();

            // Liga Estados a regiao Brasil
            $states = new State();
            $estados_brasil = $states->getStatesByIdCountry($country->id);

            foreach ($estados_brasil as $estado) {
                Db::getInstance()->update('state', array('id_zone' => $regioes->id), 'id_state = '.(int)$estado['id_state']);
            }

        }

        return true;
    }
	
	private function wsRegistraLicenca($referencia, $dominio, $proprietario) {

		try {
            $this->_erroWs = '';

			$parm = array('iRefPedido' => $referencia, 'iDominio' => $dominio, 'iProprietario' => $proprietario, 'iIdProduto' => $this->_idProduto);
			$soap = new SoapClient($this->_urlWs, array('exceptions' => 1, "connection_timeout" => 30));
			$result = $soap->setRegistraLicencaOnline($parm);
	
			if ($result->status->oCodigo != '0') {
				$this->_erroWs = $result->status->oMensagem;
				return false;
			}
			 
		} catch (Exception $e) {
			$this->_erroWs = $e;
			return false;
		}
	
		return true;
	}
	
	private function wsVerificaLicenca($referencia, $dominio) {

		try {
            $this->_erroWs = '';
	
			$parm = array('iRefPedido' => $referencia, 'iDominio' => $dominio, 'iIdProduto' => $this->_idProduto);
			$soap = new SoapClient($this->_urlWs, array('exceptions' => 1, "connection_timeout" => 30));
			$result = $soap->getVerificaLicencaOnline($parm);
	
			if ($result->status->oCodigo != '0') {
				$this->_erroWs = $result->status->oMensagem;
				return false;
			}
	
		} catch (Exception $e) {
			$this->_erroWs = $e;
			return false;
		}
	
		return true;
	}
	
	private function wsAlteraLicenca($referencia, $dominio) {

		try {
			$this->_erroWs = '';
	
			$parm = array('iRefPedido' => $referencia, 'iDominio' => $dominio, 'iIdProduto' => $this->_idProduto);
			$soap = new SoapClient($this->_urlWs, array('exceptions' => 1, "connection_timeout" => 30));
			$result = $soap->setAlteraLicencaOnline($parm);
	
			if ($result->status->oCodigo != '0') {
				$this->_erroWs = $result->status->oMensagem;
				return false;
			}
	
		} catch (Exception $e) {
			$this->_erroWs = $e;
			return false;
		}
	
		return true;
	}
	
}
