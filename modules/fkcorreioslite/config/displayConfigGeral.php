<html>

<form action="<?php echo Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI'])?>&id_tab=2&section=configGeral" method="post" class="form" id="configGeral">

	<div class="fkcorreios_opcoes">
		<input id="fkcorreios_button_ajuda" name="fkcorreios_button_ajuda" type="button" value="" onClick="window.open('http://www.fokusfirst.com/fokusfirst/ajuda/fkcorreios_2014_lite.pdf','Janela','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=900,height=500,left=500,top=150'); return false;">
		<p>Ajuda</p>
	</div>
	
	<div class="fkcorreios_margin_form" id="fkcorreios_config_geral">
	
        <div class="fkcorreios_divisao">
            <div><?php echo $this->l('Meu CEP');?></div>
        </div>

        <div class="fkcorreios_form_group">
            <label></label>
            <input class="fkcorreios_text_cep" type="text" size="10" name="fkcorreios_meu_cep" value="<?php echo (!Tools::getValue('fkcorreios_meu_cep') ? Configuration::get('FKCORREIOS_MEU_CEP') : Tools::getValue('fkcorreios_meu_cep'));?>"/>
        </div>
        
        <div class="fkcorreios_form_group">
            <label></label>
            <input value="1" name="fkcorreios_embalagem" type="radio" <?php echo (Configuration::get('FKCORREIOS_EMBALAGEM') == '1' ? 'checked="checked" ' : '')?>> <?php echo $this->l('Utilizar embalagens padrão');?>
        </div>
        <div class="fkcorreios_form_group">
            <label></label>
            <input value="0" name="fkcorreios_embalagem" type="radio" <?php echo (Configuration::get('FKCORREIOS_EMBALAGEM') == '0' ? 'checked="checked" ' : '');?>> <?php echo $this->l('Utilizar embalagem individual')?>
        </div>

        <div class="fkcorreios_divisao">
            <div><?php echo $this->l('Bloco de informações');?></div>
        </div>

        <div class="fkcorreios_form_group">
            <label></label>
            <input type="checkbox" name="fkcorreios_bloco_produto" value="on" <?php echo ((Configuration::get('FKCORREIOS_BLOCO_PRODUTO') == 'on') ? 'checked="checked"' : '');?>/> <?php echo $this->l('Produto')?>
        </div>
        
        <?php
            if (version_compare(substr(_PS_VERSION_, 0, 5), '1.6.0', '>=')) {    
        ?>
            <div class="fkcorreios_form_group">
                <label></label>
                <input style="margin-left: 15px;" type="radio" name="fkcorreios_bloco_posicao" value="0" <?php echo (Configuration::get('FKCORREIOS_BLOCO_POSICAO') == '0' ? 'checked="checked" ' : '')?>> <?php echo $this->l('Após descrição resumida');?>
            </div>
        
            <div class="fkcorreios_form_group">
                <label></label>
                <input style="margin-left: 15px;" type="radio" name="fkcorreios_bloco_posicao" value="1" <?php echo (Configuration::get('FKCORREIOS_BLOCO_POSICAO') == '1' ? 'checked="checked" ' : '')?>> <?php echo $this->l('Após descrição detalhada');?>
            </div>
        <?php
            }    
        ?>
        
        <div class="fkcorreios_form_group">
            <label></label>
            <input type="checkbox" name="fkcorreios_bloco_carrinho" value="on" <?php echo ((Configuration::get('FKCORREIOS_BLOCO_CARRINHO') == 'on') ? 'checked="checked"' : '');?>/> <?php echo $this->l('Carrinho')?>
        </div>

        <div class="fkcorreios_div_button">
            <input class="fkcorreios_button" name="submitSave" type="submit" value="<?php echo $this->l('Salvar');?>">

            <div>
                <input class="fkcorreios_button_warning" name="submitCache" type="submit" value="<?php echo $this->l('Limpar cache');?>" onclick="return fkcorreiosExcluir('<?php echo $this->l('Confirma a exclusão do cache?')?>');">
            </div>
        </div>

	</div>

</form>

</html>
