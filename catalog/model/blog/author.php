<?php
class ModelBlogAuthor extends Model {
	public function getAuthor($author_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "author a LEFT JOIN " . DB_PREFIX . "author_description ad ON (a.author_id = ad.author_id) WHERE a.author_id = '" . (int)$author_id . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}
	
}