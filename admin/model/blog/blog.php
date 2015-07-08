<?php
class ModelBlogBlog extends Model {
	public function addBlog($data) {
		$this->event->trigger('pre.admin.blog.add', $data);

		$this->db->query("INSERT INTO " . DB_PREFIX . "blog SET sort_order = '" . (int)$data['sort_order'] . "', image = '" . $this->db->escape($data['image']) . "', comments = '" . (isset($data['comments']) ? (int)$data['comments'] : 0) . "', author_id = '" . (int)$data['author_id'] . "', status = '" . (int)$data['status'] . "', date_added = NOW(), date_modified = NOW() ");
		

		$blog_id = $this->db->getLastId();

		foreach ($data['blog_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "blog_description SET blog_id = '" . (int)$blog_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['blog_store'])) {
			foreach ($data['blog_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_to_store SET blog_id = '" . (int)$blog_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
		
		if (isset($data['blog_family'])) {
			foreach ($data['blog_family'] as $family_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_to_family SET blog_id = '" . (int)$blog_id . "', family_id = '" . (int)$family_id . "'");
			}
		}

		if (isset($data['blog_layout'])) {
			foreach ($data['blog_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_to_layout SET blog_id = '" . (int)$blog_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}
		
		if (isset($data['blog_related'])) {
			foreach ($data['blog_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "blog_related WHERE blog_id = '" . (int)$blog_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_related SET blog_id = '" . (int)$blog_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "blog_related WHERE blog_id = '" . (int)$related_id . "' AND related_id = '" . (int)$blog_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_related SET blog_id = '" . (int)$related_id . "', related_id = '" . (int)$blog_id . "'");
			}
		}
		
		if (isset($data['blog_keyword'])) {
			foreach ($data['blog_keyword'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'blog_id=" . (int)$blog_id . "', language_id = '" . (int)$language_id . "', keyword = '" . $this->db->escape($value['keyword']) . "'");
			}
		}

		$this->cache->delete('blog');

		$this->event->trigger('post.admin.blog.add', $blog_id);

		return $blog_id;
	}

	public function editBlog($blog_id, $data) {
		$this->event->trigger('pre.admin.blog.edit', $data);
		
		$this->db->query("UPDATE " . DB_PREFIX . "blog SET sort_order = '" . (int)$data['sort_order'] . "', image = '" . $this->db->escape($data['image']) . "', comments = '" . (isset($data['comments']) ? (int)$data['comments'] : 0) . "', author_id = '" . (int)$data['author_id'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE blog_id = '" . (int)$blog_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_description WHERE blog_id = '" . (int)$blog_id . "'");

		foreach ($data['blog_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "blog_description SET blog_id = '" . (int)$blog_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_to_store WHERE blog_id = '" . (int)$blog_id . "'");

		if (isset($data['blog_store'])) {
			foreach ($data['blog_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_to_store SET blog_id = '" . (int)$blog_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_to_family WHERE blog_id = '" . (int)$blog_id . "'");
		
		if (isset($data['blog_family'])) {
			foreach ($data['blog_family'] as $family_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_to_family SET blog_id = '" . (int)$blog_id . "', family_id = '" . (int)$family_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_to_layout WHERE blog_id = '" . (int)$blog_id . "'");

		if (isset($data['blog_layout'])) {
			foreach ($data['blog_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_to_layout SET blog_id = '" . (int)$blog_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_related WHERE blog_id = '" . (int)$blog_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_related WHERE related_id = '" . (int)$blog_id . "'");

		if (isset($data['blog_related'])) {
			foreach ($data['blog_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "blog_related WHERE blog_id = '" . (int)$blog_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_related SET blog_id = '" . (int)$blog_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "blog_related WHERE blog_id = '" . (int)$related_id . "' AND related_id = '" . (int)$blog_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_related SET blog_id = '" . (int)$related_id . "', related_id = '" . (int)$blog_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'blog_id=" . (int)$blog_id . "'");

		if (isset($data['blog_keyword'])) {
			foreach ($data['blog_keyword'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'blog_id=" . (int)$blog_id . "', language_id = '" . (int)$language_id . "', keyword = '" . $this->db->escape($value['keyword']) . "'");
			}
		}

		$this->cache->delete('blog');

		$this->event->trigger('post.admin.blog.edit', $blog_id);
	}

	public function deleteBlog($blog_id) {
		$this->event->trigger('pre.admin.blog.delete', $blog_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "blog WHERE blog_id = '" . (int)$blog_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_description WHERE blog_id = '" . (int)$blog_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_to_store WHERE blog_id = '" . (int)$blog_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_to_family WHERE blog_id = '" . (int)$blog_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_to_layout WHERE blog_id = '" . (int)$blog_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_related WHERE blog_id = '" . (int)$blog_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_related WHERE related_id = '" . (int)$blog_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'blog_id=" . (int)$blog_id . "'");

		$this->cache->delete('blog');

		$this->event->trigger('post.admin.blog.delete', $blog_id);
	}
	
	public function getBlogRelated($blog_id) {
		$blog_related_data = array();

		$query = $this->db->query("SELECT related_id FROM " . DB_PREFIX . "blog_related WHERE blog_id = '" . (int)$blog_id . "'");

		foreach ($query->rows as $result) {
			$blog_related_data[] = $result['related_id'];
		}

		return $blog_related_data;
	}
	
	public function getBlogTitle($blog_id) {
		
		$query = $this->db->query("SELECT title FROM " . DB_PREFIX . "blog_description WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' AND blog_id = '" . (int)$blog_id . "'");

		return $query->row;
	}

	public function getBlog($blog_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "blog WHERE blog_id = '" . (int)$blog_id . "'");

		return $query->row;
	}

	public function getBlogs($data = array(), $this_blog_id = 0) {
		if ($data) {
			$sql = "SELECT *,(SELECT name FROM " . DB_PREFIX . "author auth WHERE auth.author_id = b.author_id ) AS author FROM " . DB_PREFIX . "blog b LEFT JOIN " . DB_PREFIX . "blog_description bd ON (b.blog_id = bd.blog_id) WHERE bd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			
			
	
			if (isset($this_blog_id)) {
			$sql .= " AND b.blog_id != '" . (int)$this_blog_id . "'";
			}
			
			if (!empty($data['filter_name'])) {
			$sql .= " AND bd.title LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}
			

			$sort_data = array(
				'bd.title',
				'b.sort_order',
				'b.date_added',
				'author'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY bd.title";
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
			$blog_data = $this->cache->get('blog.' . (int)$this->config->get('config_language_id'));

			if (!$blog_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog b LEFT JOIN " . DB_PREFIX . "blog_description bd ON (b.blog_id = bd.blog_id) WHERE bd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY bd.title");

				$blog_data = $query->rows;

				$this->cache->set('blog.' . (int)$this->config->get('config_language_id'), $blog_data);
			}

			return $blog_data;
		}
	}

	public function getBlogDescriptions($blog_id) {
		$blog_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_description WHERE blog_id = '" . (int)$blog_id . "'");

		foreach ($query->rows as $result) {
			$blog_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			);
		}

		return $blog_description_data;
	}
	
	public function getBlogKeyword($blog_id) {
		$blog_keyword_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE query = 'blog_id=" . (int)$blog_id . "'");

		foreach ($query->rows as $result) {
			$blog_keyword_data[$result['language_id']] = array(				
				'keyword'      	   => $result['keyword']				
			);
		}

		return $blog_keyword_data;
	}

	public function getBlogStores($blog_id) {
		$blog_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_to_store WHERE blog_id = '" . (int)$blog_id . "'");

		foreach ($query->rows as $result) {
			$blog_store_data[] = $result['store_id'];
		}

		return $blog_store_data;
	}
	
	public function getBlogFamily($blog_id) {
		$blog_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_to_family WHERE blog_id = '" . (int)$blog_id . "'");

		foreach ($query->rows as $result) {
			$blog_family_data[] = $result['family_id'];
		}

		return $blog_family_data;
	}
	
	public function getBlogLayouts($blog_id) {
		$blog_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_to_layout WHERE blog_id = '" . (int)$blog_id . "'");

		foreach ($query->rows as $result) {
			$blog_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $blog_layout_data;
	}

	public function getTotalBlogs() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "blog");

		return $query->row['total'];
	}

	public function getTotalBlogsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "blog_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}
}