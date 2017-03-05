<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
<ul id="error_message_box" class="error_message_box"></ul>
<form method="post" action="expenses/edit" name="expense_form" id="expense_form">
<fieldset id="salary_basic_information">
	<legend><?php echo $this->lang->line("salary_basic_information"); ?></legend>

	<div class="field_row clearfix">
<?php echo form_label($this->lang->line('emp_exp_no').':', 'emp_exp_no',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'expense_id',
		'class'=>'expense_id',
		'disabled'=> 'true',
		'readonly' => 'readonly',
		'id'=>'expense_id',
		'value'=>$expense_info->expense_id)
	);?>
	</div>
	</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('exp_name').':', 'exp_name',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'exp_name',
		'id'=>'exp_name',
		'value'=>$expense_info->name)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('exp_cat').':', 'exp_cat',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<select style="width:160px; height: 23px;">
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
	<?php echo form_input(array(
		'name'=>'emp_exp_name',
		'id'=>'emp_exp_name',
		'disabled'=>'disabled',
		'readonly' => 'readonly',
		'value'=>$expense_info->first_name.' '.$expense_info->last_name)
	);?>
	</div>
</div>



<div class="field_row clearfix">
<?php echo form_label($this->lang->line('exp_amount').':', 'exp_amount',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'exp_amount',		
		'id'=>'exp_amount',
		'value'=>$expense_info->amount)
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
		'value'=>$expense_info->date_paid)
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
						// set action of item_form to url without item id, so a new one can be created
				        $("#expense_form").attr("action", "<?php echo site_url("expenses/edit/")?>");
						// use a whitelist of fields to minimize unintended side effects
						// $(':text, :password, :file, #description, #asset_form').not('.quantity, #reorder_level, #tax_name_1,' + 
								// '#tax_percent_name_1, #reference_number, #name, #cost_price, #unit_price, #taxed_cost_price, #taxed_unit_price').val('');  
						// de-select any checkboxes, radios and drop-down menus
						// $(':input', '#item_form').not('#item_category_id').removeAttr('checked').removeAttr('selected');
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
			sal_gross:"<?php echo $this->lang->line('exp_cat'); ?>",
			sal_nssf:"<?php echo $this->lang->line('exp_date'); ?>",
			sal_nhif:"<?php echo $this->lang->line('exp_amount'); ?>",
			sal_tax:"<?php echo $this->lang->line('exp_name'); ?>",
			// item_number:"<?php //echo $this->lang->line('items_item_number_duplicate'); ?>",
			sal_net:"<?php echo $this->lang->line('net_required'); ?>"
			
		}
	});
	$("#purchase_date").datepicker();
});
</script>
