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
  						
  					<form id="login-form" >
  						<div class="form-group">
  							<label for="username" class="control-label">Username</label>
  							<input type="text" id="username" name="username" class="form-control">
  						</div>
  						<div class="form-group">
  							<label for="password" class="control-label">Password</label>
  							<input type="password" id="password" name="password" class="form-control">
  						</div>
  						<center><button type="submit" class="btn-sm btn-block btn-wave col-md-4 btn-primary">Login</button></center>
  					</form>
  				</div>
  			</div>
  		</div>
   

  </main>

  <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>


</body>
<script>
$(document).ready(function() {
    $('#login-form').submit(function(e){
        e.preventDefault();
        
        console.log('Form submitted'); // Debug log
        
        // Clear any existing alerts
        $('.alert').remove();
        
        // Disable button and show loading
        const loginBtn = $(this).find('button[type="submit"]');
        loginBtn.prop('disabled', true).html('Logging in...');
        
        // Debug: Log form data
        console.log('Form data:', $(this).serialize());
        
        $.ajax({
            url: 'ajax.php?action=login',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'text',
            error: function(xhr, status, error) {
                console.error('Login error:', error);
                console.log('Status:', status);
                console.log('Response:', xhr.responseText);
                
                loginBtn.prop('disabled', false).html('Login');
                $('#login-form').prepend('<div class="alert alert-danger">An error occurred during login.</div>');
            },
            success: function(resp) {
                console.log('Raw server response:', resp); // Log raw response
                
                // Remove any whitespace and HTML
                const cleanResp = resp.replace(/<\/?[^>]+(>|$)/g, "").trim();
                console.log('Cleaned response:', cleanResp);
                
				// Split the string by '/'
				let parts = cleanResp.split('!');

				// Get the last part
				let lastPart = parts[parts.length - 1];

                // Try to parse response as number
                const response = parseInt(lastPart);
                console.log('Parsed response:', response);
                
                if(response === 1) {
                    console.log('Login successful, attempting redirect...');
                    // Try both methods of redirect
                    window.location.href = 'index.php?page=home';
                    if(!window.location.href.includes('index.php')) {
                        window.location.replace('index.php?page=home');
                    }
                } else if(response === 2) {
                    window.location.href = 'voting.php';
                } else {
                    $('#login-form').prepend('<div class="alert alert-danger">Username or password is incorrect.</div>');
                    loginBtn.prop('disabled', false).html('Login');
                }
            }
        });
    });
});
</script>	
</html>