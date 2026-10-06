<?php

namespace App\Helpers;

class Documento{

    public static function somenteNumeros($valor){
        return preg_replace('/\D/', '', (string) $valor);
    }

    public static function validarCPF($cpf){
        $cpf = self::somenteNumeros($cpf);

        if (strlen($cpf) != 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += $cpf[$i] * (($t + 1) - $i);
            }
            $digito = ((10 * $soma) % 11) % 10;
            if ($cpf[$t] != $digito) {
                return false;
            }
        }

        return true;
    }

    public static function validarCNPJ($cnpj){
        $cnpj = self::somenteNumeros($cnpj);

        if (strlen($cnpj) != 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $pesos = array(6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);
        for ($t = 12; $t < 14; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += $cnpj[$i] * $pesos[$i + (13 - $t)];
            }
            $resto = $soma % 11;
            $digito = $resto < 2 ? 0 : 11 - $resto;
            if ($cnpj[$t] != $digito) {
                return false;
            }
        }

        return true;
    }

    public static function formatarCPF($cpf){
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    public static function formatarCNPJ($cnpj){
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    public static function formatarTelefone($telefone){
        if (strlen($telefone) == 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
        }
        if (strlen($telefone) == 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
        }
        return $telefone;
    }
}
