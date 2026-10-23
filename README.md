# G7 Social

Sistema de gestão das atividades sociais do Grupo Sete de Setembro.

O **gestor** (área administrativa) controla projetos sociais, atividades, voluntários, doadores e arrecadações. A **home** (área pública) tem as páginas de inscrição de voluntários: cada projeto tem um link que lista as atividades com inscrições abertas.

## Funcionalidades

- **Painel**: página inicial após o login, com indicadores (projetos ativos, atividades abertas, voluntários, valor arrecadado, doadores), arrecadação em dinheiro por mês, progresso dos projetos ativos, próximas atividades e últimas arrecadações.
- **Projetos sociais**: listagem com situação (Ativo/Finalizado pela data fim), total de doações e valor arrecadado. Cada projeto tem um link público de inscrição de voluntários (botão "Abrir link" na listagem).
- **Atividades**: várias por projeto, com período, local, horário, vagas e status. Só as atividades ativas e ainda não encerradas aparecem na página pública do projeto.
- **Inscrições**: voluntários inscritos por atividade, pelo gestor ou pela página pública, respeitando o limite de vagas. Na página pública, o voluntário informa primeiro o CPF: se já estiver no banco, confere e atualiza os seus dados; se não, preenche o cadastro.
- **Banco de voluntários**: uma ficha por pessoa (nome, CPF, e-mail, telefone), com listagem, alteração de dados e detalhamento (datas de cadastro e da última atualização e os projetos/atividades em que participou).
- **Doadores**: empresas (CNPJ) e pessoas (CPF), com validação dos documentos e histórico de doações.
- **Arrecadações**: doações em dinheiro ou itens, vinculadas a um projeto e a um doador.
- **Relatórios**: geral (projetos de um período, com resumo, arrecadação por mês, maiores doadores e voluntários mais ativos) e por projeto (atividades, voluntários inscritos, arrecadações e doadores). Podem ser impressos/salvos em PDF pelo navegador e exportados em Excel (.xlsx). Toda exportação fica registrada no log.
- **Usuários**: três perfis de acesso (veja abaixo).
- Layout responsivo (desktop, tablet e celular).

### Perfis de usuário

| Perfil | Acesso |
|---|---|
| `admin` | Acesso a todos os módulos, inclusive cadastrar/alterar projetos sociais e o módulo de usuários (cadastrar, alterar e ativar/desativar). |
| `gestor` | Acesso a tudo (atividades, voluntários, doadores, arrecadações, relatórios), menos cadastrar/alterar projetos sociais e o módulo de usuários. |

## Requisitos

- PHP 8.1 ou superior, com as extensões `pdo_mysql`, `curl`, `zip`, `gd`, `mbstring` e `xml` (as quatro últimas são usadas pela exportação em Excel, via PhpSpreadsheet)
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
- [ ] Ativar CAPTCHA também no formulário público de inscrição (`home/views/inscricao.php`), que hoje tem apenas um campo anti-robô.

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
  views/projeto.php     Página pública do projeto: lista as atividades com inscrições abertas
  views/inscricao.php   Formulário de inscrição em uma atividade
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

As tabelas de requisitos estão em [`docs/requisitos/`](docs/requisitos/):

- [Requisitos funcionais](docs/requisitos/tabela_requisitos_funcionais.png)
- [Requisitos não funcionais](docs/requisitos/tabela_requisitos_nao_funcionais.png)
- [Versão em texto](docs/requisitos/requisitos.md), para copiar para o documento

O script `gerar_tabelas.php` recria os HTMLs e o texto; os PNGs são capturas dos HTMLs.

As capturas de todas as telas do sistema (gestor e páginas públicas, com dados de exemplo) estão em [`docs/telas/`](docs/telas/), numeradas na ordem de navegação.

---

GTEC — Departamento de Desenvolvimento de Software · Grupo Sete de Setembro
