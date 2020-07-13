<?php

class Main_page extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        App::get_ci()->load->model('User_model');
        App::get_ci()->load->model('Login_model');
        App::get_ci()->load->model('Post_model');

        $this->load->library('form_validation');

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

    /**
     * @return object|string|void
     * @throws Exception
     */
    public function comment()
    {
        if (!User_model::is_logged()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $this->fill_global_post_with_input_stream();

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

    /**
     * @return object|string|void
     * @throws CriticalException
     */
    public function login()
    {
        $this->fill_global_post_with_input_stream();

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

    /**
     * @return object|string|void
     */
    public function add_money()
    {
        if (!User_model::is_logged()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $this->fill_global_post_with_input_stream();

        if (!$this->form_validation->run('money')) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS,);
        }

        $user = User_model::get_user();

        $balance = ($user->get_wallet_balance() ?? 0) + $_POST['sum'];
        $total = ($user->get_wallet_total_refilled() ?? 0) + $_POST['sum'];

        $this->s->start_trans();

        $user->set_wallet_balance($balance);
        $user->set_wallet_total_refilled($total);

        $this->s->commit();

        return $this->response_success(['amount' => $balance]);
    }

    /**
     * @return object|string|void
     */
    public function buy_boosterpack()
    {
        if (!User_model::is_logged()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $this->fill_global_post_with_input_stream();

        $id = $_POST['id'];
        if (!$this->form_validation->run('boosterpack') || !in_array($id, [1,2,3])) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS,);
        }

        $this->load->model('Boosterpack_model');

        try {
            $pack = new Boosterpack_model($id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        $user = User_model::get_user();

        if ($user->get_wallet_balance() < $pack->get_price()) {
            return $this->response_error('err_not_enough_dollars');
        }

        $balance   = $user->get_wallet_balance() - $pack->get_price();
        $withdrawn = ($user->get_wallet_total_withdrawn() ?? 0) + $pack->get_price();
        $amount    = rand(1, $pack->get_price());
        $bank      = $pack->get_price() - $amount;
        $likes     = $user->get_likes_total() + $amount;

        $this->s->start_trans();

        $user->set_wallet_balance($balance);
        $user->set_wallet_total_withdrawn($withdrawn);
        $user->set_likes_total($likes);
        $pack->set_bank($bank);

        $this->s->commit();

        return $this->response_success(['amount' => $amount]);
    }

    /**
     * @return object|string|void
     * @throws Exception
     */
    public function like()
    {
        if (!User_model::is_logged()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $this->fill_global_post_with_input_stream();

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

                // comment_id will be null in case if user likes to post not a comment
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
