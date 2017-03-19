<?php
class Financials extends CI_Model
{
	public function generate_ctime($current_time=null){
		$current_time = date('Y-m-d');
		return $current_time;
	}

	public function generate_ptime($current_time=null,$previous_time=null){		
		$previous_time = date('Y-m-01',strtotime("-1 month",strtotime($current_time)));				
		return $previous_time;
	}
	
	public function get_sales($filter=null)
	{		
		$this->db->select_sum('sales_payments.payment_amount','amount',FALSE);
		$this->db->select('sales.sale_time as time',FALSE);
		$this->db->select('sales_payments.payment_type as name',FALSE);
		$this->db->from('sales');		
		$this->db->join('sales_payments', 'sales.sale_id = sales_payments.sale_id', 'left');				
		if(isset($filter)){
			$current_time = $this->generate_ctime();
			$previous_time = $this->generate_ptime($current_time);
			$this->db->where("sales.sale_time <= '$current_time'");
		}
		$this->db->order_by('sales_payments.payment_type', 'asc');		
		return $this->db->get();
	}

	public function get_sales_tax($filter=null)
	{		
		// if(isset($filter)){
		// 	$this->db->where('and $filter');
		// }
		$sql = "select sum((sit.percent*sp.payment_amount)/100) as amount,sit.name from ospos_sales_payments sp,ospos_sales_items_taxes sit where sit.sale_id = sp.sale_id";	
		return $this->db->query($sql);
	}

	
	public function get_assets($filter=null)
	{
		$this->db->select_sum('assets.price','amount',FALSE);
		$this->db->select('assets.date_of_purchase as time',FALSE);
		$this->db->select('assets.name as name',FALSE);
		$this->db->select('assets.depreciation as depreciation',FALSE);
		$this->db->select('assets.resale_price as resale_price',FALSE);
		$this->db->from('assets');		
		// if(isset($filter)){
		// 	$this->db->where('assets.date_of_purchase $filter');
		// }
		if(isset($filter)){
			$current_time = $this->generate_ctime();
			$previous_time = $this->generate_ptime($current_time);
			$this->db->where("assets.date_of_purchase <='$current_time'");
		}
		$this->db->group_by('assets.price');
		$this->db->order_by('assets.name', 'asc');		
		return $this->db->get();
	}

	public function get_salaries($filter=null)
	{
		$this->db->select_sum('salary.net_sal','amount',FALSE);
		$this->db->select('salary.pay_date as time',FALSE);		
		$this->db->from('salary');
		// if(isset($filter)){
		// 	$this->db->where('salary.pay_date $filter');
		// }		
		if(isset($filter)){
			$current_time = $this->generate_ctime();
			$previous_time = $this->generate_ptime($current_time);
			$this->db->where("salary.pay_date <= '$current_time'");
		}				
		return $this->db->get();
	}

	public function get_nssf($filter=null)
	{
		$this->db->select_sum('salary.nssf','amount',FALSE);
		$this->db->select('salary.pay_date as time',FALSE);		
		$this->db->from('salary');	
		if(isset($filter)){
			$current_time = $this->generate_ctime();
			$previous_time = $this->generate_ptime($current_time);
			$this->db->where("salary.pay_date <='$current_time'");
		}						
		return $this->db->get();
	}

	public function get_nhif($filter=null)
	{
		$this->db->select_sum('salary.nhif','amount',FALSE);
		$this->db->select('salary.pay_date as time',FALSE);		
		$this->db->from('salary');	
		if(isset($filter)){
			$current_time = $this->generate_ctime();
			$previous_time = $this->generate_ptime($current_time);
			$this->db->where("salary.pay_date <= '$current_time'");
		}						
		return $this->db->get();
	}
	public function get_paye($filter=null)
	{
		$this->db->select_sum('salary.tax','amount',FALSE);
		$this->db->select('salary.pay_date as time',FALSE);		
		$this->db->from('salary');		
		if(isset($filter)){
			$current_time = $this->generate_ctime();
			$previous_time = $this->generate_ptime($current_time);
			$this->db->where("salary.pay_date <= '$current_time'");
		}					
		return $this->db->get();
	}



	
	
}
?>