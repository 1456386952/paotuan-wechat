<?php
namespace common\component;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use yii\base\Object;
use yii\base\Exception;
require \Yii::getAlias("@vendor")."/phpmailer/PHPMailerAutoload.php"; 
/**
 * Description of MailManage
 *
 * @author wubaoxin
 */
class MailManage extends Object
{
    protected $Mail;
    //put your code here
    public function init() {
        parent::init();
        $this->Mail=new \PHPMailer();
        $this->Mail->CharSet = "UTF-8"; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码   
        $this->Mail->IsSMTP(); // 设定使用SMTP服务
        //$this->Mail->SMTPDebug  = 1;                     // 启用SMTP调试功能   
        // 1 = errors and messages   
        // 2 = messages only   
        $this->Mail->SMTPAuth = true;                  // 启用 SMTP 验证功能   
        //$this->Mail->SMTPSecure = "ssl";                 // 安全协议   
        $this->Mail->Host = "smtp.landc-consulting.com";      // SMTP 服务器     
//         $this->Mail->Username = "keycode@landc-consulting.com";  // SMTP服务器用户名   
        $this->Mail->Username = "services@landc-consulting.com";
//         $this->Mail->Password = "Run12345678";            // SMTP服务器密码   
        $this->Mail->Password = "Running2015";
        $this->Mail->SetFrom('services@landc-consulting.com', '跑步去');
        //$this->Mail->AddReplyTo('irunner@runningtogether.net',"小i爱跑步"); 
        $this->Mail->IsHTML(true); //是否使用HTML格式 
    }
    
    public function getMail()
    {
        return $this->Mail;
    }
    
    // 发送绑定邮件
    public function SendBindEmail($uid,$email,$bind_code)
    {
        $this->Mail->Subject = '跑步去APP账号邮箱绑定验证发送';
        $en_uid = base64_encode($uid);
        $en_email = base64_encode($email);
        $en_bind_code = base64_encode($bind_code);
        $url = \Yii::$app->params['site_url']."bindemail?uid=".$en_uid."&email=".$en_email."&bindCode=".$en_bind_code;
        $body='跑步去E-mail绑定验证：'.$url;
        $this->Mail->MsgHTML($body);
        $this->Mail->AddAddress($email, "收件人：");
        if (!$this->Mail->Send()){
            throw new Exception($this->Mail->ErrorInfo,1);
        }
        return TRUE;
    }
    
    /**
     * 发送报名成功邮件
     * @param array $param
     */
    public function SendRegisterSuccEmail($param)
    {
        $this->Mail->Subject = $param["title"];
        $this->Mail->MsgHTML($param["body"]);
        $this->Mail->AddAddress($param["email"], "收件人：");
        $this->Mail->Send();
    }
    
    /**
     * 发送报名通道邮件
     * @param array $param
     * @throws Exception
     * @return boolean
     */
    public function SendChannelModule($param)
    {
        $this->Mail->Subject = $param["title"];
        $this->Mail->MsgHTML($param["body"]);
        $this->Mail->addAttachment($param['attachment_url'],$param['attachment_name']);
        $this->Mail->AddAddress($param["email"], "收件人：");
        if (!$this->Mail->Send()){
            throw new Exception($this->Mail->ErrorInfo,1);
        } 
        return TRUE;
    }
}
