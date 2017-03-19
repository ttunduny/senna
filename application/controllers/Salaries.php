
<?php
require_once ("Secure_area.php");
class Salaries extends Secure_area
{
	function __construct()
	{
		parent::__construct('salaries');
		$this->load->library('Salaries_lib');
		$this->load->library('barcode_lib');
	}

	function index()
	{
		$this->_reload();
	}

	function search()
	{
		$search = $this->input->post('search') != '' ? $this->input->post('search') : null;
		$limit_from = $this->input->post('limit_from');
		$lines_per_page = $this->Appconfig->get('lines_per_page');

		$giftcards = $this->Giftcard->search_salary($search, $lines_per_page, $limit_from);
		$total_rows = $this->Giftcard->get_found_salary_rows($search);
		// $links = $this->_initialize_pagination($this->Giftcard, $lines_per_page, $limit_from, $total_rows);
		$data_rows = get_salary_manage_table($giftcards, $this);

		echo json_encode(array('total_rows' => $total_rows, 'rows' => $data_rows, 'pagination' => $links));
	}

	function employee_search()
	{
		$suggestions = $this->Supplier->get_employee_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Giftcard->get_salary_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function supplier_search()
	{
		$suggestions = $this->Supplier->get_suppliers_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function select_supplier()
	{
		$employee_id = $this->input->post('supplier');
		if ($this->Supplier->exists($employee_id))
		{
			$this->salaries_lib->set_supplier($employee_id);
		}
		$this->_reload();
	}

	function change_mode()
	{
		$stock_destination = $this->input->post('stock_destination');
		$stock_source = $this->input->post('stock_source');
		if ((!$stock_source || $stock_source == $this->salaries_lib->get_stock_source()) &&
			(!$stock_destination || $stock_destination == $this->salaries_lib->get_stock_destination()))
		{
			$this->salaries_lib->clear_invoice_number();
			$mode = $this->input->post('mode');
			$this->salaries_lib->set_mode($mode);
		}
		else if ($this->Stock_location->is_allowed_location($stock_source, 'receivings'))
		{
			$this->salaries_lib->set_stock_source($stock_source);
			$this->salaries_lib->set_stock_destination($stock_destination);
		}
		$this->_reload();
	}
	
	function set_comment()
	{
		$this->salaries_lib->set_comment($this->input->post('comment'));
	}
	
	function set_invoice_number_enabled()
	{
		$this->salaries_lib->set_invoice_number_enabled($this->input->post('recv_invoice_number_enabled'));
	}
	
	function set_print_after_sale()
	{
		$this->salaries_lib->set_print_after_sale($this->input->post('recv_print_after_sale'));
	}
	
	function set_invoice_number()
	{
		$this->salaries_lib->set_invoice_number($this->input->post('recv_invoice_number'));
	}
	
	function add()
	{
		$data=array();
		
		$emp_id = $this->input->post('item');
		$gross_sal=$this->input->post('gross_sal');
		$nssf=$this->input->post('nssf');
		$nhif=$this->input->post('nhif');
		$tax=$this->input->post('vat');

            if(!$this->salaries_lib->add_item($emp_id,$gross_sal,$nssf,$nhif,$tax))
                   $data['error']=$this->lang->line('recvs_unable_to_add_item');
            	
		
		$this->_reload($data);
	}

	// function edit_item($item_id)
	// {
	// 	$data= array();		
	// 	$this->form_validation->set_rules('price', 'lang:items_price', 'required|numeric');
	// 	$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|numeric');
	// 	$this->form_validation->set_rules('discount', 'lang:items_discount', 'required|numeric');

 //    	$description = $this->input->post('description');
 //    	$receiving_quantity = $this->input->post('receiving_quantity');
 //    	if($receiving_quantity==null){
 //    		$items = $this->salaries_lib->get_cart();
 //    		if(count($items)<=2){
 //    			$receiving_quantity = $items[1]['receiving_quantity'];
 //    		}else{
 //    			$my_count = count($items);
 //    			$receiving_quantity = $items[$my_count]['receiving_quantity'];

 //    		}
 //    		// echo "<pre>";print_r($items);
 //    		// $receiving_quantity = $items[1]['receiving_quantity'];
 //    	}
    	
	// 	$price = $this->input->post('price');
	// 	$quantity = $this->input->post('quantity');
	// 	$discount = $this->input->post('discount');
	// 	$item_location = $this->input->post('location');
	// 	$expiry = $this->input->post('expiry');
	// 	$vat = $this->input->post('vat');
	// 	// echo "Editt RQ $receiving_quantity  DC $discount<br/>";

	// 	if ($this->form_validation->run() != FALSE)
	// 	{
	// 		$this->salaries_lib->edit_item($item_id,$description,$receiving_quantity,$quantity,$discount,$expiry,$price,$vat);
	// 		// $line,$,$serialnumber,$,$,$,$,
	// 	}
	// 	else
	// 	{
	// 		$data['error']=$this->lang->line('recvs_error_editing_item');
	// 	}

		
		
	// 	$this->_reload($data);
	// }
	
	function edit($receiving_id)
	{
		$data = array();
	
		$data['suppliers'] = array('' => 'No Supplier');
		foreach ($this->Supplier->get_all()->result() as $supplier)
		{
			$data['suppliers'][$supplier->person_id] = $supplier->first_name . ' ' . $supplier->last_name;
		}
	
		$data['employees'] = array();
		foreach ($this->Employee->get_all()->result() as $employee)
		{
			$data['employees'][$employee->person_id] = $employee->first_name . ' '. $employee->last_name;
		}
	
		$receiving_info = $this->Receiving->get_info($receiving_id)->row_array();
		$person_name = $receiving_info['first_name'] . " " . $receiving_info['last_name'];
		$data['selected_supplier'] = !empty($receiving_info['employee_id']) ? $receiving_info['employee_id'] . "|" . $person_name : "";
		$data['receiving_info'] = $receiving_info;
	
		$this->load->view('salaries/form', $data);
	}

	function delete_item($item_number)
	{
		$this->salaries_lib->delete_item($item_number);
		$this->_reload();
	}
	
	function delete($receiving_id = -1, $update_inventory=TRUE) 
	{
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$receiving_ids=$receiving_id == -1 ? $this->input->post('ids') : array($receiving_id);
	
		if($this->Receiving->delete_list($receiving_ids, $employee_id, $update_inventory))
		{
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('recvs_successfully_deleted').' '.
					count($receiving_ids).' '.$this->lang->line('recvs_one_or_multiple'),'ids'=>$receiving_ids));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('recvs_cannot_be_deleted')));
		}
	}

	function delete_supplier()
	{
		$this->salaries_lib->clear_invoice_number();
		$this->salaries_lib->delete_supplier();
		$this->_reload();
	}

		
	private function _substitute_variable($text, $variable, $object, $function)
	{
		// don't query if this variable isn't used
		if (strstr($text, $variable))
		{
			$value = call_user_func(array($object, $function));
			$text = str_replace($variable, $value, $text);
		}
		return $text;
	}
	
	private function _substitute_variables($text,$supplier_info)
	{
		$text=$this->_substitute_variable($text, '$YCO', $this->Receiving, 'get_invoice_number_for_year');
		$text=$this->_substitute_variable($text, '$CO', $this->Receiving , 'get_invoice_count');
		$text=strftime($text);
		$text=$this->_substitute_supplier($text, $supplier_info);
		return $text;
	}
	

	private function _substitute_supplier($text,$supplier_info)
	{
		$employee_id=$this->salaries_lib->get_employee();
		if($employee_id!=-1)
		{
			$text=str_replace('$SU',$supplier_info->company_name,$text);
			$words = preg_split("/\s+/", trim($supplier_info->company_name));
			$acronym = "";
			foreach ($words as $w) {
				$acronym .= $w[0];
			}
			$text=str_replace('$SI',$acronym,$text);
		}
		return $text;

	}
	
	private function _substitute_invoice_number($supplier_info='')
	{
		$invoice_number=$this->salaries_lib->get_invoice_number();
		$invoice_number=$this->config->config['recv_invoice_format'];
		$invoice_number = $this->_substitute_variables($invoice_number,$supplier_info);
		$this->salaries_lib->set_invoice_number($invoice_number);
		return $this->salaries_lib->get_invoice_number();
	}

    function requisition_complete()
    {
    	if ($this->salaries_lib->get_stock_source() != $this->salaries_lib->get_stock_destination()) 
    	{
    		foreach($this->salaries_lib->get_cart() as $item)
    		{
    			$this->salaries_lib->delete_item($item['line']);
    			$this->salaries_lib->add_item($item['item_id'],$item['quantity'],$this->salaries_lib->get_stock_destination());
    			$this->salaries_lib->add_item($item['item_id'],-$item['quantity'],$this->salaries_lib->get_stock_source());
    		}
    		
			$this->complete();
    	}
    	else 
    	{
    		$data['error']=$this->lang->line('recvs_error_requisition');
    		$this->_reload($data);	
    	}
    }
    
	function receipt($receiving_id)
	{
		$receiving_info = $this->Receiving->get_info($receiving_id)->row_array();
		$this->salaries_lib->copy_entire_receiving($receiving_id);
		$data['cart']=$this->salaries_lib->get_cart();
		$data['total']=$this->salaries_lib->get_total();
		$data['mode']=$this->salaries_lib->get_mode();
		$data['receipt_title']=$this->lang->line('recvs_receipt');
		$data['transaction_time']= date($this->config->item('dateformat').' '.$this->config->item('timeformat'), strtotime($receiving_info['receiving_time']));
		$data['show_stock_locations']=$this->Stock_location->show_locations('receivings');
		$employee_id=$this->salaries_lib->get_supplier();
		$emp_info=$this->Employee->get_info($receiving_info['employee_id']);
		$data['payment_type']=$receiving_info['payment_type'];
		$data['invoice_number']=$this->salaries_lib->get_invoice_number();
		$data['receiving_id']='RECV '.$receiving_id;
		$data['barcode']=$this->barcode_lib->generate_receipt_barcode($data['receiving_id']);
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name;

		if($employee_id!=-1)
		{
			$supplier_info=$this->Supplier->get_info($employee_id);
			$data['supplier']=$supplier_info->first_name.' '.$supplier_info->last_name;
		}
		$data['print_after_sale'] = FALSE;
		$this->load->view("salaries/receipt",$data);
		$this->salaries_lib->clear_all();
	}

	private function _reload($data=array())
	{
		
		$data['cart']=$this->salaries_lib->get_cart();
	
		$this->load->view("salaries/salaries",$data);
	}
	
	function save()
	{
		// echo "<pre>";print_r($data);echo "</pre>"; 
		$items=$this->salaries_lib->get_cart();

		// echo $expire;
		// echo "<pre>";print_r($items);echo "</pre>"; 
		foreach($items as $line=>$item)
		 {
		 	$salary_info = array(
				'person_id'=>$item['person_id'],
				'gross_sal'=>$item['gross_sal'],
				'nhif'=>$item['nhif'],
				'nssf'=>$item['nssf'],
				'tax'=>$item['tax'],
				'net_sal'=>($item['gross_sal']-($item['nhif']+$item['nssf']+$item['tax']))	,
				'pay_date'=>date('Y-m-d')	

			);
		$this->db->insert('salary',$salary_info);		
		// echo "<pre>";print_r($salary_info);echo "</pre>"; 
		}
		$this->db->trans_complete();


		$this->salaries_lib->empty_cart();
		redirect('salaries');
	}

    function cancel_receiving()
    {
    	$this->salaries_lib->clear_all();
    	$this->_reload();
    }
    
    function check_invoice_number()
    {
		$receiving_id=$this->input->post('receiving_id');
		$invoice_number=$this->input->post('invoice_number');
		$exists=!empty($invoice_number) && $this->Receiving->invoice_number_exists($invoice_number,$receiving_id);
		echo !$exists ? 'true' : 'false';
    }
}
?>
