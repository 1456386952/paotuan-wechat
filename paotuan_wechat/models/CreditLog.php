<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "credit_log".
 *
 * @property string $log_id
 * @property string $uid
 * @property string $rid
 * @property string $club_id
 * @property string $desc
 * @property integer $credits
 * @property integer $curcredits
 * @property string $record_time
 * @property string $create_time
 * @property string $operator
 */
class CreditLog extends \yii\db\ActiveRecord {

	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'credit_log';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[[ 'uid', 'rid', 'club_id', 'credits', 'curcredits', 'record_time', 'create_time', 'operator' ], 'integer' ],
			[[ 'desc' ], 'string', 'max' => 100 ]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'log_id' => '流水ID',
			'uid' => '被操作用户ID',
			'rid' => '对应的规则id',
			'club_id' => '用户所属跑团ID',
			'desc' => '积分操作描述',
			'credits' => '积分变化值',
			'curcredits' => '当前剩余积分',
			'record_time' => '记录时间',
			'create_time' => '创建时间',
			'operator' => '记录执行人 （0为系统生成。大于0为具体用户ID）',
		];
	}

	public static function getCreditsList( $club_id, $uid, $offset = 0, $limit = 20 ) {
		return self::find()->where( ["club_id" => $club_id, "uid" => $uid ] )->orderBy( "record_time desc" )->offset( $offset )->limit( $limit )->all();
	}

}
