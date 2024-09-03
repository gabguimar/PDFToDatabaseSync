<?php
$APP = 'AppName';
$PATH = '../';
$CONN = ['DatabaseConnection'];
include_once $PATH.'_connect.php';
include_once $PATH.'_security.php';
// include_once $PATH.'_classes/general.php';
$APP_PERMISSOES = $ADMIN_PERMISSOES[$APP]['ALL'];
// $general_class = new General();
?>
<!doctype html>
<html lang="pt-pt">
<head>
    <meta name="csrf-token" content="XYZ123">
    <meta charset="utf-8">
    <title>Warranty Monitoring | GenericCompany</title>
    <meta name="keywords" content="Backoffice, GenericCompany">
    <meta name="description" content="GenericCompany - IT Department">
    <?php include_once $PATH.'_head.php'; ?>
</head>

<body>
    <?php include_once $PATH.'_header.php';?>
    <article>
        <?php $SEP='index'; include_once $PATH.'_menu.php';?>
        <div class="article-conteudo">
            <div class="page-titulo">Warranty Monitoring</div>
            <div class="row row10"> 
                <div class="col-lg-3 col10">
                    <a class="modulo-botao" href="import_pdf.php">
                        <div class="modulo-botao-txt">Import PDF</div>
                    </a>
                </div>
            </div>
        </div>
    </article>
    <?php include_once $PATH.'_footer.php'; ?>
    <?php include_once $PATH.'_assets.php'; ?>
</body>
</html>
