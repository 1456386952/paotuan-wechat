<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'irunner@landc-consulting.com',
    'user.passwordResetTokenExpire' => 3600,
    'user_info' => [
        'passport_name'=>'姓名',
        'screen_name'=>'网名',
        'user_gender'=>[
            'title'=>'性别',
            'val' => [
                0=>'未知',
                1=>'男',
                2=>'女',
            ]
        ],
        'nationality'=>'国籍',
        'id_type'=>[
           'title' => '证件类型',
            'val' =>[
                1=>'身份证',
                2=>'护照',
                3=>'台胞证',
                4=>'港澳通行证',
                0=>'其它',
            ],
        ],
        'id_number'=>'证件号码',
        'birthday'=>'生日',
        'tshirt_size'=>'上衣尺码',
        'shoes_size'=>'鞋子尺码',
        'address'=>'通信地址',
        'blood_type'=>'血型',
        'height'=>'身高[CM]',
        'weight'=>'体重[KG]',
        'morning_pulse'=>'晨脉[次/分]',
        'waistline'=>'腰围',
        'allergen'=>'过敏源',
        'emerge_name'=>'紧急联系人姓名',
        'emerge_cell'=>'紧急联系人关系',
        'emerge_ship'=>'紧急联系人电话',
        'emerge_addr'=>'紧急联系人地址',
        'user_email'=>'用户邮箱',
        'user_cell'=>'用户手机',
        'education'=>'学历',
        'company_name'=>'公司名称',
        'job_title'=>'职务',
        'best_score'=>'最好赛事成绩',
    ]
];
