<?php

namespace paotuan_wechat\controllers;

use Yii;
use paotuan_wechat\models\Marketing;
use common\component\CustomHelper;
use yii\helpers\ArrayHelper;
use paotuan_wechat\component\PaotuanController;
use paotuan_wechat\models\MarketingClub;
use paotuan_wechat\models\Club;
use paotuan_wechat\models\MarketingVote;
use paotuan_wechat\models\Mileage;
use paotuan_wechat\models\ClubMember;

class MarketingController extends PaotuanController {
	private $marketing=null;
	private $result=["status"=>0];
	public function beforeAction($action) {
		$id =trim(\Yii::$app->request->post("marketing_id",""));
		if($id){
			$this->marketing =  Marketing::findOne($id);
			if(!$this->marketing){
				throw new NotFoundHttpException();
			}
		}
		return parent::beforeAction($action);
	}
	
	public function actionInfo(){
	   $clubs = ArrayHelper::toArray(Club::findMarketingClubs($this->marketing->marketing_id));
		$can_reg=true;
		$reg_end=false;
		$vote_end=false;
		if(strtotime($this->marketing->reg_start_time)<=time()&&strtotime($this->marketing->reg_end_time)>=time()){
			$can_reg=true;
		}else{
			$can_reg=false;
			$reg_end=true;
		}
	   $can_vote=true;
		$voted=false;
		$votes = [];
		if(strtotime($this->marketing->vote_start_time)<=time()&&strtotime($this->marketing->vote_end_time)>=time()){
			$can_vote=true;
		}else{
			$can_vote=false;
			$vote_end=true;
		}
		if($this->user){
			foreach ($clubs as $club){
				if($club["uid"]==$this->user->uid){
					$can_reg=false;
					break;
				}
			}
			if($can_vote){
				$votes=MarketingVote::findAll(["marketing_id"=>$this->marketing->marketing_id,"uid"=>$this->user->uid]);
				if(count($votes)>0){
					$voted=true;
					$can_vote=false;
				}else{
					$voted=false;
				}
			}
		}
		
		CustomHelper::RetrunJson(["reg_end"=>$reg_end,"vote_end"=>$vote_end,"can_reg"=>$can_reg,"marketing"=>ArrayHelper::toArray($this->marketing),"clubs"=>$clubs,"can_vote"=>$can_vote,"voted"=>$voted]);
	}
	
	public function actionInfovote(){
		$clubs = ArrayHelper::toArray(Club::findMarketingClubs($this->marketing->marketing_id));
		$can_vote=true;
		$voted=false;
		$votes = [];
		if(strtotime($this->marketing->vote_start_time)<=time()&&strtotime($this->marketing->vote_end_time)>=time()){
			$votes=MarketingVote::findAll(["marketing_id"=>$this->marketing->marketing_id,"uid"=>$this->user->uid]);
			if(count($votes)>0){
				$voted=true;
				$can_vote=false;
			}else{
				$voted=false;
				$can_vote=true;
			}
		}else{
			$can_vote=false;
		}
		CustomHelper::RetrunJson(["can_vote"=>$can_vote,"marketing"=>ArrayHelper::toArray($this->marketing),"clubs"=>$clubs,"voted"=>$voted]);
	}
	
	public function actionVoterank(){
		$clubs = Club::findMarketingVoteClubs($this->marketing->marketing_id,$this->user->uid);
		
		$can_vote=true;
		if(strtotime($this->marketing->vote_start_time)<=time()&&strtotime($this->marketing->vote_end_time)>=time()){
			$can_vote=true;
		}else{
			$can_vote=false;
		}
		if(strtotime($this->marketing->valid_data_start_time)<=time()){
			$go_mileage=true;
		}else{
			$go_mileage=false;
		}
		$parent = 0;
		if(count($clubs)>0){
			$parent =$clubs[0]["like_sum"]; 
		}
		$myVotes=[];
		for($i=0;$i!=count($clubs);$i++){
			$club = $clubs[$i];
			if($parent>0){
				$club["percent"] = round($club["like_sum"]/$parent,2)*100;
			}
			$club["index"] = $i+1;
			if($club["vote_uid"]==$this->user->uid){
				array_push($myVotes, $club);
			}
			$clubs[$i]=$club;
		}
		CustomHelper::RetrunJson([
				                  "can_vote"=>$can_vote,
				                  "clubs"=>$clubs,
				                  "marketing"=>ArrayHelper::toArray($this->marketing,["paotuan_wechat\models\Marketing"=>[
				                  		"marketing_name",
				                  		"marketing_desc",
				                  		"marketing_icon",
				                  		"marketing_id"
				                  ]]),
				                  "my_votes"=>$myVotes,
				                  "go_mileage"=>$go_mileage
		]);
	}
	
	public function actionMileagerank(){
		$clubs = MarketingClub::mileageRank($this->marketing->marketing_id,$this->user->uid,date("Y-m-d",strtotime($this->marketing->valid_data_start_time)),date("Y-m-d H:i:s",strtotime($this->marketing->valid_data_end_time)));
		$parent = 0;
		if(count($clubs)>0){
			$parent =$clubs[0]["avg"];
		}
		$myVotes=[];
		$aviClubs = [];
		$inaviClubs=[];
		for($i=0;$i!=count($clubs);$i++){
			$club = $clubs[$i];
			$club["avg"] = round($club["avg"],2);
			$club["mileage"] = round($club["mileage"],2);
			if($club["u_count"]>=28){
				if($parent>0){
					$club["percent"] = round($club["avg"]/$parent,2)*100;
				}
				array_push($aviClubs, $club);
			}else{
				$club["avg"] = round($club["avg"],2);
				$club["percent"]=0;
				array_push($inaviClubs, $club);
			}
			
		}
		$clubs = array_merge($aviClubs,$inaviClubs);
		for($i=0;$i!=count($clubs);$i++){
			$club = $clubs[$i];
			$club["index"] = $i+1;
			$clubs[$i]=$club;
			if($club["vote_uid"]==$this->user->uid){
				array_push($myVotes, $club);
			}
		}
		CustomHelper::RetrunJson([
				"clubs"=>$clubs,
				"marketing"=>ArrayHelper::toArray($this->marketing,["paotuan_wechat\models\Marketing"=>[
						"marketing_name",
						"marketing_desc",
						"marketing_icon",
						"marketing_id"
				]]),
				"my_votes"=>$myVotes
		]);
		
	}
	
	public function actionClubvote(){
		$clubs = trim(\Yii::$app->request->post("clubs",""));
		if($clubs&&$this->user&&$this->marketing){
			if(strtotime($this->marketing->vote_start_time)<=time()&&strtotime($this->marketing->vote_end_time)>=time()){
				$mv =MarketingVote::findOne(["marketing_id"=>$this->marketing->marketing_id,"uid"=>$this->user->uid]);
				if($mv){
					$this->result["status"]=2;
					$this->result["msg"]="您已经投票";
				}else{
					$clubs = explode(",", $clubs);
					$trans = \Yii::$app->db->beginTransaction();
					$success = true;
					foreach ($clubs as $club){
						$mv = new MarketingVote();
						$mv->club_id = $club;
						$mv->marketing_id =$this->marketing->marketing_id;
						$mv->uid =  $this->user->uid;
						$mc = MarketingClub::findOne(["club_id"=>$club,"marketing_id"=>$this->marketing->marketing_id]);
						$mc->like_sum++;
						if(!$mv->save(false)||!$mc->save()){
							$trans->rollBack();
							$this->result["msg"]="系统错误";
							$success=false;
							break;
						}
					}
					if($success){
						$this->result["status"]=1;
						$trans->commit();
					}
				}
			}else{
				$this->result["msg"]="投票已结束";
			}
		}else{
			$this->result["msg"]="请选择投票的跑团";
		}
		CustomHelper::RetrunJson($this->result);
	}
	
	public function actionClubreg(){
		$member_sum = $this->club->member_sum;
		if(strtotime($this->marketing->reg_start_time)<=time()&&strtotime($this->marketing->reg_end_time)>=time()){
			if($this->marketing->club_member_min!=0){
				if($member_sum>$this->marketing->club_member_min){
					$this->result["msg"]="跑团人数必须大于".$this->marketing->club_member_min."人";
					CustomHelper::RetrunJson($this->result);
					return;
				}
			}
			$mc = MarketingClub::findOne(["marketing_id"=>$this->marketing->marketing_id,"club_id"=>$this->club->clubid]);
			if(!$mc){
				if($this->user){
					if($this->user->uid!=$this->club->uid){
						$this->result["msg"]="只有团长才能报名";
					}else{
						$mc = new MarketingClub();
						$mc->marketing_id = $this->marketing->marketing_id;
						$mc->club_id = $this->club->clubid;
						$mc->uid = $this->user->uid;
						if($mc->save(false)){
							$this->result["status"]=1;
						}else{
							$this->result["msg"]="系统错误!";
						}
					}
				}
			}else{
				$this->result["status"]=1;
			}
		}else{
			$this->result["msg"]="报名已结束";
		}
		
	    CustomHelper::RetrunJson($this->result);
	}
}
