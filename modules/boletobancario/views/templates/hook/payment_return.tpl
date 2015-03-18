{if $status == 'ok'}
	<p style="font-size:14px;">{l s='Seu pedido na' mod='boletobancario'} <b>{$shop_name|escape:htmlall:'UTF-8'}</b> {l s='foi conclu&iacute;do com sucesso!' mod='boletobancario'}
    </p>
    <p>
	{l s='Por favor efetue o pagamento do Boleto Banc&aacute;rio, no valor de:' mod='boletobancario'} <b><span class="price">{$total_to_pay}</span></b>
	<br /><br />
	{l s='Em até' mod='boletobancario'} <b>{$prazo} {l s='dias.' mod='boletobancario'}</b> {l s=' para que o pedido seja enviado dentro no prazo.'}	
	</p>
	<p>
	{l s='Voc&ecirc; receberá estas informa&ccedil;&otilde;es no seu email.' mod='boletobancario'}
	</p>
	<p>
	<span class="bold">{l s='Ser&aacute; enviado uma c&oacute;pia do seu pedido e um link para o Boleto.' mod='boletobancario'}</span>
	<br /><br />{l s='Em caso de d&uacute;vidas, por favor entre em contato com o nosso' mod='boletobancario'} 
	<a href="{$link->getPageLink('contact', true)}">{l s='Atendimento ao Cliente' mod='boletobancario'}</a>.
	<br />
    </p>
    <div align="center">
    	<form action="{$url}" method="post" target="_blank" name="boletobancario" id="boletobancario">
            <input type="hidden" name="total" value="{$total}">
            <input type="hidden" name="id_cart" value="{$cart}">
            <input type="hidden" name="id_order" value="{$id_order}">
            <input type="hidden" name="id_banco" value="{$id_banco}">
            <input type="hidden" name="id_module" value="{$id_module}">
            <input type="hidden" name="secure_key" value="{$secure_key}">
            <input type="submit" value="Gerar Boleto" class="exclusive">
        </form>
    </div>
	<br />
	<p align="center" style="font-weight:bold; font-size:14px;">
		{l s='Obrigado por comprar conosco!' mod='boletobancario'}
	</p>
    <script type="text/javascript">
		$(document).ready(function(){
			$('#boletobancario').submit();
		}
    </script>

{else}
	<p class="warning">
	{l s='Houve alguma falha no envio do seu pedido. Por Favor entre em contato com o nosso Suporte' mod='boletobancario'} 
	<a href="{$link->getPageLink('contact', true)}">{l s='Atendimento ao Cliente' mod='boletobancario'}</a>.
	</p>
{/if}
