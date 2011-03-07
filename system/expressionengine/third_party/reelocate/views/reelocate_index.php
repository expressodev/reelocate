<?= form_open($submit_url); ?>

<p><?= lang('reelocate_index_text') ?><br /><br /></p>

<?php
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(
		lang('reelocate_ee_setting'),
		lang('reelocate_search_value'),
		lang('reelocate_replacement_value')
	);
	
	$this->table->add_row(
		lang('url'),
		form_input('current_site_url', $current_site_url),
		form_input('site_url', $site_url));
	
	$this->table->add_row(
		lang('server_path'),
		form_input('current_site_path', $current_site_path),
		form_input('site_path', $site_path));
	
	echo $this->table->generate();
?>

<?= form_submit(array('name' => 'submit', 'value' => lang('reelocate_preview_changes'), 'class' => 'submit')); ?>

<?= form_close(); ?>