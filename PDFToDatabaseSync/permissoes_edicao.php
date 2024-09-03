<?php
$APP = 'EXAMPLE_APP';
$PATH = '../';
$CONN = ['DATABASE_CONN'];
include_once $PATH.'_connect.php';
include_once $PATH.'_security.php';
include_once $PATH.'_classes/general.php';
$APP_PERMISSIONS = $ADMIN_PERMISSIONS[$APP]['ALL'];
$general_class = new General();

// Check user
$user_id = (!empty($_GET['user_id'])) ? $_GET['user_id'] : 0;
$result_user = sqlsrv_query($conn, "SELECT * FROM [dbo].user_aux WHERE id = '".$user_id."'");
if (!$APP_PERMISSIONS['admin'] || !sqlsrv_has_rows($result_user)) {
    header('Location: permissions.php');
    exit;
}
extract(sqlsrv_fetch_array($result_user), EXTR_PREFIX_ALL, 'usr');

$result_permissions = sqlsrv_query($conn, "SELECT * FROM [dbo].permissions WHERE app_key='$APP' AND user_id='$user_id'");
if (sqlsrv_has_rows($result_permissions)) {
    extract(sqlsrv_fetch_array($result_permissions), EXTR_PREFIX_ALL, 'pms');
}
$tag_admin = !empty($pms_admin) ? '<span class="tag tag-purple">Administrator</span>' : '<span class="tag tag-gray">User</span>';
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Permission | Example</title>
        <meta name="keywords" content="Backoffice, Example">
        <meta name="description" content="Example - IT Department">
        <?php $VENDORS=['SELECT2','MULTISELECT']; include_once $PATH.'_head.php'; ?>
    </head>

    <body>
        <?php include_once $PATH.'_header.php';?>
        <article>
            <?php $SEP='permissions'; include_once $PATH.'_menu.php'; ?>
            <div class="article-content">
                <div class="page-title">Edit Permissions - <?php echo $usr_fullname.' ('.$usr_email.' | '.$usr_company.')&ensp;'.$tag_admin; ?></div>
                <form id="FORM" class="check-circle" method="POST" enctype="multipart/form-data" action="permissions_ajax.php">
                    <input type="hidden" name="form" value="edit_form">
                    <input type="hidden" name="user_id" value="<?php echo $usr_id; ?>">
                    <div class="row">
                        <div class="col-lg-6">
                            <div>
                                <label class="label">JLR or MB Permission</label>
                                <select class="select2" name="permission_value" onchange="toggleLocalWips(this);">
                                    <option value="" selected>&nbsp;</option>
                                    <option value="JLR" <?php if(isset($pms_permission_value) && $pms_permission_value == 'JLR'){ echo 'selected'; }?>>JLR</option>
                                    <option value="JLR_DESC" disabled>Employees belonging to JLR.</option>
                                    <option value="MB" <?php if(isset($pms_permission_value) && $pms_permission_value == 'MB'){ echo 'selected'; }?>>MB</option>
                                    <option value="MB_DESC" disabled>Employees belonging to MB.</option>
                                </select>
                            </div>
                            <div>
                                <label class="label">Additional</label>
                                <div class="height-10"></div>
                                <?php if (!empty($pms_id)) { ?>
                                    <div class="height-10"></div>
                                    <input type="checkbox" name="active" id="check_active" <?php if(!empty($pms_active)) echo "checked";?>>
                                    <label for="check_active"><span></span><b>Active Permission</b></label>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix height-20"></div>
                    <button class="btn btn-green float-right" type="submit"><i class="fas fa-check"></i>&ensp;Save</button>
                    <label class="width-10 height-40 float-right"></label>
                    <a href="permissions.php" class="btn btn-red float-right"><i class="fas fa-times"></i>&ensp;Cancel</a>
                    <?php if (!empty($pms_id)) { ?>
                        <label class="width-10 height-40 float-right"></label>
                        <button class="btn btn-blue float-right" type="button" title="Allows editing permissions." onclick="changePermission('admin', '<?php echo $user_id; ?>')"><?php if(!empty($pms_admin)) { ?><i class="fas fa-lock"></i>&ensp;Administrator<?php }else{ ?><i class="fas fa-unlock-alt"></i>&ensp;User<?php } ?></button>
                    <?php } ?>
                    <div class="clearfix"></div>
                    <div class="height-20"></div>
                    <label id="labelSuccess" class="alert-100 alert-green display-none"><span id="spanSuccess">Saved successfully.</span> <i class="fas fa-times" onclick="$(this).parent().hide();"></i></label>
                    <label id="labelWarning" class="alert-100 alert-yellow display-none"><span id="spanWarning"></span> <i class="fas fa-times" onclick="$(this).parent().hide();"></i></label>
                    <label id="labelError" class="alert-100 alert-red display-none"><span id="spanError"></span> <i class="fas fa-times" onclick="$(this).parent().hide();"></i></label>
                </form>  
            </div>
        </article>
        <?php include_once $PATH.'_footer.php'; ?>
        <?php include_once $PATH.'_assets.php'; ?>
        <script type="text/javascript">
        // ----------------------------------------------------------------
        // SELECT2
        // ----------------------------------------------------------------
        $('.select2').select2();

        // ----------------------------------------------------------------
        // MULTISELECT
        // ----------------------------------------------------------------
        $('.multiselect').multiSelect();

        // ----------------------------------------------------------------
        // CUSTOM
        // ----------------------------------------------------------------
        function toggleLocalWips(element) {
            if (element.value == 'VARIOUS') {
                $('#div_local_wips').show();
            } else {
                $('#div_local_wips').hide();
            }
        }

        function changePermission(permission, user_id, company_key=''){
            $.ajax({
                type: "POST",
                url: 'permissions_ajax.php',
                data: { form:'edit', user_id:user_id, permission:permission, company_key:company_key }
            })
            .done(function(response) {
                response = response.replace(/^\s+|\s+$/g,"");
                if(response=='success'){
                    $.notific8('Successfully edited.', {heading:'Edited'});
                    setTimeout(function(){ location.reload(); }, 1000);
                } else {
                    $.notific8(response, {heading:'Error', color:'ruby'});
                }
            });
        }

        $('#FORM').on('submit',function(e) {
            $('#loading').show();
            $("#labelSuccess").hide();
            $("#labelWarning").hide();
            $("#labelError").hide();
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
                if(response=='success'){
                    $("#spanSuccess").html('Saved successfully.');
                    $("#labelSuccess").show();
                    // $.notific8('Document added successfully.', {heading:'Added'});
                    // setTimeout(function(){ location.reload(); }, 1000);
                }  
                else {
                    $("#spanError").html(response);
                    $("#labelError").show();
                    // $.notific8(response, {heading:'Error', color:'ruby'});
                }
                $('#loading').hide();
            });
        });
        </script>
    </body>
</html>
