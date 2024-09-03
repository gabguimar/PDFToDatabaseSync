<?php
$APP = 'ImageUploader';
$PATH = '../';
$CONN = ['DatabaseConnection'];
include_once $PATH.'_connect.php';
include_once $PATH.'_security.php';
// include_once $PATH.'_classes/general.php';
$APP_PERMISSOES = $ADMIN_PERMISSOES[$APP]['ALL'];
// $general_class = new General();
$category = !empty($_GET['category']) ? $_GET['category'] : '';
?>
<!doctype html>
<html lang="pt-pt">
<head>
    <meta name="csrf-token" content="ABC123">
    <meta charset="utf-8">
    <title>Image Upload | GenericCompany</title>
    <meta name="keywords" content="Backoffice, GenericCompany, Image Upload">
    <meta name="description" content="GenericCompany - Image Upload System">
    <?php include_once $PATH.'_head.php'; ?>
</head>

<body>
    <?php include_once $PATH.'_header.php';?>
    <article>
        <?php $SEP='upload_image'; include_once $PATH.'_menu.php';?>
        <div class="article-conteudo">
            <div class="page-titulo">Upload Image</div>
            <form id="form_inserir_imagem" enctype="multipart/form-data" action="upload_image_ajax.php">
                <input type="hidden" name="form" value="inserir">
                <div class="row">
                    <div class="col-lg-4">
                        <label class="lb">Image*</label>
                        <div>
                            <div class="div-50">
                                <div class="div-50" id="imagem"><label for="selecao-arquivo-imagem" class="a-dotted-white overflow-hidden nowrap" id="uploads_imagem">&nbsp;</label></div>
                                <label for="selecao-arquivo-imagem" class="lb-40 bt-azul float-right"><i class="fas fa-upload"></i></label>
                                <input id="selecao-arquivo-imagem" type="file" name="imagem" accept="image/*" onchange="lerFicheiros(this,'uploads_imagem');">
                            </div>
                            <label class="lb-40 bt-azul float-right" onclick="limparFicheiros('imagem');"><i class="fa fa-trash-alt"></i></label>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <button type="submit" id="inserir_imagem" class="bt bt-verde float-left mt-5"><i class="fas fa-image"></i>&ensp;Upload&ensp;Image</button>
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
        $('#form_inserir_imagem').on('submit',function(e) {
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
                    $.notific8('Image uploaded successfully.', {heading:'Uploaded'});
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
            $('#'+ref).html('<label for="selecao-arquivo-imagem" class="a-dotted-white overflow-hidden nowrap" id="uploads_'+ref+'">&nbsp;</label>');
        }
    </script>
</body>
</html>
