<?php include('db_connect.php');?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-md-4">
                <form action="" id="manage-allowances">
                    <div class="card">
                        <div class="card-header">
                            Allowances Form
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="id">
                            <div class="form-group">
                                <label class="control-label">Allowance</label>
                                <textarea name="allowance" cols="30" rows="2" class="form-control" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Description</label>
                                <textarea name="description" cols="30" rows="2" class="form-control" required></textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-12">
                                    <button class="btn btn-sm btn-primary col-sm-3 offset-md-3"> Save</button>
                                    <button class="btn btn-sm btn-default col-sm-3" type="button" onclick="_reset()"> Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Allowance Information</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "EXEC sp_show_allowances";
                                $stmt = sqlsrv_query($conn, $sql);

                                if ($stmt === false) {
                                    die(print_r(sqlsrv_errors(), true));
                                }

                                $i = 1;
                                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)):
                                    ?>
                                    <tr>  
                                        <td><?php echo $i++; ?></td>
                                        <td>
                                            <p>Name: <b><?php echo $row['allowance'] ?></b></p>
                                            <p class="truncate"><small>Description: <b><?php echo $row['description'] ?></b></small></p>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-primary edit_allowances" type="button" data-id="<?php echo $row['id'] ?>" data-allowance="<?php echo $row['allowance'] ?>" data-description="<?php echo $row['description'] ?>">Edit</button>
                                            <button class="btn btn-sm btn-danger delete_allowances" type="button" data-id="<?php echo $row['id'] ?>">Delete</button>
                                        </td>
                                    </tr>  
                                    <?php endwhile;
                                sqlsrv_free_stmt($stmt);
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>  
</div>

<style>
    td { vertical-align: middle !important; }
    td p { margin: unset; }
    img { max-width: 100px; max-height: 150px; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function _reset(){
        $('[name="id"]').val('');
        $('#manage-allowances').get(0).reset();
    }
    
    $('#manage-allowances').submit(function(e){
        e.preventDefault()
        $.ajax({
            url: 'ajax.php?action=save_allowances',
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            success: function(resp){
                if(resp == 1){
                    Swal.fire('Success', 'Data successfully added', 'success').then(() => location.reload());
                } else if(resp == 2){
                    Swal.fire('Success', 'Data successfully updated', 'success').then(() => location.reload());
                }
            }
        })
    })

    $('.edit_allowances').click(function(){
        var form = $('#manage-allowances')
        form.get(0).reset()
        form.find("[name='id']").val($(this).attr('data-id'))
        form.find("[name='allowance']").val($(this).attr('data-allowance'))
        form.find("[name='description']").val($(this).attr('data-description'))
    })

    $('.delete_allowances').click(function(){
        let id = $(this).attr('data-id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                delete_allowances(id);
            }
        })
    })

    function delete_allowances(id){
        $.ajax({
            url: 'ajax.php?action=delete_allowances',
            method: 'POST',
            data: {id: id},
            success: function(resp){
                if(resp == 1){
                    Swal.fire('Deleted!', 'Data has been deleted.', 'success').then(() => location.reload());
                }
            }
        })
    }
</script>
