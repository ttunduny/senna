<?php
require_once ("Secure_area.php");
require_once (APPPATH."libraries/ofc-library/Open-flash-chart.php");

define("FORM_WIDTH", "400");

class Reports extends Secure_area
{

	function __construct()
	{
		parent::__construct('reports');
		$method_name = $this->uri->segment(2);
		$exploder = explode('_', $method_name);
		preg_match("/(?:inventory)|([^_.]*)(?:_graph|_row)?$/", $method_name, $matches);
		preg_match("/^(.*?)([sy])?$/", array_pop($matches), $matches);
		$submodule_id = $matches[1] . ((count($matches) > 2) ? $matches[2] : "s");
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		// check access to report submodule
		if (sizeof($exploder) > 1 && !$this->Employee->has_grant('reports_'.$submodule_id,$employee_id))
		{
			redirect('no_access/reports/reports_' . $submodule_id);
		}
		$this->load->helper('report');
	}

	//Initial report listing screen
	function index()
	{
		$data['grants']=$this->Employee->get_employee_grants($this->session->userdata('person_id'));
		$this->load->view("reports/listing",$data);
	}

	function _get_common_report_data()
	{
		$data = array();
		$data['report_date_range_simple'] = get_simple_date_ranges();
		$data['months'] = get_months();
		$data['days'] = get_days();
		$data['years'] = get_years();
		$data['selected_month']=date('n');
		$data['selected_day']=date('d');
		$data['selected_year']=date('Y');

		return $data;
	}

	//Input for reports that require only a date range and an export to excel. (see routes.php to see that all summary reports route here)
	function date_input_excel_export()
	{
		$data = $this->_get_common_report_data();
		$this->load->view("reports/date_input_excel_export",$data);
	}

 	function get_detailed_sales_row($sale_id)
	{
		$this->load->model('reports/Detailed_sales');
		$model = $this->Detailed_sales;

		$report_data = $model->getDataBySaleId($sale_id);

		$summary_data = array(anchor('sales/edit/'.$report_data['sale_id'] . '/width:'.FORM_WIDTH,
				'POS '.$report_data['sale_id'],
				array('class' => 'thickbox')),
				$report_data['sale_date'],
				$report_data['items_purchased'],
				$report_data['employee_name'],
				$report_data['customer_name'],
				to_currency($report_data['subtotal']),
				to_currency($report_data['total']),
				to_currency($report_data['tax']),
				to_currency($report_data['cost']),
				to_currency($report_data['profit']),
				$report_data['payment_type'],
				$report_data['comment']);
		echo get_detailed_data_row($summary_data, $this);
	}

	function get_detailed_receivings_row($receiving_id)
	{
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$report_data = $model->getDataByReceivingId($receiving_id);

		$summary_data = array(anchor('receivings/edit/'.$report_data['receiving_id'] . '/width:'.FORM_WIDTH,
				'RECV '.$report_data['receiving_id'],
				array('class' => 'thickbox')),
				$report_data['receiving_date'],
				$report_data['items_purchased'],
				$report_data['employee_name'],
				$report_data['supplier_name'],
				to_currency($report_data['total']),
				$report_data['payment_type'],
				$report_data['invoice_number'],
				$report_data['comment']);
		echo get_detailed_data_row($summary_data, $this);
	}

	function get_summary_data($start_date, $end_date = null, $sale_type=0)
	{
		$end_date = $end_date ? $end_date : $start_date;
		$this->load->model('reports/Summary_sales');
		$model = $this->Summary_sales;
		$summary = $model->getSummaryData(array(
				'start_date'=>$start_date,
				'end_date'=>$end_date,
				'sale_type' => $sale_type));
		echo get_sales_summary_totals($summary, $this);
	}

	//Summary sales report
	function summary_sales($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_sales');
		$model = $this->Summary_sales;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['sale_date'], $row['quantity_purchased'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']), to_currency($row['cost']), to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_sales_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}

	//Summary categories report
	function summary_categories($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_categories');
		$model = $this->Summary_categories;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['category'], $row['quantity_purchased'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']), to_currency($row['cost']), to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_categories_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}

	//Summary customers report
	function summary_customers($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_customers');
		$model = $this->Summary_customers;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['customer'], $row['quantity_purchased'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']), to_currency($row['cost']), to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_customers_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}

	//Summary suppliers report
	function summary_suppliers($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_suppliers');
		$model = $this->Summary_suppliers;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['supplier'], $row['quantity_purchased'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']), to_currency($row['cost']), to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_suppliers_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}

	//Summary items report
	function summary_items($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_items');
		$model = $this->Summary_items;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array(character_limiter($row['name'], 40), $row['quantity_purchased'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']), to_currency($row['cost']), to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_items_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}

	//Summary employees report
	function summary_employees($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_employees');
		$model = $this->Summary_employees;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['employee'], $row['quantity_purchased'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']), to_currency($row['cost']), to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_employees_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}

	//Summary taxes report
	function summary_taxes($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_taxes');
		$model = $this->Summary_taxes;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['percent'], $row['count'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']));
		}

		$data = array(
			"title" => $this->lang->line('reports_taxes_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}

	//Summary discounts report
	function summary_discounts($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_discounts');
		$model = $this->Summary_discounts;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['discount_percent'], $row['count']);
		}

		$data = array(
			"title" => $this->lang->line('reports_discounts_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}

	//Summary payments report
	function summary_payments($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_payments');
		$model = $this->Summary_payments;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['payment_type'], $row['count'], to_currency($row['payment_amount']));
		}

		$data = array(
			"title" => $this->lang->line('reports_payments_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}

	//Input for reports that require only a date range. (see routes.php to see that all graphical summary reports route here)
	function date_input()
	{
		$data = $this->_get_common_report_data();
		$data['mode'] = 'sale';
		$this->load->view("reports/date_input",$data);
	}

	//Input for reports that require only a date range. (see routes.php to see that all graphical summary reports route here)
	function date_input_sales()
	{
		$data = $this->_get_common_report_data();
		$stock_locations = $this->Stock_location->get_allowed_locations('sales');
		$stock_locations['all'] =  $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);
        $data['mode'] = 'sale';
		$this->load->view("reports/date_input",$data);
	}

    function date_input_recv()
    {
        $data = $this->_get_common_report_data();
		$stock_locations = $this->Stock_location->get_allowed_locations('receivings');
		$stock_locations['all'] =  $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);
 		$data['mode'] = 'receiving';
        $this->load->view("reports/date_input",$data);
    }

	//Graphical summary sales report
	function graphical_summary_sales($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_sales');
		$model = $this->Summary_sales;

		$data = array(
			"title" => $this->lang->line('reports_sales_summary_report'),
			"data_file" => site_url("reports/graphical_summary_sales_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}

	//The actual graph data
	function graphical_summary_sales_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_sales');
		$model = $this->Summary_sales;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[date($this->config->item('dateformat'), strtotime($row['sale_date']))]= $row['total'];
		}

		$data = array(
			"title" => $this->lang->line('reports_sales_summary_report'),
			"yaxis_label"=>$this->lang->line('reports_revenue'),
			"xaxis_label"=>$this->lang->line('reports_date'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/line",$data);

	}

	//Graphical summary items report
	function graphical_summary_items($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_items');
		$model = $this->Summary_items;

		$data = array(
			"title" => $this->lang->line('reports_items_summary_report'),
			"data_file" => site_url("reports/graphical_summary_items_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}

	//The actual graph data
	function graphical_summary_items_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_items');
		$model = $this->Summary_items;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['name']] = $row['total'];
		}

		$data = array(
			"title" => $this->lang->line('reports_items_summary_report'),
			"xaxis_label"=>$this->lang->line('reports_revenue'),
			"yaxis_label"=>$this->lang->line('reports_items'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/hbar",$data);
	}

	//Graphical summary customers report
	function graphical_summary_categories($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_categories');
		$model = $this->Summary_categories;

		$data = array(
			"title" => $this->lang->line('reports_categories_summary_report'),
			"data_file" => site_url("reports/graphical_summary_categories_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}

	//The actual graph data
	function graphical_summary_categories_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_categories');
		$model = $this->Summary_categories;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['category']] = $row['total'];
		}

		$data = array(
			"title" => $this->lang->line('reports_categories_summary_report'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/pie",$data);
	}

	//Graphical summary suppliers report
	function graphical_summary_suppliers($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_suppliers');
		$model = $this->Summary_suppliers;

		$data = array(
			"title" => $this->lang->line('reports_suppliers_summary_report'),
			"data_file" => site_url("reports/graphical_summary_suppliers_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}

	//The actual graph data
	function graphical_summary_suppliers_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_suppliers');
		$model = $this->Summary_suppliers;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['supplier']] = $row['total'];
		}

		$data = array(
			"title" => $this->lang->line('reports_suppliers_summary_report'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/pie",$data);
	}

	//Graphical summary employees report
	function graphical_summary_employees($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_employees');
		$model = $this->Summary_employees;

		$data = array(
			"title" => $this->lang->line('reports_employees_summary_report'),
			"data_file" => site_url("reports/graphical_summary_employees_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}

	//The actual graph data
	function graphical_summary_employees_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_employees');
		$model = $this->Summary_employees;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['employee']] = $row['total'];
		}

		$data = array(
			"title" => $this->lang->line('reports_employees_summary_report'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/pie",$data);
	}

	//Graphical summary taxes report
	function graphical_summary_taxes($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_taxes');
		$model = $this->Summary_taxes;

		$data = array(
			"title" => $this->lang->line('reports_taxes_summary_report'),
			"data_file" => site_url("reports/graphical_summary_taxes_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}

	//The actual graph data
	function graphical_summary_taxes_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_taxes');
		$model = $this->Summary_taxes;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['percent']] = $row['total'];
		}

		$data = array(
			"title" => $this->lang->line('reports_taxes_summary_report'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/pie",$data);
	}

	//Graphical summary customers report
	function graphical_summary_customers($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_customers');
		$model = $this->Summary_customers;

		$data = array(
			"title" => $this->lang->line('reports_customers_summary_report'),
			"data_file" => site_url("reports/graphical_summary_customers_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}

	//The actual graph data
	function graphical_summary_customers_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_customers');
		$model = $this->Summary_customers;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['customer']] = $row['total'];
		}

		$data = array(
			"title" => $this->lang->line('reports_customers_summary_report'),
			"xaxis_label"=>$this->lang->line('reports_revenue'),
			"yaxis_label"=>$this->lang->line('reports_customers'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/hbar",$data);
	}

	//Graphical summary discounts report
	function graphical_summary_discounts($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_discounts');
		$model = $this->Summary_discounts;

		$data = array(
			"title" => $this->lang->line('reports_discounts_summary_report'),
			"data_file" => site_url("reports/graphical_summary_discounts_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}

	//The actual graph data
	function graphical_summary_discounts_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_discounts');
		$model = $this->Summary_discounts;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['discount_percent']] = $row['count'];
		}

		$data = array(
			"title" => $this->lang->line('reports_discounts_summary_report'),
			"yaxis_label"=>$this->lang->line('reports_count'),
			"xaxis_label"=>$this->lang->line('reports_discount_percent'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/bar",$data);
	}

	//Graphical summary payments report
	function graphical_summary_payments($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_payments');
		$model = $this->Summary_payments;

		$data = array(
			"title" => $this->lang->line('reports_payments_summary_report'),
			"data_file" => site_url("reports/graphical_summary_payments_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}

	//The actual graph data
	function graphical_summary_payments_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_payments');
		$model = $this->Summary_payments;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['payment_type']] = $row['payment_amount'];
		}

		$data = array(
			"title" => $this->lang->line('reports_payments_summary_report'),
			"yaxis_label"=>$this->lang->line('reports_revenue'),
			"xaxis_label"=>$this->lang->line('reports_payment_type'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/pie",$data);
	}

	function specific_customer_input()
	{
		$data = $this->_get_common_report_data();
		$data['specific_input_name'] = $this->lang->line('reports_customer');

		$customers = array();
		foreach($this->Customer->get_all()->result() as $customer)
		{
			$customers[$customer->person_id] = $customer->first_name .' '.$customer->last_name;
		}
		$data['specific_input_data'] = $customers;
		$this->load->view("reports/specific_input",$data);
	}
	// function financial_position($export_excel=0)
	// {
	// 	$this->load->model('reports/Financial_reports');
	// 	$model = $this->Financial_reports;
	// 	$filter = $this->generate_time();
	// 	$headers = $model->getDataColumns();				
	// 	$assets_data = $model->get_assets($filter);				
	// 	$summary_data = array();
	// 	$details_data = array();		
	// 	// echo "<pre>";print_r($assets_data);die;
	// 	foreach($assets_data['summary'] as $key=>$row)
	// 	{
	// 		$summary_data[] = array($row['name'],$row['amount'],'-');			
	// 		foreach($assets_data['details'][$key] as $drow)
	// 		{			 
	// 			$details_data[$key][] = array($drow['name'], $drow['amount'], $drow['amount']);
	// 		}
			 
	// 	}	
	// 	foreach($assets_data['summary'] as $key=>$row)
	// 	{			
	// 		$depreciation_amount = 0;			
	// 		$name = $row['name']." depreciation";			
	// 		foreach($assets_data['details'][$key] as $drow)
	// 		{
	// 			$depreciation_rate = $drow['depreciation'];
	// 			$dname = $drow['name']." depreciation";
	// 			$resale_price = $drow['resale_price'];
	// 			$date_of_purchase = $drow['time'];
	// 			$amount = $drow['amount'];
	// 	    	$diff = strtotime(date("M d Y ")) - (strtotime($date_of_purchase));

	// 		    $days_diff = floor($diff/3600/24);
	// 		    $years = round(($days_diff/365),0);
			    
	// 		    $depreciation = 0;		    
	// 		    $new_price = $amount;
	// 		    for ($j=1; $j <=$years ; $j++) { 
	// 		        $current_depreciation= $new_price * ($depreciation_rate/100);
	// 		        $depreciation +=$current_depreciation;                    
	// 		        $new_price -= $current_depreciation;                    
	// 		    }
	// 		    $depreciation_amount+=$depreciation;
	// 		    $current_value = $amount - $depreciation;  
	// 			$details_data[$key][] = array($dname, '-', $depreciation);
	// 		}
	// 		$summary_data[] = array($name,'-',$depreciation_amount);
			 
	// 	}	
	// 	echo "<pre>";print_r($summary_data);
	// 	echo "<pre>";print_r($details_data);die;
	// 	$current_time = date('Y-m-d');
	// 	$current_time = date('Y-12-31',strtotime("-1 year",strtotime($current_time)));					
	// 	$data = array(
	// 		"title" =>"Financial Report",
	// 		"subtitle" =>"As at ".date('d F Y',strtotime($current_time)) ,
	// 		"headers" => $model->getDataColumns(),
	// 		"editable" => "sales",
	// 		"summary_data" => $summary_data,
	// 		"details_data" => $details_data,
	// 		"header_width" => intval(100 / count($headers['summary'])),
	// 		"overall_summary_data" => $model->getSummaryData(array('start_date'=>$current_time, 'end_date'=>$current_time, 'sale_type' => NULL, 'location_id' => 1)),
	// 		"export_excel" => $export_excel
	// 	);

	// 	$this->load->view("reports/new_tabular_details",$data);
	// }
	function financial_position($export_excel=null)
	{
		$this->load->model('reports/Financial_reports');
		$model = $this->Financial_reports;
		$filter = $this->generate_time();
		$headers = $model->getDataColumns();				
		$assets_data = $model->get_assets($filter);				
		$summary_data = array();
		$details_data = array();		
		$depreciation_array = array();	
		$totals_debit = 0;		
		$totals_credit = 0;		
		foreach($assets_data['summary'] as $key=>$row)
		{
			$depreciation_amount = 0;									
			foreach($assets_data['details'][$key] as $drow)
			{			 
				$depreciation_rate = $drow['depreciation'];
				$dname = $drow['name']." depreciation";
				$resale_price = $drow['resale_price'];
				$date_of_purchase = $drow['time'];
				$amount = $drow['amount'];
		    	$diff = strtotime(date("M d Y ")) - (strtotime($date_of_purchase));
			    $days_diff = floor($diff/3600/24);
			    $years = round(($days_diff/365),0);			    
			    $depreciation = 0;		    
			    $new_price = $amount;
			    for ($j=1; $j <=$years ; $j++) { 
			        $current_depreciation= $new_price * ($depreciation_rate/100);
			        $depreciation +=$current_depreciation;                    
			        $new_price -= $current_depreciation;                    
			    }
			    $depreciation_amount+=$depreciation;
			    $current_value = $amount - $depreciation;  				
			}
			array_push($depreciation_array, $depreciation_amount);			 
		}	

		foreach($assets_data['summary'] as $key=>$row)
		{				
			$summary_data[] = array($row['name'],to_currency(round($row['amount'],2)),'-');		
			$totals_debit+=round($row['amount'],2);			
			foreach($assets_data['details'][$key] as $drow)
			{
				$details_data[$key][] = array($drow['name'], to_currency(round($drow['amount'],2)),'-');	
			}							
			 
		}	
		foreach($assets_data['summary'] as $key=>$row)
		{		
			$name = $row['name']." Depreciation";			
			$summary_data[] = array($name,'-',to_currency(round($depreciation_array[$key],2)));		
			$totals_credit+=round($depreciation_array[$key],2);									
			$count = count($summary_data)-1;			
			foreach($assets_data['details'][$key] as $drow)
			{
				$depreciation_rate = $drow['depreciation'];
				$dname = $drow['name']." depreciation";
				$resale_price = $drow['resale_price'];
				$date_of_purchase = $drow['time'];
				$amount = $drow['amount'];
		    	$diff = strtotime(date("M d Y ")) - (strtotime($date_of_purchase));
			    $days_diff = floor($diff/3600/24);
			    $years = round(($days_diff/365),0);			    
			    $depreciation = 0;		    
			    $new_price = $amount;
			    for ($j=1; $j <=$years ; $j++) { 
			        $current_depreciation= $new_price * ($depreciation_rate/100);
			        $depreciation +=$current_depreciation;                    
			        $new_price -= $current_depreciation;                    
			    }			    
			    $current_value = $amount - $depreciation;  
			    $details_data[$count][] = array($drow['name']." depreciation",'-',to_currency(round($depreciation,2)));						
			}					 
		}	
		$sales_data = $model->get_sales('1');		
		foreach($sales_data['summary'] as $key=>$row)
		{				
			$summary_data[] = array($row['name']." Sale",'-',to_currency(round($row['amount'],2)));	
			$totals_credit+=round($row['amount'],2);									
			$count = count($summary_data)-1;					
			foreach($sales_data['details'][$key] as $drow)
			{
				$details_data[$count][] = array("Sale ".$drow['name'],'-', to_currency(round($drow['amount'],2)));	
			}										 
		}	

		$sales_data_taxes = $model->get_sales_tax('1');				
		foreach($sales_data_taxes['summary'] as $key=>$row)
		{				
			$summary_data[] = array($row['name']." Sale Taxes",to_currency(round($row['amount'],2)),'-');	
			$totals_debit+=round($row['amount'],2);									
			$count = count($summary_data)-1;					
			foreach($sales_data['details'][$key] as $drow)
			{
				$details_data[$count][] = array("Sale Tax Item ".$drow['name'], to_currency(round($drow['amount'],2)),'-');	
			}										 
		}	
		$employee_pay = $model->get_salaries('1');						
		foreach($employee_pay['summary'] as $key=>$row)
		{				
			$summary_data[] = array('Employee Pay',to_currency(round($row['amount'],2)),'-');	
			$totals_debit+=round($row['amount'],2);									
			$count = count($summary_data)-1;					
			foreach($employee_pay['details'][$key] as $drow)
			{
				$details_data[$count][] = array($drow['name'], to_currency(round($drow['amount'],2)),'-');	
			}										 
		}	

		$employee_paye = $model->get_paye('1');						
		foreach($employee_paye['summary'] as $key=>$row)
		{	
			$summary_data[] = array('Employee PAYE',to_currency(round($row['amount'],2)),'-');	
			$totals_debit+=round($row['amount'],2);									
			$count = count($summary_data)-1;					
			foreach($employee_paye['details'][$key] as $drow)
			{
				$details_data[$count][] = array($drow['name'], to_currency(round($drow['amount'],2)),'-');	
			}										 
		}

		$employee_nhif = $model->get_nhif('1');						
		foreach($employee_nhif['summary'] as $key=>$row)
		{				
			$summary_data[] = array('Employee NHIF',to_currency(round($row['amount'],2)),'-');	
			$totals_debit+=round($row['amount'],2);									
			$count = count($summary_data)-1;					
			foreach($employee_nhif['details'][$key] as $drow)
			{
				$details_data[$count][] = array($drow['name'], to_currency(round($drow['amount'],2)),'-');	
			}										 
		}	
		$employee_nssf = $model->get_nssf('1');						
		foreach($employee_nssf['summary'] as $key=>$row)
		{				
			$summary_data[] = array('Employee NSSF',to_currency(round($row['amount'],2)),'-');	
			$totals_debit+=round($row['amount'],2);									
			$count = count($summary_data)-1;					
			foreach($employee_nssf['details'][$key] as $drow)
			{
				$details_data[$count][] = array($drow['name'], to_currency(round($drow['amount'],2)),'-');	
			}										 
		}	
		$count = count($summary_data);					
		$summary_data[] = array('TOTALS',to_currency(round($totals_debit,2)),to_currency(round($totals_credit,2)));	
		$details_data[$count][] = array('-','-','-');	
		// echo "<pre>";print_r($summary_data);die;
		// echo "<pre>";print_r($details_data);die;
		
		$current_time = date('Y-m-d');
		$current_time = date('Y-12-31',strtotime("-1 year",strtotime($current_time)));					
		$time = "As at ".date('d F Y',strtotime($current_time));
		$data = array(
			"title" =>"Financial Report",
			"subtitle" =>$time,
			"headers" => $model->getDataColumns(),
			"editable" => "sales",
			"summary_data" => $summary_data,
			"details_data" => $details_data,
			"header_width" => intval(100 / count($headers['summary'])),
			"overall_summary_data" => $model->getSummaryData(array('start_date'=>$current_time, 'end_date'=>$current_time, 'sale_type' => NULL, 'location_id' => 1)),
			"export_excel" => $export_excel
		);
		if(isset($export_excel)){
			$this->generate_excel('Financial Report',$summary_data,$time);
		}else{
			$this->load->view("reports/new_tabular_details",$data);
		}
	}
	function generate_excel($filename,$financial_array,$time){
		$this->load->library('excel');		
		$inputFileName = 'excel/financial_reports_template.xlsx';			    
	    $excel2 = PHPExcel_IOFactory::createReader('Excel2007');
		$excel2 = $excel2->load($inputFileName); // Empty Sheet
		$excel2->setActiveSheetIndex(1);
	   
		$file_name =isset($file_name) ? $file_name: time().'.xls';
		$objWriter = PHPExcel_IOFactory::createWriter($excel2, 'Excel5');

		$excel2->setActiveSheetIndex(0);
		$rows = array('B','C','D');
		$excel2->getActiveSheet()->setCellValue($rows[0].'3',$time);
		$row_count = 5;
		for ($i=0; $i < count($financial_array); $i++) { 
			$name = $financial_array[$i][0];
			$debit = $financial_array[$i][1];
			$credit = $financial_array[$i][2];
			$excel2->getActiveSheet()->setCellValue($rows[0].$row_count,$name);
            $excel2->getActiveSheet()->setCellValue($rows[1].$row_count,$debit);
            $excel2->getActiveSheet()->setCellValue($rows[2].$row_count,$credit);
            $row_count++;
		}
		
		ob_end_clean();
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");	
		header("Content-Disposition: attachment; filename=$file_name");	
		$objWriter -> save('php://output');
		$excel2 -> disconnectWorksheets();
		unset($excel2);   
	}
	// function financial_position()
	// {		
	// 	$data = $this->_get_common_report_data();
	// 	$final_array = array();
	// 	$sum_debits = 0;
	// 	$sum_credits = 0;
	// 	$filter = $this->generate_time();
	// 	$sales = $this->Financials->get_sales($filter)->result_array();		
	// 	$sales_tax = $this->Financials->get_sales_tax($filter)->result_array();		
	// 	$assets = $this->Financials->get_assets($filter)->result_array();	
		// $salaries = $this->Financials->get_salaries($filter)->result_array();			
	// 	$nssf = $this->Financials->get_nssf($filter)->result_array();			
	// 	$nhif = $this->Financials->get_nhif($filter)->result_array();			
	// 	$paye = $this->Financials->get_paye($filter)->result_array();			

	// 	$assets_depreciation = null;		
		
	// 	for ($i=0; $i <count($assets) ; $i++) { 
	// 		$depreciation_rate = $assets[$i]['depreciation'];
	// 		$name = $assets[$i]['name']." depreciation";
	// 		$resale_price = $assets[$i]['resale_price'];
	// 		$date_of_purchase = $assets[$i]['time'];
	// 		$amount = $assets[$i]['amount'];
	//     	$diff = strtotime(date("M d Y ")) - (strtotime($date_of_purchase));

	// 	    $days_diff = floor($diff/3600/24);
	// 	    $years = round(($days_diff/365),0);
		    
	// 	    $depreciation = 0;		    
	// 	    $new_price = $amount;
	// 	    for ($j=1; $j <=$years ; $j++) { 
	// 	        $current_depreciation= $new_price * ($depreciation_rate/100);
	// 	        $depreciation +=$current_depreciation;                    
	// 	        $new_price -= $current_depreciation;                    
	// 	    }
	// 	    $current_value = $amount - $depreciation;  
	// 	    $assets_depreciation[] = array('name'=>$name,'amount'=>$depreciation); 
	// 	}
	// 	$assets = $this->add_array_type($assets,'debits');
	// 	$sales_tax = $this->add_array_type($sales_tax,'debits');
	// 	$assets_depreciation = $this->add_array_type($assets_depreciation,'credits');
	// 	$sales = $this->add_array_type($sales,'credits');
	// 	$salaries = $this->add_array_type($salaries,'Net pay Employees','name');
	// 	$sales_tax = $this->add_array_type($sales_tax,'Tax to Pay','name');
	// 	$salaries = $this->add_array_type($salaries,'debits');		
	// 	$sales = $this->add_array_type($sales,'Sales','name');
	// 	$nhif = $this->add_array_type($nhif,'NHIF','name');
	// 	$nhif = $this->add_array_type($nhif,'debits');		
	// 	$nssf = $this->add_array_type($nssf,'NSSF','name');
	// 	$nssf = $this->add_array_type($nssf,'debits');		
	// 	$paye = $this->add_array_type($paye,'PAYE','name');
	// 	$paye = $this->add_array_type($paye,'debits');		
	// 	$final_array = $this->push_array($final_array,$sales);
	// 	$final_array = $this->push_array($final_array,$sales_tax);
	// 	$final_array = $this->push_array($final_array,$assets);
	// 	$final_array = $this->push_array($final_array,$assets_depreciation);
	// 	$final_array = $this->push_array($final_array,$salaries);
	// 	$final_array = $this->push_array($final_array,$nhif);
	// 	$final_array = $this->push_array($final_array,$nssf);
	// 	$final_array = $this->push_array($final_array,$paye);
		
	// 	if(count($final_array)>0){
	// 		$final_array = call_user_func_array('array_merge', $final_array);
	// 	    $this->aasort($final_array,"name");
	// 		// echo "<pre>";print_r($final_array);die;
	// 		foreach ($final_array as $key => $values) {											
	// 			$amount = $values['amount'];
	// 			$type = $values['type'];
	// 			if($type=='debits'){						
	// 				$sum_debits += $amount;						
	// 			}else{
	// 				$sum_credits += $amount;						
	// 			}	
	// 		}	
	// 	}
	//     $current_time = date('Y-m-d');
	// 	$current_time = date('Y-12-31',strtotime("-1 year",strtotime($current_time)));
	// 	$current_time = date('d F Y',strtotime($current_time));
	// 	$data['title'] = "Financial Position";
	// 	$data['subtitle'] = "As at ".$current_time;
	// 	$data['final_array'] = $final_array;
	// 	$data['totals'] = array('sum_debits'=>$sum_debits,'sum_credits'=>$sum_credits);
	// 	$this->load->view("reports/financial_position",$data);
	// }

	function push_array($arrayfinal,$var){		
		if(count($var)>0){
			array_push($arrayfinal, $var);			
		}
		return $arrayfinal;
	}

	function generate_time($current_time=null,$previous_time=null){
		$current_time = date('Y-m-d');
		$current_time = date('Y-12-31',strtotime("-1 year",strtotime($current_time)));
		$previous_time = date('Y-m-01',strtotime("-1 year",strtotime($current_time)));
		$time = $previous_time." AND ".$current_time;
		// echo "Current time $current_time and Previous Time $previous_time";die;
		return $time;
	}
	function aasort (&$array, $key) {
	    $sorter=array();
	    $ret=array();
	    reset($array);
	    foreach ($array as $ii => $va) {
	        $sorter[$ii]=$va[$key];
	    }
	    asort($sorter);
	    foreach ($sorter as $ii => $va) {
	        $ret[$ii]=$array[$ii];
	    }
	    $array=$ret;
	}


	function add_array_type($arrayname,$type,$name=null){
		if(count($arrayname)>0){
			foreach ($arrayname as &$array) {
				if (isset($name)) {
					$array[$name] = $type;
				}else{
					$array['type'] =$type;
				}
			}
		}
		return $arrayname;
	}
	function specific_customer($start_date, $end_date, $customer_id, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Specific_customer');
		$model = $this->Specific_customer;

		$headers = $model->getDataColumns();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'customer_id' =>$customer_id, 'sale_type' => $sale_type));

		$summary_data = array();
		$details_data = array();

		foreach($report_data['summary'] as $key=>$row)
		{
			$summary_data[] = array(anchor('sales/receipt/'.$row['sale_id'], 'POS '.$row['sale_id'], array('target' => '_blank')), $row['sale_date'], $row['items_purchased'], $row['employee_name'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']), to_currency($row['cost']), to_currency($row['profit']), $row['payment_type'], $row['comment']);

			foreach($report_data['details'][$key] as $drow)
			{
				$details_data[$key][] = array($drow['name'], $drow['category'], $drow['serialnumber'], $drow['description'], $drow['quantity_purchased'], to_currency($drow['subtotal']), to_currency($drow['total']), to_currency($drow['tax']), to_currency($drow['cost']), to_currency($drow['profit']), $drow['discount_percent'].'%');
			}
		}

		$customer_info = $this->Customer->get_info($customer_id);
		$data = array(
			"title" => $customer_info->first_name .' '. $customer_info->last_name.' '.$this->lang->line('reports_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"summary_data" => $summary_data,
			"details_data" => $details_data,
			"header_width" => intval(100 / count($headers['summary'])),
			"overall_summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date,'customer_id' =>$customer_id, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular_details",$data);
	}

	function specific_employee_input()
	{
		$data = $this->_get_common_report_data();
		$data['specific_input_name'] = $this->lang->line('reports_employee');

		$employees = array();
		foreach($this->Employee->get_all()->result() as $employee)
		{
			$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
		}
		$data['specific_input_data'] = $employees;
		$this->load->view("reports/specific_input",$data);
	}

	function specific_employee($start_date, $end_date, $employee_id, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		$headers = $model->getDataColumns();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'employee_id' =>$employee_id, 'sale_type' => $sale_type));

		$summary_data = array();
		$details_data = array();

		foreach($report_data['summary'] as $key=>$row)
		{
			$summary_data[] = array(anchor('sales/receipt/'.$row['sale_id'], 'POS '.$row['sale_id'], array('target' => '_blank')), $row['sale_date'], $row['items_purchased'], $row['customer_name'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']), to_currency($row['cost']), to_currency($row['profit']), $row['payment_type'], $row['comment']);

			foreach($report_data['details'][$key] as $drow)
			{
				$details_data[$key][] = array($drow['name'], $drow['category'], $drow['serialnumber'], $drow['description'], $drow['quantity_purchased'], to_currency($drow['subtotal']), to_currency($drow['total']), to_currency($drow['tax']), to_currency($drow['cost']), to_currency($drow['profit']), $drow['discount_percent'].'%');
			}
		}

		$employee_info = $this->Employee->get_info($employee_id);
		$data = array(
			"title" => $employee_info->first_name .' '. $employee_info->last_name.' '.$this->lang->line('reports_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"summary_data" => $summary_data,
			"details_data" => $details_data,
			"header_width" => intval(100 / count($headers)),
			"overall_summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date,'employee_id' =>$employee_id, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular_details",$data);
	}

	function specific_discount_input()
	{
		$data = $this->_get_common_report_data();
		$data['specific_input_name'] = $this->lang->line('reports_discount');

		$discounts = array();
		for($i = 0; $i <= 100; $i += 10)
		{
			$discounts[$i] = $i . '%';
		}
		$data['specific_input_data'] = $discounts;
		$this->load->view("reports/specific_input",$data);
	}

	function specific_discount($start_date, $end_date, $discount, $sale_type, $export_excel = 0)
	{
		$this->load->model('reports/Specific_discount');
		$model = $this->Specific_discount;

		$headers = $model->getDataColumns();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'discount' =>$discount, 'sale_type' => $sale_type));

		$summary_data = array();
		$details_data = array();

		foreach($report_data['summary'] as $key=>$row)
		{
			$summary_data[] = array(anchor('sales/receipt/'.$row['sale_id'], 'POS '.$row['sale_id'], array('target' => '_blank')), $row['sale_date'], $row['items_purchased'], $row['customer_name'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']),/*to_currency($row['profit']),*/ $row['payment_type'], $row['comment']);

			foreach($report_data['details'][$key] as $drow)
			{
				$details_data[$key][] = array($drow['name'], $drow['category'], $drow['serialnumber'], $drow['description'], $drow['quantity_purchased'], to_currency($drow['subtotal']), to_currency($drow['total']), to_currency($drow['tax']),/*to_currency($drow['profit']),*/ $drow['discount_percent'].'%');
			}
		}

		$data = array(
					"title" => $discount. '% '.$this->lang->line('reports_discount') . ' ' . $this->lang->line('reports_report'),
					"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
					"headers" => $headers,
					"summary_data" => $summary_data,
					"details_data" => $details_data,
					"header_width" => intval(100 / count($headers['summary'])),
					"overall_summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date,'discount' =>$discount, 'sale_type' => $sale_type)),
					"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular_details",$data);

	}

	function detailed_sales($start_date, $end_date, $sale_type, $location_id='all', $export_excel=0)
	{
		$this->load->model('reports/Detailed_sales');
		$model = $this->Detailed_sales;

		$headers = $model->getDataColumns();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type, 'location_id' => $location_id));

		$summary_data = array();
		$details_data = array();

		$show_locations = $this->Stock_location->multiple_locations();

		foreach($report_data['summary'] as $key=>$row)
		{
			$summary_data[] = array(anchor('sales/edit/'.$row['sale_id'] . '/width:'.FORM_WIDTH, 'POS '.$row['sale_id'], array('class' => 'thickbox')), $row['sale_date'], $row['items_purchased'], $row['employee_name'], $row['customer_name'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']), to_currency($row['cost']), to_currency($row['profit']), $row['payment_type'], $row['comment']);

			foreach($report_data['details'][$key] as $drow)
			{
				$quantity_purchased = $drow['quantity_purchased'];
				if ($show_locations)
				{
					$quantity_purchased .= ' [' . $this->Stock_location->get_location_name($drow['item_location']) . ']';
				}
				$details_data[$key][] = array($drow['name'], $drow['category'], $drow['serialnumber'], $drow['description'], $quantity_purchased, to_currency($drow['subtotal']), to_currency($drow['total']), to_currency($drow['tax']), to_currency($drow['cost']), to_currency($drow['profit']).'%');
			}
		}

		$data = array(
			"title" =>$this->lang->line('reports_detailed_sales_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"editable" => "sales",
			"summary_data" => $summary_data,
			"details_data" => $details_data,
			"header_width" => intval(100 / count($headers['summary'])),
			"overall_summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type, 'location_id' => $location_id)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular_details",$data);
	}

	function detailed_receivings($start_date, $end_date, $receiving_type, $location_id='all', $export_excel=0)
	{
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$headers = $model->getDataColumns();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'receiving_type'=>$receiving_type, 'location_id' => $location_id));

		$summary_data = array();
		$details_data = array();

		$show_locations = $this->Stock_location->multiple_locations();

		foreach($report_data['summary'] as $key=>$row)
		{
			$summary_data[] = array(anchor('receivings/edit/'.$row['receiving_id'].'/width:'.FORM_WIDTH, 'RECV '.$row['receiving_id'], array('class' => 'thickbox')), $row['receiving_date'], $row['items_purchased'], $row['employee_name'], $row['supplier_name'], to_currency($row['total']), $row['payment_type'], $row['invoice_number'], $row['comment']);

			foreach($report_data['details'][$key] as $drow)
			{
				$quantity_purchased = $drow['receiving_quantity'] > 1 ? $drow['quantity_purchased'] . ' x ' . $drow['receiving_quantity'] : $drow['quantity_purchased'];
				if ($show_locations)
				{
					$quantity_purchased .= ' [' . $this->Stock_location->get_location_name($drow['item_location']) . ']';
				}
				$details_data[$key][] = array($drow['item_number'], $drow['name'], $drow['category'], $quantity_purchased, to_currency($drow['total']), $drow['discount_percent'].'%');
			}
		}

		$data = array(			
			"title" =>$this->lang->line('reports_detailed_receivings_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) .'-'.date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"header_width" => intval(100 / count($headers['summary'])),
			"editable" => "receivings",
			"summary_data" => $summary_data,
			"details_data" => $details_data,
			"header_width" => intval(100 / count($headers['summary'])),
			"overall_summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'receiving_type' => $receiving_type, 'location_id' => $location_id)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular_details",$data);
	}

	function excel_export()
	{
		$this->load->view("reports/excel_export",array());
	}

	function inventory_low($export_excel=0)
	{
		$this->load->model('reports/Inventory_low');
		$model = $this->Inventory_low;
		$tabular_data = array();
		$report_data = $model->getData(array());
		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['name'], $row['item_number'], $row['description'], $row['quantity'], $row['reorder_level'], $row['location_name']);
		}

		$data = array(
			"title" => $this->lang->line('reports_inventory_low_report'),
			"subtitle" => '',
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array()),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}

	function inventory_summary_input()
	{
		$data = array();

		$this->load->model('reports/Inventory_Summary');
		$model = $this->Inventory_Summary;
		$data['item_count'] = $model->getItemCountDropdownArray();

		$stock_locations = $this->Stock_location->get_allowed_locations();
		$stock_locations['all'] =  $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);

		$this->load->view("reports/inventory_summary_input", $data);
	}

	function inventory_summary($export_excel=0, $location_id = 'all', $item_count = 'more_than_zero')
	{
		$this->load->model('reports/Inventory_summary');
		$model = $this->Inventory_summary;
		$tabular_data = array();
		$report_data = $model->getData(array('location_id'=>$location_id,'item_count'=>$item_count));
		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['name'],
								$row['item_number'],
								$row['description'],
								$row['quantity'],
								$row['reorder_level'],
								$row['location_name'],
								to_currency(($row['cost_price'])),
								to_currency($row['unit_price']),
								to_currency($row['sub_total_value']));
		}

		$data = array(
			"title" => $this->lang->line('reports_inventory_summary_report'),
			"subtitle" => '',
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData($report_data),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}

}
?>