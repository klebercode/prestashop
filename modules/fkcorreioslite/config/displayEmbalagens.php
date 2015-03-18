<html>

<form action="<?php echo Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI'])?>&id_tab=4&section=configEmbalagens" method="post" class="form" id="configEmbalagens">

    <div class="fkcorreios_opcoes">
        <input id="fkcorreios_submit_add" name="submitAdd" type="submit" value="">
        <p>Adicionar Nova Embalagem</p>

        <input id="fkcorreios_button_ajuda" name="fkcorreios_button_ajuda" type="button" value="" onClick="window.open('http://www.fokusfirst.com/fokusfirst/ajuda/fkcorreios_2014_lite.pdf','Janela','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=900,height=500,left=500,top=150'); return false;">
        <p>Ajuda</p>
    </div>

    <?php
    $embalagens = Db::getInstance() -> ExecuteS('SELECT * FROM `'._DB_PREFIX_.'fkcorreios_embalagens` Where `id_shop` = '.$this->context->shop->id.' Order By `cubagem`');

    if ($embalagens) {
    ?>

	<div class="fkcorreios_margin_form" id="fkcorreios_embalagens">

		<table>
	        <tr>
		        <th><?php echo $this->l('Descrição')?></th>
		        <th><?php echo $this->l('Comprimento (cm)')?></th>
		        <th><?php echo $this->l('Altura (cm)')?></th>
		        <th><?php echo $this->l('Largura (cm)')?></th>
		        <th><?php echo $this->l('Peso (kg)')?></th>
		        <th><?php echo $this->l('Preço de Custo')?></th>
		        <th><?php echo $this->l('Ativo')?></th>
		        <th><?php echo $this->l('Excluir')?></th>
	        </tr>
		
			<?php 	        
		    foreach ($embalagens as $reg) {
			?>
			        
		    <tr>
				<td><input type="text" size="30" name="fkcorreios_descricao_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_descricao_'.$reg['id']) ? $reg['descricao'] : Tools::getValue('fkcorreios_descricao_'.$reg['id']));?>"/></td>
		        <td><input type="text" size="5" name="fkcorreios_comprimento_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_comprimento_'.$reg['id']) ? $reg['comprimento'] : Tools::getValue('fkcorreios_comprimento_'.$reg['id']));?>"/></td>
			    <td><input type="text" size="5" name="fkcorreios_altura_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_altura_'.$reg['id']) ? $reg['altura'] : Tools::getValue('fkcorreios_altura_'.$reg['id']));?>"/></td>
		        <td><input type="text" size="5" name="fkcorreios_largura_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_largura_'.$reg['id']) ? $reg['largura'] : Tools::getValue('fkcorreios_largura_'.$reg['id']));?>"/></td>
			    <td><input type="text" size="5" name="fkcorreios_peso_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_peso_'.$reg['id']) ? $reg['peso'] : Tools::getValue('fkcorreios_peso_'.$reg['id']));?>"/></td>
		        <td><input type="text" size="5" name="fkcorreios_custo_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_custo_'.$reg['id']) ? $reg['custo'] : Tools::getValue('fkcorreios_custo_'.$reg['id']));?>"/></td>
		        <td><input type="checkbox" name="fkcorreios_ativo[]" value="<?php echo $reg['id'];?>"<?php echo (($reg['ativo'] == 1) ? 'checked="checked"' : '')?>/></td>
		        <td><input type="checkbox" class="fkcorreios_embalagens_excluir" name="fkcorreios_excluir[]" value="<?php echo $reg['id'];?>"/></td>
		    </tr>
			
		    <?php 
		    }
	        ?>
		
		</table>
		
		<div class="fkcorreios_div_button">
			<input class="fkcorreios_button" name="submitSave" type="submit" value="<?php echo $this->l('Salvar');?>">
			
			<div>
				<input class="fkcorreios_button_warning" name="submitDel" type="submit" value="<?php echo $this->l('Excluir embalagens selecionadas');?>" onclick="return fkcorreiosExcluir('<?php echo $this->l('Confirma a exclusão das embalagens?')?>');">
			</div>
		</div>
	</div>

    <?php
    }
    ?>

</form>

</html>
