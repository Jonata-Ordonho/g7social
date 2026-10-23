<?php
// Gera o Diagrama de Casos de Uso do G7 Social em SVG (estilo do documento: pílulas azul-escuras e atores em pictograma)
// Os casos de uso seguem a tabela de requisitos funcionais (docs/requisitos) e as associações seguem src/Helpers/Permissao.php
function esc($t) { return htmlspecialchars($t, ENT_XML1); }

$tituloFigura = 'Figura 2 - Diagrama de Caso de Uso';

// [nome, atores associados]  A = Administrador, G = Gestor, V = Voluntário
$casos = array(
    array('Manter Usuários', 'A'),
    array('Manter Projeto Social', 'A'),
    array('Realizar Login', 'AG'),
    array('Alterar Dados Pessoais', 'AG'),
    array('Visualizar Painel de Indicadores', 'AG'),
    array('Manter Atividade', 'AG'),
    array('Ativar/Desativar Atividade', 'AG'),
    array('Manter Voluntário', 'AG'),
    array('Manter Inscrição de Voluntário', 'AG'),
    array('Manter Doador', 'AG'),
    array('Manter Arrecadação', 'AG'),
    array('Gerar Relatório Geral', 'AG'),
    array('Gerar Relatório por Projeto', 'AG'),
    array('Exportar Relatórios', 'AG'),
    array('Realizar Inscrição Pública', 'V'),
);

// Medidas
$W = 900;
$quadroX = 30; $quadroY = 50; $quadroW = $W - 60;
$pilulaW = 250; $pilulaH = 32; $passo = 46;
$centroX = $W / 2;
$primeiroY = $quadroY + 30;
$ultimoY = $primeiroY + (count($casos) - 1) * $passo;
$quadroH = ($ultimoY + $pilulaH) - $quadroY + 250;
$H = $quadroY + $quadroH + 50;

// Ator em pictograma (cabeça, tronco, braços e pernas) com o rótulo numa caixa cinza
function ator($cx, $topo, $rotulo){
    $s = array();
    $s[] = '<g fill="#000">';
    $s[] = '<circle cx="'.$cx.'" cy="'.($topo + 9).'" r="9"/>';
    $s[] = '<rect x="'.($cx - 12).'" y="'.($topo + 21).'" width="24" height="26" rx="5"/>';
    $s[] = '<rect x="'.($cx - 17).'" y="'.($topo + 22).'" width="6" height="22" rx="3"/>';
    $s[] = '<rect x="'.($cx + 11).'" y="'.($topo + 22).'" width="6" height="22" rx="3"/>';
    $s[] = '<rect x="'.($cx - 10).'" y="'.($topo + 44).'" width="8" height="26" rx="3"/>';
    $s[] = '<rect x="'.($cx + 2).'" y="'.($topo + 44).'" width="8" height="26" rx="3"/>';
    $s[] = '</g>';
    $s[] = '<rect x="'.($cx - 45).'" y="'.($topo + 88).'" width="90" height="32" rx="7" fill="#d9d9d9"/>';
    $s[] = '<text x="'.$cx.'" y="'.($topo + 108).'" font-size="11" font-weight="700" text-anchor="middle" fill="#111">'.esc($rotulo).'</text>';
    return implode("\n", $s);
}

$svg = array();
$svg[] = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$W.'" height="'.$H.'" viewBox="0 0 '.$W.' '.$H.'">';
$svg[] = '<rect width="100%" height="100%" fill="#fff"/>';
$svg[] = '<text x="'.$centroX.'" y="'.($quadroY - 16).'" font-family="Times New Roman, Times, serif" font-size="19" text-anchor="middle" fill="#000">'.esc($tituloFigura).'</text>';
$svg[] = '<rect x="'.$quadroX.'" y="'.$quadroY.'" width="'.$quadroW.'" height="'.$quadroH.'" fill="#fff" stroke="#333" stroke-width="1"/>';

// Posição dos atores: Administrador à esquerda e Gestor à direita, na altura dos casos compartilhados; Voluntário embaixo
$casosAG = array_keys(array_filter($casos, function($c){ return strpos($c[1], 'A') !== false || strpos($c[1], 'G') !== false; }));
$meioY = $primeiroY + (($casosAG[0] + end($casosAG)) / 2) * $passo + $pilulaH / 2;
$atorEsq = array('x' => $quadroX + 110, 'topo' => $meioY - 40);
$atorDir = array('x' => $quadroX + $quadroW - 110, 'topo' => $meioY - 40);
$ancoraEsq = array($atorEsq['x'] + 22, $meioY - 10);
$ancoraDir = array($atorDir['x'] - 22, $meioY - 10);
$atorBaixo = array('x' => $centroX, 'topo' => $ultimoY + $pilulaH + 90);

// Associações (desenhadas antes das pílulas para ficarem por baixo)
$svg[] = '<g stroke="#000" stroke-width="1.2">';
foreach ($casos as $i => $c) {
    $y = $primeiroY + $i * $passo + $pilulaH / 2;
    if (strpos($c[1], 'A') !== false) {
        $svg[] = '<line x1="'.$ancoraEsq[0].'" y1="'.$ancoraEsq[1].'" x2="'.($centroX - $pilulaW / 2).'" y2="'.$y.'"/>';
    }
    if (strpos($c[1], 'G') !== false) {
        $svg[] = '<line x1="'.$ancoraDir[0].'" y1="'.$ancoraDir[1].'" x2="'.($centroX + $pilulaW / 2).'" y2="'.$y.'"/>';
    }
    if (strpos($c[1], 'V') !== false) {
        $svg[] = '<line x1="'.$centroX.'" y1="'.($y + $pilulaH / 2).'" x2="'.$centroX.'" y2="'.($atorBaixo['topo'] - 6).'"/>';
    }
}
$svg[] = '</g>';

// Casos de uso
foreach ($casos as $i => $c) {
    $y = $primeiroY + $i * $passo;
    $svg[] = '<rect x="'.($centroX - $pilulaW / 2).'" y="'.$y.'" width="'.$pilulaW.'" height="'.$pilulaH.'" rx="16" fill="#0b0b6e"/>';
    $svg[] = '<text x="'.$centroX.'" y="'.($y + 21).'" font-family="Segoe UI, Open Sans, Arial, sans-serif" font-size="12.5" font-weight="700" text-anchor="middle" fill="#fff">'.esc($c[0]).'</text>';
}

$svg[] = '<g font-family="Segoe UI, Open Sans, Arial, sans-serif">';
$svg[] = ator($atorEsq['x'], $atorEsq['topo'], 'Administrador');
$svg[] = ator($atorDir['x'], $atorDir['topo'], 'Gestor');
$svg[] = ator($atorBaixo['x'], $atorBaixo['topo'], 'Voluntário');
$svg[] = '</g>';

$svg[] = '<text x="'.$centroX.'" y="'.($quadroY + $quadroH + 30).'" font-family="Times New Roman, Times, serif" font-size="17" text-anchor="middle" fill="#000">Fonte: Autor</text>';
$svg[] = '</svg>';

file_put_contents(__DIR__ . '/casos_de_uso.svg', implode("\n", $svg));
echo "ok\n";
