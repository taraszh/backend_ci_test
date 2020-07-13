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
            'field' => 'id',
            'label' => 'Text Field Post Id',
            'rules' => 'required|integer'
        ],
        [
            'field' => 'comment_id',
            'label' => 'Text Field Comment Id',
            'rules' => 'integer'
        ],
    ],
    'money' => [
        [
            'field' => 'sum',
            'label' => 'Numeric Field post id',
            'rules' => 'required|greater_than[0]|numeric'
        ],
    ],
];