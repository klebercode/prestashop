{*
 * Adaptado por PRESTABR <http://prestabr.com.br> 
 *}

 {capture name=path}{l s='Pagamento por Boleto Bancário' mod='boletobancario'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2 style="text-align:center;">{l s='Boleto Bancário' mod='boletobancario'|escape:htmlall:'UTF-8'} - {$banco|upper}</h1>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<p align="center">
	<img src="{$imgBtn}" alt="{l s='Boleto Bancário' mod='boletobancario'|escape:htmlall:'UTF-8'}" style="margin: 20px auto;" />
</p>

<div id="boleto">
	<form action="{$link->getModuleLink('boletobancario', 'validation', [], true)}" method="post">
        <input type="hidden" name="currency_payement" value="{$currency->id}">
        <input type="hidden" name="cart_total" value="{$total}">	
        <p>
            <b>{l s='Você escolheu pagar por Boleto Bancário.' mod='boletobancario'|escape:htmlall:'UTF-8'}</b><br /><br />
            {if $desc > 0}
                <b>{l s='Pagando com Boleto Bancário você ganha um desconto de:' mod='boletobancario'|escape:htmlall:'UTF-8'} <span class="price">{$desc}%</span></b>
                <br /><br />
            {/if}
            {l s='O valor total do seu pedido é:' mod='boletobancario'|escape:htmlall:'UTF-8'} <b><span id="amount_{$currency->id}" class="price">{$total}</span></b> {if $desc > 0}<span class="desc">{l s='(com desconto).' mod='boletobancario'|escape:htmlall:'UTF-8'}</span>{/if}<br /><br />
            {l s='Ao confirmar o pedido, você aceita pagar o Boleto Bancário em até:' mod='boletobancario'|escape:htmlall:'UTF-8'} <b><span class="price">{$prazo} {l s='dias.' mod='boletobancario'|escape:htmlall:'UTF-8'}</span></b>
            <br /><br />
        </p>
        <p>
            <b>{l s='Por favor confirme seu pedido clicando em \'Confirmar Compra\'' mod='boletobancario'|escape:htmlall:'UTF-8'}.</b>
        </p>
        <br /><br />
        <p class="cart_navigation">
			<a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Outras formas de pagamento' mod='boletobancario'|escape:htmlall:'UTF-8'}</a>
            <input type="submit" name="submit" value="{l s='Confirmar Compra' mod='boletobancario'|escape:htmlall:'UTF-8'}" class="exclusive_large" />
        </p>
    </form>
</div>
