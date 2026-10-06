<?php
// Gera o Diagrama de Casos de Uso do G7 Social em SVG
$W = 1650; $H = 1000;
function esc($t) { return htmlspecialchars($t, ENT_XML1); }

$svg = array();
$svg[] = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$W.'" height="'.$H.'" viewBox="0 0 '.$W.' '.$H.'" font-family="Segoe UI, Inter, Arial, sans-serif">';
$svg[] = '<defs>
  <marker id="seta" viewBox="0 0 12 12" refX="11" refY="6" markerWidth="12" markerHeight="12" orient="auto" markerUnits="userSpaceOnUse">
    <path d="M1 1 L11 6 L1 11" fill="none" stroke="#4f5b6a" stroke-width="1.6"/></marker>
  <marker id="heranca" viewBox="0 0 18 18" refX="17" refY="9" markerWidth="18" markerHeight="18" orient="auto" markerUnits="userSpaceOnUse">
    <path d="M1 1 L17 9 L1 17 Z" fill="#fff" stroke="#1f2933" stroke-width="1.6"/></marker>
</defs>';
$svg[] = '<rect width="100%" height="100%" fill="#f3f5f8"/>';
$svg[] = '<text x="40" y="40" font-size="22" font-weight="700" fill="#1f2933">G7 Social — Diagrama de Casos de Uso</text>';

// Limites dos sistemas
function sistema($x, $y, $w, $h, $titulo) {
    global $svg;
    $svg[] = '<rect x="'.$x.'" y="'.$y.'" width="'.$w.'" height="'.$h.'" rx="12" fill="#fff" stroke="#9aa6b5" stroke-width="1.5"/>';
    $svg[] = '<text x="'.($x + $w / 2).'" y="'.($y + 28).'" font-size="15" font-weight="700" text-anchor="middle" fill="#00649e">'.esc($titulo).'</text>';
}
sistema(300, 70, 860, 900, 'Gestor — área administrativa');
sistema(1210, 270, 270, 470, 'Home — página pública');

// Casos de uso: id => [cx, cy, texto (| quebra linha), cor]
$rx = 128; $ry = 30;
$uc = array(
 'login'      => array(560, 140, 'Fazer login', 'g'),
 'dados'      => array(560, 215, 'Alterar dados pessoais', 'g'),
 'projetos'   => array(560, 290, 'Consultar e alterar|projetos sociais', 'g'),
 'atividades' => array(560, 375, 'Gerenciar atividades', 'g'),
 'voluntarios'=> array(560, 450, 'Gerenciar voluntários|da atividade', 'g'),
 'doadores'   => array(560, 535, 'Gerenciar doadores', 'g'),
 'arrecad'    => array(560, 620, 'Gerenciar arrecadações', 'g'),
 'listar_u'   => array(560, 720, 'Listar usuários', 'a'),
 'cad_u'      => array(560, 800, 'Cadastrar usuário', 'a'),
 'alt_u'      => array(560, 895, 'Alterar dados e status|de usuários', 'm'),
 // casos estendidos / incluídos
 'status_at'  => array(930, 330, 'Ativar/desativar|inscrições', 'x'),
 'link'       => array(930, 415, 'Copiar link de inscrição', 'x'),
 'detalhar'   => array(930, 535, 'Detalhar doador|e suas doações', 'x'),
 'sel_doador' => array(930, 620, 'Selecionar doador', 'x'),
 // página pública
 'acessar'    => array(1345, 360, 'Acessar página|da atividade', 'p'),
 'inscrever'  => array(1345, 495, 'Inscrever-se como|voluntário', 'p'),
 'validar'    => array(1345, 640, 'Validar vagas e|e-mail repetido', 'x'),
);
$cores = array(
 'g' => array('#e6f2fa', '#007DC5'), 'a' => array('#fff1e6', '#f60'), 'm' => array('#fdecec', '#c53030'),
 'x' => array('#f5f7fa', '#8a94a3'), 'p' => array('#e6f5ec', '#2e9e5b'),
);

// Atores: id => [x, y (centro do corpo), nome]
$atores = array(
 'gestor' => array(130, 375, 'Gestor'),
 'admin'  => array(130, 740, 'Administrador'),
 'master' => array(130, 890, 'Admin master (DEV)'),
 'volunt' => array(1555, 430, 'Voluntário'),
);

// Associações ator -> caso de uso
$assoc = array(
 array('gestor', array('login', 'dados', 'projetos', 'atividades', 'voluntarios', 'doadores', 'arrecad')),
 array('admin', array('listar_u', 'cad_u')),
 array('master', array('alt_u')),
 array('volunt', array('acessar', 'inscrever')),
);
foreach ($assoc as $a) {
    list($ax, $ay) = $atores[$a[0]];
    foreach ($a[1] as $id) {
        list($cx, $cy) = $uc[$id];
        $x2 = $ax < $cx ? $cx - $rx : $cx + 112;   // borda do caso de uso (lado do ator)
        $x1 = $ax < $cx ? $ax + 26 : $ax - 26;
        $svg[] = '<line x1="'.$x1.'" y1="'.($ay - 10).'" x2="'.$x2.'" y2="'.$cy.'" stroke="#1f2933" stroke-width="1.3"/>';
    }
}

// Herança entre atores (Administrador é um Gestor; Admin master é um Administrador)
$svg[] = '<line x1="130" y1="692" x2="130" y2="448" stroke="#1f2933" stroke-width="1.5" marker-end="url(#heranca)"/>';
$svg[] = '<line x1="130" y1="842" x2="130" y2="812" stroke="#1f2933" stroke-width="1.5" marker-end="url(#heranca)"/>';

// «extend» (do caso estendido para o caso base) e «include» (do caso base para o incluído)
function dep($de, $para, $rotulo, $lx, $ly, $pontos = null) {
    global $svg, $uc;
    $svg[] = '<polyline points="'.$pontos.'" fill="none" stroke="#4f5b6a" stroke-width="1.3" stroke-dasharray="6 4" marker-end="url(#seta)"/>';
    $svg[] = '<text x="'.$lx.'" y="'.$ly.'" font-size="11.5" fill="#4f5b6a" text-anchor="middle">«'.$rotulo.'»</text>';
}
dep('status_at', 'atividades', 'extend', 755, 336, '815,338 690,368');
dep('link', 'atividades', 'extend', 738, 418, '802,412 690,382');
dep('detalhar', 'doadores', 'extend', 750, 527, '815,535 690,535');
dep('arrecad', 'sel_doador', 'include', 750, 612, '690,620 800,620');
dep('inscrever', 'validar', 'include', 1395, 572, '1345,527 1345,606');

// Desenha os casos de uso
foreach ($uc as $id => $u) {
    list($cx, $cy, $txt, $c) = $u;
    $larg = ($c == 'p' || $id == 'validar') ? 112 : $rx;
    $svg[] = '<ellipse cx="'.$cx.'" cy="'.$cy.'" rx="'.$larg.'" ry="'.$ry.'" fill="'.$cores[$c][0].'" stroke="'.$cores[$c][1].'" stroke-width="1.6"/>';
    $linhas = explode('|', $txt);
    $y0 = $cy + 5 - (count($linhas) - 1) * 8;
    foreach ($linhas as $i => $l) {
        $svg[] = '<text x="'.$cx.'" y="'.($y0 + $i * 16).'" font-size="13" text-anchor="middle" fill="#1f2933">'.esc($l).'</text>';
    }
}

// Desenha os atores (boneco palito)
foreach ($atores as $id => $a) {
    list($x, $y, $nome) = $a;
    $cor = array('gestor' => '#007DC5', 'admin' => '#f60', 'master' => '#c53030', 'volunt' => '#2e9e5b');
    $k = $cor[$id];
    $svg[] = '<circle cx="'.$x.'" cy="'.($y - 38).'" r="11" fill="#fff" stroke="'.$k.'" stroke-width="2"/>';
    $svg[] = '<path d="M'.$x.' '.($y - 27).' V'.($y + 8).' M'.($x - 22).' '.($y - 14).' H'.($x + 22).' M'.$x.' '.($y + 8).' L'.($x - 16).' '.($y + 34).' M'.$x.' '.($y + 8).' L'.($x + 16).' '.($y + 34).'" fill="none" stroke="'.$k.'" stroke-width="2" stroke-linecap="round"/>';
    $svg[] = '<text x="'.$x.'" y="'.($y + 54).'" font-size="13.5" font-weight="700" text-anchor="middle" fill="#1f2933">'.esc($nome).'</text>';
}

// Notas
$svg[] = '<text x="1210" y="770" font-size="12" fill="#4f5b6a">A página pública só aceita inscrições quando a</text>';
$svg[] = '<text x="1210" y="786" font-size="12" fill="#4f5b6a">atividade está ativa, dentro do prazo e com vagas.</text>';
$svg[] = '<text x="1210" y="812" font-size="12" fill="#4f5b6a">O link é gerado em «Copiar link de inscrição».</text>';

// Legenda
$lx = 1210; $ly = 840;
$svg[] = '<rect x="'.$lx.'" y="'.$ly.'" width="410" height="130" rx="8" fill="#fff" stroke="#c9d1db"/>';
$svg[] = '<text x="'.($lx + 14).'" y="'.($ly + 24).'" font-size="14" font-weight="700" fill="#1f2933">Legenda</text>';
$leg = array(
 array('g', 'Todos os usuários do gestor'), array('a', 'Administrador e admin master'),
 array('m', 'Somente admin master'), array('p', 'Público (sem login)'),
);
foreach ($leg as $i => $l) {
    $xx = $lx + 16 + ($i % 2) * 200; $yy = $ly + 48 + intdiv($i, 2) * 26;
    $svg[] = '<ellipse cx="'.($xx + 14).'" cy="'.($yy - 4).'" rx="14" ry="8" fill="'.$cores[$l[0]][0].'" stroke="'.$cores[$l[0]][1].'" stroke-width="1.5"/>';
    $svg[] = '<text x="'.($xx + 34).'" y="'.$yy.'" font-size="11.5" fill="#1f2933">'.esc($l[1]).'</text>';
}
$svg[] = '<line x1="'.($lx + 16).'" y1="'.($ly + 108).'" x2="'.($lx + 50).'" y2="'.($ly + 108).'" stroke="#1f2933" stroke-width="1.5" marker-end="url(#heranca)"/>';
$svg[] = '<text x="'.($lx + 60).'" y="'.($ly + 112).'" font-size="11.5" fill="#1f2933">herda os casos de uso</text>';
$svg[] = '<line x1="'.($lx + 196).'" y1="'.($ly + 108).'" x2="'.($lx + 230).'" y2="'.($ly + 108).'" stroke="#4f5b6a" stroke-width="1.3" stroke-dasharray="6 4" marker-end="url(#seta)"/>';
$svg[] = '<text x="'.($lx + 240).'" y="'.($ly + 112).'" font-size="11.5" fill="#1f2933">«include» / «extend»</text>';

$svg[] = '</svg>';
file_put_contents('C:/Sisitemas/g7social/docs/diagramas/casos_de_uso.svg', implode("\n", $svg));
echo "ok\n";
