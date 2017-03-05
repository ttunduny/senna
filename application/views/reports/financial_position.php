<?php 
	$this->load->view("partial/header");
?>
<div id="page_title" style="margin-bottom:8px;"><span id="company_title"><img src="<?php echo base_url().'images/senna.jpg';?>" border="0" alt="Menubar Image " 
				height="60" width="150"></span><br /><?php echo $title ?></div>
<div id="page_subtitle" style="margin-bottom:8px;"><?php echo $subtitle ?></div>
<div id="table_holder">
	<table class="" id="sortable_table">
		<thead>
			<tr>
				<th>Description</th>				
				<th>Debit</th>				
				<th>Credit</th>								
			</tr>
		</thead>
		<tbody>
		<?php
			foreach ($final_array as $key => $values) {					
				$name = $values['name'];
				$amount = round($values['amount'],2);
				$type = $values['type'];
				$debit = null;
				$credit = null;
				if($type=='debits'){
					$debit = $amount;
					$credit = '-';
				}else{
					$credit = $amount;
					$debit = '-';
				}				
		
				?>
				<tr>
					<td><?php echo $name;?></td>
					<td><?php echo $debit;?></td>
					<td><?php echo $credit;?></td>					
				</tr>
				<?php 				
			}
		?>	
		<tr>
			<b>
				<td>TOTALS</td>
				<td><?php echo round($totals['sum_debits'],2);?></td>
				<td><?php echo round($totals['sum_credits'],2);?></td>
				
			</b>
		</tr>		
		</tbody>
	</table>
</div>
<?php 
	$this->load->view("partial/footer"); 
?>

<script type="text/javascript" language="javascript">
function init_table_sorting()
{
	//Only init if there is more than one row
	if($('.tablesorter tbody tr').length >1)
	{
		$("#sortable_table").tablesorter(); 
	}
}
$(document).ready(function()
{
	init_table_sorting();
});
</script>
