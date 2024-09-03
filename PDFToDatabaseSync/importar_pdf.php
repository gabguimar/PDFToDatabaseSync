<?php
$APP = 'AppName';
$PATH = '../';
$CONN = ['DatabaseConnection'];
include_once $PATH.'_connect.php';
include_once $PATH.'_security.php';
// include_once $PATH.'_classes/general.php';
$APP_PERMISSOES = $ADMIN_PERMISSOES[$APP]['ALL'];
// $general_class = new General();
$brand = !empty($_GET['brand']) ? $_GET['brand'] : '';
?>
<!doctype html>
<html lang="pt-pt">
<head>
    <meta name="csrf-token" content="XYZ123">
    <meta charset="utf-8">
    <title>PDF Import | GenericCompany</title>
    <meta name="keywords" content="Backoffice, GenericCompany">
    <meta name="description" content="GenericCompany - IT Department">
    <?php include_once $PATH.'_head.php'; ?>
</head>

<body>
    <?php include_once $PATH.'_header.php';?>
    <article>
        <?php $SEP='import_pdf'; include_once $PATH.'_menu.php';?>
        <div class="article-conteudo">
            <div class="page-titulo">Import PDF</div>
            <form id="form_inserir_pdf" enctype="multipart/form-data" action="import_pdf_ajax.php">
                <input type="hidden" name="form" value="inserir">
                <div class="row">
                    <div class="col-lg-4">
                        <label class="lb">Document*</label>
                        <div>
                            <div class="div-50">
                                <div class="div-50" id="documento"><label for="selecao-arquivo-documento" class="a-dotted-white overflow-hidden nowrap" id="uploads_documento">&nbsp;</label></div>
                                <label for="selecao-arquivo-documento" class="lb-40 bt-azul float-right"><i class="fas fa-upload"></i></label>
                                <input id="selecao-arquivo-documento" type="file" name="documento" accept="application/pdf" onchange="lerFicheiros(this,'uploads_documento');">
                            </div>
                            <label class="lb-40 bt-azul float-right" onclick="limparFicheiros('documento');"><i class="fa fa-trash-alt"></i></label>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <button type="submit" id="inserir_pdf" class="bt bt-verde float-left mt-5"><i class="fas fa-file-pdf"></i>&ensp;Insert&ensp;PDF</button>
                    </div>
                </div>
            </form>
            <div class="clearfix height-20"></div>
            <label id="labelErro" class="av-100 av-vermelho display-none"><span id="spanErro"></span> <i class="fas fa-times" onclick="$(this).parent().hide();"></i></label>
        </div>
    </article>
    <?php include_once $PATH.'_footer.php'; ?>
    <?php include_once $PATH.'_assets.php'; ?>
    <script>
        // ----------------------------------------------------------------
        // CUSTOM
        // ----------------------------------------------------------------
        $('#form_inserir_pdf').on('submit',function(e) {
            $('#loading').show();
            var form = $(this);
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: form.attr('action'),
                data: new FormData(this),
                contentType: false,
                processData: false,
                cache: false
            })
            .done(function(response){
                response = response.replace(/^\s+|\s+$/g,"");
                if(response == 'success'){
                    $.notific8('Document added successfully.', {heading:'Added'});
                }  
                else {
                    $("#spanErro").html(response);
                    $("#labelErro").show();
                }
                $('#loading').hide();
            });
        });
        /****************************************************************************
            Functions to manage file reading and clearing for attachment
        ****************************************************************************/
        function lerFicheiros(input, id) {
            var quantity = input.files.length;
            var name = input.value;
            var ref = id.replace("uploads_", "");
            if(quantity == 0){ limparFicheiros(ref) }
            else if(quantity == 1){ $('#'+id).html(name); }
            else { $('#'+id).html(quantity+' files selected'); }
        }
        function limparFicheiros(ref) {
            $('#selecao-arquivo-'+ref).val('');
            $('#uploads_'+ref).html('&nbsp;');
            // $('#'+ref+'_old').val('');
            $('#'+ref).html('<label for="selecao-arquivo-documento" class="a-dotted-white overflow-hidden nowrap" id="uploads_'+ref+'">&nbsp;</label>');
        }
    </script>
</body>
</html>
