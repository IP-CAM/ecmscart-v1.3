<?php
/**
 * Modifcation XML Documentation can be found here:
 *
 * https://github.com/opencart/opencart/wiki/Modification-System
 */
class ControllerExtensionModification extends Controller {
	private $error = array();
	// sorting and filter array orderList of openbay
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('extension/modification');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('extension/modification');

		$this->getList();
	}

	public function delete() {
		$this->data = $this->load->language('extension/modification');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('extension/modification');

		if (isset($this->request->post['selected']) && $this->validate()) {
			foreach ($this->request->post['selected'] as $modification_id) {
				$this->model_extension_modification->deleteModification($modification_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			// filter and sorting function for modification
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('extension/modification', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function refresh() {
		$this->data = $this->load->language('extension/modification');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('extension/modification');

		if ($this->validate()) {
			//Log
			$log = array();

			// Clear all modification files
			$files = array();
			
			// Make path into an array
			$path = array(DIR_MODIFICATION . '*');

			// While the path array is still populated keep looping through
			while (count($path) != 0) {
				$next = array_shift($path);

				foreach (glob($next) as $file) {
					// If directory add to path array
					if (is_dir($file)) {
						$path[] = $file . '/*';
					}

					// Add the file to the files to be deleted array
					$files[] = $file;
				}
			}
			
			// Reverse sort the file array
			rsort($files);
			
			// Clear all modification files
			foreach ($files as $file) {
				if ($file != DIR_MODIFICATION . 'index.html') {
					// If file just delete
					if (is_file($file)) {
						unlink($file);
	
					// If directory use the remove directory function
					} elseif (is_dir($file)) {
						rmdir($file);
					}
				}
			}	
			
			// Begin
			$xml = array();

			// Load the default modification XML
			$xml[] = file_get_contents(DIR_SYSTEM . 'modification.xml');
			
			// This is purly for developers so they can run mods directly and have them run without upload sfter each change.
			$files = glob(DIR_SYSTEM . '*.ocmod.xml');

			if ($files) {
				foreach ($files as $file) {
					$xml[] = file_get_contents($file);
				}
			}
			
			// Get the default modification file
			$results = $this->model_extension_modification->getModifications();

			foreach ($results as $result) {
				if ($result['status']) {
					$xml[] = $result['xml'];
				}
			}

			$modification = array();

			foreach ($xml as $xml) {
				$dom = new DOMDocument('1.0', 'UTF-8');
				$dom->preserveWhiteSpace = false;
				$dom->loadXml($xml);

				// Wipe the past modification store in the backup array
				$recovery = array();
				
				// Set the a recovery of the modification code in case we need to use it if an abort attribute is used.
				if (isset($modification)) {
					$recovery = $modification;
				}
								
				// Log
				$log[] = 'MOD: ' . $dom->getElementsByTagName('name')->item(0)->textContent;

				$files = $dom->getElementsByTagName('modification')->item(0)->getElementsByTagName('file');

				foreach ($files as $file) {
					$operations = $file->getElementsByTagName('operation');

					$path = '';

					// Get the full path of the files that are going to be used for modification
					if (substr($file->getAttribute('path'), 0, 7) == 'catalog') {
						$path = DIR_CATALOG . str_replace('../', '', substr($file->getAttribute('path'), 8));
					}

					if (substr($file->getAttribute('path'), 0, 5) == 'admin') {
						$path = DIR_APPLICATION . str_replace('../', '', substr($file->getAttribute('path'), 6));
					}

					if (substr($file->getAttribute('path'), 0, 6) == 'system') {
						$path = DIR_SYSTEM . str_replace('../', '', substr($file->getAttribute('path'), 7));
					}

					if ($path) {
						$files = glob($path, GLOB_BRACE);

						if ($files) {
							foreach ($files as $file) {
								// Get the key to be used for the modification cache filename.
								if (substr($file, 0, strlen(DIR_CATALOG)) == DIR_CATALOG) {
									$key = 'catalog/' . substr($file, strlen(DIR_CATALOG));
								}

								if (substr($file, 0, strlen(DIR_APPLICATION)) == DIR_APPLICATION) {
									$key = 'admin/' . substr($file, strlen(DIR_APPLICATION));
								}

								if (substr($file, 0, strlen(DIR_SYSTEM)) == DIR_SYSTEM) {
									$key = 'system/' . substr($file, strlen(DIR_SYSTEM));
								}
								
								// If file contents is not already in the modification array we need to load it.
								if (!isset($modification[$key])) {
									$content = file_get_contents($file);

									$modification[$key] = preg_replace('~\r?\n~', "\n", $content);
									$original[$key] = preg_replace('~\r?\n~', "\n", $content);

									// Log
									$log[] = 'FILE: ' . $key;
								}
								
								foreach ($operations as $operation) {
									$error = $operation->getAttribute('error');
									
									// Ignoreif
									$ignoreif = $operation->getElementsByTagName('ignoreif')->item(0);
									
									if ($ignoreif) {
										if ($ignoreif->getAttribute('regex') != 'true') {
											if (strpos($modification[$key], $ignoreif->textContent) !== false) {
												continue;
											}												
										} else {
											if (preg_match($ignoreif->textContent, $modification[$key])) {
												continue;
											}
										}
									}
									
									$status = false;
									
									// Search and replace
									if ($operation->getElementsByTagName('search')->item(0)->getAttribute('regex') != 'true') {
										// Search
										$search = $operation->getElementsByTagName('search')->item(0)->textContent;
										$trim = $operation->getElementsByTagName('search')->item(0)->getAttribute('trim');
										$index = $operation->getElementsByTagName('search')->item(0)->getAttribute('index');
										
										// Trim line if no trim attribute is set or is set to true.
										if (!$trim || $trim == 'true') {
											$search = trim($search);
										}
																				
										// Add
										$add = $operation->getElementsByTagName('add')->item(0)->textContent;
										$trim = $operation->getElementsByTagName('add')->item(0)->getAttribute('trim');
										$position = $operation->getElementsByTagName('add')->item(0)->getAttribute('position');
										$offset = $operation->getElementsByTagName('add')->item(0)->getAttribute('offset');										
										
										if ($offset == '') {
                                            $offset = 0;
                                        }

										// Trim line if is set to true.
										if ($trim == 'true') {
											$add = trim($add);
										}
										
										// Log
										$log[] = 'CODE: ' . $search;
										
										// Check if using indexes
										if ($index !== '') {
											$indexes = explode(',', $index);
										} else {
											$indexes = array();
										}
										
										// Get all the matches
										$i = 0;
										
										$lines = explode("\n", $modification[$key]);

										for ($line_id = 0; $line_id < count($lines); $line_id++) {
											$line = $lines[$line_id];
											
											// Status
											$match = false;
											
											// Check to see if the line matches the search code.
											if (stripos($line, $search) !== false) {
												// If indexes are not used then just set the found status to true.
												if (!$indexes) {
													$match = true;
												} elseif (in_array($i, $indexes)) {
													$match = true;
												}
												
												$i++;
											}
											
											// Now for replacing or adding to the matched elements
											if ($match) {
												switch ($position) {
													default:
													case 'replace':
														if ($offset < 0) {
															array_splice($lines, $line_id + $offset, abs($offset) + 1, array(str_replace($search, $add, $line)));
															
															$line_id -= $offset;
														} else {
															array_splice($lines, $line_id, $offset + 1, array(str_replace($search, $add, $line)));
														}
														break;
													case 'before':
														$new_lines = explode("\n", $add);
														
														array_splice($lines, $line_id - $offset, 0, $new_lines);
														
														$line_id += count($new_lines);
														break;
													case 'after':
														array_splice($lines, ($line_id + 1) + $offset, 0, explode("\n", $add));
														break;
												}
												
												// Log
												$log[] = 'LINE: ' . $line_id;
												
												$status = true;										
											}
										}
										
										$modification[$key] = implode("\n", $lines);
									} else {									
										$search = $operation->getElementsByTagName('search')->item(0)->textContent;
										$limit = $operation->getElementsByTagName('search')->item(0)->getAttribute('limit');
										$replace = $operation->getElementsByTagName('add')->item(0)->textContent;
										
										// Limit
										if (!$limit) {
											$limit = -1;
										}

										// Log
										$match = array();

										preg_match_all($search, $modification[$key], $match, PREG_OFFSET_CAPTURE);

										// Remove part of the the result if a limit is set.
										if ($limit > 0) {
											$match[0] = array_slice($match[0], 0, $limit);
										}

										if ($match[0]) {
											$log[] = 'REGEX: ' . $search;

											for ($i = 0; $i < count($match[0]); $i++) {
												$log[] = 'LINE: ' . (substr_count(substr($modification[$key], 0, $match[0][$i][1]), "\n") + 1);
											}
											
											$status = true;
										}

										// Make the modification
										$modification[$key] = preg_replace($search, $replace, $modification[$key], $limit);
									}
									
									if (!$status) {
										// Log
										$log[] = 'NOT FOUND!';

										// Skip current operation
										if ($error == 'skip') {
											break;
										}
										
										// Abort applying this modification completely.
										if ($error == 'abort') {
											$modification = $recovery;
											
											// Log
											$log[] = 'ABORTING!';
										
											break 4;
										}
									}									
								}
							}
						}
					}
				}
				
				// Log
				$log[] = '----------------------------------------------------------------';				
			}

			// Log
			$ocmod = new Log('ocmod.log');
			$ocmod->write(implode("\n", $log));

			// Write all modification files
			foreach ($modification as $key => $value) {
				// Only create a file if there are changes
				if ($original[$key] != $value) {
					$path = '';

					$directories = explode('/', dirname($key));

					foreach ($directories as $directory) {
						$path = $path . '/' . $directory;

						if (!is_dir(DIR_MODIFICATION . $path)) {
							@mkdir(DIR_MODIFICATION . $path, 0777);
						}
					}

					$handle = fopen(DIR_MODIFICATION . $key, 'w');

					fwrite($handle, $value);

					fclose($handle);
				}
			}

			$this->session->data['success'] = $this->data['text_success'];

			// filter and sorting function for modification
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('extension/modification', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function clear() {
		$this->data = $this->load->language('extension/modification');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('extension/modification');

		if ($this->validate()) {
			$files = array();
			
			// Make path into an array
			$path = array(DIR_MODIFICATION . '*');

			// While the path array is still populated keep looping through
			while (count($path) != 0) {
				$next = array_shift($path);

				foreach (glob($next) as $file) {
					// If directory add to path array
					if (is_dir($file)) {
						$path[] = $file . '/*';
					}

					// Add the file to the files to be deleted array
					$files[] = $file;
				}
			}
			
			// Reverse sort the file array
			rsort($files);
			
			// Clear all modification files
			foreach ($files as $file) {
				if ($file != DIR_MODIFICATION . 'index.html') {
					// If file just delete
					if (is_file($file)) {
						unlink($file);
	
					// If directory use the remove directory function
					} elseif (is_dir($file)) {
						rmdir($file);
					}
				}
			}					

			$this->session->data['success'] = $this->data['text_success'];

			// filter and sorting function for modification
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('extension/modification', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function enable() {
		$this->data = $this->load->language('extension/modification');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('extension/modification');

		if (isset($this->request->get['modification_id']) && $this->validate()) {
			$this->model_extension_modification->enableModification($this->request->get['modification_id']);

			$this->session->data['success'] = $this->data['text_success'];

			// filter and sorting function for modification
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('extension/modification', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function disable() {
		$this->data = $this->load->language('extension/modification');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('extension/modification');

		if (isset($this->request->get['modification_id']) && $this->validate()) {
			$this->model_extension_modification->disableModification($this->request->get['modification_id']);

			$this->session->data['success'] = $this->data['text_success'];

			// filter and sorting function for modification
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('extension/modification', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function clearlog() {
		$this->data = $this->load->language('extension/modification');

		if ($this->validate()) {
			$handle = fopen(DIR_LOGS . 'ocmod.log', 'w+');

			fclose($handle);

			$this->session->data['success'] = $this->data['text_success'];

			// filter and sorting function for modification
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('extension/modification', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort','name',false);
		
		$order = $this->request->get('order','DESC',false);
		
		$page = $this->request->get('page',1,false);

		// filter and sorting function for modification
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('extension/modification', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							
						));
		
		$this->data['refresh'] = $this->url->link('extension/modification/refresh', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['clear'] = $this->url->link('extension/modification/clear', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('extension/modification/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['modifications'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$modification_total = $this->model_extension_modification->getTotalModifications();

		$results = $this->model_extension_modification->getModifications($filter_data);

		foreach ($results as $result) {
			$this->data['modifications'][] = array(
				'modification_id' => $result['modification_id'],
				'name'            => $result['name'],
				'author'          => $result['author'],
				'version'         => $result['version'],
				'status'          => $result['status'] ? $this->data['text_enabled'] : $this->data['text_disabled'],
				'date_added'      => date($this->data['date_format_short'], strtotime($result['date_added'])),
				'link'            => $result['link'],
				'enable'          => $this->url->link('extension/modification/enable', 'token=' . $this->session->data['token'] . '&modification_id=' . $result['modification_id'], 'SSL'),
				'disable'         => $this->url->link('extension/modification/disable', 'token=' . $this->session->data['token'] . '&modification_id=' . $result['modification_id'], 'SSL'),
				'enabled'         => $result['status'],
			);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->data['error_warning'] = (isset($this->error['warning'])) ?  $this->error['warning']: '';

		$this->data['success'] = (isset($this->session->data['success']))?  $this->session->data['success']: '';
	
		if (isset($this->session->data['success'])) 
			unset($this->session->data['success']);

		$this->data['selected'] = (isset($this->request->post['selected'])) ? (array)$this->request->post['selected']: array();

		$url = ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // important part for order in url to set ASC or DESC
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_name'] = $this->url->link('extension/modification', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_author'] = $this->url->link('extension/modification', 'token=' . $this->session->data['token'] . '&sort=author' . $url, 'SSL');
		$this->data['sort_version'] = $this->url->link('extension/version', 'token=' . $this->session->data['token'] . '&sort=author' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('extension/modification', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('extension/modification', 'token=' . $this->session->data['token'] . '&sort=date_added' . $url, 'SSL');

		// sorting and filter array orderList of openbay
		$url_data = array(
				'sort',
				'order',
			);	
		// filter and sorting function for openbay orderList pagination
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $modification_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/modification', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($modification_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($modification_total - $this->config->get('config_limit_admin'))) ? $modification_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $modification_total, ceil($modification_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		// Log
		$file = DIR_LOGS . 'ocmod.log';

		if (file_exists($file)) {
			$this->data['log'] = htmlentities(file_get_contents($file, FILE_USE_INCLUDE_PATH, null));
		} else {
			$this->data['log'] = '';
		}

		$this->data['clear_log'] = $this->url->link('extension/modification/clearlog', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/modification.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/modification')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}
