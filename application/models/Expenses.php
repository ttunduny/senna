<?php
class Expenses extends CI_Model
{
	function get_info($receiving_id)
	{
		$this->db->from('employees');
		$this->db->join('people', 'people.person_id = employees.person_id', 'INNER');
		$this->db->where('person_id',$receiving_id);
		return $this->db->get();
	}

	function get_info_expense($expense_id)
	{
		$this->db->select("expenses.expense_id as expense_id,people.person_id as person_id,people.first_name as first_name, people.last_name as last_name,expenses.name as name, expense_category.category_name as category_name, expenses.amount as amount, expenses.date_paid as date_paid");
		$this->db->from('expenses');
		$this->db->join('people', 'people.person_id = expenses.created_by', 'left');
		$this->db->join('expense_category', 'expense_category.id = expenses.category', 'left');
		$this->db->where('expenses.id',$expense_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $supplier_id is NOT an supplier
			$person_obj=parent::get_info(-1);
			
			//Get all the fields from supplier table
			$fields = $this->db->list_fields('expenses');
			
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$person_obj->$field='';
			}
			
			return $person_obj;
		}
	}

	public function get_all_expense($rows=0, $limit_from=0)
	{
		$this->db->select("expenses.expense_id as expense_id,people.person_id as person_id,people.first_name as first_name, people.last_name as last_name,expenses.name as name, expense_category.category_name as category_name, expenses.amount as amount, expenses.date_paid as date_paid");
		$this->db->from('expenses');
		$this->db->join('people', 'people.person_id = expenses.created_by', 'left');
		$this->db->join('expense_category', 'expense_category.id = expenses.category', 'left');

	
		$this->db->where('expenses.isDeleted', 0);
		
		// order by name of item
		$this->db->order_by('people.last_name', 'desc');

		if ($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}


	
	function get_invoice_count()
	{
		$this->db->from('receivings');
		$this->db->where('invoice_number is not null');
		return $this->db->count_all_results();
	}
	
	function get_receiving_by_invoice_number($invoice_number)
	{
		$this->db->from('receivings');
		$this->db->where('invoice_number', $invoice_number);
		return $this->db->get();
	}

	function get_search_suggestions($search, $limit=25)
	{
		$suggestions = array();

		$this->db->from('giftcards');
		$this->db->like('giftcard_number', $search);
		$this->db->where('deleted', 1);
		$this->db->order_by('giftcard_number', 'asc');
		$by_number = $this->db->get();
		
		foreach($by_number->result() as $row)
		{
			$suggestions[]=$row->giftcard_number;
		}

 		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id=people.person_id', 'left');
		$this->db->like('first_name', $this->db->escape_like_str($search));
		$this->db->or_like('last_name', $this->db->escape_like_str($search)); 
		$this->db->or_like('CONCAT(first_name, " ", last_name)', $this->db->escape_like_str($search));
		$this->db->where('deleted', 0);
		$this->db->order_by('last_name', 'asc');
		$by_name = $this->db->get();
		
		foreach($by_name->result() as $row)
		{
			$suggestions[]=$row->first_name.' '.$row->last_name;
		}			

		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}
	
	function get_invoice_number_for_year($year='', $start_from = 0)
	{
		$year = $year == '' ? date('Y') : $year;
		$this->db->select("COUNT( 1 ) AS invoice_number_year", FALSE);
		$this->db->from('receivings');
		$this->db->where("DATE_FORMAT(receiving_time, '%Y' ) = ", $year, FALSE);
		$this->db->where("invoice_number IS NOT ", "NULL", FALSE);
		$result = $this->db->get()->row_array();
		return ($start_from + $result[ 'invoice_number_year' ] + 1);
	}
	
	function exists($receiving_id)
	{
		$this->db->from('receivings');
		$this->db->where('receiving_id',$receiving_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	function update($receiving_data, $receiving_id)
	{
		$this->db->where('receiving_id', $receiving_id);
		$success = $this->db->update('receivings',$receiving_data);
	
		return $success;
	}

	

	function save ()
	{
		echo "<pre>";print_r($cart);echo "</pre>";

		// foreach($items as $line=>$item)
		// {
		// 	$cur_item_info = $this->Item->get_info($item['item_id']);
			
		// 		$salary_info = array(
		// 		'receiving_id'=>$receiving_id,
		// 		'item_id'=>$item['item_id'],
		// 		'line'=>$item['line'],
		// 		'description'=>$item['description'],
		// 		'serialnumber'=>$item['serialnumber'],
		// 		'quantity_purchased'=>$item['receiving_quantity'] != 0 ? $item['quantity'] * $item['receiving_quantity'] : $item['quantity'],
		// 		'receiving_quantity'=>$item['receiving_quantity'],
		// 		'expiry_date'=>$item['expiry'],
		// 		'discount_percent'=>$item['discount'],
				
		// 		'item_unit_price'=>$item['price'],
		// 		'item_location'=>$item['item_location']
		// 	);

		// 	$this->db->insert('receivings_items',$salary_info);
	
		// 	$this->db->trans_complete();
			
		// 	return $receiving_id;
	}
	
	function delete_list($supplier_ids)
	{
		$this->db->where_in('person_id',$supplier_ids);
		return $this->db->update('suppliers', array('deleted' => 1));
 	}
	
	function delete($receiving_id,$employee_id,$update_inventory=TRUE)
	{
		// start a transaction to assure data integrity
		$this->db->trans_start();
		if ($update_inventory) {
			// defect, not all item deletions will be undone??
			// get array with all the items involved in the sale to update the inventory tracking
			$items = $this->get_receiving_items($receiving_id)->result_array();
			foreach($items as $item) {
				// create query to update inventory tracking
				$inv_data = array
				(
						'trans_date'=>date('Y-m-d H:i:s'),
						'trans_items'=>$item['item_id'],
						'trans_user'=>$employee_id,
						'trans_comment'=>'Deleting receiving ' . $receiving_id,
						'trans_location'=>$item['item_location'],
						'trans_inventory'=>$item['quantity_purchased']*-1
	
				);
				// update inventory
				$this->Inventory->insert($inv_data);

				// update quantities
				$this->Item_quantity->change_quantity($item['item_id'],
														$item['item_location'],
														$item['quantity_purchased']*-1);
			}
		}
		// delete all items
		$this->db->delete('receivings_items', array('receiving_id' => $receiving_id));
		// delete sale itself
		$this->db->delete('receivings', array('receiving_id' => $receiving_id));
		// execute transaction
		$this->db->trans_complete();
	
		return $this->db->trans_status();
	}

	function get_receiving_items($receiving_id)
	{
		$this->db->from('employees');
		$this->db->join('people','employees.person_id=people.person_id');	
		$this->db->where('person_id',$receiving_id);
		return $this->db->get();
	}
	
	function get_employee($receiving_id)
	{
		$this->db->from('people');
		$this->db->where('person_id',$receiving_id);
		return $this->Employee->get_info($this->db->get()->row()->employee_id);
	}
	
	function invoice_number_exists($invoice_number,$receiving_id='')
	{
		$this->db->from('receivings');
		$this->db->where('invoice_number', $invoice_number);
		if (!empty($receiving_id))
		{
			$this->db->where('receiving_id !=', $receiving_id);
		}
		$query=$this->db->get();
		return ($query->num_rows()==1);
	}
	

	
   
}

?>
