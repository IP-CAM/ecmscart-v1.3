<?php
class ModelBlogComment extends Model {
	public function addComment($blog_id, $data) {
		$this->event->trigger('pre.comment.add', $data);

		$this->db->query("INSERT INTO " . DB_PREFIX . "comment SET commenter = '" . $this->db->escape($data['commenter']) . "', customer_id = '" . (int)$this->customer->getId() . "', blog_id = '" . (int)$blog_id . "', text = '" . $this->db->escape($data['text']) . "', date_added = NOW()");

		$comment_id = $this->db->getLastId();

		if ($this->config->get('config_blog_comment_mail')) {
			$this->load->language('mail/comment');
			$this->load->model('blog/blog');
			$blog_info = $this->model_blog_blog->getblog($blog_id);

			$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));

			$message  = $this->language->get('text_waiting') . "\n";
			$message .= sprintf($this->language->get('text_blog'), $this->db->escape(strip_tags($blog_info['name']))) . "\n";
			$message .= sprintf($this->language->get('text_commenter'), $this->db->escape(strip_tags($data['commenter']))) . "\n";
			$message .= $this->language->get('text_comment') . "\n";
			$message .= $this->db->escape(strip_tags($data['text'])) . "\n\n";

			$mail = new Mail($this->config->get('config_mail'));
			$mail->setTo(array($this->config->get('config_email')));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject($subject);
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();

			// Send to additional alert emails
			$emails = explode(',', $this->config->get('config_mail_alert'));

			foreach ($emails as $email) {
				if ($email && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
					$mail->setTo($email);
					$mail->send();
				}
			}
		}

		$this->event->trigger('post.comment.add', $comment_id);
	}

	public function getCommentsByBlogId($blog_id, $start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$query = $this->db->query("SELECT c.comment_id, c.commenter, c.text, c.blog_id, c.date_added FROM " . DB_PREFIX . "comment c LEFT JOIN " . DB_PREFIX . "blog a ON (c.blog_id = a.blog_id) LEFT JOIN " . DB_PREFIX . "blog_description ad ON (a.blog_id = ad.blog_id) WHERE a.blog_id = '" . (int)$blog_id . "' AND a.status = '1' AND c.status = '1' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY c.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalCommentsByBlogId($blog_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "comment c LEFT JOIN " . DB_PREFIX . "blog a ON (c.blog_id = a.blog_id) LEFT JOIN " . DB_PREFIX . "blog_description ad ON (a.blog_id = ad.blog_id) WHERE a.blog_id = '" . (int)$blog_id . "' AND a.status = '1' AND c.status = '1' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row['total'];
	}
}