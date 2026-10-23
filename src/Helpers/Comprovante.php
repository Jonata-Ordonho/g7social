<?php

namespace App\Helpers;

// Arquivo de comprovação de recebimento da arrecadação (nota fiscal, recibo etc.)
class Comprovante{

    const TAMANHO_MAXIMO = 5242880; // 5 MB

    // Tipo real do arquivo (lido do conteúdo, não da extensão) => extensão gravada
    private static $tipos = array(
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/pdf' => 'pdf'
    );

    private static function pasta(){
        return __DIR__.'/../../media/comprovantes/';
    }

    public static function caminho($nome){
        return self::pasta().basename($nome);
    }

    // TRUE se o arquivo informado está gravado na pasta de comprovantes
    public static function existe($nome){
        return $nome != '' && is_file(self::caminho($nome));
    }

    public static function tipo($caminho){
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $tipo = $finfo->file($caminho);

        return isset(self::$tipos[$tipo]) ? $tipo : NULL;
    }

    public static function foiEnviado($arquivo){
        return isset($arquivo['error']) && $arquivo['error'] != UPLOAD_ERR_NO_FILE;
    }

    // Retorna a mensagem de erro, ou '' se o arquivo é válido
    public static function validar($arquivo){
        if($arquivo['error'] == UPLOAD_ERR_INI_SIZE || $arquivo['error'] == UPLOAD_ERR_FORM_SIZE || $arquivo['size'] > self::TAMANHO_MAXIMO){
            return 'O comprovante deve ter no máximo 5 MB.';
        }
        if($arquivo['error'] != UPLOAD_ERR_OK || !is_uploaded_file($arquivo['tmp_name'])){
            return 'Não foi possível enviar o comprovante. Tente novamente.';
        }
        if(self::tipo($arquivo['tmp_name']) == NULL){
            return 'O comprovante deve ser um arquivo JPG, PNG ou PDF.';
        }

        return '';
    }

    // Grava o arquivo com nome aleatório e retorna esse nome, ou NULL se falhar
    public static function salvar($arquivo){
        if(!is_dir(self::pasta())){
            mkdir(self::pasta(), 0755, true);
        }

        $nome = bin2hex(random_bytes(16)).'.'.self::$tipos[self::tipo($arquivo['tmp_name'])];

        return move_uploaded_file($arquivo['tmp_name'], self::caminho($nome)) ? $nome : NULL;
    }

    public static function excluir($nome){
        if(self::existe($nome)){
            unlink(self::caminho($nome));
        }
    }
}
