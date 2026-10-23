<?php
// Gera o Diagrama de Entidade e Relacionamento (DER) do G7 Social em SVG (estilo do documento: fundo pontilhado,
// entidades com campos PK/FK e cardinalidades nas pontas). Estrutura conferida com database/g7social.sql.
function esc($t) { return htmlspecialchars($t, ENT_XML1); }

$tituloFigura = 'Figura 3 - Diagrama Entidade e Relacionamento';

// nome => [x, y, campos]  (dentro do quadro)
$entidades = array(
    'acoes' => array(40, 40, array('id_acao: PK', 'descricao')),
    'logs' => array(300, 40, array('id_log: PK', 'id_acao: FK', 'id_usuario: FK', 'descricao', 'data_registro')),
    'usuarios' => array(600, 30, array('id_usuario: PK', 'nome', 'email', 'senha', 'tipo', 'status', 'data_registro')),
    'doadores' => array(110, 330, array('id_doador: PK', 'tipo', 'nome', 'documento', 'email', 'telefone', 'id_usuario: FK', 'data_registro')),
    'projetos' => array(600, 330, array('id_projeto: PK', 'titulo', 'descricao', 'data_inicio', 'data_fim', 'arrecadacao_estimada', 'codigo', 'id_usuario: FK', 'data_registro')),
    'atividades' => array(940, 230, array('id_atividade: PK', 'id_projeto: FK', 'titulo', 'descricao', 'local', 'data_inicio', 'data_fim', 'horario', 'vagas', 'codigo', 'status', 'id_usuario: FK', 'data_registro')),
    'arrecadacoes' => array(340, 600, array('id_arrecadacao: PK', 'id_projeto: FK', 'id_doador: FK', 'tipo', 'valor', 'descricao', 'data_arrecadacao', 'data_registro')),
    'inscricoes' => array(860, 600, array('id_inscricao: PK', 'id_voluntario: FK', 'id_atividade: FK', 'data_inscricao')),
    'voluntarios' => array(1150, 560, array('id_voluntario: PK', 'nome', 'cpf', 'email', 'telefone', 'data_registro', 'data_atualizacao')),
);

// [entidade do lado "um", cardinalidade dela, entidade do lado "muitos", cardinalidade dela]
$relacoes = array(
    array('acoes', '(1, 1)', 'logs', '(0, n)'),
    array('usuarios', '(1, 1)', 'logs', '(0, n)'),
    array('usuarios', '(0, 1)', 'projetos', '(0, n)'),
    array('usuarios', '(0, 1)', 'atividades', '(0, n)'),
    array('usuarios', '(0, 1)', 'doadores', '(0, n)'),
    array('projetos', '(1, 1)', 'atividades', '(0, n)'),
    array('projetos', '(1, 1)', 'arrecadacoes', '(0, n)'),
    array('doadores', '(1, 1)', 'arrecadacoes', '(0, n)'),
    array('atividades', '(1, 1)', 'inscricoes', '(0, n)'),
    array('voluntarios', '(1, 1)', 'inscricoes', '(0, n)'),
);

// Medidas
$LARG = 165; $CAB = 24; $LIN = 15; $RODAPE = 14;
$quadroX = 30; $quadroY = 50; $quadroW = 1330; $quadroH = 820;
$W = $quadroX * 2 + $quadroW;
$H = $quadroY + $quadroH + 50;
$fonte = 'Segoe UI, Arial, sans-serif';

// Caixa de cada entidade em coordenadas absolutas
$caixas = array();
foreach ($entidades as $nome => $e) {
    $h = $CAB + count($e[2]) * $LIN + 8 + $RODAPE;
    $caixas[$nome] = array('x' => $quadroX + $e[0], 'y' => $quadroY + $e[1], 'w' => $LARG, 'h' => $h);
}

// Ponto em que a reta entre os centros das caixas cruza a borda da caixa $a
function borda($a, $b){
    $cx = $a['x'] + $a['w'] / 2; $cy = $a['y'] + $a['h'] / 2;
    $dx = ($b['x'] + $b['w'] / 2) - $cx; $dy = ($b['y'] + $b['h'] / 2) - $cy;
    $tx = $dx != 0 ? ($a['w'] / 2) / abs($dx) : INF;
    $ty = $dy != 0 ? ($a['h'] / 2) / abs($dy) : INF;
    $t = min($tx, $ty);
    return array($cx + $dx * $t, $cy + $dy * $t);
}

$svg = array();
$svg[] = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$W.'" height="'.$H.'" viewBox="0 0 '.$W.' '.$H.'">';
$svg[] = '<defs><pattern id="pontos" width="14" height="14" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="0.9" fill="#c8c8c8"/></pattern></defs>';
$svg[] = '<rect width="100%" height="100%" fill="#fff"/>';
$svg[] = '<text x="'.($W / 2).'" y="'.($quadroY - 16).'" font-family="Times New Roman, Times, serif" font-size="19" text-anchor="middle" fill="#000">'.esc($tituloFigura).'</text>';
$svg[] = '<rect x="'.$quadroX.'" y="'.$quadroY.'" width="'.$quadroW.'" height="'.$quadroH.'" fill="url(#pontos)" stroke="#222" stroke-width="1"/>';

// Relacionamentos com as cardinalidades perto de cada ponta
$svg[] = '<g font-family="'.$fonte.'" font-size="11" fill="#222">';
foreach ($relacoes as $r) {
    $a = $caixas[$r[0]]; $b = $caixas[$r[2]];
    $pa = borda($a, $b); $pb = borda($b, $a);
    $svg[] = '<line x1="'.round($pa[0], 1).'" y1="'.round($pa[1], 1).'" x2="'.round($pb[0], 1).'" y2="'.round($pb[1], 1).'" stroke="#333" stroke-width="1.2"/>';
    $dx = $pb[0] - $pa[0]; $dy = $pb[1] - $pa[1];
    $len = sqrt($dx * $dx + $dy * $dy);
    $ux = $dx / $len; $uy = $dy / $len;
    // Rótulo afastado da caixa e deslocado para o lado da linha
    foreach (array(array($pa, 1, $r[1]), array($pb, -1, $r[3])) as $ponta) {
        list($p, $sentido, $rotulo) = $ponta;
        $lx = $p[0] + $ux * 26 * $sentido - $uy * 11;
        $ly = $p[1] + $uy * 26 * $sentido + $ux * 11 + 4;
        $svg[] = '<rect x="'.round($lx - 17, 1).'" y="'.round($ly - 11, 1).'" width="34" height="14" fill="#fff" opacity="0.85"/>';
        $svg[] = '<text x="'.round($lx, 1).'" y="'.round($ly, 1).'" text-anchor="middle">'.esc($rotulo).'</text>';
    }
}
$svg[] = '</g>';

// Entidades
foreach ($entidades as $nome => $e) {
    $c = $caixas[$nome];
    $svg[] = '<g font-family="'.$fonte.'">';
    $svg[] = '<rect x="'.$c['x'].'" y="'.$c['y'].'" width="'.$c['w'].'" height="'.$c['h'].'" fill="#fff" stroke="#555" stroke-width="1"/>';
    $svg[] = '<text x="'.($c['x'] + $c['w'] / 2).'" y="'.($c['y'] + 16).'" font-size="12" font-weight="700" text-anchor="middle" fill="#000">'.esc($nome).'</text>';
    $svg[] = '<line x1="'.$c['x'].'" y1="'.($c['y'] + $CAB).'" x2="'.($c['x'] + $c['w']).'" y2="'.($c['y'] + $CAB).'" stroke="#555"/>';
    foreach ($e[2] as $i => $campo) {
        $svg[] = '<text x="'.($c['x'] + 8).'" y="'.($c['y'] + $CAB + 14 + $i * $LIN).'" font-size="11.5" fill="#111">'.esc($campo).'</text>';
    }
    $svg[] = '<line x1="'.$c['x'].'" y1="'.($c['y'] + $c['h'] - $RODAPE).'" x2="'.($c['x'] + $c['w']).'" y2="'.($c['y'] + $c['h'] - $RODAPE).'" stroke="#555"/>';
    $svg[] = '</g>';
}

$svg[] = '<text x="'.($W / 2).'" y="'.($quadroY + $quadroH + 30).'" font-family="Times New Roman, Times, serif" font-size="17" text-anchor="middle" fill="#000">Fonte: Autor</text>';
$svg[] = '</svg>';

file_put_contents(__DIR__ . '/der.svg', implode("\n", $svg));
echo "ok\n";
