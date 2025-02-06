<?php include 'db_connect.php'; ?>

<div class="container-fluid">
    <form action="" id="manage-deductions">
        <input type="hidden" name="employee_id" value="<?php echo $_GET['id'] ?? ''; ?>">

        <div class="row form-group">
            <div class="col-md-5">
                <label class="control-label">Deduction</label>
                <select id="deduction_id" class="browser-default select2">
                    <option value=""></option>
                    <?php
                    $deduction_sql = "SELECT * FROM deductions ORDER BY deduction ASC";
                    $deduction_stmt = sqlsrv_query($conn, $deduction_sql);
                    if ($deduction_stmt === false) {
                        die(print_r(sqlsrv_errors(), true));
                    }
                    while ($row = sqlsrv_fetch_array($deduction_stmt, SQLSRV_FETCH_ASSOC)): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['deduction']; ?></option>
                    <?php endwhile;
                    sqlsrv_free_stmt($deduction_stmt);
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="control-label">Type</label>
                <select id="type" class="browser-default custom-select">
                    <option value="1">Monthly</option>
                    <option value="2">Semi-Monthly</option>
                    <option value="3">Once</option>
                </select>
            </div>
            <div class="col-md-3" style="display: none" id="dfield">
                <label class="control-label">Effective Date</label>
                <input type="date" id="edate" class="form-control">
            </div>
        </div>
        <div class="row form-group">
            <div class="col-md-5">
                <label class="control-label">Amount</label>
                <input type="number" id="amount" class="form-control text-right" step="any">
            </div>
            <div class="col-md-2 offset-md-2">
                <label class="control-label">&nbsp;</label>
                <button class="btn btn-primary btn-block btn-sm" type="button" id="add_list">Add to List</button>
            </div>
        </div>
        <hr>
        <div class="row">
            <table class="table table-bordered" id="deduction-list">
                <thead>
                    <tr>
                        <th class="text-center">Deduction</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Amount</th>
                        <th class="text-center">Date</th>
                        <th class="text-center"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <button class="btn btn-success btn-block" type="submit">Save Deductions</button>
    </form>
</div>

<div id="tr_clone" style="display: none">
    <table>
        <tr>
            <td>
                <input type="hidden" name="deduction_id[]">
                <p class="deduction"></p>
            </td>
            <td>
                <input type="hidden" name="type[]">
                <p class="type"></p>
            </td>
            <td>
                <input type="hidden" name="amount[]">
                <p class="amount"></p>
            </td>
            <td>
                <input type="hidden" name="effective_date[]">
                <p class="edate"></p>
            </td>
            <td class="text-center">
                <button class="btn-sm btn-danger" type="button" onclick="$(this).closest('tr').remove()"><i class="fa fa-trash"></i></button>
            </td>
        </tr>
    </table>
</div>

<script>
    $('.select2').select2({ placeholder: "Select here", width: "100%" });

    $('#type').change(function () {
        if ($(this).val() == 3) {
            $('#dfield').show();
        } else {
            $('#dfield').hide();
        }
    });

    $('#add_list').click(function () {
        var deduction_id = $('#deduction_id').val(),
            type = $('#type').val(),
            amount = $('#amount').val(),
            edate = $('#edate').val();

        if (!deduction_id || !type || !amount) {
            alert("Please fill in all required fields");
            return;
        }

        var tr = $('#tr_clone tr').clone();
        tr.find('[name="deduction_id[]"]').val(deduction_id);
        tr.find('[name="type[]"]').val(type);
        tr.find('[name="effective_date[]"]').val(edate);
        tr.find('[name="amount[]"]').val(amount);
        tr.find('.deduction').html($('#deduction_id option[value="' + deduction_id + '"]').html());
        tr.find('.type').html($('#type option[value="' + type + '"]').html());
        tr.find('.amount').html(amount);
        tr.find('.edate').html(edate);

        $('#deduction-list tbody').append(tr);
        $('#deduction_id').val('').select2({ placeholder: "Select here", width: "100%" });
        $('#type').val('');
        $('#amount').val('');
        $('#edate').val('');
    });

    $('#manage-deductions').submit(function (e) {
        e.preventDefault();
        start_load();

        $.ajax({
            url: 'ajax.php?action=save_employee_deduction',
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (resp) {
                if (resp.status == 1) {
                    alert_toast(resp.message, "success");
                    end_load();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    console.error("Error saving deduction:", resp.message);
                    alert_toast("Error saving deduction. " + resp.message, "danger");
                }
            },
            error: function (err) {
                console.log(err);
                alert_toast("AJAX Error. Check console for details.", "danger");
            }
        });
    });
</script>
