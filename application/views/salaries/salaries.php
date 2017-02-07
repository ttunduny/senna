<?php $this->load->view("partial/header"); ?>
<div id="page_title" style="margin-bottom:8px;"><?php echo $this->lang->line('sal_register'); ?></div>

<link rel="stylesheet" type="text/css" href="css/dataTables.min.css" />
<script src="<?php echo base_url();?>js/dataTables.min.js"></script>

<?php
// echo "<pre>";print_r($cart);echo "</pre>";
if(isset($error))
{
	echo "<div class='error_message'>".$error."</div>";
}
?>
<div id="new_main1">
	<div id="register_wrapper" >
		

		<?php echo form_open("salaries/add",array('id'=>'add_item_form')); ?>
		<label id="item_label" for="item">
		<?php 
			
			echo $this->lang->line('sal_find_or_scan_sal');
		?>
		</label>
				
		<?php echo form_input(array('name'=>'item','id'=>'item','size'=>'40'));?>
		<div id="new_item_button_register" >
			<?php echo anchor("employees/view/-1/width:650","<div class='small_button'><span>".$this->lang->line('employees_new')."</span></div>",array('class'=>'thickbox none','title'=>$this->lang->line('employees_new')));	?>
			
		</div>

		</form>
		


		<table id="register" class="stripe">
			<thead>
				<tr>
					<th style="width:9%;"><?php echo $this->lang->line('common_delete'); ?></th>
					<th style="width:30%;"><?php echo $this->lang->line('sal_item_name'); ?></th>
					<th style="width:10%;"><?php echo $this->lang->line('sal_gross'); ?></th>
					<th style="width:8%;"><?php echo $this->lang->line('sal_nssf'); ?></th>
					<th style="width:8%;"><?php echo $this->lang->line('sal_nhif'); ?></th>
					<th style="width:8%;"><?php echo $this->lang->line('sal_tax'); ?></th>
					<th style="width:14%;"><?php echo $this->lang->line('recvs_total'); ?></th>
					
				</tr>
			</thead>
			<tbody id="cart_contents">
			<?php 
				if(count($cart)==0)
				{?>
				<tr>
					
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr><?php 
				}else{
				foreach(array_reverse($cart, true) as $line=>$item){					
        			echo form_open("salaries/save",array('id'=>'edit_item_form_'.$line));
        		?>
        		<tr>
        			<td><?php echo anchor("salaries/delete_item/$line",'['.$this->lang->line('common_delete').']');?></td>
        			
					<td><?php echo form_input(array('name'=>'first_name','value'=>$item['first_name'],'size'=>'12', 'disabled'=>'True'));?></td>
					<td><?php echo form_input(array('name'=>'gross_sal','value'=>$item['gross_sal'],'size'=>'6', 'disabled'=>'True'));?></td>
					<td><?php echo form_input(array('name'=>'nssf','value'=>$item['nssf'],'size'=>'2', 'disabled'=>'True'));?></td>
					<td><?php echo form_input(array('name'=>'nhif','value'=>$item['nhif'],'size'=>'2', 'disabled'=>'True'));?></td>

					<td><?php echo form_input(array('name'=>'vat','size'=>'5','value'=>$item['tax'], 'disabled'=>'True'));?></td>
					
		     		<?php 

		     			$ju = 1;
		     			$gross_sal = ($item['gross_sal']);
		     			$tax = ($item['tax']);
		     			$nhif = ($item['nhif']);
						$nssf = ($item['nssf']);

						$taxcash = ($tax*$gross_sal)/100;
						$nhifcash = ($nhif*$gross_sal)/100;
						$nssfcash = ($nssf*$gross_sal)/100;

						$totalsfinal = 0;
						
						$totalsfinal = $gross_sal - $taxcash - $nhifcash - $nssfcash;								
					?>
			
		 					
					<td><?php echo to_currency($totalsfinal); }?></td>			 					
					
				</tr>			
				</form>
				<?php
			} 
			?>
			</tbody>
		</table>
	</div>


	<div id="overall_sale" >
	
				    	<div class='small_button' id='sal_edit' style='float:left;margin-top:5px;'>
							<span><?php echo $this->lang->line('sal_edit')?></span>
						</div>
			<div class="clearfix">&nbsp;</div>
		
		
		
						<div id='sale_details'>
			<div class="float_left" style='width:55%;'>Total Salaries</div>
			
			<div class="float_left" style="width:45%;font-weight:bold;">

			<?php 
				$total = 0;
				
				if(count($cart)>0){
					foreach ($cart as $item) {
						$new_value = 1;
		     			$gross_sal = ($item['gross_sal']);
		     			$tax = ($item['tax']);
		     			$nhif = ($item['nhif']);
						$nssf = ($item['nssf']);
						$totalsfinal = 0;

						$taxcash = ($tax*$gross_sal)/100;
						$nhifcash = ($nhif*$gross_sal)/100;
						$nssfcash = ($nssf*$gross_sal)/100;
													
												
						$totalsfinal = $gross_sal - $taxcash - $nhifcash - $nssfcash;
						$total+=$totalsfinal;
					}
				}
				echo to_currency($total); 
			?></div>
		</div>
		<?php 
	        
	        if(count($cart) > 0)
			{
				
				?>
				    
				    <div  style='border-top:2px solid #000;' />
				    
			    <?php
			        ?>

			        <!-- The side div that appears to the right just about completion of adding a salary -->
					<div id="finish_sale">

						<?php echo form_open("salaries/save",array('id'=>'finish_receiving_form')); ?>
						<br />
						        			<br />
						<div class='small_button' id='finish_receiving_button' style='float:right;margin-top:5px;'>
							<span><?php echo $this->lang->line('recvs_complete_receiving') ?></span>
						</div>
	        
						</form>

		   				<?php echo form_open("receivings/cancel_receiving",array('id'=>'cancel_receiving_form')); ?>
				    	<div class='small_button' id='cancel_receiving_button' style='float:left;margin-top:5px;'>
							<span><?php echo $this->lang->line('recvs_cancel_receiving')?></span>
						</div>
	       				</form>
				</div>
				<?php 
				
			}
		?>

	</div>
</div>




<div class="clearfix" style="margin-bottom:30px;">&nbsp;</div>
<script type="text/javascript" language="javascript">
$(document).ready(function()
{
	$(".expiry_datepicker" ).datepicker();
    $("#search").autocomplete('<?php echo site_url("salaries/item_search"); ?>',
    {
    	minChars:0,
    	max:100,
       	delay:10,
       	selectFirst: false,
    	formatItem: function(row) {
			return row[1];
		}
    });

    $("#item").result(function(event, data, formatted)
    {
		$("#add_item_form").submit();
    });

    $('#item').focus();

	$('#item').blur(function()
    {
    	$(this).attr('value',"<?php echo $this->lang->line('sales_start_typing_item_name'); ?>");
    });

	$('#comment').keyup(function() 
	{
		$.post('<?php echo site_url("receivings/set_comment");?>', {comment: $('#comment').val()});
	});

	$('#recv_invoice_number').keyup(function() 
	{
		$.post('<?php echo site_url("receivings/set_invoice_number");?>', {recv_invoice_number: $('#recv_invoice_number').val()});
	});

	$("#recv_print_after_sale").change(function()
	{
		$.post('<?php echo site_url("receivings/set_print_after_sale");?>', {recv_print_after_sale: $(this).is(":checked")});
	});

	var enable_invoice_number = function() 
	{
		var enabled = $("#recv_invoice_enable").is(":checked");
		$("#recv_invoice_number").prop("disabled", !enabled).parents('tr').show();
		return enabled;
	}

	enable_invoice_number();

	$("#recv_invoice_enable").change(function() {
		var enabled = enable_invoice_number();
		$.post('<?php echo site_url("receivings/set_invoice_number_enabled");?>', {recv_invoice_number_enabled: enabled});
		
	});

	$('#item,#supplier').click(function()
    {
    	$(this).attr('value','');
    });

    $("#item").autocomplete('<?php echo site_url("salaries/employee_search"); ?>',
    {
    	minChars:0,
    	delay:10,
    	max:100,
    	formatItem: function(row) {
			return row[1];
		}
    });

    $("#supplier").result(function(event, data, formatted)
    {
		$("#select_supplier_form").submit();
    });

    $('#supplier').blur(function()
    {
    	$(this).attr('value',"<?php echo $this->lang->line('recvs_start_typing_supplier_name'); ?>");
    });

    $("#finish_receiving_button").click(function()
    {
    	if (confirm('<?php echo $this->lang->line("recvs_confirm_finish_receiving"); ?>'))
    	{
    		$('#finish_receiving_form').submit();
    	}
    });

    $("#sal_edit").click(function()
    {
    	if (confirm('<?php echo $this->lang->line("recvs_confirm_edit_salary"); ?>'))
    	{
    		window.location="index.php/salaryedit";
    	}
    });

    $("#cancel_receiving_button").click(function()
    {
    	if (confirm('<?php echo $this->lang->line("recvs_confirm_cancel_receiving"); ?>'))
    	{
    		$('#cancel_receiving_form').submit();
    	}
    });


});

function post_item_form_submit(response, stay_open)
{
	if(response.success)
	{
		$("#item").attr("value",response.item_id);
		if (stay_open)
		{
			$("#add_item_form").ajaxSubmit();
		}
		else
		{
			$("#add_item_form").submit();
		}
	}
}

function post_person_form_submit(response)
{
	if(response.success)
	{
		$("#supplier").attr("value",response.person_id);
		$("#select_supplier_form").submit();
	}
}

</script>


<?php $this->load->view("partial/footer"); ?>