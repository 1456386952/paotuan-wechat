<?php

namespace paotuan_wechat\models;

use Yii;
use yii\db\Expression;
use yii\db\Query;

class Club extends \yii\db\ActiveRecord {

	const CLUB_TYPE_CLUB = 1;
	const CLUB_TYPE_ZHUBAN = 2;
	const CLUB_TYPE_PERSON = 3;
	const CLUB_STATUS_NORMAL = 1;
	const CLUB_STATUS_PROCESS = 2;
	const CLUB_STATUS_INVALID = 3;

	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'club';
	}

	public function getSubs() {
		return $this->hasMany( ClubSub::className(), ["club_id" => 'clubid' ] );
	}

	public function getConfigs() {
		return $this->hasMany( ClubConfig::className(), ["club_id" => 'clubid' ] )->orderBy( "sort asc" );
		;
	}

	public function getMembers() {
		return $this->hasMany( ClubMember::className(), ["club_id" => 'clubid' ] )->where( "member_status=" . ClubMember::STATUS_NORMAL . " or member_status=" . ClubMember::STATUS_SIMPLE )->orderby( "create_time" )->inverseOf( 'club' );
	}

	public function getActs() {
		return $this->hasMany( Activity::className(), ['act_id' => 'act_id' ] )
						->viaTable( 'activity_club', ['club_id' => 'clubid' ] )->where( "act_status=" . Activity::STATUS_NORMAL )->orderBy( "act_create_time desc" );
	}

	public function getAnnouns() {
		return $this->hasMany( Announcement::className(), ['clubid' => 'clubid' ] )->where( ["status" => Announcement::STATUS_NORMAL ] )->orderBy( "create_time desc" );
	}

	public static function getUserClubs( $uid ) {
		$query = new Query();
		return $query->select( "cm.count as member_sum,club_eng,club_name,clubid,club_slogan,club_logo,club_status,cm1.is_default,cm1.set_default_time,cm1.set_default_interval,cm1.id as member_id" )
						->from( Club::tableName() )
						->innerJoin( ClubMember::tableName() . " as cm1", "clubid=cm1.club_id" )
						->leftJoin( "(select count(id) as count,club_id from club_member where member_status=" . ClubMember::STATUS_NORMAL . " or  member_status=" . ClubMember::STATUS_SIMPLE . " GROUP BY club_id) as cm", "clubid = cm.club_id" )->where( "cm1.uid=$uid and club_type=" . Club::CLUB_TYPE_CLUB . " and (club_status=" . Club::CLUB_STATUS_NORMAL . " or club_status=" . Club::CLUB_STATUS_PROCESS . ") and (cm1.member_status=" . ClubMember::STATUS_NORMAL . " or cm1.member_status=" . ClubMember::STATUS_SIMPLE . ")" )->orderBy( "is_default desc,cm1.create_time desc" )->all();
	}

	public static function getOwnClubs( $uid ) {
		$query = new Query();
		return $query->select( "cm.count as member_sum,club_eng,club_name,clubid,club_slogan,club_logo,club_status,cm1.is_default" )
						->from( Club::tableName() )
						->leftJoin( ClubMember::tableName() . " as  cm1", "cm1.uid=club.uid and cm1.club_id = club.clubid" )
						->leftJoin( "(select count(id) as count,club_id from club_member where member_status=" . ClubMember::STATUS_NORMAL . " or  member_status=" . ClubMember::STATUS_SIMPLE . " GROUP BY club_id) as cm", "clubid = cm.club_id" )->where( "club.uid=$uid and club_type=" . Club::CLUB_TYPE_CLUB . " and (club_status=" . Club::CLUB_STATUS_NORMAL . " or club_status=" . Club::CLUB_STATUS_PROCESS . ")" )->orderBy( "cm1.is_default desc,club_name asc" )->all();
	}

	public static function findClubs( $name, $offset = 0, $limit = 20 ) {
		return Club::find()->select( "cm.count as member_sum,club_eng,club_name,clubid,club_slogan,club_logo,club_status" )
						->leftJoin( "(select count(id) as count,club_id from club_member where member_status=" . ClubMember::STATUS_NORMAL . " or  member_status=" . ClubMember::STATUS_SIMPLE . " GROUP BY club_id) as cm", "clubid = cm.club_id" )->where( "club_name like :club_name and club_type=" . Club::CLUB_TYPE_CLUB . " and (club_status=" . Club::CLUB_STATUS_NORMAL . " or club_status=" . Club::CLUB_STATUS_PROCESS . ")" )->addParams( [":club_name" => "%$name%" ] )->offset( $offset )->limit( $limit )->all();
	}

	public static function findClubWithMemberSum( $club_eng ) {
		return Club::find()->select( "cm.count as member_sum,club.*" )
						->leftJoin( "(select count(id) as count,club_id from club_member where member_status=" . ClubMember::STATUS_NORMAL . " or  member_status=" . ClubMember::STATUS_SIMPLE . " GROUP BY club_id) as cm", "clubid = cm.club_id" )->where( "club_eng=:club_eng and club_type=" . Club::CLUB_TYPE_CLUB . " and (club_status=" . Club::CLUB_STATUS_NORMAL . " or club_status=" . Club::CLUB_STATUS_PROCESS . ")" )->addParams( [":club_eng" => $club_eng ] )->one();
	}

	public static function findMarketingClubs( $marketing_id ) {
		return Club::find()->select( "cm.count as member_sum,club_eng,club_name,clubid,club_slogan,club_logo,club_status,mc.uid as uid" )
						->innerJoin( MarketingClub::tableName() . " as mc", "mc.club_id=club.clubid" )
						->leftJoin( "(select count(id) as count,club_id from club_member where member_status=" . ClubMember::STATUS_NORMAL . " or  member_status=" . ClubMember::STATUS_SIMPLE . " GROUP BY club_id) as cm", "clubid = cm.club_id" )->where( "mc.marketing_id=:marketing_id and club_type=" . Club::CLUB_TYPE_CLUB . " and (club_status=" . Club::CLUB_STATUS_NORMAL . " or club_status=" . Club::CLUB_STATUS_PROCESS . ")" )->addParams( [":marketing_id" => $marketing_id ] )->orderBy( "club_name asc" )->all();
	}

	public static function findMarketingVoteClubs( $marketing_id, $uid ) {
		$query = new Query();
		return $query->select( "club_eng,club_name,club.clubid,club_slogan,club_logo,club_status,mc.like_sum,mv1.uid as vote_uid" )
						->from( Club::tableName() )
						->innerJoin( MarketingClub::tableName() . " as mc", "mc.club_id=club.clubid" )
						->leftJoin( "(select club_id from " . MarketingVote::tableName() . " group by club_id) mv ", "mv.club_id = mc.club_id" )
						->leftJoin( "(select club_id,uid from " . MarketingVote::tableName() . " where uid = $uid) mv1 ", "mv1.club_id = mc.club_id" )
						->where( "mc.marketing_id=:marketing_id and club_type=" . Club::CLUB_TYPE_CLUB . " and (club_status=" . Club::CLUB_STATUS_NORMAL . " or club_status=" . Club::CLUB_STATUS_PROCESS . ")" )
						->addParams( [":marketing_id" => $marketing_id ] )
						->orderBy( "like_sum desc,mc.create_time asc" )->all();
	}

	public static function findMarketingClubsMileageRank( $marketing_id, $uid ) {
		$query = new Query();
		return $query->select( "club_eng,club_name,club.clubid,club_slogan,club_logo,club_status,mc.like_sum,mv1.uid as vote_uid" )
						->from( Club::tableName() )
						->innerJoin( MarketingClub::tableName() . " as mc", "mc.club_id=club.clubid" )
						->leftJoin( "(select club_id from " . MarketingVote::tableName() . " group by club_id) mv ", "mv.club_id = mc.club_id" )
						->leftJoin( "(select club_id,uid from " . MarketingVote::tableName() . " where uid = $uid) mv1 ", "mv1.club_id = mc.club_id" )
						->where( "mc.marketing_id=:marketing_id and club_type=" . Club::CLUB_TYPE_CLUB . " and (club_status=" . Club::CLUB_STATUS_NORMAL . " or club_status=" . Club::CLUB_STATUS_PROCESS . ")" )
						->addParams( [":marketing_id" => $marketing_id ] )
						->orderBy( "like_sum desc,mc.create_time asc" )->all();
	}

	public function beforeSave( $insert ) {
		if ( parent::beforeSave( $insert ) ) {
			$this->update_time = date( "Y-m-d H:i:s" );
			if ( $this->isNewRecord ) {
				$this->create_time = date( "Y-m-d H:i:s" );
			}
			return true;
		}
		return false;
	}

}
