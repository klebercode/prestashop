<?php

class FKcorreiosLiteFuncoes {

    public function retornaUF($cep) {

        $cep = preg_replace("/[^0-9]/", "", $cep);

        // Consulta cadastro de cep
        $cadastro_cep = Db::getInstance()->executeS('SELECT `estado`, `cep_estado` FROM `'._DB_PREFIX_.'fkcorreios_cadastro_cep`');

        $localizado = false;

        foreach ($cadastro_cep as $reg) {

            $cepArray = explode('/', $reg['cep_estado']);

            foreach ($cepArray as $intervalo_cep) {

                if ($intervalo_cep == '') {
                    continue;
                }

                if ($cep >= substr($intervalo_cep, 0, 8) And $cep <= substr($intervalo_cep, 9, 8)) {
                    $localizado = true;
                    break;
                }
            }

            if ($localizado == true){
                return $reg['estado'];
                break;
            }
        }

        return 'erro';
    }

    public function verificaUfCapital($cep_pesquisa) {

        $cadastro_cep = Db::getInstance()->executeS('SELECT `cep_capital` FROM '._DB_PREFIX_.'fkcorreios_cadastro_cep');

        foreach ($cadastro_cep as $reg) {

            $cep_capital = explode('/', $reg['cep_capital']);

            foreach ($cep_capital as $cep) {
                if ($cep == '') {
                    continue;
                }

                if ($cep_pesquisa >= substr($cep, 0, 8) And $cep_pesquisa <= substr($cep, 9, 8)) {
                    return true;
                }
            }
        }

        return false;
    }

}

