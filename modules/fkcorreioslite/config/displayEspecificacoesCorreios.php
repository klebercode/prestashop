<html>

	<div class="fkcorreios_opcoes">
		<input id="fkcorreios_button_ajuda" name="fkcorreios_button_ajuda" type="button" value="" onClick="window.open('http://www.fokusfirst.com/fokusfirst/ajuda/fkcorreios_2014_lite.pdf','Janela','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=900,height=500,left=500,top=150'); return false;">
		<p>Ajuda</p>
	</div>

	<?php
    // Verifica se existem especificacoes cadastrados para o shop, se nao existir cria
    $esp_correios = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'fkcorreios_especificacoes_correios` WHERE `id_shop` = '.$this->context->shop->id);

    if (!$esp_correios) {
        
         $sql = "INSERT INTO `"._DB_PREFIX_."fkcorreios_especificacoes_correios` (`id_shop`, `id_interno`, `servico`, `cod_servico`, `cod_administrativo`, `senha`, `comprimento_min`, `comprimento_max`, `largura_min`, `largura_max`, `altura_min`, `altura_max`, `somatoria_dimensoes_max`, `peso_estadual_max`, `peso_nacional_max`, `intervalo_pesos_estadual`, `intervalo_pesos_nacional`, `cubagem_max_isenta`, `cubagem_base_calculo`, `mao_propria_valor`, `aviso_recebimento_valor`, `valor_declarado_percentual`, `valor_declarado_max`, `seguro_automatico_valor`) VALUES
            ('".$this->context->shop->id."', '2', 'PAC',         '41106', '', '', '16', '105', '11', '105', '2', '105', '200', '30', '30', '1/2/3/4/5/6/7/8/9/10/11/12/13/14/15/16/17/18/19/20/21/22/23/24/25/26/27/28/29/30',         '1/2/3/4/5/6/7/8/9/10/11/12/13/14/15/16/17/18/19/20/21/22/23/24/25/26/27/28/29/30',     '0',        '6000',        '4', '3', '1', '10000', '50'),
            ('".$this->context->shop->id."', '3', 'SEDEX',       '40010', '', '', '16', '105', '11', '105', '2', '105', '200', '30', '30', '0.3/1/2/3/4/5/6/7/8/9/10/11/12/13/14/15/16/17/18/19/20/21/22/23/24/25/26/27/28/29/30',     '0.3/1/2/3/4/5/6/7/8/9/10/11/12/13/14/15/16/17/18/19/20/21/22/23/24/25/26/27/28/29/30', '60000',    '6000',     '4', '3', '1', '10000', '50');";

        Db::getInstance()->execute($sql);
        
    }

	$esp_correios = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'fkcorreios_especificacoes_correios` WHERE `id_shop` = '.$this->context->shop->id.' ORDER BY `servico`');
	foreach($esp_correios as $reg) {
	?>

	<form action="<?php echo Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI'])?>&id_tab=5&section=configEspecifCorreios&id_esp_correios=<?php echo $reg['id']?>" method="post" class="form" id="configEspecifCorreios">
	
		<div class="fkcorreios_margin_form" id="fkcorreios_especificacoes_correios">
			
			<div class="fkcorreios_toggle_titulo" onclick="fkcorreiosToggle('fkcorreios_especificacoes_correios_itens_<?php echo $reg['id'];?>')">
                <?php echo $reg['servico'];?>
			</div>
			
			<div class="fkcorreios_toggle_itens" id="fkcorreios_especificacoes_correios_itens_<?php echo $reg['id'];?>">

                <div class="fkcorreios_form_group">
                    <label>Código serviço:</label>
                    <input disabled="" type="text" size="6" name="fkcorreios_cod_servico_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_cod_servico_'.$reg['id']) ? $reg['cod_servico'] : Tools::getValue('fkcorreios_cod_servico_'.$reg['id']));?>"/>
                </div>
                
                <div class="fkcorreios_form_group">
                    <label>Comprimento mínimo:</label>
                    <input type="text" size="6" name="fkcorreios_comprimento_min_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_comprimento_min_'.$reg['id']) ? $reg['comprimento_min'] : Tools::getValue('fkcorreios_comprimento_min_'.$reg['id']));?>"/>
                    <label>Comprimento máximo:</label>
                    <input type="text" size="6" name="fkcorreios_comprimento_max_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_comprimento_max_'.$reg['id']) ? $reg['comprimento_max'] : Tools::getValue('fkcorreios_comprimento_max_'.$reg['id']));?>"/>
                </div>
                <div class="fkcorreios_form_group">
                    <label>Largura mínima:</label>
                    <input type="text" size="6" name="fkcorreios_largura_min_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_largura_min_'.$reg['id']) ? $reg['largura_min'] : Tools::getValue('fkcorreios_largura_min_'.$reg['id']));?>"/>
                    <label>Largura máxima:</label>
                    <input type="text" size="6" name="fkcorreios_largura_max_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_largura_max_'.$reg['id']) ? $reg['largura_max'] : Tools::getValue('fkcorreios_largura_max_'.$reg['id']));?>"/>
                </div>
                <div class="fkcorreios_form_group">
                    <label>Altura mínima:</label>
                    <input type="text" size="6" name="fkcorreios_altura_min_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_altura_min_'.$reg['id']) ? $reg['altura_min'] : Tools::getValue('fkcorreios_altura_min_'.$reg['id']));?>"/>
                    <label>Altura máxima:</label>
                    <input type="text" size="6" name="fkcorreios_altura_max_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_altura_max_'.$reg['id']) ? $reg['altura_max'] : Tools::getValue('fkcorreios_altura_max_'.$reg['id']));?>"/>
                </div>
                <div class="fkcorreios_form_group">
                    <label>Somatória dimensões:</label>
                    <input type="text" size="6" name="fkcorreios_somatoria_dimensoes_max_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_somatoria_dimensoes_max_'.$reg['id']) ? $reg['somatoria_dimensoes_max'] : Tools::getValue('fkcorreios_somatoria_dimensoes_max_'.$reg['id']));?>"/>
                </div>
                <div class="fkcorreios_form_group">
                    <label>Peso máximo - Estadual:</label>
                    <input type="text" size="6" name="fkcorreios_peso_estadual_max_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_peso_estadual_max_'.$reg['id']) ? $reg['peso_estadual_max'] : Tools::getValue('fkcorreios_peso_estadual_max_'.$reg['id']));?>"/>
                    <label>Peso máximo - Nacional:</label>
                    <input type="text" size="6" name="fkcorreios_peso_nacional_max_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_peso_nacional_max_'.$reg['id']) ? $reg['peso_nacional_max'] : Tools::getValue('fkcorreios_peso_nacional_max_'.$reg['id']));?>"/>
                </div>

				<div class="fkcorreios_div_button">
			        <input class="fkcorreios_button" name="submitSave" type="submit" value="<?php echo $this->l('Salvar');?>">
				</div>
				
			</div>
				
		</div>
		
	</form>
	
	<?php 		
	}
	?>

</html>