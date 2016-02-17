<?php

namespace paotuan_wechat\models;

use yii;

class ClubSub extends yii\db\ActiveRecord {

	public static function tableName() {
		return '{{club_sub}}';
	}

	/**
	 * 根据跑团ID查找所有分跑团
	 * @param integer $clubId 跑团ID 
	 * @return array
	 */
	public static function fetchAllByClubId( $clubId ) {
		return static::find()->where( ['club_id' => $clubId ] )
						->asArray()
						->all();
	}

	public static function fetchSubNameById( $subId ) {
		$row = static::findOne( [$subId ] );
		return $row ? $row['sub_name'] : '';
	}

	/**
	 * 根据跑团ID查找所有分跑团，并以 id=>分跑团名的数组返回
	 * @param integer $clubId 跑团ID
	 * @return array
	 */
	public static function fetchAllByClubIdFormatToOption( $clubId ) {
		$return = [ ];
		foreach ( static::fetchAllByClubId( $clubId ) as $row ) {
			$return[$row['id']] = $row['sub_name'];
		}
		return $return;
	}

}
