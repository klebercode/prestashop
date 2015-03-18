<html>

	<form action="<?php echo Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI'])?>&id_tab=6&section=configServicosCorreios" method="post" class="form" id="configServicosCorreios">
	
		<?php 
		// Selecao dos servicos dos Correios disponiveis
		$sql_servicos = 'SELECT `id`, `servico`
		                 FROM `'._DB_PREFIX_.'fkcorreios_especificacoes_correios`
						 WHERE   `id_shop` = '.$this->context->shop->id.' AND
						         `id` NOT IN (SELECT `id_correios` FROM `'._DB_PREFIX_.'fkcorreios_correios_transp` WHERE `id_shop` = '.$this->context->shop->id.')';
		
		$servicos_correios = Db::getInstance()->executeS($sql_servicos);
		?>
	
		<div class="fkcorreios_opcoes">
	
			<?php 
			if ($servicos_correios) {
			?>
			
			<input id="fkcorreios_submit_add" name="submitAdd" type="submit" value="">
			<p>Adicionar Serviços Selecionados</p>
			
			<?php 
			}
			?>
			
			<input id="fkcorreios_button_ajuda" name="fkcorreios_button_ajuda" type="button" value="" onClick="window.open('http://www.fokusfirst.com/fokusfirst/ajuda/fkcorreios_2014_lite.pdf','Janela','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=900,height=500,left=500,top=150'); return false;">
			<p>Ajuda</p>
			
		</div>

        <?php
        if ($servicos_correios) {
        ?>
		
		<div class="fkcorreios_margin_form" id="fkcorreios_servicos_correios">

            <p><?php echo $this->l('Serviços disponíveis:');?></p>

            <?php
            // Recupera os servicos ainda nao selecionados
            foreach ($servicos_correios as $reg_servicos_correios) {
            ?>
                <input type="checkbox" name="fkcorreios_servicos_correios[]" value="<?php echo $reg_servicos_correios['id']?>"><?php echo ' '.$reg_servicos_correios['servico']?>
            <?php
            }
            ?>

		</div>

        <?php
        }
        ?>

	</form>

	<?php
	// Recupera os servicos dos Correios disponibilizados 
	$sql_correios_transp = 'SELECT `id`, `id_carrier`, `nome_carrier`, `grade`, `ativo` FROM `'._DB_PREFIX_.'fkcorreios_correios_transp` WHERE `id_correios` > 0 AND `id_shop` = '.$this->context->shop->id.' ORDER BY `nome_carrier`';
	$correios_transp = Db::getInstance()->executeS($sql_correios_transp);
	
	foreach($correios_transp as $reg_correios_transp) {
	?>
	
	<form action="<?php echo Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI'])?>&id_tab=6&section=configServicosCorreios&id_correios_transp=<?php echo $reg_correios_transp['id']?>" method="post" class="form" id="configServicosCorreios" enctype="multipart/form-data">

		<div class="fkcorreios_margin_form" id="fkcorreios_correios_selecionados">
		
            <div class="fkcorreios_toggle_titulo" onclick="fkcorreiosToggle('fkcorreios_correios_selecionados_itens_<?php echo $reg_correios_transp['id'];?>')">
                <?php echo $reg_correios_transp['nome_carrier'];?>
            </div>

            <div class="fkcorreios_toggle_itens" id="fkcorreios_correios_selecionados_itens_<?php echo $reg_correios_transp['id'];?>">

                <div class="fkcorreios_form_group">
                    <label>Serviço ativo:</label>
                    <input type="checkbox" name="fkcorreios_correios_ativo_<?php echo $reg_correios_transp['id'];?>" value="on" <?php echo ($reg_correios_transp['ativo'] == 1 ? 'checked="checked"' : '');?>/>
                </div>

                <div class="fkcorreios_form_group">
                    <label>Grade velocidade:</label>
                    <input type="number" name="fkcorreios_correios_grade_<?php echo $reg_correios_transp['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_correios_grade_'.$reg_correios_transp['id']) ? $reg_correios_transp['grade'] : Tools::getValue('fkcorreios_correios_grade_'.$reg_correios_transp['id']));?>"/>
                </div>

                <label>Logo:</label>

                <div class="fkcorreios_img" id="fkcorreios_correios_img">
                    <?php
                        $path_logo = Tools::getShopDomainSsl(true, true)._PS_IMG_.'s/'.$reg_correios_transp['id_carrier'].'.jpg';

                        if (!file_exists(_PS_IMG_DIR_.'s/'.$reg_correios_transp['id_carrier'].'.jpg')) {
                            $path_logo = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/fkcorreioslite/upload/'.'no_image.jpg';
                        }
                    ?>

                    <div class="fkcorreios_form_group">
                        <img id="fkcorreios_logo_correios_<?php echo $reg_correios_transp['id_carrier'];?>" alt="Logo serviço" src="<?php echo $path_logo;?>">
                    </div>
                    <div class="fkcorreios_form_group">
                        <input type="file" name="fkcorreios_correios_imagem_<?php echo $reg_correios_transp['id'];?>">
                    </div>

                    <script type="text/javascript">
                        d = new Date();
                        $("#fkcorreios_logo_correios_<?php echo $reg_correios_transp['id_carrier'];?>").attr("src", "<?php echo $path_logo;?>?" + d.getTime());
                    </script>
                </div>

                <div class="fkcorreios_div_button">
                    <input class="fkcorreios_button" name="submitSave" type="submit" value="<?php echo $this->l('Salvar');?>">

                    <div>
                        <input class="fkcorreios_button_warning" name="submitDel" type="submit" value="<?php echo $this->l('Excluir serviço');?>" onclick="return fkcorreiosExcluir('<?php echo $this->l('Confirma a exclusão do serviço?')?>');">
                    </div>
                </div>

            </div>
		</div>
			
	</form>

	<?php 
	}
	?>

</html>