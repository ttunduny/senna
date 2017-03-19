<?php

class Salaries_lib
{
    var $CI;

    function __construct()
    {
        $this->CI =& get_instance();
    }

    function get_cart()
    {
        if(!$this->CI->session->userdata('cartRecv'))
            $this->set_cart(array());

        return $this->CI->session->userdata('cartRecv');
    }

    function set_cart($cart_data)
    {
        $this->CI->session->set_userdata('cartRecv',$cart_data);
    }

    function get_employee()
    {
        if(!$this->CI->session->userdata('employee'))
            $this->set_employee(-1);

        return $this->CI->session->userdata('employee');
    }

    function set_employee($employee_id)
    {
        $this->CI->session->set_userdata('employee',$employee_id);
    }

    

    function set_mode($mode)
    {
        $this->CI->session->set_userdata('recv_mode',$mode);
    }
    
    function get_stock_source()
    {
        if(!$this->CI->session->userdata('recv_stock_source'))
        {
             $location_id = $this->CI->Stock_location->get_default_location_id();
             $this->set_stock_source($location_id);
        }
        return $this->CI->session->userdata('recv_stock_source');
    }
    
    function get_comment()
    {
        // avoid returning a null that results in a 0 in the comment if nothing is set/available
        $comment = $this->CI->session->userdata('comment');
        return empty($comment) ? '' : $comment;
    }
    
    function set_comment($comment)
    {
        $this->CI->session->set_userdata('comment', $comment);
    }
    
    function clear_comment()
    {
        $this->CI->session->unset_userdata('comment');
    }
   
    function get_invoice_number()
    {
        return $this->CI->session->userdata('recv_invoice_number');
    }
    
    function set_invoice_number($invoice_number, $keep_custom = FALSE)
    {
        $current_invoice_number = $this->CI->session->userdata('recv_invoice_number');
        if (!$keep_custom || empty($current_invoice_number))
        {
            $this->CI->session->set_userdata('recv_invoice_number', $invoice_number);
        }
    }
    
    function clear_invoice_number()
    {
        $this->CI->session->unset_userdata('recv_invoice_number');
    }
    
    function is_invoice_number_enabled()
    {
        return $this->CI->session->userdata('recv_invoice_number_enabled') == 'true' ||
        $this->CI->session->userdata('recv_invoice_number_enabled') == '1';
    }
    
    function set_invoice_number_enabled($invoice_number_enabled)
    {
        return $this->CI->session->set_userdata('recv_invoice_number_enabled', $invoice_number_enabled);
    }
    
    function is_print_after_sale()
    {
        return $this->CI->session->userdata('recv_print_after_sale') == 'true' ||
        $this->CI->session->userdata('recv_print_after_sale') == '1';
    }
    
    function set_print_after_sale($print_after_sale)
    {
        return $this->CI->session->set_userdata('recv_print_after_sale', $print_after_sale);
    }
    
    function set_stock_source($stock_source)
    {
        $this->CI->session->set_userdata('recv_stock_source',$stock_source);
    }
    
    function clear_stock_source()
    {
        $this->CI->session->unset_userdata('recv_stock_source');
    }
    
    function get_stock_destination()
    {
        if(!$this->CI->session->userdata('recv_stock_destination'))
        {
            $location_id = $this->CI->Stock_location->get_default_location_id();
            $this->set_stock_destination($location_id);
        }
        return $this->CI->session->userdata('recv_stock_destination');
    }

    function set_stock_destination($stock_destination)
    {
        $this->CI->session->set_userdata('recv_stock_destination',$stock_destination);
    }
    
    function clear_stock_destination()
    {
        $this->CI->session->unset_userdata('recv_stock_destination');
    }

    function add_item($person_id,$gross_sal=null,$nssf=null,$nhif=null,$tax=null)
    {
        //make sure item exists in database.
        if(!$this->CI->Employee->exists($person_id))
        {
            //try to get item id given an item_number
            $item_id = $this->CI->Employee->get_multiple_info($person_id);

            if(!$item_id)
                return false;
        }

        //Get items in the receiving so far.
        $items = $this->get_cart();

         $maxkey=0;                       //Highest key so far
        $itemalreadyinsale=FALSE;        //We did not find the item yet.
        $insertkey=0;                    //Key to use for new entry.
        $updatekey=0;                    //Key to use to update(quantity)

       
        $insertkey=$maxkey+1;
        $item_info=$this->CI->Employee->get_info($person_id);
       
        $item = array(($insertkey)=>
        array(
            'person_id'=>$person_id,
            'gross_sal'=>$item_info->gross_sal,
            'nhif'=>$item_info->nhif,
            'nssf'=>$item_info->nssf,
            'tax'=>$item_info->tax,
            'first_name'=>$item_info->first_name.' '.$item_info->last_name,
            'total'=>$this->get_item_total($gross_sal, $nhif, $nssf,$tax)
            )
        );

       
            //add to existing array
            $items= array_merge($item,$items);
        

        $this->set_cart($items);
        // echo "<pre>";print_r($items);echo "</pre>";
        return true;

    }
        // $item_id,$description,$receiving_quantity,$quantity,$discount,$expiry,$price,$vat);
    function edit_item($line,$description,$receiving_quantity,$quantity,$discount,$expiry,$price,$vat)
    {        
        $items = $this->get_cart();
        
        if(isset($items[$line]))
        {
            $line = &$items[$line];
            $line['description'] = $description;
            // $line['serialnumber'] = $serialnumber;
            $line['quantity'] = $quantity;
            $line['discount'] = $discount;
            $line['price'] = $price;
            $line['expiry'] = $expiry;
            $line['vat'] = $vat;
            $line['total'] = $this->get_item_total($quantity, $price, $receiving_quantity,$discount,$vat); 
            $this->set_cart($items);
        }

        return false;
    }
  
     function delete_item($line)
    {
        $items=$this->get_cart();
        unset($items[$line]);
        $this->set_cart($items);
    }
     

    function empty_cart()
    {
        $this->CI->session->unset_userdata('cartRecv');
    }

    function delete_supplier()
    {
        $this->CI->session->unset_userdata('supplier');
    }
    
    function clear_mode()
    {
        $this->CI->session->unset_userdata('receiving_mode');
    }

    function clear_all()
    {
        $this->clear_mode();
        $this->empty_cart();
        $this->delete_supplier();
        $this->clear_comment();
        $this->clear_invoice_number();
    }
    // $quantity, $price, $receiving_quantity,$discount,$vat); 
    
    function get_item_total($gross_sal, $nssf,$nhif, $tax)
    {
            $totalsfinal = 0;   
            $vat_amount = 0;

            // $nssfcash = ($nssf*$gross_sal)/100;
            // $nhifcash = ($nhif*$gross_sal)/100;
            // $taxcash = ($tax*$gross_sal)/100;
            $nssfcash = $nssf;
            $nhifcash = $nhif;
            $taxcash = $tax;
           
            $totalsfinal = round(($gross_sal- $nssfcash-$nhifcash-$taxcash),2);
     
        return $totalsfinal;
    
    }

    
      function get_total()
    {
        

        $total = 0;

        foreach($this->get_cart() as $item)
        {
          
                // $quantity, $price, $receiving_quantity,$discount,$vat); 
            $total =  $this->get_item_total($item['gross_sal'], $item['nssf'],$item['nhif'], $item['tax']);

            $total = round($total, 0);
        
        
        return $total;
    

}}}
?>
