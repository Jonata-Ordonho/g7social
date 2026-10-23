<?php
// Gera as tabelas de requisitos funcionais e não funcionais do G7 Social
// Saída: tabela_requisitos_funcionais.html, tabela_requisitos_nao_funcionais.html e requisitos.md (nesta pasta)
// Os PNGs são capturas dos HTMLs (ex.: Edge headless com --screenshot)

$funcionais = array(
    array('RF001', 'Realizar Login'),
    array('RF002', 'Manter Usuários'),
    array('RF003', 'Manter Projeto Social'),
    array('RF004', 'Manter Atividade'),
    array('RF005', 'Ativar/Desativar Atividade'),
    array('RF006', 'Manter Voluntário'),
    array('RF007', 'Manter Inscrição de Voluntário'),
    array('RF008', 'Realizar Inscrição Pública'),
    array('RF009', 'Manter Doador'),
    array('RF010', 'Manter Arrecadação'),
    array('RF011', 'Visualizar Painel de Indicadores'),
    array('RF012', 'Gerar Relatório Geral'),
    array('RF013', 'Gerar Relatório por Projeto'),
    array('RF014', 'Exportar Relatórios'),
    array('RF015', 'Alterar Dados Pessoais'),
    array('RF016', 'Registrar Log de Ações'),
);

$naoFuncionais = array(
    array('RNF001', 'Sistema web acessado pelo navegador'),
    array('RNF002', 'Desenvolvido em PHP com banco de dados MySQL'),
    array('RNF003', 'Interface responsiva (computador, tablet e celular)'),
    array('RNF004', 'Controle de acesso por perfil (administrador e gestor)'),
    array('RNF005', 'Autenticação por e-mail e senha'),
    array('RNF006', 'Validação de CPF e CNPJ nos cadastros'),
    array('RNF007', 'Página pública de inscrição sem necessidade de login'),
    array('RNF008', 'Registro das ações dos usuários em log'),
    array('RNF009', 'Exportação de relatórios em Excel (.xlsx) e PDF'),
    array('RNF010', 'CPF dos voluntários mascarado nos relatórios'),
    array('RNF011', 'Consultas ao banco parametrizadas (proteção contra SQL Injection)'),
    array('RNF012', 'Interface em língua portuguesa'),
);

function esc($t){
    return htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
}

function tabelaHtml($titulo, $linhas, $larguraDescricao){
    $html = '<!DOCTYPE html><html lang="pt-br"><head><meta charset="utf-8"><title>' . esc($titulo) . '</title><style>
        body { margin: 0; background: #fff; font-family: "Times New Roman", Times, serif; color: #000; }
        .quadro { display: inline-block; padding: 24px 36px 30px; }
        .titulo { text-align: center; font-size: 19px; margin-bottom: 18px; }
        table { border-collapse: collapse; }
        th, td { border: 1.5px solid #000; text-align: center; font-size: 19px; padding: 6px 16px; line-height: 1.3; }
        th { font-weight: bold; height: 52px; }
        td { height: 52px; }
        .id { width: 130px; }
        .descricao { width: ' . $larguraDescricao . 'px; }
    </style></head><body><div class="quadro">';
    $html .= '<div class="titulo">' . esc($titulo) . '</div>';
    $html .= '<table><tr><th class="id">Identificador</th><th class="descricao">Descrição</th></tr>';
    foreach ($linhas as $l) {
        $html .= '<tr><td>' . esc($l[0]) . '</td><td>' . esc($l[1]) . '</td></tr>';
    }
    $html .= '</table></div></body></html>';
    return $html;
}

function tabelaMarkdown($titulo, $linhas){
    $md = "**" . $titulo . "**\n\n| Identificador | Descrição |\n|---|---|\n";
    foreach ($linhas as $l) {
        $md .= '| ' . $l[0] . ' | ' . $l[1] . " |\n";
    }
    return $md;
}

$tituloRF = 'Tabela 01 - Requisitos Funcionais';
$tituloRNF = 'Tabela 02 - Requisitos Não Funcionais';

file_put_contents(__DIR__ . '/tabela_requisitos_funcionais.html', tabelaHtml($tituloRF, $funcionais, 300));
file_put_contents(__DIR__ . '/tabela_requisitos_nao_funcionais.html', tabelaHtml($tituloRNF, $naoFuncionais, 470));
file_put_contents(__DIR__ . '/requisitos.md', "# G7 Social - Requisitos\n\n" . tabelaMarkdown($tituloRF, $funcionais) . "\n" . tabelaMarkdown($tituloRNF, $naoFuncionais));

echo "Gerados: tabela_requisitos_funcionais.html, tabela_requisitos_nao_funcionais.html e requisitos.md\n";
