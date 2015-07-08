<?php
class ModelBlogComment extends Model {
	public function addComment($data) {
		$this->event->trigger('pre.admin.comment.add', $data);

		$this->db->query("INSERT INTO " . DB_PREFIX . "comment SET commenter = '" . $this->db->escape($data['commenter']) . "', blog_id = '" . (int)$data['blog_id'] . "', text = '" . $this->db->escape(strip_tags($data['text'])) . "', status = '" . (int)$data['status'] . "', date_added = NOW()");

		$comment_id = $this->db->getLastId();

		$this->cache->delete('blog');

		$this->event->trigger('post.admin.comment.add', $comment_id);

		return $comment_id;
	}

	public function editComment($comment_id, $data) {
		$this->event->trigger('pre.admin.comment.edit', $data);

		$this->db->query("UPDATE " . DB_PREFIX . "comment SET commenter = '" . $this->db->escape($data['commenter']) . "', blog_id = '" . (int)$data['blog_id'] . "', text = '" . $this->db->escape(strip_tags($data['text'])) . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE comment_id = '" . (int)$comment_id . "'");

		$this->cache->delete('blog');

		$this->event->trigger('post.admin.comment.edit', $comment_id);
	}

	public function deleteComment($comment_id) {
		$this->event->trigger('pre.admin.comment.delete', $comment_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "comment WHERE comment_id = '" . (int)$comment_id . "'");

		$this->cache->delete('blog');

		$this->event->trigger('post.admin.comment.delete', $comment_id);
	}

	public function getComment($comment_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT ad.title FROM " . DB_PREFIX . "blog_description ad WHERE ad.blog_id = c.blog_id AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "') AS blog FROM " . DB_PREFIX . "comment c WHERE c.comment_id = '" . (int)$comment_id . "'");

		return $query->row;
	}

	public function getComments($data = array()) {
		$sql = "SELECT c.comment_id, ad.title, c.commenter, c.status, c.date_added FROM " . DB_PREFIX . "comment c LEFT JOIN " . DB_PREFIX . "blog_description ad ON (c.blog_id = ad.blog_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_blog'])) {
			$sql .= " AND ad.title LIKE '" . $this->db->escape($data['filter_blog']) . "%'";
		}

		if (!empty($data['filter_commenter'])) {
			$sql .= " AND c.commenter LIKE '" . $this->db->escape($data['filter_commenter']) . "%'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND c.status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		$sort_data = array(
			'ad.title',
			'c.commenter',
			'c.status',
			'c.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY c.date_added";
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

	public function getTotalComments($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "comment c LEFT JOIN " . DB_PREFIX . "blog_description ad ON (c.blog_id = ad.blog_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_blog'])) {
			$sql .= " AND ad.title LIKE '" . $this->db->escape($data['filter_blog']) . "%'";
		}

		if (!empty($data['filter_commenter'])) {
			$sql .= " AND c.commenter LIKE '" . $this->db->escape($data['filter_commenter']) . "%'";
		}

		if (!empty($data['filter_status'])) {
			$sql .= " AND c.status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalCommentsAwaitingApproval() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "comment WHERE status = '0'");

		return $query->row['total'];
	}
}