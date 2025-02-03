<?php include 'db_connect.php' ?>

<div class="container-fluid">
    <form action="" id="employee-allowance">
        <input type="hidden" name="employee_id" value="<?php echo $_GET['id'] ?>">
        
        <div class="row form-group">
            <div class="col-md-5">
                <label class="control-label">Allowance</label>
                <select id="allowance_id" class="browser-default select2">
                    <option value=""></option>
                    <?php
                    $allowance_sql = "SELECT * FROM allowances ORDER BY allowance ASC";
                    $allowance_stmt = sqlsrv_query($conn, $allowance_sql);

                    if ($allowance_stmt === false) {
                        die(print_r(sqlsrv_errors(), true)); // Debugging if query fails
                    }

                    while ($row = sqlsrv_fetch_array($allowance_stmt, SQLSRV_FETCH_ASSOC)):
                    ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['allowance']; ?></option>
                    <?php endwhile;
                    sqlsrv_free_stmt($allowance_stmt);
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
                <label class="control-label">&nbsp</label>
                <button class="btn btn-primary btn-block btn-sm" type="button" id="add_list"> Add to List</button>
            </div>
        </div>

        <hr>

        <div class="row">
            <table class="table table-bordered" id="allowance-list">
                <thead>
                    <tr>
                        <th class="text-center">Allowance</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Amount</th>
                        <th class="text-center">Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </form>
</div>

<div id="tr_clone" style="display: none">
    <table>
        <tr>
            <td>
                <input type="hidden" name="allowance_id[]">
                <p class="allowance"></p>
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
                <button class="btn-sm btn-danger remove_row" type="button">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    </table>
</div>

<!-- Include jQuery and Select2 if not already loaded -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {
        console.log("Script loaded successfully"); // Debugging
        
        // Initialize Select2
        $('.select2').select2({
            placeholder: "Select here",
            width: "100%"
        });

        // Show effective date field only for "Once" type
        $('#type').change(function () {
            if ($(this).val() == "3") {
                $('#dfield').show();
            } else {
                $('#dfield').hide();
            }
        });

        // Add to List
        $('#add_list').click(function () {
            console.log("Add button clicked"); // Debugging
            
            var allowance_id = $('#allowance_id').val(),
                type = $('#type').val(),
                amount = $('#amount').val(),
                edate = $('#edate').val();

            console.log("Allowance ID:", allowance_id);
            console.log("Type:", type);
            console.log("Amount:", amount);
            console.log("Effective Date:", edate);

            if (!allowance_id || !type || !amount) {
                alert("Please fill in all required fields.");
                return;
            }

            var tr = $('#tr_clone tr').clone();
            tr.find('[name="allowance_id[]"]').val(allowance_id);
            tr.find('[name="type[]"]').val(type);
            tr.find('[name="effective_date[]"]').val(edate);
            tr.find('[name="amount[]"]').val(amount);

            tr.find('.allowance').html($('#allowance_id option:selected').text());
            tr.find('.type').html($('#type option:selected').text());
            tr.find('.amount').html(amount);
            tr.find('.edate').html(edate);

            $('#allowance-list tbody').append(tr);

            // Reset fields after adding
            $('#allowance_id').val('').trigger('change');
            $('#type').val('');
            $('#amount').val('');
            $('#edate').val('');
        });

        // Remove row from table
        $(document).on('click', '.remove_row', function () {
            $(this).closest('tr').remove();
        });

        // Submit Form
        $('#employee-allowance').submit(function (e) {
            e.preventDefault();
            console.log("Form submission triggered"); // Debugging

            $.ajax({
                url: 'ajax.php?action=save_employee_allowance',
                method: "POST",
                data: $(this).serialize(),
                error: function (err) {
                    console.error("AJAX Error:", err);
                },
                success: function (resp) {
                    console.log("Response from server:", resp); // Debugging
                    if (resp == 1) {
                        alert("Employee's allowance successfully saved!");
                        location.reload();
                    } else {
                        alert("Error saving allowance. Check console for details.");
                    }
                }
            });
        });
    });
</script>
