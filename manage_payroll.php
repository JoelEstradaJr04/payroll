<?php include 'db_connect.php' ?>

<div class="container-fluid">
    <div class="col-lg-12">
        <form id="manage-payroll">
            <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '' ?>">  
            <div class="form-group">
                <label for="" class="control-label">Date From :</label>
                <input type="date" class="form-control" name="date_from" value="<?php echo isset($date_from) ? $date_from : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="" class="control-label">Date To :</label>
                <input type="date" class="form-control" name="date_to" value="<?php echo isset($date_to) ? $date_to : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="" class="control-label">Payroll Type :</label>
                <select name="type" class="custom-select browser-default" id="">
                    <option value="1" <?php echo (isset($type) && $type == 1) ? 'selected' : ''; ?>>Monthly</option>
                    <option value="2" <?php echo (isset($type) && $type == 2) ? 'selected' : ''; ?>>Semi-Monthly</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

<script>
    $('#manage-payroll').submit(function (e) {
        e.preventDefault()
        start_load()
        $.ajax({
            url: 'ajax.php?action=save_payroll',
            method: "POST",
            data: $(this).serialize(),
            error: err => console.log(err), // Log errors to the console
            success: function (resp) {
                if (resp == 1) {
                    alert_toast("Payroll successfully saved", "success");
                    setTimeout(function () {
                        location.reload()
                    }, 1000)
                } else {
                    console.error("Error saving payroll:", resp); // Log the error
                    alert_toast("Error saving payroll. Check console.", "danger"); // User-friendly message
                }
            }
        })
    })
</script>