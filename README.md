# G7 Social

Sistema de gestão das atividades sociais do Grupo Sete de Setembro.

O **gestor** (área administrativa) controla projetos sociais, atividades, voluntários, doadores e arrecadações. A **home** (área pública) tem a página de inscrição de voluntários, acessada pelo link de cada atividade.

## Funcionalidades

- **Projetos sociais**: listagem com situação (Ativo/Finalizado pela data fim), total de doações e valor arrecadado.
- **Atividades**: várias por projeto, com período, local, horário, vagas e status (ativa/desativada). Cada atividade tem um link público de inscrição.
- **Voluntários**: inscritos por atividade, pelo gestor ou pela página pública, respeitando o limite de vagas.
- **Doadores**: empresas (CNPJ) e pessoas (CPF), com validação dos documentos e histórico de doações.
- **Arrecadações**: doações em dinheiro ou itens, vinculadas a um projeto e a um doador.
- **Usuários**: três perfis de acesso (veja abaixo).
- Layout responsivo (desktop, tablet e celular).

### Perfis de usuário

| Perfil | Acesso |
|---|---|
| `admin_master` | Conta de desenvolvimento. Acesso total, inclusive alterar dados e status de usuários. |
| `admin` | Tudo do gestor + listar usuários e cadastrar novos gestores. Não altera usuários. |
| `gestor` | Projetos, atividades, voluntários, doadores e arrecadações. |

## Requisitos

- PHP 8.0 ou superior, com as extensões `pdo_mysql` e `curl`
- MySQL 8 ou MariaDB 10.4+
- Apache (o desenvolvimento foi feito no WampServer)
- [Composer](https://getcomposer.org/)

## Instalação

1. **Clone o repositório** dentro da pasta servida pelo Apache (ou crie um alias apontando para ela):

   ```bash
   git clone https://github.com/Jonata-Ordonho/g7social.git
   ```

2. **Instale as dependências** (gera a pasta `vendor/` com o autoload):

   ```bash
   composer install
   ```

3. **Crie o banco de dados** e importe a estrutura:

   ```bash
   mysql -u root -p -e "CREATE DATABASE g7social CHARACTER SET utf8"
   mysql -u root -p g7social < database/g7social.sql
   ```

4. **Configure a conexão**: copie `connections/Connections_structure.txt` para `connections/Connections.php` e preencha os dados do banco e as chaves do [Cloudflare Turnstile](https://developers.cloudflare.com/turnstile/) (CAPTCHA do login).

   O arquivo `Connections.php` contém senhas e **não é versionado** (está no `.gitignore`).

5. **Acesse o gestor** em `http://localhost/g7social/gestor/` com o usuário inicial:

   - E-mail: `admin@g7social.local`
   - Senha: `admin`

   **Troque a senha no primeiro acesso** (menu do usuário → Alterar dados pessoais).

## Antes de publicar em produção

- [ ] Reativar o CAPTCHA do login em `gestor/index.php` (está comentado para o desenvolvimento local, marcado com `TODO`).
- [ ] Trocar a senha do usuário inicial.
- [ ] Ativar CAPTCHA também na página pública de inscrição (`home/views/inscricao.php`), que hoje tem apenas um campo anti-robô.
- [ ] Trocar os logos em `gestor/images/` e `home/images/` (ainda são os do C7 Atividades / Colégio Sete).

## Estrutura

```
connections/       Conexão com o banco (Connections.php não versionado)
database/          g7social.sql (estrutura do banco e usuário inicial)
docs/diagramas/    Diagramas de casos de uso e de entidade-relacionamento
gestor/            Área administrativa
  css/  images/
  includes/        Topo, menu, rodapé, verificações de login/permissão, formulários compartilhados
  views/           Telas do gestor
home/              Área pública
  views/inscricao.php   Página de inscrição de voluntários
js/                jQuery, máscara e validação de formulários
src/
  Controllers/     Acesso ao banco (PDO)
  Models/          Entidades
  Helpers/         Documento (CPF/CNPJ/telefone) e Permissao (perfis de usuário)
```

## Documentação

Os diagramas estão em [`docs/diagramas/`](docs/diagramas/):

- [Diagrama de casos de uso](docs/diagramas/casos_de_uso.png)
- [Diagrama de entidade-relacionamento](docs/diagramas/der.png)

Os scripts `gerar_*.php` da mesma pasta recriam os SVGs quando o sistema mudar.

---

GTEC — Departamento de Desenvolvimento de Software · Grupo Sete de Setembro
