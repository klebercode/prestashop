    <?php

    /**
    * @author Dlani
    * @copyright 2008
    * @Hack by Carlos Lorenzon http://www.4host.com.br
    */

    $this->_html .= '
          <br>
          <fieldset>
             <legend><img src="../img/admin/money.gif" />Configura&ccedil;&otilde;es do Boleto</legend>
             <br />
             <label>Logotipo do Boleto</label>         
             <div class="margin-form">
             <img src="'.$logo_url.'"><br /><br />
             <input type="text" class="text" name="logotipo" id="logotipo" value="'.$logo_url.'">
             Requisitos: PNG - Altura Max.: 100px Largura Max.: 200px</div>
             <br />         

             <label>Prazo de Vencimento</label>         
             <div class="margin-form">
             <input type="text" size="33" name="prazo" value="'.$prazo.'" /> Ex. 5 </div>
             <br />
             
             <label>Taxa de desconto para Boleto</label>         
             <div class="margin-form">
             <input type="text" size="33" name="taxa" value="'.$taxa.'" /> Ex. 1,5 </div>
             <br />
             
       ';

    foreach ( $dadosboleto as $a => $b ){

    $label = str_replace('_', ' ', ucwords($a));

    $this->_html .=  '   
             <label>'.$label.'</label>         
             <div class="margin-form">
             <input type="text" size="33" name="'.$a.'" value="'.$dados_boleto[$a].'" /> Ex.: '                        .htmlspecialchars( utf8_encode($b)).'</div>
             <br />
       ';
    };

       
             
    $this->_html .= '
          </fieldset>';

    ?>



