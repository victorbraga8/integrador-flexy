<?php
require 'vendor/autoload.php'; // Caminho para o autoload do PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

function lerDadosDoXLSX($uploadedFile) {
    $spreadsheet = IOFactory::load($uploadedFile);
    $worksheet = $spreadsheet->getActiveSheet();
    
    $dados = array();
    $primeiraLinha = true;
    
    foreach ($worksheet->getRowIterator() as $row) {
        if ($primeiraLinha) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                $headers[] = $cell->getValue();
            }

            $primeiraLinha = false;
            continue;
        }

        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        $rowData = array();
        
        foreach ($cellIterator as $cell) {
            $rowData[] = $cell->getValue();
        }
        
        // Verifica se pelo menos uma célula não está vazia
        if (!empty(array_filter($rowData))) {
            $dados[] = $rowData;
        }
    }

    return $dados;
}

function ftp(){
	$ftp_server = "185.212.70.64";
	$ftp_username = "u766130877";
	$ftp_userpass = 'Niteroi@11';
	$ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
	$login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass);

	$ftp = array();

	$ftp["conn"] = $ftp_conn;
	$ftp["login"] = $login;

	return $ftp;
}

function removeSpacesAndSpecialChars($inputString) {
    // Remover espaços em branco
    $stringWithoutSpaces = str_replace(' ', '', $inputString);
    
    // Remover caracteres especiais (manter apenas letras e números)
    $stringWithoutSpecialChars = preg_replace('/[^a-zA-Z0-9]/', '', $stringWithoutSpaces);
    
    return $stringWithoutSpecialChars;
}

function montaCategoria($categoriaGeral){

	$xlsxFile = 'categorias.xlsx';
	$spreadsheet = IOFactory::load($xlsxFile);

	$sheet = $spreadsheet->getActiveSheet();

	$categorias = explode(">", $categoriaGeral);
	// unset($categorias[0]);	
	$categoriaSelecionada = array();
	
	foreach ($categorias as $parteStringBusca){	
		// Colocar no parteStringBusca e limpeza de caracteres e colocar tudo em caixa alta.	
		$parteStringBusca = removeSpacesAndSpecialChars(strtoupper($parteStringBusca));

		foreach ($sheet->getRowIterator() as $row){
		    foreach ($row->getCellIterator() as $cell) {
		        $valorCelula = $cell->getValue();
		        $valorCelula = removeSpacesAndSpecialChars(strtoupper($valorCelula));
		        // Colocar no valorCelula e limpeza de caracteres e colocar tudo em caixa alta.
		        if($valorCelula == $parteStringBusca){
		            $linha = $row->getRowIndex();
		            $coluna = $cell->getColumn();
		            $valor1 = $sheet->getCell('A'.$linha)->getValue();
		            // $valor2 = $sheet->getCell('B'.$linha)->getValue();
		            array_push($categoriaSelecionada, $valor1);
		        }
		        // if (strpos($valorCelula, $parteStringBusca) !== false) {
		        //     $linha = $row->getRowIndex();
		        //     $coluna = $cell->getColumn();
		        //     $valor1 = $sheet->getCell('A'.$linha)->getValue();
		        //     $valor2 = $sheet->getCell('B'.$linha)->getValue();
		        //     echo "Valor 1: ".$valor1.' - Valor 2: '.$valor2.'<br>';
		        //     // echo "Parte da string encontrada na célula ({$coluna}, {$linha}): " . $valorCelula.'<br>';
		        //     array_push($categoriaSelecionada, $valorCelula);		            
		        // }
		    }
		}	
	}	

	return array_unique($categoriaSelecionada);	
}

function montaProduto($produto){
	$jsondata = file_get_contents('matriz.json');
	$result = json_decode($jsondata, true);
		
	$result['product']['name'] = $produto[1];
	$result['product']['slug'] = str_replace(",","-", str_replace("/","-", str_replace(" ","-",strtolower($produto[1]))));
	$result['product']['referenceCode'] = str_replace(",","-", str_replace("/","-", str_replace(" ","-",strtolower($produto[5]))));
	$result['product']['ncm'] = $produto[6];	
	$result['product']['integrationCode'] = str_replace(",","-", str_replace("/","-", str_replace(" ","-",strtolower($produto[5]))));

	if($produto[2]){
		$categoriasLoja = montaCategoria($produto[2]);
		foreach ($categoriasLoja as $categoriaLoja){	
			array_push($result['product']['shoppingCategories'], $categoriaLoja);		
		}	
	}

	if($produto[3]){
		$categorias = explode(">", $produto[3]);
		foreach ($categorias as $categoria){			
			array_push($result['product']['categories'], $categoria);
		}		
	}

	$result['product']['variants'][0]['referenceCode'] = str_replace(",","-", str_replace("/","-", str_replace(" ","-",strtolower($produto[1]))));
	$result['product']['variants'][0]['presentation'] = str_replace(",","-", str_replace("/","-", str_replace(" ","-",strtolower($produto[1]))));
	$result['product']['variants'][0]['price'] = $produto[0];
	$result['product']['variants'][0]['stock']['quantity'] = $produto[4];
	$result['product']['descriptionGroup']['descriptionOne'] = $produto[8];
	$result['product']['descriptionGroup']['descriptionTwo'] = $produto[9];
	$result['product']['descriptionGroup']['descriptionThree'] = $produto[10];
	$result['product']['descriptionGroup']['descriptionFour'] = "";
	$result['product']['metaTags']['title'] = str_replace(",","-", str_replace("/","-", str_replace(" ","-",strtolower($produto[1]))));
	$result['product']['metaTags']['url'] = '';
	$result['product']['metaTags']['keyword'] = '';
	$result['product']['metaTags']['description'] = '';
	$result['product']['metaTags']['trackingId'] = '';
	
	$quantidadeFotos = sizeof($produto) - 11;
	
	if($quantidadeFotos == 1){
		$result['product']['images'][0] = $produto[11];
		$result['product']['images'][1]['url'] = $produto[11];
		$result['product']['images'][1]['isMaster'] = 1;
		$result['product']['images'][2]['url'] = $produto[11];
	}elseif($quantidadeFotos == 0){
		$result['product']['images'][0] = "";
		$result['product']['images'][1]['url'] = "";
		$result['product']['images'][1]['isMaster'] = 1;
		$result['product']['images'][2]['url'] = "";
	}

	if($quantidadeFotos >= 2){
		$indiceFotos = 2;
		$indiceArrayProduto = 12;
		for($i=1; $i < $quantidadeFotos; $i++) { 
			$result['product']['images'][0] = $produto[11];
			$result['product']['images'][1]['url'] = $produto[11];
			$result['product']['images'][1]['isMaster'] = 1;
			$result['product']['images'][$indiceFotos]['url'] = $produto[$indiceArrayProduto];
			$indiceFotos ++;
			$indiceArrayProduto++;
		}
	}
	
	$result['product']['videos'][0]['code'] = '#';
	$result['product']['videos'][0]['description']  = '#';

	$jsondataFinal = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	return $jsondataFinal;
}	


function enviaProduto($json){
    $header = array();
    $header[] = 'Content-Type: application/json';

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://cellairis.api.flexy.com.br/platform/api/products/?token=a5oitdgdu95tbnx7k9ysi&referenceCodeStore=mmacielcellairis.com.br&version=1",
        CURLOPT_POST => TRUE,
        CURLOPT_POSTFIELDS => $json,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HEADER => TRUE,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_HTTPHEADER => $header,
        CURLINFO_HEADER_OUT => TRUE
    ]);

    $response = curl_exec($curl);
    $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $info = curl_getinfo($curl);

    return $statusCode;
}

?>