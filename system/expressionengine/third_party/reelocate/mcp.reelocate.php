<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Reelocate module by Crescendo (support@crescendo.net.nz)
 * 
 * Copyright (c) 2010 Crescendo Multimedia Ltd
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class Reelocate_mcp {
	
	public function __construct()
	{
		ee()->load->library('table');
		ee()->load->helper('form');
		ee()->load->helper('path');
		
		define('REELOCATE_CP', 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=reelocate');
	}
	
	public function index()
	{
		ee()->view->cp_page_title = lang('reelocate_module_name');
		
		$data = array(
			'submit_url' => REELOCATE_CP.AMP.'method=preview',
			'current_site_url' => rtrim(ee()->config->item('site_url'), '/\\'),
			'current_site_path' => rtrim(dirname(dirname(ee()->config->item('avatar_path'))), '/\\'),
			'site_path' => rtrim(set_realpath('..'), '/\\'),
			'cp_url' => ee()->config->item('site_url'),
		);
		
		$data['current_site_url'] = preg_replace('/http[s]?:\/\//i', '', $data['current_site_url']);
		$data['site_url'] = $data['current_site_url'];
		
		return ee()->load->view('index', $data, true);
	}
	
	public function preview()
	{
		ee()->cp->set_breadcrumb(BASE.AMP.REELOCATE_CP, lang('reelocate_module_name'));
		ee()->view->cp_page_title = lang('reelocate_preview_changes');
		ee()->lang->loadfile('admin');
		ee()->lang->loadfile('admin_content');
		ee()->lang->loadfile('design');
		ee()->lang->loadfile('tools');
		
		$data = array(
			'config' => array(),
			'submit_url' => REELOCATE_CP.AMP.'method=update',
			'current_site_url' => rtrim(ee()->input->post('current_site_url', true), '/\\'),
			'current_site_path' => rtrim(ee()->input->post('current_site_path', true), '/\\'),
			'site_url' => rtrim(ee()->input->post('site_url', true), '/\\'),
			'site_path' => rtrim(ee()->input->post('site_path', true), '/\\')
		);
		
		if (empty($data['current_site_url']) OR empty($data['current_site_path']) OR
			empty($data['site_url']) OR empty($data['site_path']))
		{
			ee()->session->set_flashdata('message_failure', lang('reelocate_no_postdata'));
			ee()->functions->redirect(BASE.AMP.REELOCATE_CP);
		}
		
		// search for offending settings
		$search = array($data['current_site_url'], $data['current_site_path']);
		$replace = array($data['site_url'], $data['site_path']);
		
		// remove identical replacements
		foreach ($search as $id => $search_str)
		{
			if ($search_str == $replace[$id])
			{
				unset($search[$id]);
				unset($replace[$id]);
			}
		}
		
		$data['site_prefs'] = $this->_find_site_prefs($search, $replace);
		$data['channel_prefs'] = $this->_find_channel_prefs($search, $replace);
		$data['upload_prefs'] = $this->_find_upload_prefs($search, $replace);
		
		return ee()->load->view('preview', $data, true);
	}
	
	private function _find_site_prefs($search, $replace)
	{
		if (!is_array($search) OR !is_array($replace)) return;
		if (count($search) != count($replace)) return;
		
		// search for standard site preferences
		$results = array();
		foreach (ee()->config->config as $key => $value)
		{
			if (is_string($value))
			{
				// loop through find/replace strings
				foreach ($search as $id => $search_str)
				{
					if (stripos($value, $search_str) !== false)
					{
						$results[$key]['title'] = lang($key);
						$results[$key]['current'] = $value;
						$results[$key]['current_html'] = preg_replace('/('.preg_quote($search_str, '/').')/i', '<strong>$1</strong>', $value);
						$results[$key]['updated'] = str_ireplace($search_str, $replace[$id], $value);
					}
				}
			}
		}
		
		ksort($results);
		return $results;
	}
	
	private function _find_channel_prefs($search, $replace)
	{
		if (!is_array($search) OR !is_array($replace)) return;
		if (count($search) != count($replace)) return;
		
		// search for upload preferences
		$results = array();
		$channel_prefs = ee()->db->where('site_id',ee()->config->item('site_id'))->get('channels')->result_array();
		// loop through channels
		foreach ($channel_prefs as $row)
		{
			// loop through find/replace strings
			foreach ($search as $id => $search_str)
			{
				// loop through channel preference names
				foreach (array('channel_url', 'channel_notify_emails', 'comment_url', 'search_results_url', 'rss_url') as $upload_pref_name)
				{
					if (stripos($row[$upload_pref_name], $search_str) !== false)
					{
						$key = 'channel_id_'.$row['channel_id'].'_'.$upload_pref_name;
						$results[$key]['title'] = $row['channel_title'].': '.lang($upload_pref_name);
						$results[$key]['current'] = $row[$upload_pref_name];
						$results[$key]['current_html'] = preg_replace('/('.preg_quote($search_str, '/').')/i', '<strong>$1</strong>', $row[$upload_pref_name]);
						$results[$key]['updated'] = str_ireplace($search_str, $replace[$id], $row[$upload_pref_name]);
					}
				}
			}
		}
		
		ksort($results);
		return $results;
	}
	
	private function _find_upload_prefs($search, $replace)
	{
		if (!is_array($search) OR !is_array($replace)) return;
		if (count($search) != count($replace)) return;
		
		// search for upload preferences
		$results = array();
		$upload_prefs = ee()->db->where('site_id',ee()->config->item('site_id'))->get('upload_prefs')->result_array();
		// loop through upload directories
		foreach ($upload_prefs as $row)
		{
			// loop through find/replace strings
			foreach ($search as $id => $search_str)
			{
				// loop through upload preference names
				foreach (array('server_path', 'url') as $upload_pref_name)
				{
					if (stripos($row[$upload_pref_name], $search_str) !== false)
					{
						$key = 'upload_dir_'.$row['id'].'_'.$upload_pref_name;
						$results[$key]['title'] = $row['name'].' '.lang($upload_pref_name);
						$results[$key]['current'] = $row[$upload_pref_name];
						$results[$key]['current_html'] = preg_replace('/('.preg_quote($search_str, '/').')/i', '<strong>$1</strong>', $row[$upload_pref_name]);
						$results[$key]['updated'] = str_ireplace($search_str, $replace[$id], $row[$upload_pref_name]);
					}
				}
			}
		}
		
		ksort($results);
		return $results;
	}
	
	public function update()
	{
		ee()->cp->set_breadcrumb(BASE.AMP.REELOCATE_CP, lang('reelocate_module_name'));
		ee()->view->cp_page_title = lang('reelocate_success');
		
		if (ee()->input->post('submit') === false)
		{
			ee()->session->set_flashdata('message_failure', lang('reelocate_no_postdata'));
			ee()->functions->redirect(BASE.AMP.REELOCATE_CP);
		}
		
		$site_prefs = array();
		$channel_prefs = array();
		$upload_prefs = array();
		
		// loop through the POST data
		foreach ($_POST as $key => $value)
		{
			if ($value === 'y' AND $pos = strpos($key, '_update'))
			{
				$setting_id = substr($key, 0, $pos);
				
				// find out what type of setting we are dealing with
				if (ee()->config->item($setting_id) !== false)
				{
					// standard site preference
					$site_prefs[$setting_id] = ee()->input->post($setting_id, true);
				}
				elseif (strpos($setting_id, 'channel_id_') !== false)
				{
					// channel preference
					preg_match('/channel_id_([\d]+)_([\w]+)/i', $setting_id, $matches);
					$channel_prefs[$matches[1]][$matches[2]] = ee()->input->post($setting_id, true);
				}
				elseif (strpos($setting_id, 'upload_dir_') !== false)
				{
					// upload dir preference
					preg_match('/upload_dir_([\d]+)_([\w]+)/i', $setting_id, $matches);
					$upload_prefs[$matches[1]][$matches[2]] = ee()->input->post($setting_id, true);
				}
			}
		}
		
		// update the site prefs
		ee()->config->update_site_prefs($site_prefs);
		$updated_count = count($site_prefs);
		
		// update the channel prefs
		foreach ($channel_prefs as $id => $data)
		{
			ee()->db->update('channels', $data, array('channel_id' => $id));
			$updated_count += count($data);
		}
		
		// update the upload prefs
		foreach ($upload_prefs as $id => $data)
		{
			ee()->db->update('upload_prefs', $data, array('id' => $id));
			$updated_count += count($data);
		}
		
		return sprintf(lang('reelocate_updated'), $updated_count);
	}
}

/* End of file ./system/expressionengine/third_party/reelocate/mcp.reelocate.php */
