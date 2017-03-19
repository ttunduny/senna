<?php
require_once("Report.php");
class Financial_reports extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array('summary' => array('Description','Debit','Credit'),
					 'details' => array('Item Description','Debit','Credit')
		);		
	}
	public function generate_ctime($current_time=null){
		$current_time = date('Y-m-d');
		return $current_time;
	}

	public function generate_ptime($current_time=null,$previous_time=null){		
		$previous_time = date('Y-m-01',strtotime("-1 month",strtotime($current_time)));				
		return $previous_time;
	}

	public function get_sales_tax($filter=null)
	{	$where = null;		
		if(isset($filter)){
			$current_time = $this->generate_ctime();
			$previous_time = $this->generate_ptime($current_time);
			$where = "and s.sale_time <= '$current_time'";
		}
		$sql = "select sum((sit.percent*sp.payment_amount)/100) as amount,sp.payment_type as name from ospos_sales_payments sp,ospos_sales_items_taxes sit,ospos_sales s where sit.sale_id = sp.sale_id and s.sale_id = sit.sale_id and sit.percent>0 $where group by sp.payment_type";	
		$data = array();
		$data['summary'] = $this->db->query($sql)->result_array();
		$data['details'] = array();		

		foreach($data['summary'] as $key=>$value)
		{				
			$sql = "select sum((sit.percent*sp.payment_amount)/100) as amount,sit.item_id as name,sp.payment_type from ospos_sales_payments sp,ospos_sales_items_taxes sit,ospos_sales s where sit.sale_id = sp.sale_id and s.sale_id = sit.sale_id and sit.percent>0 $where group by sp.sale_id";	
			
			$data['details'][$key] = $this->db->query($sql)->result_array();
		}
		return $data;
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
		$this->db->group_by('sales_payments.payment_type');
		$this->db->order_by('sales_payments.payment_type', 'asc');
		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();		

		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select_sum('sales_payments.payment_amount','amount',FALSE);
			$this->db->select('sales.sale_time as time',FALSE);
			$this->db->select('sales_payments.sale_id as name',FALSE);
			$this->db->from('sales');		
			$this->db->join('sales_payments', 'sales.sale_id = sales_payments.sale_id', 'left');				
			if(isset($filter)){
				$current_time = $this->generate_ctime();
				$previous_time = $this->generate_ptime($current_time);
				$this->db->where("sales.sale_time <= '$current_time'");
			}
			$this->db->group_by('sales_payments.sale_id');
			$this->db->order_by('sales_payments.payment_type', 'asc');			
			$data['details'][$key] = $this->db->get()->result_array();
		}

		return $data;
	}

	public function get_assets($filter=null)
	{
		$this->db->select_sum('assets.price','amount',FALSE);
		$this->db->select('assets.date_of_purchase as time',FALSE);
		$this->db->select('asset_category.category_name as name',FALSE);
		$this->db->select('asset_category.id as cat_id',FALSE);
		$this->db->select('assets.name as asset_name',FALSE);
		$this->db->select('assets.depreciation as depreciation',FALSE);
		$this->db->select('assets.resale_price as resale_price',FALSE);
		$this->db->from('assets');				
		$this->db->join('asset_category','assets.category=asset_category.id','left');				
		if(isset($filter)){
			$current_time = $this->generate_ctime();
			$previous_time = $this->generate_ptime($current_time);
			$this->db->where("assets.date_of_purchase <='$current_time'");
		}
		$this->db->group_by('asset_category.id');
		$this->db->order_by('asset_category.category_name', 'asc');		
		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();		
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select_sum('price','amount',FALSE);
			$this->db->select('date_of_purchase as time',FALSE);
			$this->db->select('asset_id,name, category,name, depreciation,resale_price');
			$this->db->from('assets');			
			$this->db->where('category', $value['cat_id']);
			$this->db->group_by('assets.asset_id');
			$this->db->order_by('assets.name', 'asc');	
			$data['details'][$key] = $this->db->get()->result_array();
		}
		return $data;
	}

	public function get_salaries($filter=null)
	{
		$this->db->select_sum('salary.net_sal','amount',FALSE);
		$this->db->select('salary.pay_date as time',FALSE);		
		$this->db->from('salary');		
		if(isset($filter)){
			$current_time = $this->generate_ctime();
			$previous_time = $this->generate_ptime($current_time);
			$this->db->where("salary.pay_date <= '$current_time'");
		}				
		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();		
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('salary.pay_date as time, sum(net_sal) as amount, CONCAT(employee.first_name," ",employee.last_name) as name', false);
			$this->db->from('salary');
			$this->db->join('people as employee', 'salary.person_id = employee.person_id');
			if(isset($filter)){
				$current_time = $this->generate_ctime();
				$previous_time = $this->generate_ptime($current_time);
				$this->db->where("salary.pay_date <= '$current_time'");
			}
			$this->db->group_by('salary.person_id');
			$data['details'][$key] = $this->db->get()->result_array();
		}
		return $data;
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
		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();		
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('salary.pay_date as time, sum(nhif) as amount, CONCAT(employee.first_name," ",employee.last_name) as name', false);
			$this->db->from('salary');
			$this->db->join('people as employee', 'salary.person_id = employee.person_id');
			if(isset($filter)){
				$current_time = $this->generate_ctime();
				$previous_time = $this->generate_ptime($current_time);
				$this->db->where("salary.pay_date <= '$current_time'");
			}
			$this->db->group_by('salary.person_id');
			$data['details'][$key] = $this->db->get()->result_array();
		}
		return $data;
	}
	
	public function get_nssf($filter=null)
	{
		$this->db->select_sum('salary.nssf','amount',FALSE);
		$this->db->select('salary.pay_date as time',FALSE);		
		$this->db->from('salary');		
		if(isset($filter)){
			$current_time = $this->generate_ctime();
			$previous_time = $this->generate_ptime($current_time);
			$this->db->where("salary.pay_date <= '$current_time'");
		}				
		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();		
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('salary.pay_date as time, sum(nssf) as amount, CONCAT(employee.first_name," ",employee.last_name) as name', false);
			$this->db->from('salary');
			$this->db->join('people as employee', 'salary.person_id = employee.person_id');
			if(isset($filter)){
				$current_time = $this->generate_ctime();
				$previous_time = $this->generate_ptime($current_time);
				$this->db->where("salary.pay_date <= '$current_time'");
			}
			$this->db->group_by('salary.person_id');
			$data['details'][$key] = $this->db->get()->result_array();
		}
		return $data;
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
		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();		
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('salary.pay_date as time, sum(tax) as amount, CONCAT(employee.first_name," ",employee.last_name) as name', false);
			$this->db->from('salary');
			$this->db->join('people as employee', 'salary.person_id = employee.person_id');
			if(isset($filter)){
				$current_time = $this->generate_ctime();
				$previous_time = $this->generate_ptime($current_time);
				$this->db->where("salary.pay_date <= '$current_time'");
			}
			$this->db->group_by('salary.person_id');
			$data['details'][$key] = $this->db->get()->result_array();
		}
		return $data;
	}
	
	public function getDataBySaleId($sale_id)
	{
		$this->db->select('sale_id, DATE_FORMAT(sale_time, "%d-%m-%Y") AS sale_date, sum(quantity_purchased) as items_purchased, CONCAT(employee.first_name, " ", employee.last_name) as employee_name, CONCAT(customer.first_name," ",customer.last_name) as customer_name, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(cost) as cost, sum(profit) as profit, payment_type, comment', false);
		$this->db->from('sales_items_temp');
		$this->db->join('people as employee', 'sales_items_temp.employee_id = employee.person_id');
		$this->db->join('people as customer', 'sales_items_temp.customer_id = customer.person_id', 'left');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get()->row_array();
	}
	
	public function getData(array $inputs)
	{
		$this->db->select('sale_id, sale_date, sum(quantity_purchased) as items_purchased, CONCAT(employee.first_name," ",employee.last_name) as employee_name, CONCAT(customer.first_name," ",customer.last_name) as customer_name, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(cost) as cost, sum(profit) as profit, payment_type, comment', false);
		$this->db->from('sales_items_temp');
		$this->db->join('people as employee', 'sales_items_temp.employee_id = employee.person_id');
		$this->db->join('people as customer', 'sales_items_temp.customer_id = customer.person_id', 'left');
		if(isset($inputs['filter'])){
			$current_time = $this->generate_ctime();
			$previous_time = $this->generate_ptime($current_time);
			$this->db->where("sale_date <= '$current_time'");
		}
		
		if ($inputs['sale_type'] == 'sales')
        {
            $this->db->where('quantity_purchased > 0');
        }
        elseif ($inputs['sale_type'] == 'returns')
        {
            $this->db->where('quantity_purchased < 0');
        }

		$this->db->group_by('sale_id');
		$this->db->order_by('sale_date');

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();
		
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('name, category, quantity_purchased, item_location, serialnumber, sales_items_temp.description, subtotal, total, tax, cost, profit, discount_percent');
			$this->db->from('sales_items_temp');
			$this->db->join('items', 'sales_items_temp.item_id = items.item_id');
			$this->db->where('sale_id', $value['sale_id']);
			$data['details'][$key] = $this->db->get()->result_array();
		}
		
		return $data;
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(cost) as cost, sum(profit) as profit');
		$this->db->from('sales_items_temp');
		$this->db->where('sale_date BETWEEN '. $this->db->escape($inputs['start_date']). ' and '. $this->db->escape($inputs['end_date']));

		if ($inputs['location_id'] != 'all')
		{
			$this->db->where('item_location', $inputs['location_id']);
		}

		if ($inputs['sale_type'] == 'sales')
        {
            $this->db->where('quantity_purchased > 0');
        }
        elseif ($inputs['sale_type'] == 'returns')
        {
            $this->db->where('quantity_purchased < 0');
        }

		return $this->db->get()->row_array();
	}
}
?>