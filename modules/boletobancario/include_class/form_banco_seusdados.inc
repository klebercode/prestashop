<?php

/**
 * @author Dlani
 * @copyright 2008
 */

$this->_html .= '
		<br>
		<fieldset>
			<legend><img src="../img/admin/nav-user.gif" />Seus Dados</legend>
			
			<label>Identifica&ccedil;&atilde;o</label>			
			<div class="margin-form">
			<input type="text" size="33" name="identificacao" value="'.$identificacao.'" /> Ex.: BoletoPhp - C&oacute;digo Aberto de Sistema de Boletos</div>

			
			<label>CPF ou CNPJ</label>			
			<div class="margin-form">
			<input type="text" size="33" name="cpf_cnpj" value="'.$cpf_cnpj.'" /> Ex.: 00000000000</div>

			
			<label>Endere&ccedil;o</label>			
			<div class="margin-form">
			<input type="text" size="33" name="endereco" value="'.$endereco_seu.'" /> Coloque o Endere&ccedil;o aqui</div>

			
			<label>Cidade e Estado</label>			
			<div class="margin-form">
			<input type="text" size="33" name="cidade_uf" value="'.$cidade_uf.'" /> Cidade / Estado</div>

			
			<label>Cedente</label>			
			<div class="margin-form">
			<input type="text" size="33" name="cedente" value="'.$cedente.'" /> Coloque a Raz&atilde;o Social da sua empresa aqui</div>

					
			<br />
			
		<center><input type="submit" name="submitBoletoBancario" value="Atualizar" class="button" /></center>
		</fieldset>';

?>