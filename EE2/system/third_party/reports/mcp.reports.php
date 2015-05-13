<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports_mcp {

    //module vars
	private $module_name = 'Reports';
	private $module_version = '1.0.1';
	private $backend_bool = 'y';

	var $base = "";
	var $export_type = 'csv';

	function Reports_mcp( $switch = TRUE )
    {

        $this->base = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name;

        //  Onward!
        ee()->load->library('table');
        ee()->lang->loadfile('reports');
        
    }

    function index()
    {

		ee()->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=reports', lang('reports_module_name'));
        ee()->view->cp_page_title = lang('page_title_all_reports');

        ee()->load->library('table');
        ee()->load->helper('date');

        $this->base = 'C=modules&M=Reports';
        
        $js = "";

		$vars = array();

		// Get reports list
		ee()->db->select('report_id, title, description, sdate, edate, append');

        $query = ee()->db->get('reports');

		if ($query->num_rows > 0)
		{
			$vars['reports'] = $query->result_array();
		}
		else
		{
			$vars['reports'] = NULL;
		}
		
		ee()->cp->set_right_nav(array(
			'Add New Report' => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=reports'.AMP.'method=report_edit'
		));

        $js .= '
				var draft_target = "";

				$("<div id=\"report_delete_warning\">Warning of Deleting Report</div>").dialog({
					autoOpen: false,
					resizable: false,
					title: "Are you sure you want to delete?",
					modal: true,
					position: "center",
					minHeight: "0px", 
					buttons: {
						Cancel: function() {
						$(this).dialog("close");
						},
					"Delete Report": function() {
						location=draft_target;
					}
					}});
	
				$(".report_delete_warning").click( function (){
					$("#report_delete_warning").dialog("open");
					draft_target = $(this).attr("href");
					$(".ui-dialog-buttonpane button:eq(2)").focus();	
					return false;
			});';

		ee()->javascript->output($js);
		
        return ee()->load->view('index', $vars, TRUE);

    }
    
    // add edit function to module
    function report_edit()
    {
		ee()->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=reports', lang('reports_module_name'));
        ee()->view->cp_page_title = 'Edit a Report';
		
		ee()->load->helper('form');
        ee()->load->library('table');

        $this->base = 'C=modules&M=Reports';
		
		$js = '';
		
		$values = array(
			'report_id' => false,
			'site_id' => '1',	
			'title' => '',
			'description' => '',
			'file_name' => '',
			'member_id' => '',
			'query' => '',
			'post_processing' => '',
			
			'datetime' => '',
			'sdate' => '',
			'edate' => '',
			'append' => ''
		);

		if (ee()->input->get('report_id')!==false)
		{
			$q = ee()->db->select('*')
					->from('reports')
					->where('report_id', ee()->input->get('report_id'))
					->get();
			if ($q->num_rows()==0)
			{
				show_error('Unauthorized Access');
			}
	
			foreach ($values as $field_name=>$default_field_val)
			{
				if (is_array($default_field_val))
				{
					$values["$field_name"] = ($q->row("$field_name")!='')?unserialize($q->row("$field_name")):array();
				}
				else
				{
					$values["$field_name"] = $q->row("$field_name");
				}
			}
		}
		ee()->cp->add_js_script(
			array('ui' => array(
				'core', 'datepicker'
			)
		));

		// Get reports list
        //$query = ee()->db->get($q);
		if($values['sdate'] != "")
        {
	        $values['sdate'] = $values['sdate']."000";
	    }
	    if($values['edate'] != "")
	    {
		    $values['edate'] = $values['edate']."000";
	    }
		
		$append = array(
			"No" => "No",
			"Yes" => "Yes"
		);
		
		$vars['reports'] = array();
		$vars['reports']['Report ID'] = $values['report_id'];
		$vars['reports']['Site ID'] = form_input('site_id', $values['site_id'], 'style="width: 95%"').form_hidden('report_id', $values['report_id']);
		$vars['reports']['Title'] = form_input('title', $values['title'], 'style="width: 95%"');           
		$vars['reports']['Description'] = form_textarea('description', $values['description'], 'style="width: 95%"');  
		$vars['reports']['File Name'] = form_input('file_name', $values['file_name']); 
		$vars['reports']['Member ID'] = form_input('member_id', $values['member_id']); 
		$vars['reports']['Query'] = form_textarea('query', $values['query'], 'style="width: 95%"');  
		$vars['reports']['Post Processing'] = form_textarea('post_processing', $values['post_processing'], 'style="width: 95%"');  
		$vars['reports']['Created Date'] = form_input('datetime', $values['datetime'], 'class="datepicker"');    
		$vars['reports']['Start Date'] = form_input('sdate', $values['sdate'], 'class="datepicker"');    
		$vars['reports']['End Date'] = form_input('edate', $values['edate'], 'class="datepicker"');    
		$vars['reports']['Append Dates to Main Query'] = form_dropdown('append', $append, $values['append']);    

        $js .= "
            $(function() {
				$(\"input.datepicker\").datepicker({ dateFormat: \"@\", changeMonth: true, changeYear: true, numberOfMonths: 3, showButtonPanel: true, showOtherMonths: true, selectOtherMonths: true });
			});
        ";

        ee()->javascript->output($js);

        return ee()->load->view('edit', $vars, TRUE);
    }
	// add save function to module
    function report_save()
    {
    	if (empty($_POST))
    	{
    		show_error('unauthorized_access');
    	}
    	
        unset($_POST['submit']);
        $data = array();

		foreach ($_POST as $key=>$val)
        {
        	if (is_array($val))
        	{
        		$data[$key] = serialize($val);
        	}
        	else
        	{
        		switch ($data[$key])
        		{
	        		case $data['sdate']:
	        			if($val == "")
	        			{
		        			$data['sdate'] = "";
	        			}
	        			else
	        			{
			        		if(strlen($val) == 13)
			        		{
				        		$val = substr($val, 0, -3);
				        	}
		        			$data['sdate'] = $val;
		        		}
	        			break;
	        		case $data['edate']:
	        			if($val == "")
	        			{
		        			$data['edate'] = "";
	        			}
	        			else
	        			{
			        		if(strlen($val) == 13)
			        		{
				        		$val = substr($val, 0, -3);
				        	}
		        			$data['edate'] = $val;
		        		}
	        			break;
        		}
				$data[$key] = $val;
        	}
        }
        
        $db_fields = ee()->db->list_fields('reports');
        foreach ($db_fields as $id=>$field)
        {
        	if (!isset($data[$field])) $data[$field] = '';
        }
      	
		if (ee()->input->post('report_id')!='')
        {
            ee()->db->where('report_id', ee()->input->post('report_id'));
			ee()->db->update('reports', $data);
            
        }
        else
        {
            ee()->db->insert('reports', $data);
        }
                        
        ee()->session->set_flashdata('message_success', 'updated');
        
        ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=reports'.AMP.'method=index');
    }

    function report_delete()
    {
		$success = false;
        if (ee()->input->get_post('report_id')!='')
        {
            ee()->db->where('report_id', ee()->input->get_post('report_id'));
            ee()->db->delete('reports');
            
            $success = ee()->db->affected_rows();
        }
        
        if ($success != false)
        {
            ee()->session->set_flashdata('message_success', 'success'); 
        }
        else
        {
            ee()->session->set_flashdata('message_failure', 'error');  
        }

        ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=reports'.AMP.'method=index');
        
    }

    function report_run()
    {
    	// Get the report data
		$report = ee()->db->query("SELECT * FROM exp_reports WHERE report_id=".ee()->input->get('report_id')." LIMIT 1");
		$report = $report->row_array();

		if (empty($report['query'])) {

            show_error(lang('error_no_query'));

        }
		// added start date and end date for report request if they exist
        if (!empty($report['sdate']) AND !empty($report['edate']))
        {
	        // pass sdate and edate as variables
	        $sdate = $report['sdate'];
	        $edate = $report['edate'];
	        
	        // append AND statement to Query IF append is Yes
			if($report['append'] == "Yes")
			{
				//Need to find out how to map d.field_id_29 to be universal
				$report['query'] = $report['query']." AND (d.field_id_29 BETWEEN $sdate AND $edate)";
			}
	    }

		// Run the query
		$query = ee()->db->query($report['query']);

    	if ($query->num_rows() > 0) {
	    	// do any post processing which is required
	    	if (!empty($report['post_processing'])) {
				$report['data'] = eval($report['post_processing']);
                if (!$report['data']) {
                    show_error(lang('error_post_processing'));
                    exit;
                }
			} else {
                $report['data'] = $query->result_array();
            }

    		$this->export($report);

    	} else {

            show_error(lang('error_no_results'));

        }

    }

	function export($report)
	{

		$tab  = ($this->export_type == 'csv') ? ',' : "\t";
		$cr	  = "\n";
		$data = '';

		if (!isset($report['data'][0]) OR !is_array($report['data'][0])) {
            show_error(lang('error_no_table_column_headings'));
        }

        foreach($report['data'][0] as $key => $value)
		{
            $data .= $key.$tab;
		}

		$data = trim($data).$cr; // Remove end tab and add carriage

		foreach($report['data'] as $row)
		{
			$datum = '';

			foreach($row as $key => $value)
			{
				if (strpos($value, ",") !== FALSE) {
					$datum .= '"'.$value.'"'.$tab;
				} else {
					$datum .= '"'.$value.'"'.$tab;
				}
			}

			$data .= trim($datum).$cr;
		}
		
		//added time stamp to exported file name
		$now = date("m-d-Y-G-i-s");

		if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
   		{
        	header('Content-Type: application/octet-stream');
        	header('Content-Disposition: inline; filename="'.$report['file_name'].'-'.$now.'.'.$this->export_type.'"');
        	header('Expires: 0');
        	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        	header('Pragma: public');
    	} else {
        	header('Content-Type: application/octet-stream');
        	header('Content-Disposition: attachment; filename="'.$report['file_name'].'-'.$now.'.'.$this->export_type.'"');
        	header('Expires: 0');
        	header('Pragma: no-cache');
    	}

		echo $data;
		exit;
	}

}

/* End of file mcp.reports.php */
/* Location: ./system/expressionengine/third_party/reports/mcp.reports.php */