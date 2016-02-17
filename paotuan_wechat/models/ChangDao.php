<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "changdao".
 *
 * @property integer $id
 * @property integer $orderid
 * @property string $bm_code
 * @property string $bm_type
 * @property string $cs_code
 * @property string $cs_type
 * @property string $name
 * @property string $xb
 * @property string $sr
 * @property string $cell
 * @property string $email
 * @property string $id_type
 * @property string $id_no
 * @property string $guojia
 * @property string $jinji_cell
 * @property string $gexing_code
 * @property double $amount
 * @property string $pay_type
 * @property string $reg_time
 */
class ChangDao extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'changdao';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orderid'], 'required'],
            [['orderid'], 'integer'],
            [['amount'], 'number'],
            [['bm_code', 'bm_type', 'cs_code', 'cs_type', 'name', 'sr', 'id_type', 'guojia', 'gexing_code', 'pay_type'], 'string', 'max' => 10],
            [['xb'], 'string', 'max' => 2],
            [['cell', 'jinji_cell'], 'string', 'max' => 11],
            [['email'], 'string', 'max' => 50],
            [['id_no', 'reg_time'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'orderid' => 'Orderid',
            'bm_code' => 'Bm Code',
            'bm_type' => 'Bm Type',
            'cs_code' => 'Cs Code',
            'cs_type' => 'Cs Type',
            'name' => 'Name',
            'xb' => 'Xb',
            'sr' => 'Sr',
            'cell' => 'Cell',
            'email' => 'Email',
            'id_type' => 'Id Type',
            'id_no' => 'Id No',
            'guojia' => 'Guojia',
            'jinji_cell' => 'Jinji Cell',
            'gexing_code' => 'Gexing Code',
            'amount' => 'Amount',
            'pay_type' => 'Pay Type',
            'reg_time' => 'Reg Time',
        ];
    }
}
