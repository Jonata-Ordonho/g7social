<?php
// Gera o Diagrama de Entidade-Relacionamento (DER) do G7 Social em SVG
$W = 1460; $H = 800;
$LARG = 280; $CAB = 36; $LIN = 23;

// [x, y, campos]  campo: [nome, tipo, chave]
$ent = array(
 'acoes' => array(40, 60, array(
   array('id_acao', 'INT', 'PK'), array('descricao', 'VARCHAR(100)', ''))),
 'logs' => array(40, 230, array(
   array('id_log', 'INT', 'PK'), array('id_acao', 'INT', 'FK'), array('id_usuario', 'INT', 'FK'),
   array('descricao', 'TEXT', ''), array('data_registro', 'DATETIME', ''))),
 'usuarios' => array(400, 60, array(
   array('id_usuario', 'INT', 'PK'), array('nome', 'VARCHAR(150)', ''), array('email', 'VARCHAR(150)', 'UK'),
   array('senha', 'CHAR(40)', ''), array('tipo', 'ENUM', ''), array('status', 'ENUM', ''), array('data_registro', 'DATETIME', ''))),
 'voluntarios' => array(400, 560, array(
   array('id_voluntario', 'INT', 'PK'), array('id_atividade', 'INT', 'FK'), array('nome', 'VARCHAR(150)', ''),
   array('email', 'VARCHAR(150)', ''), array('telefone', 'VARCHAR(11)', ''), array('data_registro', 'DATETIME', ''))),
 'projetos' => array(770, 60, array(
   array('id_projeto', 'INT', 'PK'), array('titulo', 'VARCHAR(200)', ''), array('descricao', 'TEXT', ''),
   array('captacao_empresas', 'ENUM', ''), array('captacao_voluntarios', 'ENUM', ''), array('data_inicio', 'DATE', ''),
   array('data_fim', 'DATE', ''), array('id_usuario', 'INT', 'FK'), array('data_registro', 'DATETIME', ''))),
 'atividades' => array(770, 400, array(
   array('id_atividade', 'INT', 'PK'), array('id_projeto', 'INT', 'FK'), array('titulo', 'VARCHAR(200)', ''),
   array('descricao', 'TEXT', ''), array('local', 'VARCHAR(200)', ''), array('data_inicio', 'DATE', ''),
   array('data_fim', 'DATE', ''), array('horario', 'VARCHAR(100)', ''), array('vagas', 'INT', ''),
   array('codigo', 'CHAR(32)', 'UK'), array('status', 'ENUM', ''), array('id_usuario', 'INT', 'FK'),
   array('data_registro', 'DATETIME', ''))),
 'doadores' => array(1140, 60, array(
   array('id_doador', 'INT', 'PK'), array('tipo', 'ENUM', ''), array('nome', 'VARCHAR(200)', ''),
   array('documento', 'VARCHAR(14)', 'UK'), array('email', 'VARCHAR(150)', ''), array('telefone', 'VARCHAR(11)', ''),
   array('id_usuario', 'INT', 'FK'), array('data_registro', 'DATETIME', ''))),
 'arrecadacoes' => array(1140, 400, array(
   array('id_arrecadacao', 'INT', 'PK'), array('id_projeto', 'INT', 'FK'), array('id_doador', 'INT', 'FK'),
   array('tipo', 'ENUM', ''), array('valor', 'DECIMAL(12,2)', ''), array('descricao', 'TEXT', ''),
   array('data_arrecadacao', 'DATE', ''), array('data_registro', 'DATETIME', ''))),
);

function alt($campos) { global $CAB, $LIN; return $CAB + count($campos) * $LIN + 8; }
function esc($t) { return htmlspecialchars($t, ENT_XML1); }

$svg = array();
$svg[] = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$W.'" height="'.$H.'" viewBox="0 0 '.$W.' '.$H.'" font-family="Segoe UI, Inter, Arial, sans-serif">';
$svg[] = '<defs>
  <marker id="um" viewBox="0 0 20 20" refX="19" refY="10" markerWidth="20" markerHeight="20" orient="auto-start-reverse" markerUnits="userSpaceOnUse">
    <path d="M9 3 V17 M14 3 V17" stroke="#1f2933" stroke-width="1.8" fill="none"/></marker>
  <marker id="zeroum" viewBox="0 0 24 20" refX="23" refY="10" markerWidth="24" markerHeight="20" orient="auto-start-reverse" markerUnits="userSpaceOnUse">
    <circle cx="8" cy="10" r="4.5" fill="#fff" stroke="#1f2933" stroke-width="1.8"/><path d="M17 3 V17" stroke="#1f2933" stroke-width="1.8"/></marker>
  <marker id="muitos" viewBox="0 0 26 20" refX="25" refY="10" markerWidth="26" markerHeight="20" orient="auto-start-reverse" markerUnits="userSpaceOnUse">
    <circle cx="6" cy="10" r="4.5" fill="#fff" stroke="#1f2933" stroke-width="1.8"/><path d="M13 10 L25 2 M13 10 L25 10 M13 10 L25 18" stroke="#1f2933" stroke-width="1.8" fill="none"/></marker>
  <filter id="sombra" x="-10%" y="-10%" width="120%" height="120%"><feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#101828" flood-opacity="0.12"/></filter>
</defs>';
$svg[] = '<rect width="100%" height="100%" fill="#f3f5f8"/>';
$svg[] = '<text x="40" y="36" font-size="22" font-weight="700" fill="#1f2933">G7 Social — Diagrama de Entidade-Relacionamento</text>';

// Relacionamentos: [pontos, marcador início (lado "1"), marcador fim (lado "N"), rótulo, x, y]
$rel = array(
 array('175,150 175,230', 'um', 'muitos', 'classifica', 183, 196),
 array('400,250 320,250', 'um', 'muitos', 'executa', 334, 242),
 array('680,150 770,150', 'zeroum', 'muitos', 'cadastra', 698, 142),
 array('540,265 540,470 770,470', 'zeroum', 'muitos', 'cadastra', 610, 462),
 array('610,60 610,48 1280,48 1280,60', 'zeroum', 'muitos', 'cadastra', 1040, 42),
 array('910,311 910,400', 'um', 'muitos', 'possui', 918, 362),
 array('770,640 680,640', 'um', 'muitos', 'recebe', 696, 632),
 array('1050,200 1100,200 1100,480 1140,480', 'um', 'muitos', 'recebe', 1050, 340),
 array('1280,288 1280,400', 'um', 'muitos', 'faz', 1288, 350),
);
foreach ($rel as $r) {
    $svg[] = '<polyline points="'.$r[0].'" fill="none" stroke="#1f2933" stroke-width="1.6" marker-start="url(#'.$r[1].')" marker-end="url(#'.$r[2].')"/>';
    $svg[] = '<text x="'.$r[4].'" y="'.$r[5].'" font-size="12" font-style="italic" fill="#4f5b6a">'.$r[3].'</text>';
}

// Entidades
foreach ($ent as $nome => $e) {
    list($x, $y, $campos) = $e;
    $h = alt($campos);
    $svg[] = '<g filter="url(#sombra)"><rect x="'.$x.'" y="'.$y.'" width="'.$LARG.'" height="'.$h.'" rx="8" fill="#fff" stroke="#c9d1db"/></g>';
    $svg[] = '<path d="M'.$x.' '.($y+$CAB).' V'.($y+8).' Q'.$x.' '.$y.' '.($x+8).' '.$y.' H'.($x+$LARG-8).' Q'.($x+$LARG).' '.$y.' '.($x+$LARG).' '.($y+8).' V'.($y+$CAB).' Z" fill="#007DC5"/>';
    $svg[] = '<text x="'.($x+14).'" y="'.($y+24).'" font-size="15" font-weight="700" fill="#fff">'.esc($nome).'</text>';
    foreach ($campos as $i => $c) {
        $cy = $y + $CAB + 4 + $i * $LIN;
        if ($i % 2 == 1) $svg[] = '<rect x="'.($x+1).'" y="'.$cy.'" width="'.($LARG-2).'" height="'.$LIN.'" fill="#f5f7fa"/>';
        $ty = $cy + 16;
        if ($c[2] != '') {
            $cores = array('PK' => array('#fff4d6', '#9a6b00'), 'FK' => array('#e6f2fa', '#00649e'), 'UK' => array('#eceff3', '#4f5b6a'));
            $svg[] = '<rect x="'.($x+10).'" y="'.($cy+4).'" width="26" height="15" rx="3" fill="'.$cores[$c[2]][0].'"/>';
            $svg[] = '<text x="'.($x+23).'" y="'.($cy+15.5).'" font-size="9.5" font-weight="700" text-anchor="middle" fill="'.$cores[$c[2]][1].'">'.$c[2].'</text>';
        }
        $peso = $c[2] == 'PK' ? ' font-weight="700"' : '';
        $svg[] = '<text x="'.($x+44).'" y="'.$ty.'" font-size="13" fill="#1f2933"'.$peso.'>'.esc($c[0]).'</text>';
        $svg[] = '<text x="'.($x+$LARG-12).'" y="'.$ty.'" font-size="11.5" text-anchor="end" fill="#6f7a89">'.esc($c[1]).'</text>';
    }
}

// Legenda
$lx = 40; $ly = 560;
$svg[] = '<rect x="'.$lx.'" y="'.$ly.'" width="300" height="210" rx="8" fill="#fff" stroke="#c9d1db"/>';
$svg[] = '<text x="'.($lx+14).'" y="'.($ly+26).'" font-size="14" font-weight="700" fill="#1f2933">Legenda</text>';
$itens = array(array('um', 'exatamente um'), array('zeroum', 'zero ou um (opcional)'), array('muitos', 'zero ou muitos'));
foreach ($itens as $i => $it) {
    $yy = $ly + 52 + $i * 28;
    $svg[] = '<line x1="'.($lx+16).'" y1="'.$yy.'" x2="'.($lx+76).'" y2="'.$yy.'" stroke="#1f2933" stroke-width="1.6" marker-end="url(#'.$it[0].')"/>';
    $svg[] = '<text x="'.($lx+90).'" y="'.($yy+4).'" font-size="12.5" fill="#1f2933">'.$it[1].'</text>';
}
$chaves = array(array('PK', '#fff4d6', '#9a6b00', 'chave primária'), array('FK', '#e6f2fa', '#00649e', 'chave estrangeira'), array('UK', '#eceff3', '#4f5b6a', 'valor único'));
foreach ($chaves as $i => $k) {
    $yy = $ly + 146 + $i * 22;
    $svg[] = '<rect x="'.($lx+16).'" y="'.($yy-11).'" width="26" height="15" rx="3" fill="'.$k[1].'"/>';
    $svg[] = '<text x="'.($lx+29).'" y="'.$yy.'" font-size="9.5" font-weight="700" text-anchor="middle" fill="'.$k[2].'">'.$k[0].'</text>';
    $svg[] = '<text x="'.($lx+52).'" y="'.$yy.'" font-size="12.5" fill="#1f2933">'.$k[3].'</text>';
}
$svg[] = '</svg>';
file_put_contents('C:/Sisitemas/g7social/docs/diagramas/der.svg', implode("\n", $svg));
foreach ($ent as $n => $e) echo str_pad($n, 13) . " x={$e[0]} y={$e[1]} h=" . alt($e[2]) . " (fim y=" . ($e[1] + alt($e[2])) . ")\n";
