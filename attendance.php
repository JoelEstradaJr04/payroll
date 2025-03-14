<?php include('db_connect.php') ?>
<div class="container-fluid">
    <div class="col-lg-12">
        <br />
        <br />
        <div class="card">
            <div class="card-header">
                <span><b>Attendance List</b></span>
                <button class="btn btn-primary btn-sm btn-block col-md-3 float-right" type="button" id="new_attendance_btn"><span class="fa fa-plus"></span> Add Attendance</button>
            </div>
            <div class="card-body">
                <table id="table" class="table table-bordered table-striped">
                    <colgroup>
                        <col width="10%">
                        <col width="20%">
                        <col width="30%">
                        <col width="30%">
                        <col width="10%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee No</th>
                            <th>Name</th>
                            <th>Time Record</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT a.*, e.employee_no, CONCAT(e.lastname, ', ', e.firstname, ' ', e.middlename) AS ename 
                        FROM attendance a 
                        INNER JOIN employee e ON a.employee_id = e.id 
                        WHERE a.isDeleted = 0  -- Only include records that are not deleted
                        ORDER BY a.datetime_log ASC";  // SQL Server query
                
                        $stmt = sqlsrv_query($conn, $sql);

                        if ($stmt === false) {
                            die(print_r(sqlsrv_errors(), true)); // Error handling
                        }

                        $lt_arr = array(1 => " Time-in AM", 2 => "Time-out AM", 3 => " Time-in PM", 4 => "Time-out PM");
                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                            $date = $row['datetime_log']->format("Y-m-d");
                            $attendance[$row['employee_id'] . "_" . $date]['details'] = array("eid" => $row['employee_id'], "name" => $row['ename'], "eno" => $row['employee_no'], "date" => $date);
                            if ($row['log_type'] == 1 || $row['log_type'] == 3) {
                                if (!isset($attendance[$row['employee_id'] . "_" . $date]['log'][$row['log_type']])) {
                                    $attendance[$row['employee_id'] . "_" . $date]['log'][$row['log_type']] = array('id' => $row['id'], "date" => $row['datetime_log']);
                                }
                            } else {
                                $attendance[$row['employee_id'] . "_" . $date]['log'][$row['log_type']] = array('id' => $row['id'], "date" => $row['datetime_log']);
                            }
                        }

                        foreach ($attendance as $key => $value) {
                            ?>
                            <tr>
                                <td><?php echo date("M d,Y", strtotime($attendance[$key]['details']['date'])) ?></td>
                                <td><?php echo $attendance[$key]['details']['eno'] ?></td>
                                <td><?php echo $attendance[$key]['details']['name'] ?></td>
                                <td>
                                    <div class="row">
                                        <?php
                                        $att_ids = array();
                                        foreach ($attendance[$key]['log'] as $k => $v):
                                            ?>
                                            <div class="col-sm-6">
                                                <p>
                                                    <small><b><?php echo $lt_arr[$k] . ": <br/>" ?>
													<?php echo $attendance[$key]['log'][$k]['date']->format("h:i A") ?>
													</b>
                                                        <span class="badge badge-danger rem_att" data-id="<?php echo $attendance[$key]['log'][$k]['id'] ?>"><i class="fa fa-trash"></i></span>
                                                    </small>
                                                </p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <center>
                                        <button class="btn btn-sm btn-outline-danger remove_attendance" data-id="<?php echo $key ?>" type="button"><i class="fa fa-trash"></i></button>
                                    </center>
                                </td>
                            </tr>
                            <?php
                        }
                        sqlsrv_free_stmt($stmt); // Free the statement resource
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    td p {
        margin: unset;
    }

    .rem_att {
        cursor: pointer;
    }
</style>

<script type="text/javascript">
    $(document).ready(function () {
        $('#table').DataTable();

        $('.edit_attendance').click(function () {
            var $id = $(this).attr('data-id');
            uni_modal("Edit Employee", "manage_attendance.php?id=" + $id)
        });

        $('.view_attendance').click(function () {
            var $id = $(this).attr('data-id');
            uni_modal("Employee Details", "view_attendance.php?id=" + $id, "mid-large")
        });

        $('#new_attendance_btn').click(function () {
            uni_modal("New Time Record/s", "manage_attendance.php", 'mid-large')
        })

        $('.remove_attendance').click(function () {
            var d = '"' + ($(this).attr('data-id')).toString() + '"';
            _conf("Are you sure to delete this employee's time log record?", "remove_attendance", [d])
        })

        $('.rem_att').click(function () {
            var $id = $(this).attr('data-id');
            _conf("Are you sure to delete this time log?", "rem_att", [$id])
        })
    });

    function remove_attendance(id) {
    start_load();
    console.log("Removing attendance with ID:", id);
    $.ajax({
        url: 'ajax.php?action=delete_employee_attendance',
        method: "POST",
        data: { id: id.replace(/['"]+/g, '') },
        success: function (resp) {
            console.log("Server response:", resp);
            if (resp == 1) {
                alert_toast("Attendance record marked as deleted.", "success");
                setTimeout(function () {
                    location.reload();
                }, 1000);
            } else {
                alert_toast("Error deleting record. Server returned: " + resp, "error");
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", {xhr: xhr, status: status, error: error});
            alert_toast("Error deleting record: " + error, "error");
        }
    });
}

    function rem_att(id) {
        start_load();
        console.log("Removing single attendance with ID:", id);
        $.ajax({
            url: 'ajax.php?action=delete_employee_attendance_single',
            method: "POST",
            data: { id: id },
            success: function (resp) {
                console.log("Server response:", resp);
                if (resp == 1) {
                    alert_toast("Single time log marked as deleted.", "success");
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else {
                    alert_toast("Error deleting time log. Server returned: " + resp, "error");
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {xhr: xhr, status: status, error: error});
                alert_toast("Error deleting time log: " + error, "error");
            }
        });
    }
</script>
