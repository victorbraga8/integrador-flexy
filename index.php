<?php
ini_set('display_errors', 'Off');
require_once('includes/functions/head.php');
require_once('includes/functions/functions.php');

$textoBtn = 'Enviar XLS';    	
$styleBnt = 'btn-info';

$styleBnt2 = 'btn-success';
$textoBtn2 = 'Baixar XLS Base';

if($_POST['integracao']){
	$limparTela = '<a href= "index.php"><button type="button" class="btn btn-warning" style="margin-top:50px;">Limpar Tela</button></a>';
	if($_FILES){
		$ftp = ftp();
		ftp_pasv($ftp["conn"], true);

		$quantidadeAnexos = sizeof($_FILES);
		$fotosProdutos = array();

		for($i=1; $i < $quantidadeAnexos+1; $i++){ 
			if(strlen($_FILES[$i.'-arquivos']['name'][0]) > 0 ){
				$quantidadeArquivos = sizeof($_FILES[$i.'-arquivos']['name']);
				for($j=0; $j < $quantidadeArquivos; $j++){ 
					$nomeArquivo = "arquivo-".$i."-".$_FILES[$i.'-arquivos']["name"][$j];
					$enviaArquivo = ftp_put($ftp["conn"], "/domains/victorbraga.com.br/public_html/uploads/".$nomeArquivo, $_FILES[$i.'-arquivos']["tmp_name"][$j], FTP_BINARY);
					$arquivoFtp = "https://www.victorbraga.com.br/uploads/".$nomeArquivo;
					// $arquivoFtp = $nomeArquivo;
					array_push($fotosProdutos, $arquivoFtp);
				}
			}
		}
		
		unset($_POST['integracao']);
		unset($_POST['quantidadeProdutos']);	

		$dados = array_chunk($_POST, 11);
		$produtosEnvio = array();

		$j = 1;
		for($i=0; $i < sizeof($dados); $i++) { 
			$produtosEnvio['arquivos-'.$j] = $dados[$i];
			foreach($fotosProdutos as $foto){
				if (stripos($foto, "arquivo-".$j) !== false){
					array_push($produtosEnvio['arquivos-'.$j], $foto);
				}
			}
			array_push($produtosEnvio, $produtosEnvio['arquivos-'.$j]);
			unset($produtosEnvio[$i]);
			$j++;
		}	
		
		foreach ($produtosEnvio as $produto) {
			$json = montaProduto($produto);
			$resultado = enviaProduto($json);
			if(stripos($resultado, "200") !== false || stripos($resultado, "201") !== false || stripos($resultado, "202") !== false ){
				echo "<p>Produto Integrado: - ".$produto[1].'</p>';
			}else{
				echo "<p>Erro: ".$produto[1].'</p>';				
			}
			sleep(5);
			echo $limparTela;			
		}
	}
		
	die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['xls_file'])) {
    $uploadedFile = $_FILES['xls_file']['tmp_name'];
    $dadosLidos = lerDadosDoXLSX($uploadedFile);

    if($dadosLidos){
    	$textoBtn = 'XLS Enviado';
    	$disabled = 'disabled';
    	$display = 'style="display:none;"';
    	$styleBnt = 'btn-danger';    	
    }else{
    	$textoBtn = 'Enviar XLS';    	
    	$styleBnt = 'btn-info';
    }
    
}

?>

<body>
	<div class="container">	
		<div class="row" style="margin-left: unset; justify-content: center; margin-top: 50px;">
		    <form action="" method="POST" enctype="multipart/form-data">
			    <div class="file-input-wrapper">			    	
			    	<input <?=$display?> class="btn <?=$styleBnt?>" type="submit" value="<?=$textoBtn?>" <?=$disabled?>> 			    	
			        <input <?=$display?> class="xls_file" type="file" id="myFile" name="xls_file" required>
			        <label <?=$display?> class="file-input-label" for="myFile" data-label="Arquivo Selecionado"></label>
			    </div>        
		    </form> 
	    </div> 
	    <div class="row" style="margin-top: 150px;">
	    	<div class="col-sm-12">
	    		<label <?=$display?>>Não possui o XLS Base ? <br> Clique no Botão abaixo e faça o download do arquivo.</label><br>
	    		<a href="includes/functions/download.php">
	    			<input <?=$display?> class="btn <?=$styleBnt2?>" type="submit" value="<?=$textoBtn2?>" <?=$disabled?>> 
	    		</a>
	    	</div>
	    </div>
    
<?php 
	if(isset($dadosLidos)){
?>
<form action="" method="post" enctype="multipart/form-data">
	<table class="table">
		 <thead class="thead-dark">
		    <tr>
		      <th scope="col">#</th>
		      <th scope="col">Produto</th>
		      <th scope="col">Preço</th>		      
		      <th scope="col">Imagens</th>
		    </tr>
		  </thead>
<?php	
		$c = 1;	
		foreach ($dadosLidos as $dadoLido) {
?>			
		  <tbody>
		    <tr>
		      <th scope="row"><?=$c?></th>
		      <td><?=$dadoLido[2]?></td>
		      <td><?=$dadoLido[0]?></td>
		      <td><input type="file" name="<?=$c?>-arquivos[]" multiple></td>
		    </tr>
		    <input type="hidden" name="<?=$c?>-preco" value="<?=$dadoLido[0]?>">		    
		    <input type="hidden" name="<?=$c?>-nome" value="<?=$dadoLido[2]?>">
		    <input type="hidden" name="<?=$c?>-categoria" value="<?=$dadoLido[3]?>">
		    <input type="hidden" name="<?=$c?>-categorialoja" value="<?=$dadoLido[4]?>">
		    <input type="hidden" name="<?=$c?>-quantidade" value="<?=$dadoLido[5]?>">
		    <input type="hidden" name="<?=$c?>-codtotvs" value="<?=$dadoLido[6]?>">
		    <input type="hidden" name="<?=$c?>-ncm" value="<?=$dadoLido[7]?>">
		    <input type="hidden" name="<?=$c?>-ean" value="<?=$dadoLido[8]?>">
		    <input type="hidden" name="<?=$c?>-descricao1" value="<?=$dadoLido[9]?>">
		    <input type="hidden" name="<?=$c?>-descricao2" value="<?=$dadoLido[10]?>">
		    <input type="hidden" name="<?=$c?>-descricao3" value="<?=$dadoLido[11]?>">		    
		  </tbody>		
<?php			
			$c++;
		}
?>		
	</table>	
	<input type="hidden" name="quantidadeProdutos" value="<?=sizeof($dadosLidos)?>">			
	<input class="btn btn-success" style="margin-right: 10px;" type="submit" name="integracao" value="Executar Integração">
	<a href= "index.php"><button type="button" class="btn btn-warning">Limpar Tela</button></a>
</form>
<?php
	}	
?>
		
    
    </div>
	    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>
