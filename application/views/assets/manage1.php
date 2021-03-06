<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
$(document).ready(function()
{
    init_table_sorting();
    enable_select_all();
    enable_checkboxes();
    enable_row_selection();
    var widget = enable_search({suggest_url : '<?php echo site_url("$controller_name/suggest")?>',
        confirm_message : '<?php echo $this->lang->line("common_confirm_search")?>',
        extra_params : {
            'is_deleted' : function () {
                return $("#is_deleted").is(":checked") ? 1 : 0;
        }
    }});
    // clear suggestion cache when toggling filter
    $("#is_deleted").change(function() {
        widget.flushCache();
    });
    enable_delete('<?php echo $this->lang->line($controller_name."_confirm_delete")?>','<?php echo $this->lang->line($controller_name."_none_selected")?>');
    enable_bulk_edit('<?php echo $this->lang->line($controller_name."_none_selected")?>');

    $('#generate_barcodes').click(function()
    {
        var selected = get_selected_values();
        if (selected.length == 0)
        {
            alert('<?php echo $this->lang->line('items_must_select_item_for_barcode'); ?>');
            return false;
        }

        $(this).attr('href','index.php/items/generate_barcodes/'+selected.join(':'));
    });

    $("#search_filter_section input").click(function() 
    {
        // reset page number when selecting a specific page number
        $('#limit_from').val("0");
        do_search(true);
    });

    $('#search').keypress(function (e) {
        if (e.which == 13) {
            $('#search_form').submit();
        }
    });

    $(".date_filter").datepicker({onSelect: function(d,i)
    {
        if(d !== i.lastVal){
            $(this).change();
        }
    }, dateFormat: '<?php echo dateformat_jquery($this->config->item("dateformat"));?>',
       timeFormat: '<?php echo dateformat_jquery($this->config->item("timeformat"));?>'
    }).change(function() {
        do_search(true);
        return false;
    });
    
    resize_thumbs();
});

function resize_thumbs()
{
    $('a.rollover').imgPreview();
}

function init_table_sorting()
{
    //Only init if there is more than one row
    if($('.tablesorter tbody tr').length >1)
    {
        $("#sortable_table").tablesorter(
        {
            sortList: [[1,0]],
            headers:
            {
                0: { sorter: false},
                8: { sorter: false},
                9: { sorter: false},
                10: { sorter: false}
            }
        });
    }
}

function post_asset_form_submit(response)
{
    if(!response.success)
    {
        set_feedback(response.message,'error_message',true);
    }
    else
    {
        //This is an update, just update one row
        if(jQuery.inArray(response.item_id,get_visible_checkbox_ids()) != -1)
        {
            update_row(response.item_id,'<?php echo site_url("$controller_name/get_row")?>',resize_thumbs);
            set_feedback(response.message,'success_message',false);
        }
        else //refresh entire table
        {
            do_search(true,function()
            {
                //highlight new row
                hightlight_row(response.item_id);
                set_feedback(response.message,'success_message',false);
            });
        }
    }
}

function post_bulk_form_submit(response)
{
    if(!response.success)
    {
        set_feedback(response.message,'error_message',true);
    }
    else
    {
        var selected_item_ids=get_selected_values();
        for(k=0;k<selected_item_ids.length;k++)
        {
            update_row(selected_item_ids[k],'<?php echo site_url("$controller_name/get_row")?>',resize_thumbs);
        }
        set_feedback(response.message,'success_message',false);
    }
}

function show_hide_search_filter(search_filter_section, switchImgTag)
{
    var ele = document.getElementById(search_filter_section);
    var imageEle = document.getElementById(switchImgTag);
    var elesearchstate = document.getElementById('search_section_state');

    if(ele.style.display == "block")
    {
        ele.style.display = "none";
        imageEle.innerHTML = '<img src=" <?php echo base_url()?>images/plus.png" style="border:0;outline:none;padding:0px;margin:0px;position:relative;top:-5px;" >';
        elesearchstate.value="none";
    }
    else
    {
        ele.style.display = "block";
        imageEle.innerHTML = '<img src=" <?php echo base_url()?>images/minus.png" style="border:0;outline:none;padding:0px;margin:0px;position:relative;top:-5px;" >';
        elesearchstate.value="block";
    }
}
</script>


<div id="title_bar">
    <div id="title" class="float_left"><?php echo $this->lang->line('common_list_of').' '.$this->lang->line('module_'.$controller_name); ?></div>
    <div id="new_button">
        <?php echo anchor("$controller_name/view/-1/width:$form_width",
        "<div class='big_button' style='float: left;'><span>".$this->lang->line($controller_name.'_new')."</span></div>",
        array('class'=>'thickbox none','title'=>$this->lang->line($controller_name.'_new')));
        ?>
        <?php echo anchor("$controller_name/excel_import/width:$form_width",
        "<div class='big_button' style='float: left;'><span>" . $this->lang->line('common_import_excel') . "</span></div>",
        array('class'=>'thickbox none','title'=>'Import Items from Excel'));
        ?>
    </div>
</div>


<div id="pagination"><?= $links ?></div>
<div id="titleTextImg" style="background-color:#EEEEEE;height:30px;position:relative;">
    <div style="float:left;vertical-align:text-top;"><?php echo $this->lang->line('common_search_options'); ?> :</div>
    <a id="imageDivLink" href="javascript:show_hide_search_filter('search_filter_section', 'imageDivLink');" style="outline:none;">
    <img src="
    <?php echo isset($search_section_state)? ( ($search_section_state)? base_url().'images/minus.png' : base_url().'images/plus.png') : base_url().'images/plus.png';?>" style="border:0;outline:none;padding:0px;margin:0px;position:relative;top:-5px;"></a>
</div>
<?php echo form_open("$controller_name/search",array('id'=>'search_form')); ?>
<div id="search_filter_section" style="display: <?php echo isset($search_section_state)?  ( ($search_section_state)? 'block' : 'none') : 'none';?>; background-color:#EEEEEE;">
    <?php echo form_label($this->lang->line('items_empty_upc_items').' '.':', 'empty_upc');?>
    <?php echo form_checkbox(array('name'=>'empty_upc','id'=>'empty_upc','value'=>1,'checked'=> isset($empty_upc)?  ( ($empty_upc)? 1 : 0) : 0)).' | ';?>
    <?php echo form_label($this->lang->line('items_low_inventory_items').' '.':', 'low_inventory');?>
    <?php echo form_checkbox(array('name'=>'low_inventory','id'=>'low_inventory','value'=>1,'checked'=> isset($low_inventory)?  ( ($low_inventory)? 1 : 0) : 0)).' | ';?>
    <?php echo form_label($this->lang->line('items_serialized_items').' '.':', 'is_serialized');?>
    <?php echo form_checkbox(array('name'=>'is_serialized','id'=>'is_serialized','value'=>1,'checked'=> isset($is_serialized)?  ( ($is_serialized)? 1 : 0) : 0)).' | ';?>
    <?php echo form_label($this->lang->line('items_no_description_items').' '.':', 'no_description');?>
    <?php echo form_checkbox(array('name'=>'no_description','id'=>'no_description','value'=>1,'checked'=> isset($no_description)?  ( ($no_description)? 1 : 0) : 0)).' | ';?>
    <?php echo form_label($this->lang->line('items_search_custom_items').' '.':', 'search_custom');?>
    <?php echo form_checkbox(array('name'=>'search_custom','id'=>'search_custom','value'=>1,'checked'=> isset($search_custom)?  ( ($search_custom)? 1 : 0) : 0)).' | ';?>
    <?php echo form_label($this->lang->line('items_is_deleted').' '.':', 'is_deleted');?> 
    <?php echo form_checkbox(array('name'=>'is_deleted','id'=>'is_deleted','value'=>1,'checked'=> isset($is_deleted)?  ( ($is_deleted)? 1 : 0) : 0));?> 

    <?php echo form_label($this->lang->line('sales_date_range').' :', 'start_date');?>
    <?php echo form_input(array('name'=>'start_date','value'=>$start_date, 'class'=>'date_filter', 'size' => '15'));?>
    <?php echo form_label(' - ', 'end_date');?>
    <?php echo form_input(array('name'=>'end_date','value'=>$end_date, 'class'=>'date_filter', 'size' => '15'));?>
    
    <input type="hidden" name="search_section_state" id="search_section_state" value="<?php echo isset($search_section_state)?  ( ($search_section_state)? 'block' : 'none') : 'none';?>" />
</div>
<div id="table_action_header">
    <ul>
        <li class="float_left"><span><?php echo anchor("$controller_name/delete",$this->lang->line("common_delete"),array('id'=>'delete')); ?></span></li>
        <!-- <li class="float_left"><span><?php //echo anchor("$controller_name/bulk_edit/width:$form_width",$this->lang->line("items_bulk_edit"),array('id'=>'bulk_edit','title'=>$this->lang->line('items_edit_multiple_items'))); ?></span></li> -->
        <!-- <li class="float_left"><span><?php //echo anchor("$controller_name/generate_barcodes",$this->lang->line("items_generate_barcodes"),array('id'=>'generate_barcodes', 'target' =>'_blank','title'=>$this->lang->line('items_generate_barcodes'))); ?></span></li>         -->
        <li class="float_right">
            <img src='<?php echo base_url()?>images/spinner_small.gif' alt='spinner' id='spinner' />
            <input type="text" name ='search' id='search'/>
            <input type="hidden" name ='limit_from' id='limit_from'/>
        </li>
    </ul>
</div>

<?php echo form_close(); ?>

<div id="table_holder" style="width:100%">
    <table id="sortable_table" class="stripe cell-border row-border display" cellspacing="0" width="100%" >
        <thead>
            <tr>
                <th><input type="checkbox" id="select_all" /></th> <th>Serial Number</th><th>Asset Name</th><th>Asset Category</th><th>Depreciation Rate</th><th>Purchase Price</th><th>Resale Price</th><th>Date of Purchase</th><th>Depreciation</th><th>Current Value</th><th>Added By</th><th></th><th></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach ($assets as $key => $value) {
                $id = $value['id'];
                $name = $value['name'];
                $serial_no = $value['serial_no'];
                $category = $value['category_name'];
                $depreciation_rate = $value['depreciation'];
                $price = $value['price'];
                $user = ucfirst($value['first_name']).' '.ucfirst($value['last_name']);                
                $date_of_purchase = $value['date_of_purchase'];
                $resale_price = $value['resale_price'];

                $diff = strtotime(date("M d Y ")) - (strtotime($date_of_purchase));
                $days_diff = floor($diff/3600/24);
                $years = round(($days_diff/365),0);
                
                $depreciation = 0;
                $i =1;
                $new_price = $price;
                for ($i=1; $i <=$years ; $i++) { 
                    $current_depreciation= $new_price * ($depreciation_rate/100);
                    $depreciation +=$current_depreciation;                    
                    $new_price -= $current_depreciation;                    
                }
                $current_value = $price - $depreciation;          
                

                ?>
            <tr>                
                <td><input type='checkbox' id="<?php echo $id;?>" value="<?php echo $id;?>"/></td>
                <td><?php echo $serial_no;?></td>
                <td><?php echo $name;?></td>
                <td><?php echo $category;?></td>
                <td><?php echo $depreciation_rate.'%';?></td>
                <td><?php echo to_currency($price);?></td>
                <td><?php echo to_currency($resale_price);?></td>
                <td><?php echo date('F d Y',strtotime($date_of_purchase));?></td>
                <td><?php echo to_currency($depreciation);?></td>
                <td><?php echo to_currency($current_value);?></td>
                <td><?php echo $user;?></td>
                <td><?php echo anchor($controller_name."/view/$id/", $this->lang->line('common_edit'),array('class'=>'thickbox','title'=>$this->lang->line($controller_name.'_update')));?></td>                  
                <td><?php echo anchor($controller_name."/count_details/$id/", $this->lang->line('common_det'),array('class'=>'thickbox','title'=>$this->lang->line($controller_name.'_details_count')));?></td>
            </tr>
            <?php }

            ?>
        </tbody>
    </table>
</div>


<div id="feedback_bar"></div>

<?php $this->load->view("partial/footer"); ?>
