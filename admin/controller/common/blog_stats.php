<?php
class ControllerCommonBlogStats extends Controller {
	public function index() {
		$this->data = $this->load->language('common/blog_stats');

		$this->load->model('blog/blog');
		
		$this->load->model('blog/comment');

		$this->data['blog_total'] = $this->model_blog_blog->getTotalBlogs();

		$this->data['comments_total'] = $this->model_blog_comment->getTotalComments();
		
		$this->data['comments_approval'] = $this->model_blog_comment->getTotalCommentsAwaitingApproval();

		return $this->load->view('common/blog_stats.tpl', $this->data);
	}
}