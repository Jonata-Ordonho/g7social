<?php
namespace App\Helpers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Gera e envia para download uma planilha Excel (.xlsx) com uma ou mais abas.
// Cada aba: array('nome' => 'Projetos', 'colunas' => array(array('titulo' => 'Valor', 'formato' => 'moeda', 'largura' => 16), ...),
//                 'linhas' => array(array(...), ...), 'total' => array(...) opcional)
// Formatos: texto (padrão), inteiro, moeda, data (Y-m-d), mes (Y-m-01), datahora (Y-m-d H:i:s), percentual (0 a 1)
class Planilha{
    const FORMATOS = array(
        'inteiro' => '0',
        'moeda' => '"R$" #,##0.00',
        'data' => 'dd/mm/yyyy',
        'mes' => 'mmm/yyyy',
        'datahora' => 'dd/mm/yyyy hh:mm',
        'percentual' => '0%',
    );

    const LINHA_CABECALHO = 4;

    public static function baixar($nomeArquivo, $titulo, $subtitulo, $abas){
        $planilha = new Spreadsheet;
        $planilha->getProperties()->setCreator('G7social')->setTitle($titulo);
        $planilha->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
        $planilha->removeSheetByIndex(0);

        foreach ($abas as $aba) {
            self::montarAba($planilha, $titulo, $subtitulo, $aba);
        }
        $planilha->setActiveSheetIndex(0);

        // Descarta qualquer saída anterior para não corromper o arquivo
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
        header('Cache-Control: max-age=0');

        (new Xlsx($planilha))->save('php://output');
        exit;
    }

    private static function montarAba($planilha, $titulo, $subtitulo, $aba){
        $folha = $planilha->createSheet();
        $folha->setTitle(mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '', $aba['nome']), 0, 31));

        $totalColunas = count($aba['colunas']);
        $ultimaColuna = Coordinate::stringFromColumnIndex($totalColunas);

        $folha->setCellValue('A1', $titulo . ' - ' . $aba['nome']);
        $folha->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('005A8F');
        $folha->setCellValue('A2', $subtitulo);
        $folha->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('5F6B7A');

        // Cabeçalho das colunas
        $linha = self::LINHA_CABECALHO;
        foreach ($aba['colunas'] as $i => $coluna) {
            $letra = Coordinate::stringFromColumnIndex($i + 1);
            $folha->setCellValue($letra . $linha, $coluna['titulo']);
            $folha->getColumnDimension($letra)->setWidth(isset($coluna['largura']) ? $coluna['largura'] : 18);
        }
        $estiloCabecalho = $folha->getStyle('A' . $linha . ':' . $ultimaColuna . $linha);
        $estiloCabecalho->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $estiloCabecalho->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('007DC5');

        // Linhas de dados
        $primeiraLinha = $linha + 1;
        foreach ($aba['linhas'] as $dados) {
            $linha++;
            self::escreverLinha($folha, $aba['colunas'], $dados, $linha);
        }

        if (count($aba['linhas']) == 0) {
            $linha++;
            $folha->setCellValue('A' . $linha, 'Nenhum registro.');
            $folha->getStyle('A' . $linha)->getFont()->setItalic(true);
        } else {
            $folha->setAutoFilter('A' . self::LINHA_CABECALHO . ':' . $ultimaColuna . $linha);
        }

        // Linha de total (opcional)
        if (isset($aba['total'])) {
            $linha++;
            self::escreverLinha($folha, $aba['colunas'], $aba['total'], $linha);
            $estiloTotal = $folha->getStyle('A' . $linha . ':' . $ultimaColuna . $linha);
            $estiloTotal->getFont()->setBold(true);
            $estiloTotal->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        }

        // Formatos por coluna
        foreach ($aba['colunas'] as $i => $coluna) {
            if (isset($coluna['formato']) && isset(self::FORMATOS[$coluna['formato']]) && $linha >= $primeiraLinha) {
                $letra = Coordinate::stringFromColumnIndex($i + 1);
                $folha->getStyle($letra . $primeiraLinha . ':' . $letra . $linha)->getNumberFormat()->setFormatCode(self::FORMATOS[$coluna['formato']]);
            }
        }

        $folha->freezePane('A' . (self::LINHA_CABECALHO + 1));
    }

    private static function escreverLinha($folha, $colunas, $dados, $linha){
        $dados = array_values($dados);
        foreach ($colunas as $i => $coluna) {
            $valor = isset($dados[$i]) ? $dados[$i] : NULL;
            if ($valor === NULL || $valor === '') {
                continue;
            }
            $formato = isset($coluna['formato']) ? $coluna['formato'] : 'texto';
            $celula = Coordinate::stringFromColumnIndex($i + 1) . $linha;

            if (in_array($formato, array('data', 'datahora', 'mes')) && strtotime($valor)) {
                $folha->setCellValue($celula, Date::PHPToExcel(new \DateTime($valor)));
            } elseif (in_array($formato, array('inteiro', 'moeda', 'percentual')) && is_numeric($valor)) {
                $folha->setCellValue($celula, $valor + 0);
            } else {
                // Texto explícito: evita que CPF, telefone ou "=..." virem número ou fórmula
                $folha->setCellValueExplicit($celula, (string) $valor, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
        }
    }
}
