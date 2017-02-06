<?php $this->load->view("partial/header"); ?>

<link rel="stylesheet" type="text/css" href="css/dataTables.min.css" />
<script src="<?php echo base_url();?>js/dataTables.min.js"></script>
<?php $this->load->view('partial/print_receipt', array('print_after_sale'=>1, 'selected_printer'=>'receipt_printer')); ?>
<div id="receipt_wrapper">

	<table id="receipt_items" style="font-size: 140% !important">
		<thead>
			<tr>
				<th style="width:30%;">Header1</th>
				<th style="width:20%;">Header2</th>
				<th style="width:20%;">Header2</th>
				<th style="width:30%;" class="total-value">10000 (Kshs)</th>
			</tr>
		</thead>
		<tbody>
		<?php 
			for ($i=0; $i < 4; $i++) { ?>
			<tr>
				<td style="width:40%;">Drug Names Here</td>
				<td style="width:20%;">Price Here</td>
				<td style="width:30%;">5</td>
				<td style="width: 40%"><div class="total-value">150 ksh</div></td>
			</tr>
			<!-- <tr>
				<td colspan="2">Allergy Medication</td>
				<td>111111111</td>
			</tr> -->
				
		<?php	}

		?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
		$('#receipt_items').DataTable( {			 
	        "paging":   false,
	        "ordering": false,
	        "info":     false,
	        "searching":false
	    });	
	    $(".dataTables_wrapper").css("width","100%");
	    
</script>
<?php $this->load->view("partial/footer"); ?>
