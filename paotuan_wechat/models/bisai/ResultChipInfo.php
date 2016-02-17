<?php

namespace paotuan_wechat\models\bisai;

use Yii;

/**
 * This is the model class for table "result_chip_info".
 *
 * @property string $chip_id
 * @property string $race_id
 * @property string $chip_no
 * @property string $chip_mac
 * @property integer $chip_status
 * @property string $create_time
 */
class ResultChipInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'result_chip_info';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_bisai');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['race_id', 'chip_status'], 'integer'],
            [['create_time'], 'safe'],
            [['chip_no'], 'string', 'max' => 12],
            [['chip_mac'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'chip_id' => 'Chip ID',
            'race_id' => '赛事ID',
            'chip_no' => '芯片编号',
            'chip_mac' => '芯片读取码',
            'chip_status' => '芯片状态',
            'create_time' => '创建时间',
        ];
    }
}
