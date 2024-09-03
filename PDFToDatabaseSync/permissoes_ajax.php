<?php
$APP = 'EXAMPLE_APP';
$PATH = '../';
$CONN = ['CONNECTION'];
include_once $PATH.'_connect.php';
include_once $PATH.'_security.php';
include_once $PATH.'_classes/general.php';
$APP_PERMISSIONS = $ADMIN_PERMISSIONS[$APP];

$general_class = new General();

$form = isset($_POST["form"]) ? $_POST["form"] : '';

##################################### edit_form #####################################
if ($form == 'edit_form') {
    $user_id = $_POST["user_id"];
    $permission_value = (!empty($_POST["permission_value"])) ? $_POST["permission_value"] : '';
    $active = isset($_POST["active"]) ? 1 : 0;

    $result = sqlsrv_query($conn, "SELECT TOP 1 * FROM [dbo].permissions WHERE app_key='$APP' AND user_id='$user_id'");
    
    if (sqlsrv_has_rows($result)) {
        $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
        $id = $row['id'];
        $query = "UPDATE [dbo].permissions SET permission_value='$permission_value', active='$active', updated_at=CURRENT_TIMESTAMP, updated_by='$ADMIN_ID' WHERE id='$id'";
        if (sqlsrv_query($conn, $query) === false) { echo $query; return; }
    } else {
        $row_admin = sqlsrv_fetch_array(sqlsrv_query($conn, "SELECT [admin] FROM [dbo].permissions WHERE app_key='$APP' AND user_id='$user_id'"));
        $admin = !empty($row_admin['admin']) ? 1 : 0;

        $query = "INSERT INTO [dbo].permissions (user_id, app_key, [admin], permission_value, created_by) VALUES ('$user_id', '$APP', '$admin', '$permission_value', '$ADMIN_ID')";
        if (sqlsrv_query($conn, $query) === false) { echo $query; return; }

        $id = sqlsrv_fetch_array(sqlsrv_query($conn, "SELECT TOP 1 * FROM [dbo].permissions WHERE app_key='$APP' AND user_id='$user_id'"))['id'];
        $reload = TRUE;
    }

    echo 'success';
}

##################################### remove #####################################
if ($form == 'remove') {
    $id = $_POST["id"];
    $user_id = $_POST["user_id"];
    $check_all = !empty($_POST["check_all"]) ? 1 : 0;

    if ($id && $user_id) {
        $where = (!$check_all) ? " AND id='$id'" : "";
        //sqlsrv_query($conn, "DELETE FROM [dbo].permissions WHERE id = '$id'");
        $query = "UPDATE [dbo].permissions SET active='0', updated_at=CURRENT_TIMESTAMP, updated_by='$ADMIN_ID' WHERE app_key='$APP' AND user_id='$user_id' $where";
        if (sqlsrv_query($conn, $query) === false) { echo $query; return; }
        echo 'success';
    } else {
        echo 'Unable to remove permission, please try again!';
        return;
    }
}

##################################### update_permissions #####################################

// Clear the session to refresh it through the cookie in security
if (!empty($user_id) && ($user_id == $ADMIN_ID)) {
    unset($_SESSION['admin_session']);
}
?>
