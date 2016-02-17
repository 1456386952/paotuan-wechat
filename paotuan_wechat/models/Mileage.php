<?php

namespace paotuan_wechat\models;

use Yii;
use common\models\UserMaster;
use yii\base\Arrayable;
use yii\db\Query;

/**
 * This is the model class for table "mileage".
 *
 * @property integer $id
 * @property integer $uid
 * @property double $mileage
 * @property integer $duration
 * @property string $format_duration
 * @property string $pace
 * @property string $location
 * @property string $map
 * @property string $create_date
 * @property string $mileage_week
 * @property string $mileage_month
 * @property string $mileage_year
 * @property string $mileage_date
 * @property integer $status
 */
class Mileage extends \yii\db\ActiveRecord implements Arrayable {

	const FROM_TYPE_INPUT = 1;
	const FROM_TYPE_CODOON = 2;
	const FROM_TYPE_HUPU = 3;
	const FROM_TYPE_EDOON = 4;
	const FROM_TYPE_XIAOMI = 5;

	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'mileage';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[[ 'uid', 'mileage', 'mileage_date' ], 'required' ],
			[[ 'uid', 'duration', 'status', 'from' ], 'integer' ],
			[[ 'mileage' ], 'number' ],
			[[ 'create_date', 'mileage_date', 'mileage_year', 'mileage_month', 'mileage_week', 'create_date' ], 'safe' ],
			[[ 'location', 'map' ], 'string', 'max' => 100 ],
			[[ 'format_duration', 'pace' ], 'string', 'max' => 20 ]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'uid' => 'Uid',
			'mileage' => 'Mileage',
			'duration' => 'Duration',
			'format_duration' => 'Format_duration',
			'pace' => 'Pace',
			'location' => 'Location',
			'map' => 'Map',
			'create_date' => 'Create Date',
			'mileage_week' => 'Mileage_week',
			'mileage_month' => 'Mileage_month',
			'mileage_year' => 'Mileage_year',
			'mileage_date' => 'Mileage Date',
			'status' => '是否已统计（0：统计，1：不统计   默认不需要审核为统计）',
		];
	}

	public function getAlbums() {
		return $this->hasMany( MileageAlbum::className(), ["mileage_id" => "id" ] );
	}

	public static function getMileagesByDate( $uid, $startDate, $endDate, $groupby = "mileage_date" ) {
		return Mileage::find()->select( ["sum(mileage) as mileage,$groupby" ] )->where( ["uid" => $uid ] )->andWhere( "mileage_date between '$startDate' and '$endDate'" )->groupBy( $groupby )->orderBy( "mileage_date desc" )->all();
	}

	public static function getMileages( $clubid,$uid, $groupby = "mileage_date", $offset = 0, $limit = 7, $mileage_type = 'run' ) {
		if($clubid=="1075"){
			if ( count( $uid ) > 1 ) {
				return Mileage::find()->select( ["sum(mileage) as mileage,$groupby,max(mileage_date) as mileage_date" ] )->where( array( "in", "uid", $uid ) )->andWhere( ["status" => 0, 'type' => $mileage_type ] )->andWhere(['<>','from',1])->groupBy( $groupby )->orderBy( "mileage_date desc" )->limit( $limit )->offset( $offset )->all();
			} else {
				return Mileage::find()->select( ["sum(mileage) as mileage,$groupby,max(mileage_date) as mileage_date" ] )->where( array( "in", "uid", $uid ) )->andWhere( ['type' => $mileage_type ] )->andWhere(['<>','from',1])->groupBy( $groupby )->orderBy( "mileage_date desc" )->limit( $limit )->offset( $offset )->all();
			}
		}else{
			if ( count( $uid ) > 1 ) {
				return Mileage::find()->select( ["sum(mileage) as mileage,$groupby,max(mileage_date) as mileage_date" ] )->where( array( "in", "uid", $uid ) )->andWhere( ["status" => 0, 'type' => $mileage_type ] )->groupBy( $groupby )->orderBy( "mileage_date desc" )->limit( $limit )->offset( $offset )->all();
			} else {
				return Mileage::find()->select( ["sum(mileage) as mileage,$groupby,max(mileage_date) as mileage_date" ] )->where( array( "in", "uid", $uid ) )->andWhere( ['type' => $mileage_type ] )->groupBy( $groupby )->orderBy( "mileage_date desc" )->limit( $limit )->offset( $offset )->all();
			}
		}
	}

	public static function getBaseMileages( $uid ) {
		$query = new Query();
		$query->select( "sum(m.mileage) as mileage,count(m.id) as count, min(m1.mileage_date) as min_date, max(m1.mileage_date) as max_date from mileage m " .
				"join mileage m1 on m.id=m1.id " .
				" where m.uid=:uid" )->addParams( [":uid" => $uid ] );
		return $query->one();
	}

	public static function getSumAndCountByClub( $clubid, $type = 'run' ) {
		if($clubid=="1075"){ 
		 return Mileage::findBySql( "select count(id) as id,sum(mileage) as mileage from mileage mi JOIN (select uid from club_member where club_id =$clubid and member_status=" . ClubMember::STATUS_NORMAL . ") m on mi.uid = m.uid where mi.status=0 and  mi.from<>1 and mi.type='{$type}'" )->one();
		 } 
		return Mileage::findBySql( "select count(id) as id,sum(mileage) as mileage from mileage mi JOIN (select uid from club_member where club_id =$clubid and member_status=" . ClubMember::STATUS_NORMAL . ") m on mi.uid = m.uid where mi.status=0 and mi.type='{$type}'" )->one();
	}

	public static function getRecent( $clubid,$uid, $offset = 0, $limit = 7, $type = 'run' ) { 
		if($clubid=="1075"){
			return Mileage::find()->where( array( "in", "uid", $uid ))->andWhere( ['type' => $type ] )->andWhere(['<>','from',1])->with( "albums" )->orderBy( "mileage_date desc,create_date desc" )->limit( $limit )->offset( $offset )->all();
		}
		return Mileage::find()->where( array( "in", "uid", $uid ) )->andWhere( ['type' => $type ] )->with( "albums" )->orderBy( "mileage_date desc,create_date desc" )->limit( $limit )->offset( $offset )->all();
	}

	/**
	 * 
	 * @param type $uids
	 * @param type $dateCloumn
	 * @param type $count
	 * @param type $date
	 * @param type $offset
	 * @return type
	 */
	public static function getRankData( $club_id,$uids, $dateCloumn, $count, $date, $offset = 0, $type = 'run' ) {
		$sql = "";
		if ( !empty( $limit ) ) {
			$limit = " limit $limit ";
		}
		if ( is_array( $uids ) ) {
			foreach ( $uids as $uid ) {
				if($club_id=="1075"){
					       $sql = $sql . "union (select uid,sum(mileage) as mileage from mileage  where type='{$type}' AND mileage.from<>1 and  status=0 and uid =$uid and $dateCloumn='$date') ";
				 } else{
					$sql = $sql . "union (select uid,sum(mileage) as mileage from mileage  where type='{$type}' AND status=0 and uid =$uid and $dateCloumn='$date') ";
				 }
			}
		}
		if ( !empty( $sql ) ) {
			$sql = $sql . "order by mileage desc limit $count offset $offset";
			return Mileage::findBySql( substr( $sql, 5 ) )->all();
		}
	}

	public static function getRankDataBySub( $clubId, $dateCloumn, $count, $date, $offset = 0, $type = 'run' ) {
		if($clubId=="1075"){
			return (new Query() )->select( 'cm.sub_group,sum(m.mileage) as mileage' )
			->from( '{{mileage}} m' )
			->leftJoin( '{{club_member}} cm', 'm.uid = cm.uid' )
			->where( "cm.club_id = {$clubId} and m.from<>1 and m.status = 0 AND {$dateCloumn} = '{$date}' AND m.type = '{$type}'" )
			->groupBy( 'cm.sub_group' )
			->orderBy( 'mileage DESC' )
			->limit( $count )
			->offset( $offset )
			->all();
		}
		return (new Query() )->select( 'cm.sub_group,sum(m.mileage) as mileage' )
						->from( '{{mileage}} m' )
						->leftJoin( '{{club_member}} cm', 'm.uid = cm.uid' )
						->where( "cm.club_id = {$clubId} and m.status = 0 AND {$dateCloumn} = '{$date}' AND m.type = '{$type}'" )
						->groupBy( 'cm.sub_group' )
						->orderBy( 'mileage DESC' )
						->limit( $count )
						->offset( $offset )
						->all();
	}

	public function getUser() {
		return $this->hasOne( UserMaster::className(), ["uid" => "uid" ] );
	}

	public function beforeSave( $insert ) {
		if ( parent::beforeSave( $insert ) ) {
			if ( $this->isNewRecord ) {
				$this->create_date = date( "Y-m-d H:i:s" );
			}
			return true;
		}
		return false;
	}

}
