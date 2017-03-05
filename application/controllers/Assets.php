<?php
require_once ("Secure_area.php");
require_once ("interfaces/Idata_controller.php");

class Assets extends Secure_area implements iData_controller
{
	function __construct()
	{
		parent::__construct('assets');
		$this->load->library('item_lib');
		$this->load->library('receiving_lib');
	}
	
	function index($limit_from=0)
	{

		$data['controller_name'] = $this->get_controller_name();
		$data['form_width'] = $this->get_form_width();
		$lines_per_page = $this->Appconfig->get('lines_per_page');
		
		$data['links'] = $this->_initialize_pagination($this->Asset, $lines_per_page, $limit_from);
		
		// set 01/01/2010 as starting date for OSPOS
		$start_of_time = date($this->config->asset('dateformat'), mktime(0,0,0,1,1,2010));
		$today = date($this->config->asset('dateformat'));

		$start_date = $this->input->post('start_date') != null ? $this->input->post('start_date') : $start_of_time;
		$start_date_formatter = date_create_from_format($this->config->asset('dateformat'), $start_date);
		$end_date = $this->input->post('end_date') != null ? $this->input->post('end_date') : $today;
		$end_date_formatter = date_create_from_format($this->config->asset('dateformat'), $end_date);
		
		$data['start_date'] = $start_date_formatter->format($this->config->asset('dateformat'));
		$data['end_date'] = $end_date_formatter->format($this->config->asset('dateformat'));	
		
		$data['assets'] = $this->Asset->get_all($lines_per_page, $limit_from);
		$data['manage_table'] = get_assets_manage_table( $this->Asset->get_all($lines_per_page, $limit_from), $this );
		// echo "<pre>";print_r($data);die'
		$this->load->view('assets/manage', $data);
	}

	function find_item_info()
	{
		$item_number = $this->input->post('scan_item_number');
		echo json_encode($this->Item->find_item_info($item_number));
	}

	/*
	Returns Items table data rows. This will be called with AJAX.
	*/
	function search()
	{
		$search = $this->input->post('search') != '' ? $this->input->post('search') : null;

		$limit_from = $this->input->post('limit_from');
		$lines_per_page = $this->Appconfig->get('lines_per_page');		

		// set 01/01/2010 as starting date for OSPOS
		$start_of_time = date($this->config->asset('dateformat'), mktime(0,0,0,1,1,2010));
		$today = date($this->config->asset('dateformat'));

		$start_date = $this->input->post('start_date') != null ? $this->input->post('start_date') : $start_of_time;
		$start_date_formatter = date_create_from_format($this->config->asset('dateformat'), $start_date);
		$end_date = $this->input->post('end_date') != null ? $this->input->post('end_date') : $today;
		$end_date_formatter = date_create_from_format($this->config->item('dateformat'), $end_date);
		
		$filters = array('start_date' => $start_date_formatter->format('Y-m-d'), 
						'end_date' => $end_date_formatter->format('Y-m-d'),
						'status'=>'0');
		
		$assets = $this->Asset->search($search, $filters, $lines_per_page, $limit_from);		
		// $data_rows = get_assets_manage_table_data_rows($assets->result_array(), $this);
		echo "<pre>";print_r($data_rows);die;
		$total_rows = $this->Asset->get_found_rows($search, $filters);
		$links = $this->_initialize_pagination($this->Asset, $lines_per_page, $limit_from, $total_rows, 'search');
		$data_rows = get_assets_manage_table_data_rows($assets, $this);

		// do not move this line to be after the json_encode otherwise the searhc function won't work!!

		echo json_encode(array('total_rows' => $total_rows, 'rows' => $data_rows, 'pagination' => $links));
	}
	
	function pic_thumb($pic_id)
	{
		$this->load->helper('file');
		$this->load->library('image_lib');
		$base_path = "uploads/item_pics/" . $pic_id ;
		$images = glob ($base_path. "*");
		if (sizeof($images) > 0)
		{
			$image_path = $images[0];
			$ext = pathinfo($image_path, PATHINFO_EXTENSION);
			$thumb_path = $base_path . $this->image_lib->thumb_marker.'.'.$ext;
			if (sizeof($images) < 2)
			{
				$config['image_library'] = 'gd2';
				$config['source_image']  = $image_path;
				$config['maintain_ratio'] = TRUE;
				$config['create_thumb'] = TRUE;
				$config['width'] = 52;
				$config['height'] = 32;
 				$this->image_lib->initialize($config);
 				$image = $this->image_lib->resize();
				$thumb_path = $this->image_lib->full_dst_path;
			}
			$this->output->set_content_type(get_mime_by_extension($thumb_path));
			$this->output->set_output(file_get_contents($thumb_path));
		}
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{		
		$suggestions = $this->Asset->get_search_suggestions($this->input->post('q'), $this->input->post('limit'),
															$this->input->post('search_custom'), !empty($this->input->post('is_deleted')));

		echo implode("\n",$suggestions);
	}
	
	function item_search()
	{
		$suggestions = $this->Item->get_item_search_suggestions($this->input->post('q'), $this->input->post('limit'));

		echo implode("\n",$suggestions);
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest_category()
	{
		$suggestions = $this->Item->get_category_suggestions($this->input->post('q'));

		echo implode("\n",$suggestions);
	}

	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_location()
	{
		$suggestions = $this->Item->get_location_suggestions($this->input->post('q'));

		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom1()
	{
		$suggestions = $this->Item->get_custom1_suggestions($this->input->post('q'));

		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom2()
	{
		$suggestions = $this->Item->get_custom2_suggestions($this->input->post('q'));

		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom3()
	{
		$suggestions = $this->Item->get_custom3_suggestions($this->input->post('q'));

		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom4()
	{
		$suggestions = $this->Item->get_custom4_suggestions($this->input->post('q'));

		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom5()
	{
		$suggestions = $this->Item->get_custom5_suggestions($this->input->post('q'));

		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom6()
	{
		$suggestions = $this->Item->get_custom6_suggestions($this->input->post('q'));

		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom7()
	{
		$suggestions = $this->Item->get_custom7_suggestions($this->input->post('q'));

		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom8()
	{
		$suggestions = $this->Item->get_custom8_suggestions($this->input->post('q'));

		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom9()
	{
		$suggestions = $this->Item->get_custom9_suggestions($this->input->post('q'));

		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom10()
	{
		$suggestions = $this->Item->get_custom10_suggestions($this->input->post('q'));

		echo implode("\n",$suggestions);
	}
		
	function get_row()
	{
		$id = $this->input->post('row_id');
		$asset_info = $this->Asset->get_info($id);		
		$data_row = get_asset_data_row($asset_info,$this);
		
		echo $data_row;

	}

	function view($asset_id=-1)
	{
		$data['asset_info']=$this->Asset->get_info($asset_id);		
		$categories = array('' => $this->lang->line('assets_none'));
		foreach($this->Asset->get_all_categories()->result_array() as $row)
		{
			$categories[$row['id']] = $row['category_name'];
		}

		$data['categories']=$categories;
		$data['selected_category'] = $this->Asset->get_info($asset_id)->category;		                
		// $data['manage_table'] = get_assets_manage_table( $this->Asset->get_all($lines_per_page, $limit_from), $this );
		// echo "<pre>";print_r($data);die;
		$this->load->view("assets/form", $data);
	}

	function count_details($asset_id=-1)
	{
		$data['asset_info']=$this->Asset->get_info($asset_id);		
		$categories = array('' => $this->lang->line('assets_none'));
		foreach($this->Asset->get_all_categories()->result_array() as $row)
		{
			$categories[$row['id']] = $row['category_name'];
		}

		$data['categories']=$categories;
		$data['selected_category'] = $this->Asset->get_info($asset_id)->category;		                		

		$this->load->view("assets/count_details", $data);
	}
    
	function inventory($item_id=-1)
	{
		$data['item_info']=$this->Item->get_info($item_id);
        
        $data['stock_locations'] = array();
        $stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
        foreach($stock_locations as $location_data)
        {            
            $data['stock_locations'][$location_data['location_id']] = $location_data['location_name'];
            $data['item_quantities'][$location_data['location_id']] = $this->Item_quantity->get_item_quantity($item_id,$location_data['location_id'])->quantity;
        }     
        
		$this->load->view("items/inventory", $data);
	}
	
	
	function generate_barcodes($item_ids)
	{
		$this->load->library('barcode_lib');
		$result = array();

		$item_ids = explode(':', $item_ids);
		$result = $this->Item->get_multiple_info($item_ids)->result_array();
		$config = $this->barcode_lib->get_barcode_config();

		$data['barcode_config'] = $config;
		
		// check the list of items to see if any item_number field is empty
		foreach($result as &$item)
		{
			// update the UPC/EAN/ISBN field if empty / null with the newly generated barcode
			if (empty($item['item_number']) && $this->Appconfig->get('barcode_generate_if_empty'))
			{
				// get the newly generated barcode
				$barcode_instance = Barcode_lib::barcode_instance($item, $config);
				$item['item_number'] = $barcode_instance->getData();
				// remove from item any suppliers table info to avoid save failure because of unknown fields
				// WARNING: if suppliers table is changed this list needs to be upgraded, which makes the matter a bit tricky to maintain
				unset($item['person_id']);
				unset($item['company_name']);
				unset($item['account_number']);
				unset($item['agency_name']);
				
				// update the item in the database in order to save the UPC/EAN/ISBN field
				$this->Item->save($item, $item['item_id']);
			}
		}
		$data['items'] = $result;
		// display barcodes
		$this->load->view("barcode_sheet", $data);

	}

	function bulk_edit()
	{
		$data = array();
		$suppliers = array('' => $this->lang->line('items_none'));
		foreach($this->Supplier->get_all()->result_array() as $row)
		{
			$suppliers[$row['person_id']] = $row['company_name'];
		}
		$data['suppliers'] = $suppliers;
		$data['allow_alt_description_choices'] = array(
			''=>$this->lang->line('items_do_nothing'), 
			1 =>$this->lang->line('items_change_all_to_allow_alt_desc'),
			0 =>$this->lang->line('items_change_all_to_not_allow_allow_desc'));
				
		$data['serialization_choices'] = array(
			''=>$this->lang->line('items_do_nothing'), 
			1 =>$this->lang->line('items_change_all_to_serialized'),
			0 =>$this->lang->line('items_change_all_to_unserialized'));

		$this->load->view("items/form_bulk", $data);
	}

	function save($asset_id=-1)
	{
		$upload_success = $this->_handle_image_upload();
		$upload_data = $this->upload->data();
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		//Save item data
		$asset_data = array(
			'name'=>$this->input->post('name'),			
			'category'=>$this->input->post('category_id'),			
			'serial_no'=>$this->input->post('serial_number') == '' ? null : $this->input->post('serial_number'),
			'price'=>$this->input->post('cost_price'),
			'depreciation'=>$this->input->post('depreciation'),
			'date_of_purchase'=>date('Y-m-d',strtotime($this->input->post('purchase_date'))),
			'resale_price'=>$this->input->post('resale_price'),
			'user_id'=>$employee_id,
			'status'=>'0'
		);
		
		
		if (!empty($upload_data['orig_name']))
		{
			$asset_data['pic_id'] = $upload_data['raw_name'];
		}
		
		
		$cur_asset_info = $this->Asset->get_info($asset_id);
		
		if($this->Asset->save($asset_data,$asset_id))
		{
			$success = TRUE;
			$new_asset = FALSE;
			//New item
			if ($asset_id==-1)
			{
				$asset_id = $asset_data['asset_id'];
				$new_asset = TRUE;
			}
			
			
            
			if ($success && $upload_success)
            {
            	$success_message = $this->lang->line('assets_successful_' . ($new_asset ? 'adding' : 'updating')) .' '. $asset_data['name'];

            	echo json_encode(array('success'=>true, 'message'=>$success_message, 'asset_id'=>$asset_id));
            }
            else
            {
            	$error_message = $upload_success ? 
	            	$this->lang->line('assets_error_adding_updating') .' '. $asset_data['name'] : 
    	        	$this->upload->display_errors(); 

            	echo json_encode(array('success'=>false, 'message'=>$error_message, 'asset_id'=>$asset_id)); 
            }
            
		}
		else//failure
		{
			echo json_encode(array('success'=>false, 'message'=>$this->lang->line('assets_error_adding_updating').' '.$asset_data['name'], 'id'=>-1));
		}
	}

		function save_salo($salary_id=-1)
	{
		
		$today =date("Y-m-d"); 
		$salary_data = array(
			'gross_sal'=>$this->input->post('sal_gross'),
			'nhif'=>$this->input->post('sal_nhif'),
			'nssf'=>$this->input->post('sal_nssf'),
			'tax'=>$this->input->post('sal_tax'),
			'net_sal'=>$this->input->post('sal_net'),
			// 'pay_date'=>$today,
			'salary_id'=>$this->input->post('emp_sal_no'),
		);
		
		
		$cur_salary_info = $this->salaryedit->get_info($salary_id);
		
		if($this->Asset->save_salo($salary_data,$salary_id))
		{
			$success = TRUE;
			$new_salary = FALSE;
			//New item
			if ($salary_id==-1)
			{
				$salary_id = $salary_data['salary_id'];
				$new_salary = TRUE;
			}
			
		}
		else//failure
		{
			echo json_encode(array('success'=>false, 'message'=>$this->lang->line('assets_error_adding_updating').' '.$asset_data['name'], 'id'=>-1));
		}
	}
	
	function check_item_number()
	{
		$exists = $this->Item->item_number_exists($this->input->post('item_number'),$this->input->post('item_id'));
		echo !$exists ? 'true' : 'false';
	}
	
	function _handle_image_upload()
	{
		$this->load->helper('directory');
		$map = directory_map('./uploads/item_pics/', 1);
		// load upload library
		$config = array('upload_path' => './uploads/item_pics/',
				'allowed_types' => 'gif|jpg|png',
				'max_size' => '100',
				'max_width' => '640',
				'max_height' => '480',
				'file_name' => sizeof($map));
		$this->load->library('upload', $config);
		$this->upload->do_upload('item_image');           
		
		return strlen($this->upload->display_errors()) == 0 || 
            	!strcmp($this->upload->display_errors(), 
            		'<p>'.$this->lang->line('upload_no_file_selected').'</p>');
	}
	
	function save_inventory($item_id=-1)
	{	
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$cur_item_info = $this->Item->get_info($item_id);
        $location_id = $this->input->post('stock_location');
		$inv_data = array(
			'trans_date'=>date('Y-m-d H:i:s'),
			'trans_items'=>$item_id,
			'trans_user'=>$employee_id,
			'trans_location'=>$location_id,
			'trans_comment'=>$this->input->post('trans_comment'),
			'trans_inventory'=>$this->input->post('newquantity')
		);
		
		$this->Inventory->insert($inv_data);
		
		//Update stock quantity
		$item_quantity= $this->Item_quantity->get_item_quantity($item_id,$location_id);
		$item_quantity_data = array(
			'item_id'=>$item_id,
			'location_id'=>$location_id,
			'quantity'=>$item_quantity->quantity + $this->input->post('newquantity')
		);

		if($this->Item_quantity->save($item_quantity_data,$item_id,$location_id))
		{			
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('items_successful_updating').' '.
			$cur_item_info->name,'item_id'=>$item_id));
		}
		else//failure
		{	
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('items_error_adding_updating').' '.
			$cur_item_info->name,'item_id'=>-1));
		}
	}

	function bulk_update()
	{
		$items_to_update=$this->input->post('item_ids');
		$item_data = array();

		foreach($_POST as $key=>$value)
		{
			//This field is nullable, so treat it differently
			if($key == 'supplier_id' && $value != '')
			{	
				$item_data["$key"] = $value;
			}
			elseif($value != '' && !(in_array($key, array('submit', 'item_ids', 'tax_names', 'tax_percents'))))
			{
				$item_data["$key"]=$value;
			}
		}

		//Item data could be empty if tax information is being updated
		if(empty($item_data) || $this->Item->update_multiple($item_data,$items_to_update))
		{
			$items_taxes_data = array();
			$tax_names = $this->input->post('tax_names');
			$tax_percents = $this->input->post('tax_percents');
			$tax_updated = false;
			for($k=0;$k<count($tax_percents);$k++)
			{
				if (!empty($tax_names[$k]) && is_numeric($tax_percents[$k]))
				{
					$tax_updated = true;
					
					$items_taxes_data[] = array('name'=>$tax_names[$k], 'percent'=>$tax_percents[$k] );
				}
			}
			
			if($tax_updated)
			{
				$this->Item_taxes->save_multiple($items_taxes_data, $items_to_update);
			}

			echo json_encode(array('success'=>true,'message'=>$this->lang->line('items_successful_bulk_edit')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('items_error_updating_multiple')));
		}
	}

	function delete()
	{
		$assets_to_delete=$this->input->post('ids');

		if($this->Asset->delete_list($assets_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('assets_successful_deleted').' '.
			count($assets_to_delete).' '.$this->lang->line('assets_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('assets_cannot_be_deleted')));
		}
	}
	
	function excel()
	{
		$data = file_get_contents("import_items.csv");
		$name = 'import_items.csv';
		force_download($name, $data);
	}
	
	function excel_import()
	{
		$this->load->view("items/excel_import", null);
	}

    function do_excel_import()
    {
        $msg = 'do_excel_import';
        $failCodes = array();
        if ($_FILES['file_path']['error']!=UPLOAD_ERR_OK)
        {
            $msg = $this->lang->line('items_excel_import_failed');
            echo json_encode( array('success'=>false,'message'=>$msg) );

            return;
        }
        else
		{
            if (($handle = fopen($_FILES['file_path']['tmp_name'], "r")) !== FALSE)
            {
                //Skip first row
                fgetcsv($handle);

                $i=1;
                while (($data = fgetcsv($handle)) !== FALSE)
                {
					if (sizeof($data) >= 23) {
	                    $item_data = array(
	                        'name'			=>	$data[1],
	                        'description'	=>	$data[11],
	                        'category'		=>	$data[2],
	                        'cost_price'	=>	$data[4],
	                        'unit_price'	=>	$data[5],
	                        'reorder_level'	=>	$data[10],
	                        'supplier_id'	=>  $this->Supplier->exists($data[3]) ? $data[3] : null,
	                        'allow_alt_description'	=>	$data[12] != '' ? '1' : '0',
	                        'is_serialized'	=>	$data[13] != '' ? '1' : '0',
	                        'custom1'		=>	$data[14],
	                        'custom2'		=>	$data[15],
	                        'custom3'		=>	$data[16],
	                        'custom4'		=>	$data[17],
	                        'custom5'		=>	$data[18],
	                        'custom6'		=>	$data[19],
	                        'custom7'		=>	$data[20],
	                        'custom8'		=>	$data[21],
	                        'custom9'		=>	$data[22],
	                        'custom10'		=>	$data[23]
	                    );
	                    $item_number = $data[0];
	                    $invalidated = false;
	                    if ($item_number != "")
	                    {
	                    	$item_data['item_number'] = $item_number;
		                    $invalidated = $this->Item->item_number_exists($item_number);
	                    }
					}
					else 
					{
						$invalidated = true;
					}
                    if(!$invalidated && $this->Item->save($item_data)) 
                    {
                        $items_taxes_data = null;
                        //tax 1
                        if( is_numeric($data[7]) && $data[6]!='' )
                        {
                            $items_taxes_data[] = array('name'=>$data[6], 'percent'=>$data[7] );
                        }

                        //tax 2
                        if( is_numeric($data[9]) && $data[8]!='' )
                        {
                            $items_taxes_data[] = array('name'=>$data[8], 'percent'=>$data[9] );
                        }

                        // save tax values
                        if(count($items_taxes_data) > 0)
                        {
                            $this->Item_taxes->save($items_taxes_data, $item_data['item_id']);
                        }

                        // quantities   & inventory Info
                        $employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
                        $emp_info=$this->Employee->get_info($employee_id);
                        $comment ='Qty CSV Imported';

                        $cols = count($data);

                        // array to store information if location got a quantity
                        $allowed_locations = $this->Stock_location->get_allowed_locations();
                        for ($col = 24; $col < $cols; $col = $col + 2)
                        {
                            $location_id = $data[$col];
                            if (array_key_exists($location_id, $allowed_locations))
                            {
                                $item_quantity_data = array (
                                    'item_id' => $item_data['item_id'],
                                    'location_id' => $location_id,
                                    'quantity' => $data[$col + 1],
                                );
                                $this->Item_quantity->save($item_quantity_data, $item_data['item_id'], $location_id);

                                $excel_data = array(
                                    'trans_items'=>$item_data['item_id'],
                                    'trans_user'=>$employee_id,
                                    'trans_comment'=>$comment,
                                    'trans_location'=>$data[$col],
                                    'trans_inventory'=>$data[$col + 1]
                                );
								
                                $this->Inventory->insert($excel_data);
                                unset($allowed_locations[$location_id]);
                            }
                        }

                        /*
                         * now iterate through the array and check for which location_id no entry into item_quantities was made yet
                         * those get an entry with quantity as 0.
                         * unfortunately a bit duplicate code from above...
                         */
                        foreach($allowed_locations as $location_id => $location_name)
                        {
                            $item_quantity_data = array(
                                'item_id' => $item_data['item_id'],
                                'location_id' => $location_id,
                                'quantity' => 0,
                            );
                            $this->Item_quantity->save($item_quantity_data, $item_data['item_id'], $data[$col]);

                            $excel_data = array
                                (
                                    'trans_items'=>$item_data['item_id'],
                                    'trans_user'=>$employee_id,
                                    'trans_comment'=>$comment,
                                    'trans_location'=>$location_id,
                                    'trans_inventory'=>0
                                );
                            $this->db->insert('inventory',$excel_data);
                        }
                    }
                    else//insert or update item failure
                    {
                        $failCodes[] = $i;
                    }
                }
                $i++;
            }
            else 
            {
                echo json_encode( array('success'=>false, 'message'=>'Your upload file has no data or not in supported format.') );

                return;
            }
        }

		$success = true;
		if(count($failCodes) > 0)
		{
			$msg = "Most items imported. But some were not, here is list of their CODE (" .count($failCodes) ."): ".implode(", ", $failCodes);
			$success = false;
		}
		else
		{
			$msg = "Import items successful";
		}

		echo json_encode( array('success'=>$success, 'message'=>$msg) );
	}

	/*
	get the width for the add/edit form
	*/
	function get_form_width()
	{
		return 450;
	}



	function generate_excel(){
		$this->load->library('excel');
		// $inputFileName = 'print_docs/excel/excel_template/Facility_redistributions.xlsx';
 		// if (!defined('PRECISION')) define('PRECISION', '5');
		$inputFileName = 'excel/assets_template.xlsx';		
	    $assets = $this->Asset->get_all()->result_array();
	    // echo "<pre>";print_r($data);die;
		// if ($ext == 'xls') {
		// 	$excel2 = PHPExcel_IOFactory::createReader('Excel5');
		// } else if ($ext == 'xlsx') {
		// 	$excel2 = PHPExcel_IOFactory::createReader('Excel2007');
		// }
	    $excel2 = PHPExcel_IOFactory::createReader('Excel2007');
		$excel2 = $excel2->load($inputFileName); // Empty Sheet
		$excel2->setActiveSheetIndex(1);

	   
		$file_name =isset($file_name) ? $file_name: time().'.xls';
		$objWriter = PHPExcel_IOFactory::createWriter($excel2, 'Excel5');

		$excel2->setActiveSheetIndex(0);
		$rows = array('B','C','D','E','F','G','H','I','J');
		$row_count = 4;
		foreach ($assets as $key => $value) {			
			$name = $value['name'];
            $serial_no = $value['serial_no'];
            $category = $value['category_name'];
            $depreciation_rate = $value['depreciation'];
            $price = $value['price'];            
            $date_of_purchase = $value['date_of_purchase'];
            $resale_price = $value['resale_price'];

            $diff = strtotime(date("M d Y ")) - (strtotime($date_of_purchase));
            $days_diff = floor($diff/3600/24);
            $years = round(($days_diff/365),0);
            // $years = 1;
            $depreciation = 0;
            $i =1;
            $new_price = $price;
            for ($i=1; $i <=$years ; $i++) { 
                $current_depreciation= $new_price * ($depreciation_rate/100);
                $depreciation +=$current_depreciation;                    
                $new_price -= $current_depreciation;                    
            }
            $current_value = $price - $depreciation;          
            $excel2->getActiveSheet()->setCellValue($rows[0].$row_count,$serial_no);
            $excel2->getActiveSheet()->setCellValue($rows[1].$row_count,$name);
            $excel2->getActiveSheet()->setCellValue($rows[2].$row_count,$category);
            $excel2->getActiveSheet()->setCellValue($rows[3].$row_count, to_currency($price));
            $excel2->getActiveSheet()->setCellValue($rows[4].$row_count,$depreciation_rate);
            $excel2->getActiveSheet()->setCellValue($rows[5].$row_count, to_currency($resale_price));
            $excel2->getActiveSheet()->setCellValue($rows[6].$row_count,date('F d Y',strtotime($date_of_purchase)));
            $excel2->getActiveSheet()->setCellValue($rows[7].$row_count, to_currency($depreciation));
            $excel2->getActiveSheet()->setCellValue($rows[8].$row_count, to_currency($current_value));
            
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
function download_excel_template(){
		$this->load->library('excel');		
		$inputFileName = 'excel/assets_upload_template.xlsx';			    
	    $categories = $this->Asset->get_all_categories()->result_array();
	    // echo "<pre>";print_r($categories);die;		
	    $excel2 = PHPExcel_IOFactory::createReader('Excel2007');
		$excel2 = $excel2->load($inputFileName); // Empty Sheet
	   	
		$file_name ='assets_upload_template_'.time().'.xls';
		$objWriter = PHPExcel_IOFactory::createWriter($excel2, 'Excel5');

		$excel2->setActiveSheetIndex(1);		
		$row_count = 1;
		foreach ($categories as $key => $value) {			
            $category = $value['category_name'];                        
            $excel2->getActiveSheet()->setCellValue('A'.$row_count,$category);            
            $row_count++;
		}
		$excel2->setActiveSheetIndex(0);		
		
		for ($i=4; $i < 2000; $i++) { 
			$objValidation = $excel2->getActiveSheet()->getCell('B'.$i)->getDataValidation();
			$objValidation->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
			$objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
			$objValidation->setAllowBlank(false);
			$objValidation->setShowInputMessage(true);
			$objValidation->setShowErrorMessage(true);
			$objValidation->setShowDropDown(true);
			$objValidation->setErrorTitle('Input error');
			$objValidation->setError('Value is not in list.');
			$objValidation->setPromptTitle('Pick from list');
			$objValidation->setPrompt('Please pick a value from the drop-down list.');
			$objValidation->setFormula1('"$categories.$A$1:$A$10"');

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

}
?>
