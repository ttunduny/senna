<?php

class Receiving_lib
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

    function get_supplier()
    {
        if(!$this->CI->session->userdata('supplier'))
            $this->set_supplier(-1);

        return $this->CI->session->userdata('supplier');
    }

    function set_supplier($supplier_id)
    {
        $this->CI->session->set_userdata('supplier',$supplier_id);
    }

    function get_mode()
    {
        if(!$this->CI->session->userdata('recv_mode'))
            $this->set_mode('receive');

        return $this->CI->session->userdata('recv_mode');
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

    function add_item($item_id,$quantity=1,$item_location=null,$discount=0,$price=null,$description=null,$serialnumber=null,$receiving_quantity=null)
    {
        //make sure item exists in database.
        if(!$this->CI->Item->exists($item_id))
        {
            //try to get item id given an item_number
            $item_id = $this->CI->Item->get_item_id($item_id);

            if(!$item_id)
                return false;
        }

        //Get items in the receiving so far.
        $items = $this->get_cart();

        //We need to loop through all items in the cart.
        //If the item is already there, get it's key($updatekey).
        //We also need to get the next key that we are going to use in case we need to add the
        //item to the list. Since items can be deleted, we can't use a count. we use the highest key + 1.

        $maxkey=0;                       //Highest key so far
        $itemalreadyinsale=FALSE;        //We did not find the item yet.
        $insertkey=0;                    //Key to use for new entry.
        $updatekey=0;                    //Key to use to update(quantity)

        foreach ($items as $item)
        {
            //We primed the loop so maxkey is 0 the first time.
            //Also, we have stored the key in the element itself so we can compare.
            //There is an array function to get the associated key for an element, but I like it better
            //like that!

            if($maxkey <= $item['line'])
            {
                $maxkey = $item['line'];
            }

            if($item['item_id']==$item_id && $item['item_location']==$item_location)
            {
                $itemalreadyinsale=TRUE;
                $updatekey=$item['line'];
            }
        }

        $insertkey=$maxkey+1;
        $item_info=$this->CI->Item->get_info($item_id,$item_location);
        //array records are identified by $insertkey and item_id is just another field.
        $price=$price!=null ? $price: $item_info->cost_price;
        $item = array(($insertkey)=>
        array(
            'item_id'=>$item_id,
            'item_location'=>$item_location,
            'stock_name'=>$this->CI->Stock_location->get_location_name($item_location),
            'line'=>$insertkey,
            'name'=>$item_info->name,
            'description'=>$description!=null ? $description: $item_info->description,
            'serialnumber'=>$serialnumber!=null ? $serialnumber: '',
            'allow_alt_description'=>$item_info->allow_alt_description,
            'is_serialized'=>$item_info->is_serialized,
            'quantity'=>$quantity,
            'discount'=>$discount,
            'expiry'=>date('m/d/Y'),
            'in_stock'=>$this->CI->Item_quantity->get_item_quantity($item_id, $item_location)->quantity,
            'price'=>$price,
            'vat'=>0,
            'receiving_quantity'=>$receiving_quantity!=null ? $receiving_quantity : $item_info->receiving_quantity,
            'total'=>$this->get_item_total($quantity, $price, $discount,$receiving_quantity)
            )
        );

        //Item already exists
        if($itemalreadyinsale)
        {
            $items[$updatekey]['quantity']+=$quantity;
        }
        else
        {
            //add to existing array
            $items+=$item;
        }

        $this->set_cart($items);
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
    
    function is_valid_receipt($receipt_receiving_id)
    {
        //RECV #
        $pieces = explode(' ',$receipt_receiving_id);

        if(count($pieces)==2)
        {
            return $this->CI->Receiving->exists($pieces[1]);
        }
        else 
        {
            return $this->CI->Receiving->get_receiving_by_invoice_number($receipt_receiving_id)->num_rows() > 0;
        }

        return false;
    }
    
    function is_valid_item_kit($item_kit_id)
    {
        //KIT #
        $pieces = explode(' ',$item_kit_id);

        if(count($pieces)==2)
        {
            return $this->CI->Item_kit->exists($pieces[1]);
        }

        return false;
    }

    function return_entire_receiving($receipt_receiving_id)
    {
        //POS #
        $pieces = explode(' ',$receipt_receiving_id);
        if ($pieces[0] == "RECV")
        {
            $receiving_id = $pieces[1];
        } 
        else 
        {
            $receiving = $this->CI->Receiving->get_receiving_by_invoice_number($receipt_receiving_id)->row();
            $receiving_id = $receiving->receiving_id;
        }

        $this->empty_cart();
        $this->delete_supplier();
        $this->clear_comment();

        foreach($this->CI->Receiving->get_receiving_items($receiving_id)->result() as $row)
        {
            $this->add_item($row->item_id,-$row->quantity_purchased,$row->item_location,$row->discount_percent,$row->item_unit_price,$row->description,$row->serialnumber,$row->receiving_quantity);
        }
        $this->set_supplier($this->CI->Receiving->get_supplier($receiving_id)->person_id);
    }
    
    function add_item_kit($external_item_kit_id,$item_location)
    {
        //KIT #
        $pieces = explode(' ',$external_item_kit_id);
        $item_kit_id = $pieces[1];
        
        foreach ($this->CI->Item_kit_items->get_info($item_kit_id) as $item_kit_item)
        {
            $this->add_item($item_kit_item['item_id'],$item_kit_item['quantity'],$item_location);
        }
    }

    function copy_entire_receiving($receiving_id)
    {
        $this->empty_cart();
        $this->delete_supplier();

        foreach($this->CI->Receiving->get_receiving_items($receiving_id)->result() as $row)
        {
            $this->add_item($row->item_id,$row->quantity_purchased,$row->item_location,$row->discount_percent,$row->item_unit_price,$row->description,$row->serialnumber,$row->receiving_quantity);
        }
        $this->set_supplier($this->CI->Receiving->get_supplier($receiving_id)->person_id);
        $receiving_info=$this->CI->Receiving->get_info($receiving_id);
        //$this->set_invoice_number($receiving_info->row()->invoice_number);
    }
     function get_item_tax($quantity, $price,$receiving_quantity, $discount_percentage, $tax_percentage) 
    {
        
        $price = $this->get_item_total($quantity, $price,$receiving_quantity, $discount_percentage, TRUE);

        if ($this->CI->config->config['tax_included'])
        {
            $tax_fraction = bcadd(100, $tax_percentage, PRECISION);
            $tax_fraction = bcdiv($tax_fraction, 100, PRECISION);
            $price_tax_excl = bcdiv($price, $tax_fraction, PRECISION);
            return bcsub($price, $price_tax_excl, PRECISION);
        }
        $tax_fraction = bcdiv($tax_percentage, 100, PRECISION);
        return bcmul($price, $tax_fraction, PRECISION);
    }
     function get_customer()
    {
        if(!$this->CI->session->userdata('customer'))
            $this->set_customer(-1);

        return $this->CI->session->userdata('customer');
    }
     function is_customer_taxable()
    {
        $customer_id = $this->get_customer();
        $customer = $this->CI->Customer->get_info($customer_id);
        
        //Do not charge sales tax if we have a customer that is not taxable
        return $customer->taxable or $customer_id==-1;
    }
    
    function delete_item($line)
    {
        $items=$this->get_cart();
        unset($items[$line]);
        $this->set_cart($items);
    }
     function get_taxes()
    {
        //Do not charge sales tax if we have a customer that is not taxable
        
        $taxes = array();
        foreach($this->get_cart() as $line=>$item)
        {
            $tax_info = $this->CI->Item_taxes->get_info($item['item_id']);

            foreach($tax_info as $tax)
            {
                $name = $tax['percent'].'% ' . $tax['name'];
                $tax_percentage = $tax['percent'];
                $tax_amount = $this->get_item_tax($item['quantity'], $item['price'],$item['receiving_quantity'], $item['discount'], $tax_percentage);

                if (!isset($taxes[$name]))
                {
                    $taxes[$name] = 0;
                }
                $taxes[$name] = bcadd($taxes[$name], $tax_amount, PRECISION);
            }
        }

        return $taxes;
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
    
    function get_item_total($quantity, $price,$receiving_quantity, $discount_percentage,$vat=0)
    {
        $receiving_quantity = ($receiving_quantity==0) ? 1 : $receiving_quantity ;        

        // $price = ($item['price']);
        // $vat = ($item['vat']);
        // $quantity = ($item['quantity']);
        // $discount = ($item['discount']);
        $discount = round(($discount_percentage)/100,2); 
        $totalsfinal = 0;
        // $receiving_quantity = ($item['receiving_quantity']);
        $totaldiscount = $price*$quantity*$discount*$receiving_quantity;         
        $totalcost = $price*$quantity*$receiving_quantity;

        if($discount==0){
            $totalsfinal = $totalcost;
        }else{
            $totalsfinal = ($totalcost - ($totaldiscount/100));
        }
        if($vat==0){
            $totalsfinal = $totalsfinal;
        }else{
            $vat_amount = $totalcost * ($vat/100);
            $totalsfinal = round(($totalsfinal+$vat_amount),2);
        }

        // echo "RQ $receiving_quantity PR $price  DC $discount_percentage<br/>";
        
        
        // $quantity = round($quantity*$receiving_quantity,2);
        // $total =round($quantity*$price,2);
        // $discount_fraction = round(($discount_percentage)/100,2); 
        // $discount_amount = 0;
        // $vat_amount = 0;
        // $total_final = 0;
        // if ($discount_percentage==0) {
        //     $discount_amount = 0;
        // }else{
        //     $discount_amount =  round($total*$discount_fraction, 2);
        // }
        // if ($vat==0) {
        //     $vat_amount = 0;
        // }else{
        //     $vat_amount =  round(($total*($vat/100)), 2);
        // }
        // // echo "RQ $receiving_quantity PR $price  TT $total DC $discount_percentage";
        // $total_final = round(($total - $discount_amount + $vat_amount),2);
        // echo "Discount Frac $discount_percentage and $total_final";die;       
        // $total = bcmul($quantity, $price, PRECISION);
        // $discount_fraction = bcdiv($discount_percentage, 100, PRECISION);
        // $discount_amount =  round($total*$discount_fraction, 1);
        return $totalsfinal;
        // return $total_final;
        // return round(($total-$discount_amount), 0);

        // return  $discount_amount;


    }

    // function get_total()
    // {
    //     $total = 0;
    //     foreach($this->get_cart() as $item)
    //     {
    //         $total = bcadd($total, $this->get_item_total($item['quantity'], $item['price'], $item['discount']), PRECISION);
    //     }
        
    //     return $total;
    // }
      function get_total()
    {
        

        $total = 0;

        foreach($this->get_cart() as $item)
        {
            if (!$this->CI->config->config['tax_included'])
        {
            foreach($this->get_taxes() as $tax)
            {
                // $quantity, $price, $receiving_quantity,$discount,$vat); 
            $total =  $this->get_item_total($item['quantity'], $item['price'],$item['receiving_quantity'], $item['discount']);

            $total = round($total+$tax, 0);
        }
        
        return $total;
    }

}}}
?>
