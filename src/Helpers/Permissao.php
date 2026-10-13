<?php

namespace App\Helpers;

class Permissao{

    // Tipos de usuário e seus nomes de exibição
    const TIPOS = array(
        'admin_master' => 'Admin master (DEV)',
        'admin' => 'Administrador',
        'gestor' => 'Gestor'
    );

    public static function nomeTipo($tipo){
        return isset(self::TIPOS[$tipo]) ? self::TIPOS[$tipo] : $tipo;
    }

    // Somente admin master e admin cadastram e alteram projetos sociais
    public static function podeGerenciarProjetos($tipoLogado){
        return in_array($tipoLogado, array('admin_master', 'admin'));
    }

    // Somente admin master e admin excluem inscrições de voluntários
    public static function podeExcluirVoluntario($tipoLogado){
        return in_array($tipoLogado, array('admin_master', 'admin'));
    }

    // Somente admin master e admin cadastram, alteram e ativam/desativam atividades
    public static function podeGerenciarAtividades($tipoLogado){
        return in_array($tipoLogado, array('admin_master', 'admin'));
    }

    // Somente admin master e admin inscrevem voluntários pelo gestor e alteram os dados do banco de voluntários
    public static function podeGerenciarVoluntarios($tipoLogado){
        return in_array($tipoLogado, array('admin_master', 'admin'));
    }

    // Admin master e admin veem o bloco de usuários e podem cadastrar
    public static function podeGerenciarUsuarios($tipoLogado){
        return in_array($tipoLogado, array('admin_master', 'admin'));
    }

    // Tipos que o usuário logado pode atribuir: admin master atribui qualquer tipo; admin cadastra apenas gestores
    public static function tiposPermitidos($tipoLogado){
        if ($tipoLogado == 'admin_master') {
            return self::TIPOS;
        }
        return array('gestor' => self::TIPOS['gestor']);
    }

    // Somente o admin master altera dados de usuários
    public static function podeAlterarUsuario($tipoLogado){
        return $tipoLogado == 'admin_master';
    }
}
