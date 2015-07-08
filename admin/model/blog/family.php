<?php
class ModelBlogFamily extends Model {
	public function addFamily($data) {
		$this->event->trigger('pre.admin.family.add', $data);

		$this->db->query("INSERT INTO " . DB_PREFIX . "family SET parent_id = '" . (int)$data['parent_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(), date_added = NOW()");

		$family_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "family SET image = '" . $this->db->escape($data['image']) . "' WHERE family_id = '" . (int)$family_id . "'");
		}

		foreach ($data['family_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "family_description SET family_id = '" . (int)$family_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "family_path` WHERE family_id = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "family_path` SET `family_id` = '" . (int)$family_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

			$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "family_path` SET `family_id` = '" . (int)$family_id . "', `path_id` = '" . (int)$family_id . "', `level` = '" . (int)$level . "'");



		if (isset($data['family_store'])) {
			foreach ($data['family_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "family_to_store SET family_id = '" . (int)$family_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		// Set which layout to use with this family
		if (isset($data['family_layout'])) {
			foreach ($data['family_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "family_to_layout SET family_id = '" . (int)$family_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		if (isset($data['family_keyword'])) {
			foreach ($data['family_keyword'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'family_id=" . (int)$family_id . "', language_id = '" . (int)$language_id . "', keyword = '" . $this->db->escape($value['keyword']) . "'");
			}
		}

		$this->cache->delete('family');

		$this->event->trigger('post.admin.family.add', $family_id);

		return $family_id;
	}

	public function editFamily($family_id, $data) {
		$this->event->trigger('pre.admin.family.edit', $data);

		$this->db->query("UPDATE " . DB_PREFIX . "family SET parent_id = '" . (int)$data['parent_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE family_id = '" . (int)$family_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "family SET image = '" . $this->db->escape($data['image']) . "' WHERE family_id = '" . (int)$family_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "family_description WHERE family_id = '" . (int)$family_id . "'");

		foreach ($data['family_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "family_description SET family_id = '" . (int)$family_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "family_path` WHERE path_id = '" . (int)$family_id . "' ORDER BY level ASC");

		if ($query->rows) {
			foreach ($query->rows as $family_path) {
				// Delete the path below the current one
				$this->db->query("DELETE FROM `" . DB_PREFIX . "family_path` WHERE family_id = '" . (int)$family_path['family_id'] . "' AND level < '" . (int)$family_path['level'] . "'");

				$path = array();

				// Get the nodes new parents
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "family_path` WHERE family_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Get whats left of the nodes current path
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "family_path` WHERE family_id = '" . (int)$family_path['family_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Combine the paths with a new level
				$level = 0;

				foreach ($path as $path_id) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "family_path` SET family_id = '" . (int)$family_path['family_id'] . "', `path_id` = '" . (int)$path_id . "', level = '" . (int)$level . "'");

					$level++;
				}
			}
		} else {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "family_path` WHERE family_id = '" . (int)$family_id . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "family_path` WHERE family_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "family_path` SET family_id = '" . (int)$family_id . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "family_path` SET family_id = '" . (int)$family_id . "', `path_id` = '" . (int)$family_id . "', level = '" . (int)$level . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "family_to_store WHERE family_id = '" . (int)$family_id . "'");

		if (isset($data['family_store'])) {
			foreach ($data['family_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "family_to_store SET family_id = '" . (int)$family_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "family_to_layout WHERE family_id = '" . (int)$family_id . "'");

		if (isset($data['family_layout'])) {
			foreach ($data['family_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "family_to_layout SET family_id = '" . (int)$family_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'family_id=" . (int)$family_id . "'");
		
		if (isset($data['family_keyword'])) {
			foreach ($data['family_keyword'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'family_id=" . (int)$family_id . "', language_id = '" . (int)$language_id . "', keyword = '" . $this->db->escape($value['keyword']) . "'");
			}
		}

		$this->cache->delete('family');

		$this->event->trigger('post.admin.family.edit', $family_id);
	}

	public function deleteFamily($family_id) {
		$this->event->trigger('pre.admin.family.delete', $family_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "family_path WHERE family_id = '" . (int)$family_id . "'");

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "family_path WHERE path_id = '" . (int)$family_id . "'");

		foreach ($query->rows as $result) {
			$this->deletefamily($result['family_id']);
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "family WHERE family_id = '" . (int)$family_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "family_description WHERE family_id = '" . (int)$family_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "family_to_store WHERE family_id = '" . (int)$family_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "family_to_layout WHERE family_id = '" . (int)$family_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_family WHERE family_id = '" . (int)$family_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'family_id=" . (int)$family_id . "'");

		$this->cache->delete('family');

		$this->event->trigger('post.admin.family.delete', $family_id);
	}

	public function repairFamilies($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "family WHERE parent_id = '" . (int)$parent_id . "'");

		foreach ($query->rows as $family) {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "family_path` WHERE family_id = '" . (int)$family['family_id'] . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "family_path` WHERE family_id = '" . (int)$parent_id . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "family_path` SET family_id = '" . (int)$family['family_id'] . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "family_path` SET family_id = '" . (int)$family['family_id'] . "', `path_id` = '" . (int)$family['family_id'] . "', level = '" . (int)$level . "'");

			$this->repairfamilies($family['family_id']);
		}
	}

	public function getFamily($family_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(fd1.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') FROM " . DB_PREFIX . "family_path fp LEFT JOIN " . DB_PREFIX . "family_description fd1 ON (fp.path_id = fd1.family_id AND fp.family_id != fp.path_id) WHERE fp.family_id = f.family_id AND fd1.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY fp.family_id) AS path FROM " . DB_PREFIX . "family f LEFT JOIN " . DB_PREFIX . "family_description fd2 ON (f.family_id = fd2.family_id) WHERE f.family_id = '" . (int)$family_id . "' AND fd2.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getFamilies($data = array()) {
		$sql = "SELECT fp.family_id AS family_id, GROUP_CONCAT(fd1.name ORDER BY fp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, f1.parent_id, f1.sort_order FROM " . DB_PREFIX . "family_path fp LEFT JOIN " . DB_PREFIX . "family f1 ON (fp.family_id = f1.family_id) LEFT JOIN " . DB_PREFIX . "family f2 ON (fp.path_id = f2.family_id) LEFT JOIN " . DB_PREFIX . "family_description fd1 ON (fp.path_id = fd1.family_id) LEFT JOIN " . DB_PREFIX . "family_description fd2 ON (fp.family_id = fd2.family_id) WHERE fd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND fd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND fd2.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY fp.family_id";

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getFamilyDescriptions($family_id) {
		$family_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "family_description WHERE family_id = '" . (int)$family_id . "'");

		foreach ($query->rows as $result) {
			$family_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description'      => $result['description']
			);
		}

		return $family_description_data;
	}
	
	public function getFamilyKeyword($family_id) {
		$family_keyword_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE query = 'family_id=" . (int)$family_id . "'");

		foreach ($query->rows as $result) {
			$family_keyword_data[$result['language_id']] = array(				
				'keyword'      	   => $result['keyword']				
			);
		}

		return $family_keyword_data;
	}

	public function getFamilyName($family_id) {
		
		$query = $this->db->query("SELECT family_id, name FROM " . DB_PREFIX . "family_description WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' AND family_id = '" . (int)$family_id . "'");

		return $query->row;
	}


	public function getFamilyStores($family_id) {
		$family_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "family_to_store WHERE family_id = '" . (int)$family_id . "'");

		foreach ($query->rows as $result) {
			$family_store_data[] = $result['store_id'];
		}

		return $family_store_data;
	}

	public function getFamilyLayouts($family_id) {
		$family_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "family_to_layout WHERE family_id = '" . (int)$family_id . "'");

		foreach ($query->rows as $result) {
			$family_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $family_layout_data;
	}

	public function getTotalFamilies() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "family");

		return $query->row['total'];
	}
	
	public function getTotalFamiliesByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "family_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}	
}
