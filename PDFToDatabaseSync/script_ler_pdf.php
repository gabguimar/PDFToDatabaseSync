<?php
$APP = 'MTGRT';
$PATH = '../';
$CONN = ['CTL'];
include_once $PATH . '_connect.php';
include_once $PATH . '_security.php';
include_once $PATH . '_vendor/pdfparser/autoload.php';
include_once $PATH . '_classes/geral.php';

use Smalot\PdfParser\Parser;

$parser = new Parser(); 
$geral = new Geral();

$directory = 'teste_pdf/'; // Alterar para o diretório adequado

// Verificar se o diretório existe
if (!is_dir($directory)) {
    die("O diretório especificado não existe.");
}

// Abrir o diretório
if ($handle = opendir($directory)) {

    // Loop sobre os arquivos no diretório
    while (false !== ($file = readdir($handle))) {
        // Ignorar os diretórios . e ..
        if ($file !== "." && $file !== ".." && pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {

            $filePath = $directory . $file; // Caminho completo do arquivo
            $pdf = $parser->parseFile($filePath); // Parsear o arquivo PDF
            $textContent = $pdf->getText(); // Extrair o texto do PDF

            $array = $geral->arrayMap(explode("|", $textContent));

            $tipo_pdf = '';
            $tipo_doc = '';

            // Identificar o tipo de PDF
            if (trim($array[2]) === 'Abono de Garantias' || trim($array[2]) === 'Factura de Garantias') {
                $tipo_pdf = 'JLR';
            } elseif (trim($array[4]) === 'FATURA') {
                $tipo_pdf = 'MB';
                $tipo_doc = 'fatura';
            } elseif (trim($array[2]) === 'NOTA DE CRÉDITO') {
                $tipo_pdf = 'MB';
                $tipo_doc = 'nota';
            }

            $nome_pdf = pathinfo($file, PATHINFO_FILENAME);

            // Processar PDFs do tipo MB
            if ($tipo_pdf === 'MB') {
                $keys = [
                    'num_linha',
                    'num_referencia',
                    'descricao_mb',
                    'quantidade_mb',
                    'preco_unitario_mb',
                    'desconto_mb',
                    'total_liquido',
                    'taxa_iva'
                ];

                $aux_transporte = $gravar = $passaProximaLinha = false;
                $contadorlinha = 0;
                $celula = [];
                $i = 0;
                $nif = 0;

                if ($tipo_doc === 'fatura') {
                    foreach ($array as $key => $text) {
                        if (!$nif && trim($text) === 'Número') {
                            $nif = $array[$key - 1];
                            $factura_no = $array[$key + 1];
                            $data_factura = $array[$key + 2];
                        }
                    }

                    foreach ($array as $key => $text) {
                        if ($passaProximaLinha) {
                            $passaProximaLinha = false;
                            continue;
                        }

                        $text = trim($text);

                        if (str_contains($text, 'N. Guia')) {
                            $nr_guia = explode(' ', $text)[5];
                            $gravar = true;
                            $contador++;
                            continue;
                        }

                        if (!$gravar && str_contains($text, 'Transporte')) {
                            if (!is_numeric(str_replace(',', '.', $array[$key + 2]))) {
                                $aux_transporte = $gravar = false;
                            } else {
                                $aux_transporte = $gravar = true;
                            }
                            continue;
                        }

                        if ($gravar) {
                            if ($aux_transporte && !$text) {
                                continue;
                            }

                            $aux_transporte = false;

                            if (str_contains($text, 'A transportar')) {
                                $gravar = false;
                                continue;
                            }

                            if (str_contains($text, 'TOTAL DA GUIA')) {
                                $contador++;
                                $contadorlinha = 0;
                                $gravar = false;
                                continue;
                            }

                            if (trim($text)) {
                                $celula[$i] = trim($text);

                                if ($contadorlinha === 2 && !is_numeric(str_replace(',', '.', $array[$key + 1]))) {
                                    $celula[$i] .= ' ' . $array[$key + 1];
                                    $passaProximaLinha = true;
                                }
                                $i++;
                                $contadorlinha++;
                            }

                            if ($contadorlinha === 8) {
                                $contador++;
                                $contadorlinha = 0;

                                foreach ($keys as $var) {
                                    $$var = !empty($celula[$valor]) ? $celula[$valor] : '';
                                    $valor++;
                                }

                                // Inserir na base de dados
                                $query = "INSERT INTO [dbo].garantias_jlr_mb (
                                    nif, num_fatura, data_fatura, pdf, brand, num_linha, 
                                    total_liquido, taxa_iva, descricao_mb, 
                                    num_referencia, desconto_mb, preco_unitario_mb, quantidade_mb,
                                    criado_por
                                ) VALUES (
                                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                                )";
                                $params = [
                                    trim($nif), trim($factura_no), trim($data_factura), trim($nome_pdf), trim($tipo_pdf),
                                    trim($num_linha), trim($total_liquido), trim($taxa_iva), trim($descricao_mb),
                                    trim($num_referencia), trim($desconto_mb), trim($preco_unitario_mb), trim($quantidade_mb),
                                    $ADMIN_ID
                                ];
                                $stmt = sqlsrv_prepare($conn, $query, $params);

                                if (!$stmt) {
                                    die(print_r(sqlsrv_errors(), true));
                                }

                                $result = sqlsrv_execute($stmt);

                                if ($result === false) {
                                    die(print_r(sqlsrv_errors(), true));
                                }
                                
                                $celula = [];
                                $i++;
                                $valor++;
                            }
                        }
                    }
                } else {
                    foreach ($array as $key => $text) {
                        if (!$nif && trim($text) === 'Número') {
                            $nif = $array[$key - 1];
                            $factura_no = $array[$key + 1];
                            $data_factura = $array[$key + 2];
                        }
                    }

                    foreach ($array as $key => $text) {
                        if (str_contains($text, 'IVA')) {
                            $gravar = true;
                            $contador++;
                            continue;
                        }
                    }
                }
            } elseif ($tipo_pdf === 'JLR') {
                $keys = [
                    'num_linha',
                    'num_sap_reclamacao',
                    'num_reclamacao',
                    'version',
                    'cod_programa',
                    'data_reparacao',
                    'manusemento',
                    'diversos',
                    'gasto_transporte',
                    'pecas',
                    'mao_de_obra',
                    'total_liquido',
                    'taxa_iva'
                ];

                foreach ($array as $key => $text) {
                    if (trim($text) === 'Factura No') {
                        $num_fatura = $array[$key - 1];
                    }
                    if (trim($text) === 'Data Factura') {
                        $data_fatura = $array[$key + 1];
                    }
                    if (trim($text) === 'Codigo Cliente') {
                        $cod_cliente = $array[$key - 1];
                    }
                    if (trim($text) === 'ncia no.') {
                        $num_referencia = $array[$key + 1];
                    }
                }

                $contador = $contadorlinha = 0;
                $gravar = $passaProximaLinha = false;

                foreach ($array as $key => $text) {
                    if ($passaProximaLinha) {
                        $passaProximaLinha = false;
                        continue;
                    }
                    if (trim($text) === 'Taxa do IVA') {
                        $gravar = true;
                        $contadorlinha = 0;
                        continue;
                    }
                    if (str_contains($text, 'Contato:') || str_contains($text, 'Total Liquido')) {
                        $gravar = false;
                        continue;
                    }
                    if ($gravar) {
                        if ($contadorlinha === 1 && strlen($text) >= 8) {
                            $text .= trim($array[$valor]);
                            $passaProximaLinha = true;
                        }

                        $celula[$i] = $text;
                        $i++;
                        $contadorlinha++;

                        if ($contadorlinha >= 13) {
                            foreach ($keys as $key => $var) {
                                $$var = $celula[$valor];
                                $valor++;
                            }

                            // Formatando as variáveis
                            $nova_data_reparacao = $geral->getDataFormatada(str_replace('.', '-', $data_reparacao));
                            $nova_pecas = str_replace(',', '.', $pecas);
                            $nova_gasto_transporte = str_replace(',', '.', $gasto_transporte);
                            $nova_mao_de_obra = str_replace(',', '.', $mao_de_obra);
                            $nova_total_liquido = str_replace(',', '.', $total_liquido);
                            $nova_taxa_iva = str_replace(',', '.', $taxa_iva);

                            // Inserir na base de dados
                            $query = "INSERT INTO [dbo].garantias_jlr_mb (
                                pdf, brand, num_linha, num_sap_reclamacao, num_reclamacao, version, cod_programa, 
                                data_reparacao, manusemento, diversos, gasto_transporte, pecas, 
                                mao_de_obra, total_liquido, taxa_iva, num_fatura, data_fatura, 
                                cod_cliente, num_referencia, criado_por
                            ) VALUES (
                                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                            )";
                            $params = [
                                trim($nome_pdf), trim($tipo_pdf), trim($num_linha), trim($num_sap_reclamacao), trim($num_reclamacao),
                                trim($version), trim($cod_programa), trim($nova_data_reparacao), trim($manusemento),
                                trim($diversos), trim($nova_gasto_transporte), trim($nova_pecas), trim($nova_mao_de_obra),
                                trim($nova_total_liquido), trim($nova_taxa_iva), trim($num_fatura), trim($data_fatura),
                                trim($cod_cliente), trim($num_referencia), $ADMIN_ID
                            ];
                            $stmt = sqlsrv_prepare($conn, $query, $params);

                            if (!$stmt) {
                                die(print_r(sqlsrv_errors(), true));
                            }

                            $result = sqlsrv_execute($stmt);

                            if ($result === false) {
                                die(print_r(sqlsrv_errors(), true));
                            }

                            $contadorlinha = 0;
                            $celula = [];
                            $i++;
                            $valor++;
                        }
                    }
                }
            }
        }
    }

    closedir($handle); // Fechar o diretório
} else {
    die("Não foi possível abrir o diretório.");
}
?>
