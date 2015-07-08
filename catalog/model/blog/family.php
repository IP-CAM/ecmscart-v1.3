<?php
class ModelBlogFamily extends Model {
	public function getFamily($family_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "family f LEFT JOIN " . DB_PREFIX . "family_description fd ON (f.family_id = fd.family_id) LEFT JOIN " .DB_PREFIX . "family_to_store f2s ON (f.family_id = f2s.family_id) WHERE f.family_id = '" . (int)$family_id . "' AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND f2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND f.status = '1'");

		return $query->row;
	}

	public function getFamilies($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "family f LEFT JOIN " . DB_PREFIX . "family_description fd ON (f.family_id = fd.family_id) LEFT JOIN " . DB_PREFIX . "family_to_store f2s ON (f.family_id = f2s.family_id) WHERE f.parent_id = '" . (int)$parent_id . "' AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND f2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND f.status = '1' ORDER BY f.sort_order, LCASE(fd.name)");

		return $query->rows;
	}
	
	public function getfamilyLayoutId($family_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "familey_blog_to_layout WHERE family_id = '" . (int)$family_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return 0;
		}
	}

	public function getTotalFamiliesByFamilyId($parent_id = 0) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "family f LEFT JOIN " . DB_PREFIX . "family_to_store f2s ON (f.family_id = f2s.family_id) WHERE f.parent_id = '" . (int)$parent_id . "' AND f2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND f.status = '1'");

		return $query->row['total'];
	}

	
	
}