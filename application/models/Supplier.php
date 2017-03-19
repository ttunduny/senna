<?php
class Supplier extends Person
{	
	/*
	Determines if a given person_id is a customer
	*/
	function exists($person_id)
	{
		$this->db->from('suppliers');	
		$this->db->join('people', 'people.person_id = suppliers.person_id');
		$this->db->where('suppliers.person_id',$person_id);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function get_total_rows()
	{
		$this->db->from('suppliers');
		$this->db->where('deleted',0);
		return $this->db->count_all_results();
	}
	
	/*
	Returns all the suppliers
	*/
	function get_all($limit_from = 0, $rows = 0)
	{
		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');			
		$this->db->where('deleted', 0);
		$this->db->order_by("company_name", "asc");
		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}
		return $this->db->get();		
	}

	public function get_all_salary($rows=0, $limit_from=0)
	{
		$this->db->select("salary.id as salary_id,people.person_id as person_id,people.first_name as first_name, people.last_name as last_name,salary.gross_sal as gross_sal, salary.nssf as nssf, salary.nhif as nhif, salary.tax as tax, salary.pay_date as pay_date,salary.net_sal as net_sal");
		$this->db->from('salary');
		$this->db->join('people', 'people.person_id = salary.person_id', 'left');
		$this->db->join('employees', 'people.person_id = employees.person_id', 'left');

	
		$this->db->where('employees.deleted', 0);
		
		// order by name of item
		$this->db->order_by('people.last_name', 'desc');

		if ($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	


	public function get_all_expense($rows=0, $limit_from=0)
	{
		$this->db->select("expenses.id as expense_id,people.person_id as person_id,people.first_name as first_name, people.last_name as last_name,expenses.name as name, expense_category.category_name as category_name, expenses.amount as amount, expenses.date_paid as date_paid");
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

	
	function count_all()
	{
		$this->db->from('suppliers');
		$this->db->where('deleted',0);
		return $this->db->count_all_results();
	}
	
	/*
	Gets information about a particular supplier
	*/
	function get_info($supplier_id)
	{
		$this->db->from('suppliers');	
		$this->db->join('people', 'people.person_id = suppliers.person_id');
		$this->db->where('suppliers.person_id',$supplier_id);
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
			$fields = $this->db->list_fields('suppliers');
			
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$person_obj->$field='';
			}
			
			return $person_obj;
		}
	}

	
	
	/*
	Gets information about multiple suppliers
	*/
	function get_multiple_info($suppliers_ids)
	{
		$this->db->from('suppliers');
		$this->db->join('people', 'people.person_id = suppliers.person_id');		
		$this->db->where_in('suppliers.person_id',$suppliers_ids);
		$this->db->order_by("last_name", "asc");
		return $this->db->get();		
	}
	
	/*
	Inserts or updates a suppliers
	*/
	function save_supplier(&$person_data, &$supplier_data,$supplier_id=false)
	{
		$success=false;
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
		
		if(parent::save($person_data,$supplier_id))
		{
			if (!$supplier_id or !$this->exists($supplier_id))
			{
				$supplier_data['person_id'] = $person_data['person_id'];
				$success = $this->db->insert('suppliers',$supplier_data);				
			}
			else
			{
				$this->db->where('person_id', $supplier_id);
				$success = $this->db->update('suppliers',$supplier_data);
			}
			
		}
		
		$this->db->trans_complete();		
		return $success;
	}
	
	/*
	Deletes one supplier
	*/
	function delete($supplier_id)
	{
		$this->db->where('person_id', $supplier_id);
		return $this->db->update('suppliers', array('deleted' => 1));
	}
	
	/*
	Deletes a list of suppliers
	*/
	function delete_list($supplier_ids)
	{
		$this->db->where_in('person_id',$supplier_ids);
		return $this->db->update('suppliers', array('deleted' => 1));
 	}
 	
 	/*
	Get search suggestions to find suppliers
	*/
	function get_search_suggestions($search,$limit=25)
	{
		$suggestions = array();
		
		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');	
		$this->db->where('deleted', 0);
		$this->db->like("company_name",$search);
		$this->db->order_by("company_name", "asc");		
		$by_company_name = $this->db->get();
		foreach($by_company_name->result() as $row)
		{
			$suggestions[]=$row->company_name;		
		}

		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');	
		$this->db->where('deleted', 0);
		$this->db->distinct();
		$this->db->like("agency_name",$search);
		$this->db->order_by("agency_name", "asc");		
		$by_agency_name = $this->db->get();
		foreach($by_agency_name->result() as $row)
		{
			$suggestions[]=$row->agency_name;		
		}
		
		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');	
		$this->db->where("(first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
		last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%".$this->db->escape_like_str($search)."%') and deleted=0");
		$this->db->order_by("last_name", "asc");		
		$by_name = $this->db->get();
		foreach($by_name->result() as $row)
		{
			$suggestions[]=$row->first_name.' '.$row->last_name;		
		}
		
		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');	
		$this->db->where('deleted', 0);
		$this->db->like("email",$search);
		$this->db->order_by("email", "asc");		
		$by_email = $this->db->get();
		foreach($by_email->result() as $row)
		{
			$suggestions[]=$row->email;		
		}

		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');	
		$this->db->where('deleted', 0);
		$this->db->like("phone_number",$search);
		$this->db->order_by("phone_number", "asc");		
		$by_phone = $this->db->get();
		foreach($by_phone->result() as $row)
		{
			$suggestions[]=$row->phone_number;		
		}
		
		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');	
		$this->db->where('deleted', 0);
		$this->db->like("account_number",$search);
		$this->db->order_by("account_number", "asc");		
		$by_account_number = $this->db->get();
		foreach($by_account_number->result() as $row)
		{
			$suggestions[]=$row->account_number;		
		}
		
		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;
	
	}

	
function get_employee_search_suggestions($search,$limit=25)
	{
		$suggestions = array();
		
		$this->db->from('employees');
		$this->db->join('people','employees.person_id=people.person_id');	
		$this->db->where("(first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
		last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%".$this->db->escape_like_str($search)."%') and deleted=0");
		$this->db->order_by("last_name", "asc");		
		$by_name = $this->db->get();
		foreach($by_name->result() as $row)
		{
			$suggestions[]=$row->person_id.'|'.$row->first_name.' '.$row->last_name;		
		}
		
		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;

	}
	
	/*
	Get search suggestions to find suppliers
	*/
	function get_suppliers_search_suggestions($search,$limit=25)
	{
		$suggestions = array();
		
		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');	
		$this->db->where('deleted', 0);
		$this->db->like("company_name",$search);
		$this->db->order_by("company_name", "asc");		
		$by_company_name = $this->db->get();
		foreach($by_company_name->result() as $row)
		{
			$suggestions[]=$row->person_id.'|'.$row->company_name;		
		}


		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');	
		$this->db->where('deleted', 0);
		$this->db->distinct();
		$this->db->like("agency_name",$search);
		$this->db->order_by("agency_name", "asc");		
		$by_agency_name = $this->db->get();
		foreach($by_agency_name->result() as $row)
		{
			$suggestions[]=$row->person_id.'|'.$row->agency_name;		
		}


		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');	
		$this->db->where("(first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
		last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%".$this->db->escape_like_str($search)."%') and deleted=0");
		$this->db->order_by("last_name", "asc");		
		$by_name = $this->db->get();
		foreach($by_name->result() as $row)
		{
			$suggestions[]=$row->person_id.'|'.$row->first_name.' '.$row->last_name;		
		}
		
		//only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;

	}
	
	function get_found_rows($search)
	{
		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');
		$this->db->where("(first_name LIKE '%".$this->db->escape_like_str($search)."%' or
		last_name LIKE '%".$this->db->escape_like_str($search)."%' or
		company_name LIKE '%".$this->db->escape_like_str($search)."%' or
		agency_name LIKE '%".$this->db->escape_like_str($search)."%' or
		email LIKE '%".$this->db->escape_like_str($search)."%' or
		phone_number LIKE '%".$this->db->escape_like_str($search)."%' or
		account_number LIKE '%".$this->db->escape_like_str($search)."%' or
		CONCAT(`first_name`,' ',`last_name`) LIKE '%".$this->db->escape_like_str($search)."%') and deleted=0");
		return $this->db->get()->num_rows();
	}
	
	/*
	Perform a search on suppliers
	*/
	function search($search, $rows = 0, $limit_from = 0)
	{
		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');
		$this->db->where("(first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
		last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
		company_name LIKE '%".$this->db->escape_like_str($search)."%' or 
		agency_name LIKE '%".$this->db->escape_like_str($search)."%' or 
		email LIKE '%".$this->db->escape_like_str($search)."%' or 
		phone_number LIKE '%".$this->db->escape_like_str($search)."%' or 
		account_number LIKE '%".$this->db->escape_like_str($search)."%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%".$this->db->escape_like_str($search)."%') and deleted=0");		
		$this->db->order_by("last_name", "asc");
		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}
		return $this->db->get();	
	}

}
?>
