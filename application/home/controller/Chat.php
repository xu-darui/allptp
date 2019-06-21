<?php 
namespace app\home\controller; 
use think\Db;
use app\common\exception\BaseException;  
use app\common\model\Chat as ChatModel;  
use \GatewayClient\Gateway;
/**
 * 聊天
 * Class Chat
 * @package app\api\controller
 */
class Chat extends Controller
{
protected $registerAddress='192.168.0.112:1238'; 

	public function register($client_id){ 
		$userdata=$this->getuser();
		 //绑定端口
        Gateway::$registerAddress = $this->registerAddress; 
        Gateway::bindUid($client_id, intval($userdata['user_id']));
	    //$a= Gateway::isOnline($data['client_id']) ;
		// Gateway::sendToAll("chenggong");
		// 加入某个群组（可调用多次加入多个群组）
	  //Gateway::joinGroup($client_id, $group_id);
	}
	
	public  function sendmessage($to_user_id,$content){
		$userdata=$this->getuser();
		Gateway::$registerAddress = $this->registerAddress; 
		$chat_model=new ChatModel;
		$chat_model->allowField(true)->save(['to_user_id'=>$to_user_id,'content'=>$content,'user_id'=>$userdata['user_id']]);
		// 向任意uid的网站页面发送数据
		Gateway::sendToUid(intval($to_user_id),json_encode(['type'=>'send_msg','msg_id'=>$chat_model->msg_id,'to_user_id'=>$to_user_id,'content'=>$content,'create_time'=>date("Y-m-d H:i:s")]));
	}

	public function readmessage($msg_id){
		$this->getuser();
		Gateway::$registerAddress = $this->registerAddress; 
		$chat_model=new ChatModel;
		$chat=$chat_model->where(['msg_id'=>$msg_id])->find();
		$chat_model->where(['msg_id'=>$chat['msg_id']])->update(['read_time'=>time(),'isread'=>1]);
		Gateway::sendToUid(intval($chat['user_id']),json_encode(['type'=>'is_read','msg_id'=>[$chat['msg_id']]]));

	}

	public function msg_list($to_user_id,$page=1){
		$userdata=$this->getuser();
		$chat_model=new ChatModel;
		$data=$chat_model->where(function($query) use($userdata,$to_user_id){$query->where(['user_id'=>$userdata['user_id'],'to_user_id'=>$to_user_id]);})->whereor(function($query) use($userdata,$to_user_id){$query->where(['to_user_id'=>$userdata['user_id'],'user_id'=>$to_user_id]);})->order('create_time desc')->paginate(20, false, ['query' => ["page"=>$page]]);
		//pre($data);
		$no_read_id=[]; 
		foreach($data as $key=>$value){
			//echo $value['to_user_id'].'----'.$userdata['user_id'].'------'.$value['isread'].'#';
			
			if($value['to_user_id']==$userdata['user_id']&&$value['isread']==0&&$value['status']==0){
				
				array_push($no_read_id,$value['msg_id']);
			}
		}
		if($no_read_id){
			$chat_model->where(['msg_id'=>['in',$no_read_id]])->update(['isread'=>1,'read_time'=>time()]);
		}
		
		//var_dump($chat_model->getlastsql());exit;
		//推送已读信息给发送信息者-----未写
		$data=$chat_model->with(['user.headimage','touser.headimage'])->where(function($query) use($userdata,$to_user_id){$query->where(['user_id'=>$userdata['user_id'],'to_user_id'=>$to_user_id]);})->whereor(function($query) use($userdata,$to_user_id){$query->where(['to_user_id'=>$userdata['user_id'],'user_id'=>$to_user_id]);})->order('create_time desc')->paginate(20, false, ['query' => ["page"=>$page]]);
		if($no_read_id){
			Gateway::$registerAddress = $this->registerAddress; 
			Gateway::sendToUid(intval($to_user_id),json_encode(['type'=>'is_read','msg_id'=>$no_read_id]));
		} 
		return $this->renderSuccess($data); 

	}

	public function msg_callback($msg_id){  
		if($msg_id){
			$msg_id=json_decode($msg_id,true); 
			$chat_model=new ChatModel;
			$chat=$chat_model->where(['msg_id'=>$msg_id[0]])->find();  
			$chat_model->where(['msg_id'=>['in',$msg_id]])->update(['status'=>1]);
			Gateway::$registerAddress = $this->registerAddress; 
			Gateway::sendToUid(intval($chat['to_user_id']),json_encode(['type'=>'is_del','msg_id'=>$msg_id]));
			return $this->renderSuccess("撤回成功"); 
		}
		
	}	
	public function my_msg_list(){
		$userdata=$this->getuser();
		$chat_model=new ChatModel;
		$data=$chat_model->with(['user.headimage'])->where(['to_user_id'=>$userdata['user_id'],'status'=>0])->group('user_id')->order('create_time desc')->paginate(8, false, ['query' => ["page"=>input('page')==''?0:input('page')]]);
		foreach($data as $key=>$value){
			$value['long_age']=long_ago($value['create_time']);
			$value['noread_count']=$chat_model->where(['to_user_id'=>$userdata['user_id'],'user_id'=>$value['user_id'],'isread'=>0])->count();
			$data[$key]=$value;
		}
		return $this->renderSuccess($data);
	}
	



}