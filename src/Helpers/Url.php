<?php

namespace App\Helpers;

class Url{

    // Endereço completo de uma página da área pública (home/views), a partir de uma página do gestor
    public static function paginaPublica($pagina){
        // SCRIPT_NAME: /<raiz>/gestor/views/arquivo.php -> raiz do sistema
        $raizSistema = rtrim(str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/');
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';

        return $protocolo.'://'.$_SERVER['HTTP_HOST'].$raizSistema.'/home/views/'.$pagina;
    }
}
