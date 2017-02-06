<?php $this->load->view("partial/header"); ?>
<?php
if (isset($error_message))
{
	echo "<div class='alert alert-dismissible alert-danger'>".$error_message."</div>";
	exit;
}
?>

<?php $this->load->view('partial/print_receipt', array('print_after_sale'=>$print_after_sale, 'selected_printer'=>'invoice_printer')); ?>

<div class="print_hide" id="control_buttons" style="height: 40px; margin-right: -150px; margin-top: 10px;">
	<a href="javascript:printdoc();" id="show_print_button"><div class='big_button float_right'><span><?php echo $this->lang->line('common_print'); ?></span></div></a>
	<?php /* this line will allow to print and go back to sales automatically.... echo anchor("sales", $this->lang->line('common_print'), array('class'=>'big_button', 'id'=>'show_print_button', 'onclick'=>'window.print();')); */ ?>
	<?php echo anchor("sales", "<div class='big_button float_right'><span>".$this->lang->line('sales_register')."</span></div>",
		array('id'=>'show_sales_button')); ?>
	<?php echo anchor("sales/manage", "<div class='big_button float_right'><span>".$this->lang->line('sales_takings')."</span></div>",
		array('id'=>'show_takings_button')); ?>
</div>

<div id="page-wrap">
	<div id="header"><?php echo $this->lang->line('sales_invoice'); ?></div>
	<div id="block1">
		

        <div id="logo">
	        <?php if ($this->Appconfig->get('company_logo') == '') 
	        { 
	        ?>
				<div id="company_name"><?php echo $this->config->item('company'); ?></div>
			<?php 
			}
			else 
			{ 
			?>
				<img id="image" src="<?php echo base_url('uploads/' . $this->Appconfig->get('company_logo')); ?>" alt="company_logo" />			
			<?php
			}
			?>


        </div>

        
	</div>
	
	<div class="clearfix"></div>

		
	
	<div id="block2">
       	<textarea id="company-title" rows="5" cols="35"><?php echo $company_info ?></textarea>
       	<br>
       	<br>
       	<br>
       	
       	<?php
			if(isset($customer))
			{
			?>
				<textarea id="customer" rows="5" cols="35">Customer:<?php echo $customer_info ?></textarea>
			<?php
			}
			?>
        <table id="meta">
            <tr>
                <td class="meta-head"><?php echo $this->lang->line('sales_invoice_number');?> </td>
                <td><?php echo $invoice_number; ?></td>
            </tr>
            <tr>
                <td class="meta-head"><?php echo $this->lang->line('common_date'); ?></td>
                <td><?php echo $transaction_date; ?></td>
            </tr>
            <tr>
                <td class="meta-head"><?php echo $this->lang->line('sales_amount_due'); ?></td>
                <td><?php echo to_currency($total); ?></td>
            </tr>
        </table>
	</div>

	<table id="items">
		<tr>
			<th><?php echo $this->lang->line('sales_item_number'); ?></th>
			<th><?php echo $this->lang->line('sales_item_name'); ?></th>
			<th><?php echo $this->lang->line('sales_quantity'); ?></th>
			<th><?php echo $this->lang->line('sales_price'); ?></th>
			<th><?php echo $this->lang->line('sales_discount'); ?></th>
			<th><?php echo $this->lang->line('sales_total'); ?></th>
		</tr>
		<?php
		foreach($cart as $line=>$item)
		{
		?>
			<tr class="item-row">
				<td><?php echo $item['item_number']; ?></td>
				<td class="item-name"><?php echo ($item['is_serialized'] || $item['allow_alt_description']) && !empty($item['description']) ? $item['description'] : $item['name']; ?></td>
				<td style='text-align:center;'><?php echo $item['quantity']; ?></td>
				<td><?php echo to_currency($item['price']); ?></td>
				<td style='text-align:center;'><?php echo $item['discount'] .'%'; ?></td>
				<td style='border-right: solid 1px; text-align:right;'><?php echo to_currency($item['discounted_total']); ?></td>
			</tr>
			<tr class="item-row">
				<td></td>
				<td class="item-name"><?php echo $item['description']; ?></td>
				<td style='text-align:center;'><?php echo $item['serialnumber']; ?></td>
			</tr>
		<?php
		}
		?>
		<tr>
			<td class="blank" colspan="6" align="center"><?php echo '&nbsp;'; ?></td>
		</tr>
		<tr>
			<td colspan="3" class="blank-bottom"> </td>
			<td colspan="2" class="total-line"><?php echo $this->lang->line('sales_sub_total'); ?></td>
			<td class="total-value"><?php echo to_currency($tax_exclusive_subtotal); ?></td>
		</tr>
		<?php
		foreach($taxes as $name=>$value)
		{
		?>
			<tr>
				<td colspan="3" class="blank"> </td>
				<td colspan="2" class="total-line"><?php echo $name; ?>:</td>
				<td class="total-value"><?php echo to_currency($value); ?></td>
			</tr>
		<?php
		}
		?>
		<tr>
			<td colspan="3" class="blank"> </td>
			<td colspan="2" class="total-line"><?php echo $this->lang->line('sales_total'); ?></td>
			<td class="total-value"><?php echo to_currency($total); ?></td>
		</tr>
	</table>
	
	<div id="terms">
		<div id="sale_return_policy">
		 	<h5>
			 	<textarea rows="5" cols="6"><?php echo nl2br($this->config->item('payment_message')); ?></textarea>
			  	<textarea rows="5" cols="6"><?php echo $this->lang->line('sales_comments'). ': ' . (empty($comments) ? $this->config->item('invoice_default_comments') : $comments); ?></textarea>
		  	</h5>
			<?php echo nl2br($this->config->item('return_policy')); ?>
		</div>
		<div id='barcode'>
			<img src='data:image/png;base64,<?php echo $barcode; ?>' /><br>
			<?php echo $sale_id; ?>
		</div>
	</div>
</div>

<script type="text/javascript">
$(window).load(function()
{
	// install firefox addon in order to use this plugin
	if (window.jsPrintSetup) 
	{
		<?php if (!$this->Appconfig->get('print_header'))
		{
		?>
			// set page header
			jsPrintSetup.setOption('headerStrLeft', '');
			jsPrintSetup.setOption('headerStrCenter', '');
			jsPrintSetup.setOption('headerStrRight', '');
		<?php 
		}
		if (!$this->Appconfig->get('print_footer'))
		{
		?>
			// set empty page footer
			jsPrintSetup.setOption('footerStrLeft', '');
			jsPrintSetup.setOption('footerStrCenter', '');
			jsPrintSetup.setOption('footerStrRight', '');
		<?php 
		} 
		?>
	}
});
</script>

<?php $this->load->view("partial/footer"); ?>
