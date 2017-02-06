<?php $this->load->view("partial/header"); ?>
<link rel="stylesheet" type="text/css" href="css/dataTables.min.css" />
<script src="<?php echo base_url();?>js/dataTables.min.js"></script>
<style type="text/css">
	table.dataTable tbody td {
	    word-break: break-word;
	    vertical-align: top;
	}
	<style>
	@media print {
		
		table.to_be_printed tbody td, 
		table.to_be_printed *, 
		table.to_be_printed_ancestor {
			word-break: break-word;
	    	vertical-align: top;
		}
	}
</style>
</style>
<?php
if (isset($error_message))
{
	echo "<div class='alert alert-dismissible alert-danger'>".$error_message."</div>";
	exit;
}
?>

<?php $this->load->view('partial/print_receipt', array('print_after_sale'=>$print_after_sale,'print_silently'=>1, 'selected_printer'=>'receipt_printer')); ?>

<div class="print_hide" id="control_buttons" style="height: 60px; margin-right: -150px; margin-top: 10px;">
	<a href="javascript:printdoc();" id="show_print_button"><div class='big_button float_right'><span><?php echo $this->lang->line('common_print'); ?></span></div></a>
	<?php /* this line will allow to print and go back to sales automatically.... echo anchor("sales", $this->lang->line('common_print'), array('class'=>'big_button', 'id'=>'show_print_button', 'onclick'=>'window.print();')); */ ?>
	<?php echo anchor("sales", "<div class='big_button float_right'><span>".$this->lang->line('sales_register')."</span></div>",
		array('id'=>'show_sales_button'));  ?>
	<?php echo anchor("sales/manage", "<div class='big_button float_right'><span>".$this->lang->line('sales_takings')."</span></div>",
		array('id'=>'show_takings_button')); ?>
</div>

<div id="receipt_wrapper">
	
	<div id="receipt_header">
		<?php
		if ($this->Appconfig->get('company_logo') == '') 
        { 
        ?>
			<div id="company_name"><?php echo $this->config->item('company'); ?></div>
		<?php 
		}
		else 
		{ 
		?>
			<div id="company_name"><?php echo $this->config->item('company'); ?></div>			
		<?php
		}
		?>
		<div id="company_name"><?php echo nl2br($this->config->item('address')); ?></div>
		<div id="company_phone"><?php echo $this->config->item('phone'); ?></div>
		<br>
		<div id="company_name"><?php echo $receipt_title; ?></div>
		<div id="company_name"><?php echo $transaction_time ?></div>
	</div>
<br><br><br>
	<div id="receipt_general_info">
		<?php
		if(isset($customer))
		{
		?>
			<div id="company_name" style="font-size: 12px"><?php echo $this->lang->line('customers_customer').": ".$customer; ?></div>
		<?php
		}
		?>
		
		<div id="company_name"><?php echo $this->lang->line('sales_id').": ".$sale_id; ?></div>

		<?php
		if (!empty($invoice_number))
		{
		?>
		<br><br><br>
			<div id="company_name"><?php echo $this->lang->line('recvs_invoice_number').": ".$invoice_number; ?></div>	
		<?php 
		}
		?>

		<div id="company_name"><?php echo $this->lang->line('employees_employee').": ".$employee; ?></div>
		<br><br><br>
	</div>

	<table id="receipt_items" style="font-size: 72px !important">
		<thead>
			<tr>
				<th ><?php echo $this->lang->line('sales_description_abbrv'); ?></th>
				<th ><?php echo $this->lang->line('sales_price'); ?></th>
				<th ><?php echo $this->lang->line('sales_quantity'); ?></th>
				<th  class="total-value"><?php echo $this->lang->line('sales_total'); ?>(Kshs)</th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach(array_reverse($cart, true) as $line=>$item)
		{
		?>
			<tr>
				<td style="width: 50%; "><span class='long_name'><?php echo ucfirst($item['name']); ?></span></td>
				<td><?php echo ($item['price']); ?></td>
				<td><?php echo $item['quantity']?></td>
				<td><div class="total-value"><?php echo ($item[($this->Appconfig->get('show_total_discount') ? 'total' : 'discounted_total')]); ?></div></td>
			</tr>
			<tr>
				<td colspan="2"><?php echo $item['description']; ?></td>
				<td><?php echo $item['serialnumber']; ?></td>
			</tr>
		
	
		<?php
		if ($this->Appconfig->get('show_total_discount') && $discount > 0)
		{
		?> 
			<!-- <tr>
				<td colspan="5" style='text-align:right;border-top:2px solid #000000;'><?php echo $this->lang->line('sales_sub_total'); ?></td>
				<td style='text-align:right;border-top:2px solid #000000;'><?php echo to_currency($subtotal); ?></td>
			</tr> -->
			<!-- <tr>
				<td colspan="3" class="total-value"><?php echo $this->lang->line('sales_discount'); ?>:</td>
				<td class="total-value"><?php echo to_currency($discount*-1); ?></td>
			</tr> -->
		<?php
		}
		?>

		<?php
		if ($this->Appconfig->get('receipt_show_taxes'))
		{
		?> 
			<tr>
				<!-- <td colspan="3" style='text-align:right;border-top:2px solid #000000;'><?php echo $this->lang->line('sales_sub_total'); ?></td>
				<td style='text-align:right;border-top:2px solid #000000;'><?php echo to_currency($this->config->item('tax_included') ? $tax_exclusive_subtotal : $discounted_subtotal); ?></td> -->
			</tr>
			<?php
			foreach($taxes as $name=>$value)
			{
			?>
				<!-- <tr>
					<td colspan="3" class="total-value"><?php echo $name; ?>:</td>
					<td class="total-value"><?php echo to_currency($value); ?></td>
				</tr> -->
			<?php
			}}
			?>
		<?php
		}
		?>

		<tr>
		</tr>
		
		<?php $border = (!$this->Appconfig->get('receipt_show_taxes') && !($this->Appconfig->get('show_total_discount') && $discount > 0)); ?> 
		<tr>
			<td colspan="3" style='<?php echo $border? 'border-top: 2px solid black;' :''; ?>text-align:right;'><?php echo $this->lang->line('sales_total'); ?></td>
			<td style='<?php echo $border? 'border-top: 2px solid black;' :''; ?>text-align:right'><?php echo to_currency($total); ?></td>
		</tr>

		<tr>
			<td colspan="4">&nbsp;</td>
		</tr>

		<?php
		$only_sale_check = TRUE;
		$show_giftcard_remainder = FALSE;
		foreach($payments as $payment_id=>$payment)
		{ 
			$only_sale_check &= $payment['payment_type'] == $this->lang->line('sales_check');
			$splitpayment=explode(':',$payment['payment_type']);
			$show_giftcard_remainder |= $splitpayment[0] == $this->lang->line('sales_giftcard');
		?>
			<tr>
				<td colspan="3" style="text-align:right;"><?php echo $splitpayment[0]; ?> </td>
				<td><div class="total-value"><?php echo to_currency( $payment['payment_amount'] * -1 ); ?></div></td>
			</tr>
		<?php
		}
		?>

		<tr><td colspan="4">&nbsp;</td></tr>

		<?php 
		if (isset($cur_giftcard_value) && $show_giftcard_remainder)
		{
		?>
		<tr>
			<td colspan="3" style='text-align:right;'><?php echo $this->lang->line('sales_giftcard_balance'); ?></td>
			<td style='text-align:right'><?php echo to_currency($cur_giftcard_value); ?></td>
		</tr>
		<?php 
		}
		?>
		<tr>
			<td colspan="3" style='text-align:right;'> <?php echo $this->lang->line($amount_change >= 0 ? ($only_sale_check ? 'sales_check_balance' : 'sales_change_due') : 'sales_amount_due') ; ?> </td>
			<td style='text-align:right'><?php echo to_currency($amount_change); ?></td>
		</tr>
		</tbody>
	</table>
<hr>
	<div id="company_name">
		<?php echo nl2br($this->config->item('return_policy')); ?>
	</div>


</div>
<script type="text/javascript">
	// var table = $('#receipt_items').DataTable({
	//     autoWidth: false,
	//     "paging":   false,
	//     "ordering": false,
	//     "info":     false,
	//     "searching":false,
	//     columns : [
	//         { width : '40%' },
	//         { width : '50px' },
	//         { width : '50px' },
	//         { width : '50px' }        
	//     ] 
	// });
	// $('#receipt_items').DataTable( {			 
	//     "paging":   false,
	//     "ordering": false,
	//     "info":     false,
	//     "searching":false
	// });	
	// $(".dataTables_wrapper").css("width","100%");
</script>
<?php $this->load->view("partial/footer"); ?>

