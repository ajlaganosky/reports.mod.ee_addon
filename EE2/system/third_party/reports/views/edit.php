<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=reports'.AMP.'method=report_save');?>

<?php
	ee()->table->set_heading('Fields', 'Content');

	ee()->table->set_template($cp_pad_table_template);
	foreach ($reports as $key => $val)
	{
		ee()->table->add_row(
			array('data' => lang($key, $key), 'style' => 'width:50%;'), $val
		);
	}
	echo ee()->table->generate();
	ee()->table->clear();
?>

<p><?=form_submit('submit', 'Save', 'class="submit"')?></p>

<?php
form_close();