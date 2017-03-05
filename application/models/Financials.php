<?php
class Financials extends CI_Model
{
	
	public function get_sales()
	{
		$this->db->select_sum('sales_payments.payment_amount','amount',FALSE);
		$this->db->select('sales.sale_time as time',FALSE);
		$this->db->select('sales_payments.payment_type as name',FALSE);
		$this->db->from('sales');
		$this->db->join('sales_payments', 'sales.sale_id = sales_payments.sale_id', 'left');		
		$this->db->group_by('sales_payments.payment_type');
		$this->db->order_by('sales_payments.payment_type', 'asc');		
		return $this->db->get();
	}

	public function get_assets()
	{
		$this->db->select_sum('assets.price','amount',FALSE);
		$this->db->select('assets.date_of_purchase as time',FALSE);
		$this->db->select('assets.name as name',FALSE);
		$this->db->select('assets.depreciation as depreciation',FALSE);
		$this->db->select('assets.resale_price as resale_price',FALSE);
		$this->db->from('assets');		
		$this->db->group_by('assets.price');
		$this->db->order_by('assets.name', 'asc');		
		return $this->db->get();
	}


	
	
}
?>