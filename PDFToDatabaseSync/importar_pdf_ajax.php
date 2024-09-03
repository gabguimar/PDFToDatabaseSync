<?php
$APP = 'AppName';
$PATH = '../';
$CONN = ['DatabaseConnection'];
include_once $PATH.'_connect.php';
include_once $PATH.'_security.php';
include_once $PATH.'_classes/general.php';
include_once $PATH.'_vendor\pdfparser\autoload.php';
$APP_PERMISSOES = $ADMIN_PERMISSOES[$APP]['ALL'];
$parser = new \Smalot\PdfParser\Parser(); 
$general = new General();

$form = isset($_POST["form"]) ? $_POST["form"] : '';

// Verifica se o formulário foi enviado com a ação 'inserir'
if ($form == "inserir") {

    // Obtém informações do arquivo enviado
    $temp_file = $_FILES['documento']['tmp_name'];  // Caminho temporário do arquivo
    $file_size = $_FILES['documento']['size'];      // Tamanho do arquivo em bytes
    $file_name = $_FILES['documento']['name'];      // Nome original do arquivo
    $extension = strtolower(strrchr($file_name, '.')); // Extensão do arquivo em minúsculas
    
    // Verifica se a extensão do arquivo é PDF
    if (strstr('.pdf', $extension)) {

        // Verifica se o tamanho do arquivo é menor que 5MB
        if ($file_size < 5242880) {

            // Define o caminho de destino para salvar o arquivo, incluindo o ano atual
            $destination = '/path/to/document_storage/' . date('Y') . '/';

            // Cria o diretório se não existir
            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }

            // Define um novo nome para o arquivo com base na data e hora atuais
            $newFileName = date('YmdHis') . "_document_" . $extension;
            $destination .= $newFileName;

            // Move o arquivo enviado para o destino definido
            if (@move_uploaded_file($temp_file, $destination)) {

                // URL do arquivo
                $url = $general->getURL() . "document_storage/" . date('Y') . "/" . $newFileName;

                $query1 = "SET NOCOUNT ON;
                           INSERT INTO [dbo].documents (file_url, pdf_name, file_path, created_by)
                           VALUES ('$url', '$newFileName', '$destination', '$ADMIN_ID');
                           SELECT SCOPE_IDENTITY() AS id;";
                $result1 = sqlsrv_query($conn, $query1);

                // Obtém o ID do novo registro inserido
                if ($result1) {
                    $row1 = sqlsrv_fetch_array($result1, SQLSRV_FETCH_ASSOC);
                } else {
                    echo "Erro ao inserir os dados na base de dados.";
                }
            } else {
                echo "Não foi possível adicionar o arquivo, tente novamente mais tarde.";
                return;
            }
        } else {
            echo "O tamanho do arquivo é superior ao permitido (5MB).";
            return;
        }
    } else {
        echo "O tipo de arquivo selecionado <b>$extension</b> não é suportado.";
        return;
    }

    // Inclui o script leitor_pdf.php que extrai o arquivo PDF e inclui as informações na BD
    include 'leitor_pdf.php';
    return;
}
?>
