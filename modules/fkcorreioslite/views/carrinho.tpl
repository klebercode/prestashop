
{if $fkcorreios_foco == true}
    <a name="fkcorreios-foco"></a>
    <a href="#fkcorreios-foco" id="fkcorreios-foco"></a>
    
    <script type="text/javascript">
        $(document).ready(function(){
            document.getElementById("fkcorreios-foco").click();
        });
    </script>
{/if}

<div class="fkcorreios-box">

    <div class="fkcorreios-legenda">
    
        <p>{l s='Informe o CEP para cálculo do frete do produto' mod='fkcorreios'}</p>
    
        <div class="fkcorreios-form">
            <form action="#" method="post">
                <input type="text" class="fkcorreios_cep_mask" size="10" name="fkcorreios_cep_carrinho" value="{$fkcorreios_cep}"/>
                <input type="submit" class="fkcorreios-button" value="{l s='Calcular frete' mod='fkcorreios'}" name="submitCarrinho"/>
            </form>
        </div>

    </div>
    
    <div class="fkcorreios-transportadoras">
    
        <p>{$fkcorreios_msg}</p>
    
        {if isset($fkcorreios_frete)}
            <table>
                {foreach $fkcorreios_frete as $frete}
                    <tr>
                        <td id="fkcorreios-img">
                            <img src="{$frete['url_imagem']}" alt="{$frete['nome_carrier']}"/>
                        </td>
                        <td id="fkcorreios-nome">
                            <b>{$frete['nome_carrier']}</b>
                            <br>
                            {l s='Entrega:' mod='fkcorreios'} {$frete['prazo_entrega']}
                        </td>
                        <td id="fkcorreios-valor">
                            {convertPrice price=$frete['valor_frete']}
                        </td>
                    </tr>
                {/foreach}
            </table>
        {/if}
    
    </div>
    
</div>
