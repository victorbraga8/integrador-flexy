<?php
require 'vendor/autoload.php'; // Caminho para o autoload do PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

function lerDadosDoXLSX($uploadedFile) {
    $spreadsheet = IOFactory::load($uploadedFile);
    $worksheet = $spreadsheet->getActiveSheet();
    
    $dados = array();
    $primeiraLinha = true;
    $headers = array();

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
        $headerIndex = 0; // Inicializa o índice dos headers
        
        foreach ($cellIterator as $cell) {
            $header = $headers[$headerIndex]; // Pega o header correto
            $rowData[$header] = $cell->getValue();
            $headerIndex++; // Avança para o próximo header
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

function montaProduto($produto){
	$jsondata = file_get_contents('matriz.json');
	$result = json_decode($jsondata, true);

	$result['product']['name'] = $produto[1];
	$result['product']['slug'] = str_replace(",","-", str_replace("/","-", str_replace(" ","-",strtolower($produto[1]))));
	$result['product']['referenceCode'] = str_replace(",","-", str_replace("/","-", str_replace(" ","-",strtolower($produto[1]))));
	$result['product']['integrationCode'] = str_replace(",","-", str_replace("/","-", str_replace(" ","-",strtolower($produto[1]))));
	$result['product']['categories'][0] = $produto[2];
	$result['product']['variants'][0]['referenceCode'] = str_replace(",","-", str_replace("/","-", str_replace(" ","-",strtolower($produto[1]))));
	$result['product']['variants'][0]['presentation'] = str_replace(",","-", str_replace("/","-", str_replace(" ","-",strtolower($produto[1]))));
	$result['product']['variants'][0]['price'] = $produto[0];
	$result['product']['variants'][0]['stock']['quantity'] = $produto[3];
	$result['product']['descriptionGroup']['descriptionOne'] = '';
	$result['product']['descriptionGroup']['descriptionTwo'] = '';
	$result['product']['descriptionGroup']['descriptionThree'] = '';
	$result['product']['descriptionGroup']['descriptionFour'] = '';
	$result['product']['metaTags']['title'] = str_replace(",","-", str_replace("/","-", str_replace(" ","-",strtolower($produto[1]))));
	$result['product']['metaTags']['url'] = '';
	$result['product']['metaTags']['keyword'] = '';
	$result['product']['metaTags']['description'] = '';
	$result['product']['metaTags']['trackingId'] = '';
	
	$quantidadeFotos = sizeof($produto) - 9;
	
	if($quantidadeFotos >= 1){
		$result['product']['images'][0] = $produto[9];
		$result['product']['images'][1]['url'] = $produto[9];
		$result['product']['images'][1]['isMaster'] = 1;
		$result['product']['images'][2]['url'] = $produto[9];
	}else{
		$result['product']['images'][0] = "";
		$result['product']['images'][1]['url'] = "";
		$result['product']['images'][1]['isMaster'] = 1;
		$result['product']['images'][2]['url'] = "";
	}

	if($quantidadeFotos >= 2){
		$indiceFotos = 2;
		$indiceArrayProduto = 10;
	}
	
	for($i=1; $i < $quantidadeFotos; $i++) { 
		$result['product']['images'][$indiceFotos]['url'] = $produto[$indiceArrayProduto];
		$indiceFotos ++;
		$indiceArrayProduto++;
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