<p class="payment_module">
	<a href="{$link->getModuleLink('boletobancario', 'payment')}" title="{l s='Pagamento no Boleto Banc&aacute;rio'}">
		<img src="{$base_dir_ssl}{$imgBtn}" alt="{l s='Pagamento no Boleto Banc&aacute;rio'}" />
		{l s='Pague no Boleto Bancário' mod='boletobancario'} {if $desc > 0}{l s='Com' mod='boletobancario'} {$desc}% {l s='de desconto' mod='boletobancario'}{/if}
	</a>
</p>

