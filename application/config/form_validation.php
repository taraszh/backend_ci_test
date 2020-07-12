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
];