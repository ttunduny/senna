<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
<ul id="error_message_box" class="error_message_box"></ul>
<form method="post" action="salaryedit/edit" name="salary_form" id="salary_form">
<fieldset id="salary_basic_information">
	<legend><?php echo $this->lang->line("salary_basic_information"); ?></legend>

	<div class="field_row clearfix">
<?php echo form_label($this->lang->line('emp_salr_no').':', 'emp_salr_no',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'emp_sal_no',
		'class'=>'emp_sal_no',
		'readonly' => 'readonly',
		'id'=>'emp_sal_no',
		'value'=>$salary_info->salary_id)
	);?>
	</div>
	</div>

	<div class="field_row clearfix">
<?php echo form_label($this->lang->line('emp_sal_name').':', 'emp_sal_name',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'emp_sal_name',
		'id'=>'emp_sal_name',
		'readonly' => 'readonly',
		'value'=>$salary_info->first_name.' '.$salary_info->last_name)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('sal_gross').':', 'sal_gross',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'sal_gross',
		'id'=>'sal_gross',
		'value'=>$salary_info->gross_sal)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('sal_nhif').':', 'sal_nhif',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'sal_nhif',		
		'id'=>'sal_nhif',
		'value'=>$salary_info->nhif)
	);?>
	</div>
</div>


<div class="field_row clearfix">
<?php echo form_label($this->lang->line('sal_nssf').':', 'sal_nssf',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'sal_nssf',		
		'id'=>'sal_nssf',
		'value'=>$salary_info->nssf)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('sal_tax').':', 'sal_tax',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'sal_tax',		
		'id'=>'sal_tax',
		'value'=>$salary_info->tax)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('sal_net').':', 'sal_net',array('class'=>'required wide date_filter')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'sal_net',		
		'id'=>'sal_net',
		'value'=>$salary_info->net_sal)
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
	
	
	
	$('#salary_form').validate({
		submitHandler:function(form)
		{
			$(form).ajaxSubmit({
				success:function(response)
				{
					if (stay_open) 
					{
						// set action of item_form to url without item id, so a new one can be created
				        $("#salary_form").attr("action", "<?php echo site_url("salaryedit/edit/")?>");
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
