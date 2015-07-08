<?php
class ModelBlogBlog extends Model {

	public function getBlogs($data = array()) {
		$sql = "SELECT b.blog_id FROM " . DB_PREFIX . "blog b ";	

		$sql .= " LEFT JOIN " . DB_PREFIX . "blog_description bd ON (b.blog_id = bd.blog_id) LEFT JOIN " . DB_PREFIX . "blog_to_family b2f ON (b.blog_id = b2f.blog_id) LEFT JOIN " . DB_PREFIX . "author a ON (b.author_id = a.author_id) LEFT JOIN " . DB_PREFIX . "blog_to_store b2s ON (b.blog_id = b2s.blog_id) WHERE bd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND b.status = '1' AND b2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_family_id'])) {
			
			$sql .= " AND b2f.family_id = '" . (int)$data['filter_family_id'] . "'";
			
			}

		if (!empty($data['filter_title']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_title'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_title'])));

				foreach ($words as $word) {
					$implode[] = "bd.title LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}
				
			}

			if (!empty($data['filter_title']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$sql .= "bd.tag LIKE '%" . $this->db->escape($data['filter_tag']) . "%'";
			}



			$sql .= ")";
		}

		if (!empty($data['filter_author_id'])) {
			$sql .= " AND b.author_id = '" . (int)$data['filter_author_id'] . "'";
		}

		$sql .= " GROUP BY b.blog_id";

		$sort_data = array(
			'bd.title',			
			'b.sort_order',
			'b.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			
				$sql .= " ORDER BY " . $data['sort'];
			
		} else {
			$sql .= " ORDER BY b.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(bd.title) DESC";
		} else {
			$sql .= " ASC, LCASE(bd.title) ASC";
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

		$blog_data = array();

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$blog_data[$result['blog_id']] = $this->getBlog($result['blog_id']);
		}

		return $blog_data;
	}
	
	public function getTotalBlogs($data = array()) {
	
		$sql = "SELECT COUNT(DISTINCT b.blog_id) AS total FROM " . DB_PREFIX . "blog b ";				

		$sql .= " LEFT JOIN " . DB_PREFIX . "blog_description bd ON (b.blog_id = bd.blog_id) LEFT JOIN " . DB_PREFIX . "blog_to_family b2f ON (b.blog_id = b2f.blog_id) LEFT JOIN " . DB_PREFIX . "author a ON (b.author_id = a.author_id) LEFT JOIN " . DB_PREFIX . "blog_to_store b2s ON (b.blog_id = b2s.blog_id) WHERE bd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND b.status = '1' AND b2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_family_id'])) {
			
			$sql .= " AND b2f.family_id = '" . (int)$data['filter_family_id'] . "'";
			
			}

		if (!empty($data['filter_title']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_title'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_title'])));

				foreach ($words as $word) {
					$implode[] = "bd.title LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}
				
			}

			if (!empty($data['filter_title']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$sql .= "bd.tag LIKE '%" . $this->db->escape($data['filter_tag']) . "%'";
			}



			$sql .= ")";
		}

		if (!empty($data['filter_author_id'])) {
			$sql .= " AND b.author_id = '" . (int)$data['filter_author_id'] . "'";
		}
	

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getBlog($blog_id) {
		$query = $this->db->query("SELECT *, b.image FROM " . DB_PREFIX . "blog b LEFT JOIN " . DB_PREFIX . "author auth ON (b.author_id = auth.author_id) LEFT JOIN " . DB_PREFIX . "blog_to_family bf ON (b.blog_id = bf.family_id) LEFT JOIN " .DB_PREFIX . "blog_description bd ON (b.blog_id = bd.blog_id) LEFT JOIN " .DB_PREFIX . "blog_to_store b2s ON (b.blog_id = b2s.blog_id)  WHERE b.blog_id = '" . (int)$blog_id . "' AND bd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND b2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND b.status = '1'");

		return $query->row;
	}
}
