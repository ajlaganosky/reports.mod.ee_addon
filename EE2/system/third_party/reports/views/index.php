<?php if (!empty($reports)) { ?>

	<?php

	ee()->table->set_heading(lang('table_heading_id'),lang('table_heading_title'), lang('table_heading_description'), lang('table_heading_sdate'), lang('table_heading_edate'), lang('table_heading_append'), lang('table_heading_export'), lang('table_heading_edit'), lang('table_heading_delete'));

	$i = 0;
	foreach($reports AS $report) {

		if($i&1) {
			$class = "tableCellOne";
		} else {
			$class = "tableCellTwo";
		}
		// make the dates human if they are included
		if (!empty($report['sdate']) AND !empty($report['edate']))
		{
			$sdate = $report['sdate'];
			$edate = $report['edate'];

			$sdate = mdate("%m/%d/%Y", $sdate);
			$edate = mdate("%m/%d/%Y", $edate);
		}
		else
		{
			$sdate = "";
			$edate = "";
		}
		
		$cell['id'] = array('data' => $report['report_id'], 'style' => 'white-space: nowrap; width: 5%;');
        $cell['title'] = array('data' => $report['title'], 'style' => 'white-space: nowrap; width: 10%;');
		$cell['description'] = array('data' => $report['description'], 'style' => 'width: 30%;');
		// add start date and end date
		$cell['sdate'] = array('data' => $sdate, 'style' => 'width: 5%;');
		$cell['edate'] = array('data' => $edate, 'style' => 'width: 5%;');
		$cell['append'] = array('data' => $report['append'], 'style' => 'width: 15%;');
		$cell['export'] = array('data' => '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=reports&method=report_run&report_id='.$report['report_id'].'">Export to CSV</a>', 'style' => 'white-space: nowrap;');
		// add edit button
		$cell['edit'] = array('data' => '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=reports&method=report_edit&report_id='.$report['report_id'].'">Edit Report</a>', 'style' => 'white-space: nowrap;');
		$cell['delete'] = array('data' => '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=reports&method=report_delete&report_id='.$report['report_id'].'" class="report_delete_warning" title="delete">Delete Report</a>', 'style' => 'white-space: nowrap;');

        $this->table->add_row(
        	$cell['id'],
            $cell['title'],
        	$cell['description'],
        	$cell['sdate'],
        	$cell['edate'],
        	$cell['append'],
        	$cell['export'],
        	$cell['edit'],
        	$cell['delete']
        	);
    }

	echo $this->table->generate();

	?>

<?php } else { ?>

<p>Sorry, there are currently no reports specified.</p>

<?php } ?>