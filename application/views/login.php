<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" rev="stylesheet" href="<?php echo base_url();?>css/login.css" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Senna Pharmacy <?php echo $this->lang->line('login_login'); ?></title>
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url();?>/images/favicon.ico">
	<script src="<?php echo base_url();?>js/jquery-1.8.3.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			$("#login_form input:first").focus();
		});
	</script>
</head>

<body>
	<div align="center" style="margin-top:10px"><img src=<?php echo base_url();?>/images/senna.jpg></div>

	<div id="login">

	<?php echo form_open('login') ?>
	<div id="container">
		<?php echo validation_errors(); ?>
		
		<div id="login_form">
			<center><div class="form_field_label"><?php echo $this->lang->line('login_username'); ?> </div></center>
			<div class="form_field">
				<?php echo form_input(array(
				'name'=>'username',
				'id'=>'username',
				'size'=>'15')); ?>
			</div>

			<center><div class="form_field_label"><?php echo $this->lang->line('login_password'); ?> </div></center>
			<div class="form_field">
				<?php echo form_password(array(
				'name'=>'password',
				'id' => 'password',
				'size'=>'15')); ?>
			</div>
			
			<div id="submit_button"><?php echo form_submit('loginButton','LOGIN'); ?> </div>
		</div>
	</div>
	
</body>
</html>
