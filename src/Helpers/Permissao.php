<?php

namespace App\Helpers;

// Regras de acesso por tipo de usuário:
// - admin: acesso a todos os módulos
// - gestor: acesso a tudo, menos cadastrar/alterar projetos sociais e o módulo de usuários
class Permissao{

    // Tipos de usuário e seus nomes de exibição
    const TIPOS = array(
        'admin' => 'Administrador',
        'gestor' => 'Gestor'
    );

    public static function nomeTipo($tipo){
        return isset(self::TIPOS[$tipo]) ? self::TIPOS[$tipo] : $tipo;
    }

    private static function tipoValido($tipoLogado){
        return isset(self::TIPOS[$tipoLogado]);
    }

    // Somente o admin cadastra e altera projetos sociais
    public static function podeGerenciarProjetos($tipoLogado){
        return $tipoLogado == 'admin';
    }

    // Admin e gestor cadastram, alteram e ativam/desativam atividades
    public static function podeGerenciarAtividades($tipoLogado){
        return self::tipoValido($tipoLogado);
    }

    // Admin e gestor inscrevem voluntários pelo gestor e alteram os dados do banco de voluntários
    public static function podeGerenciarVoluntarios($tipoLogado){
        return self::tipoValido($tipoLogado);
    }

    // Admin e gestor excluem inscrições de voluntários
    public static function podeExcluirVoluntario($tipoLogado){
        return self::tipoValido($tipoLogado);
    }

    // Admin e gestor excluem arrecadações
    public static function podeExcluirArrecadacao($tipoLogado){
        return self::tipoValido($tipoLogado);
    }

    // Somente o admin acessa o módulo de usuários (listar, cadastrar e alterar)
    public static function podeGerenciarUsuarios($tipoLogado){
        return $tipoLogado == 'admin';
    }

    // Tipos que o usuário logado pode atribuir (o admin atribui qualquer tipo)
    public static function tiposPermitidos($tipoLogado){
        return $tipoLogado == 'admin' ? self::TIPOS : array();
    }

    // Somente o admin altera dados de usuários
    public static function podeAlterarUsuario($tipoLogado){
        return $tipoLogado == 'admin';
    }

    // O admin altera (e ativa/desativa) gestores e a própria conta, mas não outros admins
    public static function podeAlterarEsteUsuario($tipoLogado, $idLogado, $usuarioAlvo){
        if (!self::podeAlterarUsuario($tipoLogado)) {
            return false;
        }
        return $usuarioAlvo['tipo'] != 'admin' || (int) $usuarioAlvo['id_usuario'] == (int) $idLogado;
    }
}
