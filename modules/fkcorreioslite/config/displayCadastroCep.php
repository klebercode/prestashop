<html>

<form action="<?php echo Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI'])?>&id_tab=3&section=configCadastroCep" method="post" class="form" id="configCadastroCep">

	<div class="fkcorreios_opcoes">
		<input id="fkcorreios_button_ajuda" name="fkcorreios_button_ajuda" type="button" value="" onClick="window.open('http://www.fokusfirst.com/fokusfirst/ajuda/fkcorreios_2014_lite.pdf','Janela','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=900,height=500,left=500,top=150'); return false;">
		<p>Ajuda</p>
	</div>
	
	<div class="fkcorreios_margin_form"  id="fkcorreios_cadastro_cep">
        <table>
        	<tr>
            	<th><?php echo $this->l('Estado');?></th>
                <th><?php echo $this->l('Intervalo de CEP dos Estados')?></th>
                <th><?php echo $this->l('Intervalo de CEP das Capitais')?></th>
                <th><?php echo $this->l('CEP base - Capital')?></th>
                <th><?php echo $this->l('CEP base - Interior')?></th>
          	</tr>
            
            <?php 
            $estados_capitais = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'fkcorreios_cadastro_cep` ORDER BY estado');
            foreach($estados_capitais as $reg) {
            ?>

            <tr>
                <td><?php echo $reg['estado'];?></td>
                <td>
                    <p>&nbsp;</p>
                    <input type="text" size="45" name="fkcorreios_cep_estado_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_cep_estado_'.$reg['id']) ? $reg['cep_estado'] : Tools::getValue('fkcorreios_cep_estado_'.$reg['id']));?>"/>
                </td>
	    		<td>
                    <p id="fkcorreios_cadastro_cep_capital"><?php echo $reg['capital']?></p>
                    <input type="text" size="45" name="fkcorreios_cep_capital_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_cep_capital_'.$reg['id']) ? $reg['cep_capital'] : Tools::getValue('fkcorreios_cep_capital_'.$reg['id']));?>"/>
                </td>
                <td>
                    <p>&nbsp;</p>
                    <input class="fkcorreios_text_cep" type="text" size="10" name="fkcorreios_cep_base_capital_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_cep_base_capital_'.$reg['id']) ? $reg['cep_base_capital'] : Tools::getValue('fkcorreios_cep_base_capital_'.$reg['id']));?>"/>
                </td>
                <td>
                    <p>&nbsp;</p>
                    <input class="fkcorreios_text_cep" type="text" size="10" name="fkcorreios_cep_base_interior_<?php echo $reg['id'];?>" value="<?php echo (!Tools::getValue('fkcorreios_cep_base_interior_'.$reg['id']) ? $reg['cep_base_interior'] : Tools::getValue('fkcorreios_cep_base_interior_'.$reg['id']));?>"/>
                </td>
			</tr>
             
            <?php  
            }
            ?>
            
    	</table>
    	
    	<div class="fkcorreios_div_button">
			<input class="fkcorreios_button" name="submitSave" type="submit" value="<?php echo $this->l('Salvar');?>">
		</div>
	</div>

</form>

</html>
