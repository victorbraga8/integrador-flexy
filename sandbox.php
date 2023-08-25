<?php 
	require 'vendor/autoload.php'; // Caminho para o autoload do PhpSpreadsheet

	use PhpOffice\PhpSpreadsheet\IOFactory;

	$xlsxFile = 'MATRIZ.xlsx';
	$spreadsheet = IOFactory::load($xlsxFile);

	$sheet = $spreadsheet->getActiveSheet();

	// Valor que você deseja buscar
	$parteStringBusca = 'Departamentos';

	// Percorra as células da planilha em busca da parte da string
	foreach ($sheet->getRowIterator() as $row) {
	    foreach ($row->getCellIterator() as $cell) {
	        $valorCelula = $cell->getValue();
	        if (strpos($valorCelula, $parteStringBusca) !== false) {
	            $linha = $row->getRowIndex();
	            $coluna = $cell->getColumn();
	            echo "Parte da string encontrada na célula ({$coluna}, {$linha}): " . $valorCelula;
	            exit; // Encerra a busca após encontrar a parte da string
	        }
	    }
	}

	echo "Parte da string não encontrada.";

 ?>