<?= form_open($submit_url); ?>

<?php
	$results = FALSE;
	foreach (array('site_prefs', 'channel_prefs', 'upload_prefs') as $pref_type)
	{
		if (!empty($$pref_type))
		{
			$results = TRUE;
			$this->table->clear();
			$this->table->set_template($cp_table_template);
			$this->table->set_heading(
				array('data' => lang('reelocate_'.$pref_type), 'width' => '15%'),
				array('data' => lang('reelocate_current_value'), 'width' => '40%'),
				array('data' => lang('reelocate_new_value'), 'width' => '40%'),
				array('data' => lang('reelocate_update'), 'width' => '5%')
			);
	
			foreach ($$pref_type as $key => $value)
			{
				$this->table->add_row(
					$value['title'],
					$value['current_html'],
					form_input($key, $value['updated']),
					form_checkbox($key.'_update', 'y', TRUE));
			}
	
			echo $this->table->generate();
		}
	}
?>

<?php if ($results): ?>
	<?= form_submit(array('name' => 'submit', 'value' => lang('reelocate_submit_changes'), 'class' => 'submit')); ?>
<?php else: ?>
	<p><?= lang('reelocate_no_results') ?></p>
<?php endif ?>

<?= form_close(); ?>
