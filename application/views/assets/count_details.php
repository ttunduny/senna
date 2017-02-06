<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
<ul id="error_message_box" class="error_message_box"></ul>
<?php
echo form_open('assets/save/'.$asset_info->serial_no,array('id'=>'asset_form', 'enctype'=>'multipart/form-data'));
?>
<fieldset id="asset_basic_info">
	<legend><?php echo $this->lang->line("assets_basic_information"); ?></legend>

	<div class="field_row clearfix">
<?php echo form_label($this->lang->line('assets_asset_number').':', 'asset_number',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'serial_number',
		'class'=>'serial_number',
		'id'=>'serial_number',
		'readonly' => 'readonly',
		'value'=>$asset_info->serial_no)
	);?>
	</div>
	</div>

	<div class="field_row clearfix">
<?php echo form_label($this->lang->line('assets_name').':', 'name',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'name',
		'id'=>'name',
		'readonly' => 'readonly',
		'value'=>$asset_info->name)
	);?>
	</div>
</div>



<div class="field_row clearfix">
<?php echo form_label($this->lang->line('assets_category').':', 'category',array('class'=>'required wide')); ?>
	<div class='form_field wide'>
	<?php echo form_dropdown('category_id', $categories, $selected_category);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('assets_cost_price').':', 'cost_price',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'cost_price',		
		'id'=>'cost_price',
		'readonly' => 'readonly',
		'value'=>$asset_info->price)
	);?>
	</div>
</div>


<div class="field_row clearfix">
<?php echo form_label($this->lang->line('assets_depreciation').':', 'depreciation',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'depreciation',		
		'id'=>'depreciation',
		'readonly' => 'readonly',
		'value'=>$asset_info->depreciation)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('assets_resale_price').':', 'resale_price',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'resale_price',		
		'id'=>'resale_price',
		'readonly' => 'readonly',
		'value'=>$asset_info->resale_price)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('assets_purchase_date').':', 'purchase_date',array('class'=>'required wide date_filter')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'purchase_date',		
		'id'=>'purchase_date',
		'readonly' => 'readonly',
		'value'=>$asset_info->date_of_purchase)
	);?>
	</div>
</div>


</fieldset>
<?php
echo form_close();
?>

<script type='text/javascript'>

//validation and submit handling
$(document).ready(function()
{
    $("#continue").click(function()
  	{
        stay_open = true;
    });
    	    
    $("#submit").click(function()
    {
        stay_open = false;
    });
	
	
	
	$('#asset_form').validate({
		submitHandler:function(form)
		{
			$(form).ajaxSubmit({
				success:function(response)
				{
					if (stay_open) 
					{
						// set action of item_form to url without item id, so a new one can be created
				        $("#asset_form").attr("action", "<?php echo site_url("assets/save/")?>");
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
					post_asset_form_submit(response, stay_open);	
				},
				dataType:'json'
			});

		},
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules:
		{
			name:"required",
			category:"required",
			// serial_number:
			// {
   //              remote:
			// 	{
			// 		url: "<?php //echo site_url($controller_name . '/check_serial_number')?>",
			// 		type: "POST",
			// 		data:
			// 		{
			// 			"asset_id": "<?php //echo $asset_info->id; ?>",
			// 			"id": function ()
			// 			{
			// 				return $("#ass").val();
			// 			}
			// 		}
			// 	}
			// },
			cost_price:
			{
				required:true,
				number:true
			},

			depreciation:
			{
				required:true,
				number:true
			},
			resale_price:
			{
				required:false,
				number:true
			},
			purchase_date:
			{
				required:true,
				number:false
			}
			
   		},
		messages:
		{
			name:"<?php echo $this->lang->line('assets_name_required'); ?>",
			// item_number:"<?php //echo $this->lang->line('items_item_number_duplicate'); ?>",
			category:"<?php echo $this->lang->line('assets_category_required'); ?>",
			cost_price:
			{
				required:"<?php echo $this->lang->line('assets_cost_price_required'); ?>",
				number:"<?php echo $this->lang->line('assets_cost_price_number'); ?>"
			},
			depreciation:
			{
				required:"<?php echo $this->lang->line('assets_depreciation_required'); ?>",
				number:"<?php echo $this->lang->line('assets_depreciation_number'); ?>"
			},			
			purchase_date:
			{
				required:"<?php echo $this->lang->line('assets_purchase_date_required'); ?>"				
			}

		}
	});
});
</script>
