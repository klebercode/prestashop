<html>
<form action="<?php echo Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI'])?>&id_tab=1&section=configRegistro_1" method="post" class="form" id="configRegistro_1">

	<div class="fkcorreios_opcoes">
		<input id="fkcorreios_button_ajuda" name="fkcorreios_button_ajuda" type="button" value="" onClick="window.open('http://www.fokusfirst.com/fokusfirst/ajuda/fkcorreios_2014_lite.pdf','Janela','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=900,height=500,left=500,top=150'); return false;">
		<p>Ajuda</p>
	</div>
	
	<div class="fkcorreios_margin_form" id="fkcorreios_registro_1">
	
        <div class="fkcorreios_form_group">
            <label><?php echo $this->l('Referência do pedido:');?></label>
            <input type="text" name="fkcorreios_referencia" value="<?php echo (!Tools::getValue('fkcorreios_referencia') ? Configuration::get('FKCORREIOS_REFERENCIA') : Tools::getValue('fkcorreios_referencia'));?>">
        </div>

        <div class="fkcorreios_form_group">
            <label><?php echo $this->l('Domínio licenciado:');?></label>
            <input disabled type="text" name="fkcorreios_proprietario" size="40" maxlength="100" value="<?php echo Tools::getShopDomain(false,true)?>">
        </div>

        <div class="fkcorreios_form_group">
            <label><?php echo $this->l('Proprietário do domínio:');?></label>
            <input type="text" name="fkcorreios_proprietario" size="40" maxlength="100" value="<?php echo (!Tools::getValue('fkcorreios_proprietario') ? Configuration::get('FKCORREIOS_PROPRIETARIO') : Tools::getValue('fkcorreios_proprietario'));?>">
        </div>
        
        <div id="fkcorreios_aceite">
            <h4>Prezado cliente</h4>
            Primeiramente agradecemos por utilizar nossos produtos.
            <br><br>
            Teremos o prazer em tirar suas dúvidas e fornecer-lhe o suporte necessário.
            <br>
            No entanto, no caso de módulos Free, isto será feito apenas através do fórum Prestashop.
            <br>
            Para o FKcorreios 2014 Lite, você deve descrever suas dúvidas e observações neste endereço:
            <a href="http://www.prestashop.com/forums/topic/356725-fkcorreios-2014-lite-free/" target="_blank">http://www.prestashop.com/forums/topic/356725-fkcorreios-2014-lite-free/</a>
            <br><br>
            Seu questionamento será automaticamente direcionado para o setor de suporte e desenvolvimento, e em seguida respondido no próprio fórum.
            Perguntas feitas através de qualquer outro canal ou e-mail não serão respondidas.
            <br><br>
            <input type="checkbox" name="fkcorreios_aceite" value="on" /> Entendi e concordo.
        </div>

        <div class="fkcorreios_div_button">
            <input class="fkcorreios_button" name="submitSave" type="submit" value="<?php echo $this->l('Enviar');?>">
        </div>

	</div>

</form>
</html>