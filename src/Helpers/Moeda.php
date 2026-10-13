<?php

namespace App\Helpers;

class Moeda{

    // Converte o valor digitado ("15.000,00") para o formato do banco ("15000.00").
    // Retorna NULL quando vazio e FALSE quando o valor é inválido.
    public static function paraDecimal($valor){
        $valor = trim((string) $valor);
        if ($valor === '') {
            return NULL;
        }
        $numero = str_replace(',', '.', str_replace('.', '', $valor));
        if (!is_numeric($numero)) {
            return FALSE;
        }
        return number_format((float) $numero, 2, '.', '');
    }

    // Formata um valor do banco para exibição no campo ("15000.00" -> "15.000,00")
    public static function paraCampo($valor){
        if ($valor === NULL || $valor === '') {
            return '';
        }
        return number_format((float) $valor, 2, ',', '.');
    }

    // Valor em reais para exibição ("R$ 15.000,00")
    public static function formatar($valor){
        return 'R$ '.number_format((float) $valor, 2, ',', '.');
    }

    // Valor curto para rótulos de gráfico ("R$ 800", "R$ 2,1 mil", "R$ 1,5 mi")
    public static function compacto($valor){
        $valor = (float) $valor;
        if ($valor >= 1000000) {
            return 'R$ '.rtrim(rtrim(number_format($valor / 1000000, 1, ',', '.'), '0'), ',').' mi';
        }
        if ($valor >= 1000) {
            return 'R$ '.rtrim(rtrim(number_format($valor / 1000, 1, ',', '.'), '0'), ',').' mil';
        }
        return 'R$ '.number_format($valor, 0, ',', '.');
    }
}
