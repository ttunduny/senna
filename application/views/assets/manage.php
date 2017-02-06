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
    }, dateFormat: '<?php echo dateformat_jquery($this->config->asset("dateformat"));?>',
       timeFormat: '<?php echo dateformat_jquery($this->config->asset("timeformat"));?>'
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
            sortList: [[0,0]],
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
    console.log(response);  
    if(!response.success)
    {
        set_feedback(response.message,'error_message',true);
    }
    else
    {
        //This is an update, just update one row
        if(jQuery.inArray(response.asset_id,get_visible_checkbox_ids()) != -1)
        {
            update_row(response.asset_id,'<?php echo site_url("$controller_name/get_row")?>',resize_thumbs);
            set_feedback(response.message,'success_message',false);
        }
        else //refresh entire table
        {
            do_search(true,function()
            {
                //highlight new row
                hightlight_row(response.asset_id);
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
        var selected_asset_ids=get_selected_values();
        for(k=0;k<selected_asset_ids.length;k++)
        {
            update_row(selected_asset_ids[k],'<?php echo site_url("$controller_name/get_row")?>',resize_thumbs);
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

<div id="table_action_header">
    <ul>
        <li class="float_left"><span><?php echo anchor("$controller_name/delete",$this->lang->line("common_delete"),array('id'=>'delete')); ?></span></li>        
        <li class="float_right">
            <img src='<?php echo base_url()?>images/spinner_small.gif' alt='spinner' id='spinner' />
            <input type="text" name ='search' id='search'/>
            <input type="hidden" name ='limit_from' id='limit_from'/>
        </li>
    </ul>
</div>

<?php echo form_close(); ?>

<div id="table_holder" style="width:100%">
   <?php echo $manage_table; ?>
</div>


<div id="feedback_bar"></div>

<?php $this->load->view("partial/footer"); ?>
