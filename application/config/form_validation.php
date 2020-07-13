<?php

$config = [
    'auth' => [
        [
            'field' => 'login',
            'label' => 'Text Field Login',
            'rules' => 'trim|required|max_length[60]|valid_email'
        ],
        [
            'field' => 'password',
            'label' => 'Text Field Password',
            'rules' => 'required|max_length[32]'
        ]
    ],
    'comment' => [
        [
            'field' => 'post_id',
            'label' => 'Int Field post id',
            'rules' => 'required|integer'
        ],
        [
            'field' => 'text',
            'label' => 'Text Field Password',
            'rules' => 'required|max_length[255]'
        ]
    ],
    'likes' => [
        [
            'field' => 'type',
            'label' => 'Int Field post id',
            'rules' => 'required|in_list[' . MY_Controller::LIKE_TYPE_COMMENT . ',' . MY_Controller::LIKE_TYPE_POST . ']'
        ],
        [
            'field' => 'id',
            'label' => 'Text Field Password',
            'rules' => 'required|integer'
        ]
    ],
];