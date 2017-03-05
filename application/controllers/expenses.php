<?php
require_once ("Person_controller.php");

class Expenses extends Person_controller
{
	function __construct()
	{
		parent::__construct('Expenses');
	}
	
	function index($limit_from=0)
	{
		$data['controller_name'] = $this->get_controller_name();
		$data['form_width'] = $this->get_form_width();
		$lines_per_page = $this->Appconfig->get('lines_per_page');
		$Expenses = $this->Supplier->get_all_expense($lines_per_page);
		
		$data['links'] = $this->_initialize_pagination($this->Supplier, $lines_per_page, $limit_from);
		$data['manage_table'] = get_expense_manage_table($Expenses, $this);
		$this->load->view('expenses/manage', $data);
	}
	/*
	Returns Supplier table data rows. This will be called with AJAX.
	*/
	function search()
	{
		$search = $this->input->post('search') != '' ? $this->input->post('search') : null;
		$limit_from = $this->input->post('limit_from');
		$lines_per_page = $this->Appconfig->get('lines_per_page');

		$suppliers = $this->Supplier->search($search, $lines_per_page, $limit_from);
		$total_rows = $this->Supplier->get_found_rows($search);
		$links = $this->_initialize_pagination($this->Supplier, $lines_per_page, $limit_from, $total_rows);
		$data_rows = get_supplier_manage_table_data_rows($suppliers, $this);

		echo json_encode(array('total_rows' => $total_rows, 'rows' => $data_rows, 'pagination' => $links));
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Supplier->get_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}
	/*
	Loads the supplier edit form
	*/
	function view($expense_id=-1)
	{
		$expense_data['expense_info']=$this->Asset->get_info_expense($expense_id);	
		// $data['manage_table'] = get_assets_manage_table( $this->Asset->get_all($lines_per_page, $limit_from), $this );
		// echo "<pre>";print_r($data);die;
		$this->load->view("expenses/form", $expense_data);
	}

	function view2($expense_id=-1)
	{
	
		// $data['manage_table'] = get_assets_manage_table( $this->Asset->get_all($lines_per_page, $limit_from), $this );
		// echo "<pre>";print_r($data);die;
		$this->load->view("expenses/form2");
	}

	function save($salary_id=-1)
	{
		$pay_date =date("Y-m-d"); 
		$gross_sal = $_POST['sal_gross'];
		$nhif = $_POST['sal_nhif'];
		$nssf = $_POST['sal_nssf'];
		$tax = $_POST['sal_tax'];
		$net_sal = $_POST['sal_net'];
		$salary_id = $_POST['emp_sal_no'];
	

		$this->salaryedit->save($person_data,$salary_id);
		print_r($today); die;

		$sql = "UPDATE ospos_salary SET gross_sal = '$gross_sal',nhif = '$nhif', 
		nssf = '$nssf',tax = '$tax',net_sal = '$net_sal'
		WHERE id ='$salary_id' ";	
		$this->db->query($sql);
		redirect('salaryedit');
		
		
	}
	function edit()
	{
		$exp_name = $_POST['exp_name'];
		$exp_cat = $_POST['exp_cat'];
		$exp_amount = $_POST['exp_amount'];
		$expense_id = $_POST['expense_id'];

		$salary_id = $_POST['emp_sal_no'];
	
		$sql = "UPDATE ospos_expenses SET name = '$exp_name',category = '$exp_cat', 
		amount = '$exp_amount'
		WHERE expense_id ='$expense_id' ";	
		$this->db->query($sql);
		
		header("Location: http://$_SERVER[HTTP_HOST]/senna/index.php/expenses");
	}

	function add()
	{
		$pay_date =date("Y-m-d"); 
		$exp_name = $_POST['exp_name'];
		$exp_cat = $_POST['exp_cat'];
		$emp_exp_id = $_POST['emp_exp_id'];
		$exp_amount = $_POST['exp_amount'];
		
	
		$sql = "INSERT INTO ospos_expenses (name,category,amount,date_paid,created_by)
		VALUES ('$exp_name','$exp_cat','$exp_amount','$pay_date','$emp_exp_id') ";	

		echo($this->db->query($sql));
		
		header("Location: http://$_SERVER[HTTP_HOST]/senna/index.php/expenses");
	}
	
	function delete()
	{
		$salaries_to_delete=$this->input->post('emp_sal_no');
		
		if($this->Salaries->delete_list($salaries_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('salaries_successful_deleted').' '.
			count($salaries_to_delete).' '.$this->lang->line('suppliers_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('suppliers_cannot_be_deleted')));
		}
	}
	
	/*
	Gets one row for a supplier manage table. This is called using AJAX to update one row.
	*/
	function get_row()
	{
		$id = $this->input->post('row_id');
		$expense_info = $this->Expenses->get_info_expense($id);		
		$data_row = get_expense_data_row($expense_info,$this);
		
		echo $data_row;

	}
	/*
	get the width for the add/edit form
	*/
	function get_form_width()
	{			
		return 360;
	}
}
?>