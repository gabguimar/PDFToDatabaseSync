<?php
$APP = 'MTGRT';
$PATH = '../';
$CONN = ['CTL'];
include_once $PATH . '_connect.php';
include_once $PATH . '_security.php';

$APP_PERMISSOES = $ADMIN_PERMISSOES[$APP]['ALL'];

if (!$APP_PERMISSOES['admin']) {
    header('Location: index.php');
    exit;
}

$id_user = isset($_GET['id_user']) ? htmlspecialchars($_GET['id_user']) : '';
?>
<!doctype html>
<html lang="pt-pt">
<head>
    <meta charset="utf-8">
    <title>Permissões | Monitorização Garantias</title>
    <meta name="keywords" content="Backoffice, Carclasse">
    <meta name="description" content="Carclasse - Departamento de Informática">
    <?php $VENDORS = ['DATATABLES', 'SELECT2']; include_once $PATH . '_head.php'; ?>
</head>

<body>
    <?php include_once $PATH . '_header.php'; ?>
    <article>
        <?php $SEP = 'permissoes'; include_once $PATH . '_menu.php'; ?>
        <div class="article-conteudo">
            <div class="page-titulo">Permissões</div>
            <button class="bt bt-verde" type="button" data-toggle="modal" data-target="#myModalNew">
                <i class="fas fa-plus"></i>&ensp;Adicionar
            </button>
            <div class="modulo-table">
                <div class="modulo-scroll">
                    <table class="modulo-body" id="sortable" width="100%">
                        <thead>
                            <tr>
                                <th class="display-none"></th>
                                <th class="width-40" title='ID da permissão'>ID</th>
                                <th title='Username e email do utilizador'>Utilizador</th>
                                <th title='Autorização para utilizadores JLR ou MB'>Brand</th>
                                <th>Opções</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $where = !$ADMIN_SUPERADMIN ? " AND U.superadmin = '0'" : '';
                            $where .= $id_user ? " AND U.id = " . sqlsrv_escape($id_user) : '';
                            $query = "SELECT P.*, U.nome, U.email 
                                      FROM [dbo].permissoes P 
                                      INNER JOIN aux_utilizadores U 
                                      ON P.id_utilizador = U.id AND U.ativo = '1' 
                                      WHERE P.ativa = '1' AND P.key_aplicacao = '$APP' $where 
                                      ORDER BY U.nome ASC";
                            $result = sqlsrv_query($conn, $query);

                            if ($result === false) {
                                die(print_r(sqlsrv_errors(), true));
                            }

                            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) { ?>
                                <tr class="line_user_<?php echo htmlspecialchars($row['id_utilizador'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <td class="display-none"></td>
                                    <td><?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td title="<?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['pa'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="table-opcao">
                                        <a href="permissoes_edicao.php?id_user=<?php echo htmlspecialchars($row['id_utilizador'], ENT_QUOTES, 'UTF-8'); ?>" class="table-opcao">
                                            <i class="fas fa-pencil-alt"></i>&nbsp;Editar
                                        </a>
                                        &ensp;
                                        <span id="del_<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>" class="table-opcao" onclick="mostrarModalRemover('<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($row['id_utilizador'], ENT_QUOTES, 'UTF-8'); ?>');">
                                            <i class="far fa-trash-alt"></i>&nbsp;Remover
                                        </span>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="display-none"></th>
                                <th>ID</th>
                                <th>Utilizador</th>
                                <th>Brand</th>
                                <th>Opções</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </article>

    <!-- Modal New -->
    <div class="modal fade" id="myModalNew" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Adicionar</h4>
                </div>
                <div class="modal-body">
                    <label class="lb">Utilizador</label>
                    <select class="select2InModal" id="modal_utilizador" name="utilizador">
                        <option value="" selected>&nbsp;</option>
                        <?php
                        $result_users = sqlsrv_query($conncsv, "SELECT * FROM [dbo].aux_utilizadores WHERE empresa = 'Carclasse' AND ativo = '1'");
                        if ($result_users === false) {
                            die(print_r(sqlsrv_errors(), true));
                        }

                        while ($row_user = sqlsrv_fetch_array($result_users, SQLSRV_FETCH_ASSOC)) { ?>
                            <option value="<?php echo htmlspecialchars($row_user['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($row_user['nomecompleto'], ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars($row_user['email'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="bt bt-cinza" data-dismiss="modal">
                        <i class="fas fa-times"></i>&ensp;Cancelar
                    </button>
                    <button type="button" class="bt bt-verde float-right" onclick="adicionarPermissao();">
                        <i class="fas fa-plus"></i>&ensp;Adicionar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Delete -->
    <div class="modal fade" id="myModalDelete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Remover</h4>
                </div>
                <div class="modal-body">Tem a certeza que deseja remover esta permissão?</div>
                <div class="modal-footer">
                    <button type="button" class="bt bt-cinza" data-dismiss="modal">
                        <i class="fas fa-times"></i>&ensp;Cancelar
                    </button>
                    <button type="button" class="bt bt-vermelho" data-dismiss="modal" onclick="apagarLinha();">
                        <i class="far fa-trash-alt"></i>&ensp;Remover
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include_once $PATH . '_footer.php'; ?>
    <?php include_once $PATH . '_assets.php'; ?>
    <script type="text/javascript">
        $(document).ready(function () {
            $(".select2InModal").select2({ dropdownParent: $("#myModalNew") });

            // DataTables setup
            $('#sortable tfoot th').each(function () {
                var title = $('#sortable thead th').eq($(this).index()).text();
                if (title) {
                    $(this).html('<input type="text" style="width:100%" class="inputSearch" placeholder="' + title + '"/>');
                }
            });

            var table = $('#sortable').DataTable({
                lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, 'Todos']]
            });

            table.columns().eq(0).each(function (colIdx) {
                $('input', table.column(colIdx).footer()).on('keyup change', function () {
                    table.column(colIdx).search(this.value).draw();
                });
            });
        });

        function mostrarModalRemover(id, id_utilizador) {
            $('#modal_del_id').val(id);
            $('#modal_del_id_utilizador').val(id_utilizador);
            $('#myModalDelete').modal('show');
        }

        function apagarLinha() {
            var id = $('#modal_del_id').val();
            var id_utilizador = $('#modal_del_id_utilizador').val();
            $.ajax({
                type: "POST",
                url: 'permissoes_ajax.php',
                data: { form: 'remover', id: id, id_utilizador: id_utilizador }
            })
            .done(function (resposta) {
                resposta = resposta.trim();
                if (resposta == 'sucesso') {
                    $.notific8('Removido com sucesso.', { heading: 'Removido' });
                    $('#del_' + id).parent().parent().slideUp();
                } else {
                    $.notific8(resposta, { heading: 'Erro', color: 'ruby' });
                }
            });
        }

        function adicionarPermissao() {
            var id_utilizador = $('#modal_utilizador').val();
            if (id_utilizador) {
                window.location.href = 'permissoes_edicao.php?id_user=' + id_utilizador;
            }
        }
    </script>
</body>
</html>
