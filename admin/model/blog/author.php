<?php
class ModelBlogAuthor extends Model {
	public function addAuthor($data) {
		$this->event->trigger('pre.admin.author.add', $data);

		$this->db->query("INSERT INTO " . DB_PREFIX . "author SET name = '" . $this->db->escape($data['name']) . "', image = '" . $this->db->escape($data['image']) . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_added = NOW(), date_modified = NOW() ");
		
		$author_id = $this->db->getLastId();

		foreach ($data['author_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "author_description SET author_id = '" . (int)$author_id . "', language_id = '" . (int)$language_id . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
		
		if (isset($data['author_keyword'])) {
			foreach ($data['author_keyword'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'author_id=" . (int)$author_id . "', language_id = '" . (int)$language_id . "', keyword = '" . $this->db->escape($value['keyword']) . "'");
			}
		}

		$this->cache->delete('author');

		$this->event->trigger('post.admin.author.add', $author_id);

		return $author_id;
	}

	public function editAuthor($author_id, $data) {

		$this->event->trigger('pre.admin.author.edit', $data);

		$this->db->query("UPDATE " . DB_PREFIX . "author SET name = '" . $this->db->escape($data['name']) . "', image = '" . $this->db->escape($data['image']) . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "' , date_modified = NOW() WHERE author_id = '" . (int)$author_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "author_description WHERE author_id = '" . (int)$author_id . "'");

		foreach ($data['author_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "author_description SET author_id = '" . (int)$author_id . "', language_id = '" . (int)$language_id . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'author_id=" . (int)$author_id . "'");

		if (isset($data['author_keyword'])) {
			foreach ($data['author_keyword'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'author_id=" . (int)$author_id . "', language_id = '" . (int)$language_id . "', keyword = '" . $this->db->escape($value['keyword']) . "'");
			}
		}

		$this->cache->delete('author');

		$this->event->trigger('post.admin.author.edit', $author_id);
	}

	public function deleteAuthor($author_id) {
		$this->event->trigger('pre.admin.author.delete', $author_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "author WHERE author_id = '" . (int)$author_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "author_description WHERE author_id = '" . (int)$author_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'author_id=" . (int)$author_id . "'");

		$this->cache->delete('author');

		$this->event->trigger('post.admin.author.delete', $author_id);
	}
	


	public function getAuthor($author_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "author WHERE author_id = '" . (int)$author_id . "'");

		return $query->row;
	}

	public function getAuthors($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "author";
			

			if (!empty($data['filter_name'])) {
			$sql .= " WHERE `name` LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}
			

			$sort_data = array(
				'name',
				'sort_order',
				'date_added'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY name";
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
		} else {
			$author_data = $this->cache->get('author.');

			if (!$author_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "author ORDER BY name");

				$author_data = $query->rows;

				$this->cache->set('author.', $author_data);
			}

			return $author_data;
		}
	}

	public function getAuthorDescriptions($author_id) {
		$author_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "author_description WHERE author_id = '" . (int)$author_id . "'");

		foreach ($query->rows as $result) {
			$author_description_data[$result['language_id']] = array(
				'description'      => $result['description'],
				'meta_description' => $result['meta_description'],
				'meta_title' 	   => $result['meta_title'],
				'meta_keyword'     => $result['meta_keyword']
			);
		}

		return $author_description_data;
	}
	
	public function getAuthorKeyword($author_id) {
		$author_keyword_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE query = 'author_id=" . (int)$author_id . "'");

		foreach ($query->rows as $result) {
			$author_keyword_data[$result['language_id']] = array(				
				'keyword'      	   => $result['keyword']				
			);
		}

		return $author_keyword_data;
	}

	public function getTotalAuthors() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "author");

		return $query->row['total'];
	}


}