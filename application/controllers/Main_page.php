<?php

class Main_page extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        App::get_ci()->load->model('User_model');
        App::get_ci()->load->model('Login_model');
        App::get_ci()->load->model('Post_model');

        if (is_prod())
        {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function index()
    {
        $user = User_model::get_user();

        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, 'default')]);
    }

    public function get_all_posts()
    {
        $posts =  Post_model::preparation(Post_model::get_all(), 'main_page');

        return $this->response_success(['posts' => $posts]);
    }

    public function get_post($post_id)
    {
        $post_id = intval($post_id);

        if (empty($post_id)) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try
        {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        $posts =  Post_model::preparation($post, 'full_info');
        return $this->response_success(['post' => $posts]);
    }

    public function comment()
    {
        if (!User_model::is_logged()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $this->fill_global_post_with_input_stream();
        $this->load->library('form_validation');

        if (!$this->form_validation->run('comment')) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS,);
        }

        try {
            $post = new Post_model($_POST['post_id']);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        Comment_model::create([
            'user_id' => User_model::get_user()->get_id(),
            'assign_id' => $_POST['post_id'],
            'text' => $_POST['text'],
        ]);

        $posts = Post_model::preparation($post, 'full_info');

        return $this->response_success(['post' => $posts]);
    }

    public function login()
    {
        $this->fill_global_post_with_input_stream();
        $this->load->library('form_validation');

        if (!$this->form_validation->run('auth')) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS,);
        }

        $user = User_model::get_user_by_email(trim($_POST['login']));

        if (isset($user['password']) && ($user['password'] === $_POST['password'])) {
            Login_model::start_session($user['id']);

            return $this->response_success(['user' => $user['id']]);
        }

        return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
    }

    public function logout()
    {
        Login_model::logout();
        redirect(site_url('/', 'http'));
    }

    public function add_money()
    {
        // todo: add money to user logic
        return $this->response_success(['amount' => rand(1,55)]);
    }

    public function buy_boosterpack()
    {
        // todo: add money to user logic
        return $this->response_success(['amount' => rand(1,55)]);
    }

    public function like()
    {
        if (!User_model::is_logged()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $this->fill_global_post_with_input_stream();
        $this->load->library('form_validation');

        if (!$this->form_validation->run('likes')) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS,);
        }

        $user = User_model::get_user();

        if (!$user->get_likes_total()) {
            return $this->response_error(self::NO_LIKES_ERR_MSG);
        }

        $this->s->start_trans();

        $user->set_likes_total($user->get_likes_total() - 1);

            try {
                $post = new Post_model($_POST['id']);

                // comment_id will be null in case if user throw like to post
                if ($_POST['comment_id']) {
                    $comment = new Comment_model($_POST['comment_id']);
                    $comment->set_likes($comment->get_likes() + 1);
                } else {
                    $post->set_likes($post->get_likes() + 1);
                }

            } catch (EmeraldModelNoDataException $ex){
                $this->s->rollback();
                return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
            }

        $this->s->commit();

        $posts = Post_model::preparation($post, 'full_info');
        return $this->response_success(['post' => $posts]);
    }

}
