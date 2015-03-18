<p class="payment_module">
	<a href="javascript:$('#boletobancario').submit();" target="_self" title="{l s='Gerar Boleto' mod='boletobancario'}">
		<img src="{$imgBtn}" alt="{l s='Gerar Boleto' mod='boletobancario'}" />
		{l s='Gerar Boleto' mod='boletobancario'}
	</a>
</p>

<form target="_self" action="modules/boletobancario/gera_boleto.php" method="post" name="boletobancario" id="boletobancario" />

<input type="hidden" name="banco" value="{$banco}" />
<input type="hidden" name="nosso_numero" value="{$nosso_numero}" />
<input type="hidden" name="numero_documento" value="{$numero_documento}" />
<input type="hidden" name="data_vencimento" value="{$data_vencimento}" />
<input type="hidden" name="data_documento" value="{$data_documento}" />
<input type="hidden" name="data_processamento" value="{$data_processamento}" />
<input type="hidden" name="valor_boleto" value="{$valor_boleto}" />
<input type="hidden" name="nosso_numero" value="{$nosso_numero}" />
<input type="hidden" name="numero_documento" value="{$numero_documento}" />

<input type="hidden" name="identificacao" value="{$identificacao}" />
<input type="hidden" name="cpf_cnpj" value="{$cpf_cnpj}" />
<input type="hidden" name="endereco" value="{$endereco_seu}" />
<input type="hidden" name="cidade_uf" value="{$cidade_uf}" />
<input type="hidden" name="cedente" value="{$cedente}" />

<input type="hidden" name="sacado" value="{$sacado}" />
<input type="hidden" name="endereco1" value="{$endereco1}" />
<input type="hidden" name="endereco2" value="{$endereco2}" />

{$dadosdoboleto}

</form>