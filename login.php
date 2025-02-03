<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Admin | Employee's Payroll Management System</title>
 	

  <?php 
session_start();
if(isset($_SESSION['login_id'])) {
    header("location:index.php?page=home");
    exit();
}
include('./header.php'); 
include('./db_connect.php'); 
?>

</head>
<style>
	body{
		width: 100%;
	    height: calc(100%);
	    /*background: #007bff;*/
	}
	main#main{
		width:100%;
		height: calc(100%);
		background:white;
	}
	#login-right {
    position: absolute;
    right: 0;
    width: 40%;
    height: calc(100%);
    background: orange;
    display: flex;
    align-items: center;
	}

	#login-left {
		position: absolute;
		left: 0;
		width: 90%;
		height: calc(100%);
		background: darkblue;
		display: flex;
		align-items: center;
	}

	#login-left::after {
		content: "";
		position: center;
		top: 0;
		left: 0;
		width: calc(100%);
		height: calc(100%);
		background: url(assets/images/LoginPhoto.png);
		background-repeat: no-repeat;
		background-size: 65%;
		z-index: 0;
	}

	#login-right .card {
		margin: auto;
		z-index: 1;
	}

	.logo {
		margin: auto;
		font-size: 8rem;
		background: white;
		padding: 0.5em 0.7em;
		border-radius: 50%;
		color: darkblue;
		z-index: 10;
	}

	div#login-right::before {
		content: "";
		position: absolute;
		top: 0;
		left: 0;
		width: calc(100%);
		height: calc(100%);
		background: darkblue;
	}


</style>

<body>


  <main id="main">
  		<div id="login-left">
  			
  		</div>

  		<div id="login-right">
		  <div class="card col-md-8">
				<div class="card-body">
					<form id="login-form">
						<div class="form-group">
							<label for="username">Username</label>
							<input type="text" id="username" name="username" class="form-control" required>
						</div>
						<div class="form-group">
							<label for="password">Password</label>
							<input type="password" id="password" name="password" class="form-control" required>
						</div>
						<button type="submit" class="btn btn-primary">Login</button>
					</form>
				</div>
			</div>
  		</div>
   

  </main>

  <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>


</body>

<script>
$('#login-form').submit(function(e){
    e.preventDefault();
    $.ajax({
        url:'ajax.php?action=login',
        data: $(this).serialize(),
        method: 'POST',
        dataType: 'json',
        beforeSend: function(){
            $('.alert-danger').remove();
            $('button[type="submit"]').prop('disabled', true);
        },
        success:function(resp){
            if(resp.status == 1){
                window.location.href = 'index.php?page=home';
            } else {
                $('#login-form').prepend('<div class="alert alert-danger">'+resp.message+'</div>');
            }
        },
        error:function(xhr, status, error){
            $('#login-form').prepend('<div class="alert alert-danger">System error occurred</div>');
            console.error('Login error:', error);
        },
        complete: function(){
            $('button[type="submit"]').prop('disabled', false);
        }
    });
});
</script>
</html>