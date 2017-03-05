<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
<ul id="error_message_box" class="error_message_box"></ul>
<form method="post" action="expenses/add" name="expense_form" id="expense_form">
<fieldset id="salary_basic_information">
	<legend><?php echo $this->lang->line("salary_basic_information"); ?></legend>



<div class="field_row clearfix">
<?php echo form_label($this->lang->line('exp_name').':', 'exp_name',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'exp_name',
		'id'=>'exp_name')
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('exp_cat').':', 'exp_cat',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<select style="width:160px; height: 23px;" name="exp_cat" id="exp_cat">
		<?php
          $con=mysqli_connect("localhost","root","","hosi");
          $query = "SELECT DISTINCT ospos_expense_category.id as id, ospos_expense_category.category_name as cat_name
                    FROM  ospos_expense_category
                    WHERE  ospos_expense_category.isDeleted = '0'
                    ORDER BY  cat_name ASC";
          $result = mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));;
          while($row=mysqli_fetch_array($result, MYSQL_ASSOC)){                                                 
       echo "<option value='".$row['id']."'>".$row['cat_name']."</option>";
    }?>
	</select>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('emp_exp_name').':', 'emp_exp_name',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<select style="width:160px; height: 23px;" name="emp_exp_id" id="emp_exp_id">
		<?php
          $mycon=mysqli_connect("localhost","root","","hosi");
          $user = $user_info->person_id;
          $myquery = "SELECT DISTINCT ospos_people.person_id as id,ospos_people.first_name as first_name, ospos_people.last_name as last_name
                    FROM  ospos_people
                    INNER JOIN ospos_employees
                    ON ospos_people.person_id = ospos_people.person_id
                    WHERE  ospos_people.person_id = '$user'
                    AND ospos_employees.deleted = 0
                    ORDER BY  last_name ASC";
          $myresult = mysqli_query($mycon,$myquery) or die('Could not look up user information; ' . mysqli_error($mycon));;
          while($myrow=mysqli_fetch_array($myresult, MYSQL_ASSOC)){                                                 
        echo "<option value='".$myrow['id']."'>".$myrow['first_name']." ".$myrow['last_name']." </option>";
    }?>
	</select>
	</div>
</div>



<div class="field_row clearfix">
<?php echo form_label($this->lang->line('exp_amount').':', 'exp_amount',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'exp_amount',		
		'id'=>'exp_amount',
		'type'=>'number')
	);?>
	</div>
</div>


<div class="field_row clearfix">
<?php echo form_label($this->lang->line('exp_date').':', 'exp_date',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'exp_date',				
		'id'=>'exp_date',
		'disabled'=> 'true',
		'value'=>date("Y-m-d"))
	);?>
	</div>
</div>


<?php
echo form_submit(array(
	'name'=>'submit',
	'id'=>'submit',
	'value'=>$this->lang->line('common_submit'),
	'class'=>'submit_button float_right')
);

?>
</fieldset>
</form>

<script type='text/javascript'>
//validation and submit handling
$(document).ready(function()
{
    $("#continue").click(function()
  	{
        stay_open = false;
    });
    	    
    $("#submit").click(function()
    {
        stay_open = false;
    });
	
	
	
	$('#expense_form').validate({
		submitHandler:function(form)
		{
			$(form).ajaxSubmit({
				success:function(response)
				{
					if (stay_open) 
					{
				        $("#expense_form").attr("action", "<?php echo site_url("expenses/add")?>");
					}
					else
					{
						tb_remove();
					}
					post_salary_form_submit(response, stay_open);	
				},
				dataType:'json'
			});

		},
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules:
		{
			
	
			sal_gross:
			{
				required:true,
				number:true
			},

			sal_nssf:
			{
				required:true,
				number:true
			},

			sal_nhif:
			{
				required:true,
				number:true
			},
			sal_tax:
			{
				required:true,
				number:true
			},
			sal_net:
			{
				required:true,
				number:true
			}

			
			
   		},
		messages:
		{
			sal_gross:"<?php echo $this->lang->line('gross_required'); ?>",
			sal_nssf:"<?php echo $this->lang->line('nssf_required'); ?>",
			sal_nhif:"<?php echo $this->lang->line('nhif_required'); ?>",
			sal_tax:"<?php echo $this->lang->line('tax_required'); ?>",
			// item_number:"<?php //echo $this->lang->line('items_item_number_duplicate'); ?>",
			sal_net:"<?php echo $this->lang->line('net_required'); ?>"
			
		}
	});
	$("#purchase_date").datepicker();
});
</script>
