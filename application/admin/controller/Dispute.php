<?php
namespace app\admin\controller;
use app\admin\model\Dispute as DisputeModel; 
/**
 * 纠纷管理
 * Class Dispute
 * @package app\admin\controller
 */
class Dispute extends Controller
{
   public function dispute_list(){ 
	   $dispute_model=new DisputeModel;
	   return $this->renderSuccess($dispute_model->dispute_list());
	   
   }
}
