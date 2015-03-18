{*
 * Adaptado por PRESTABR <http://prestabr.com.br> 
 *}

{if $order->payment|escape:'htmlall':'UTF-8' eq 'Boleto Bancario' && $order_history.0.id_order_state eq '10'}
<div class="block">
    <h4>{l s='Boleto Pendente' mod='boletobancario'}</h4>
    <br />
    <a href="{$base_dir_ssl}modules/boletobancario/gera_boleto.php?id_order={$order->id}&id_banco={$id_banco}&id_module={$id_module}" target="_blank" class="exclusive">{l s='Gerar 2a. via do boleto' mod='boletobancario'}</a>
</div>    
{/if}
{*<span>{$order_history.0.id_order_state|@print_r}</span>*}





