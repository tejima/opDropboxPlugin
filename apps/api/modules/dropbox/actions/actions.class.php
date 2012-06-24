<?php
class dropboxActions extends opJsonApiActions
{

  public function getDropbox(){
    $oauth = new Dropbox_OAuth_PEAR(sfConfig::get('app_opdropboxplugin_consumer'),sfConfig::get('app_opdropboxplugin_consumer_secret'));
    $oauth->setToken(opConfig::get('opdropboxplugin_oauth_token'),opConfig::get('opdropboxplugin_oauth_token_secret'));

    $dropbox = new Dropbox_API($oauth,'sandbox');
    return $dropbox;
  }

  public function executeDelete(sfWebRequest $request)
  {
    $path = $request->getParameter("path");
    $dropbox = $this->getDropbox();
    $delete_ok = false;
    if(preg_match('/^\/m(\d+)/',$path,$match)){
      $path_member_id = $match[1];
      //member mode
      $member_id = $this->getUser()->getMember()->getId();
      if($path_member_id != $member_id){
        return $this->renderJSON(array('status' => 'error','message' => 'you can delete only your own directary. ' . $path));
      }
      $delete_ok = true;
    }
    else if(preg_match('/^\/c(\d+)/',$path,$match)){
      $path_community_id = $match[1];
      $community = Doctrine::getTable("Community")->find($community_id);
      if(!$community->isAdmin($this->getUser()->getMember()->getId())){
        return $this->renderJSON(array('status' => 'error','message' => 'only community_admin can delete community directary. ' . $path));
      }
      $delete_ok = true;
    }
    if($is_ok){
      $response = $dropbox->delete($path);
      return $this->renderJSON(array('status' => 'success','data' => $response));
    }
  }

  public function executeList(sfWebRequest $request)
  {
    $path = $request->getParameter("path");
    $dropbox = $this->getDropbox();
    try{
      $response = $dropbox->getMetaData($path);
    }catch(Exception $e){
      return $this->renderJSON(array('status' => 'error','message' => $e));
    }
    return $this->renderJSON(array('status' => 'success','data' => $response));
  }

  public function executeFiles(sfWebRequest $request)
  {
    //TODO add exclusive community file dl function.
    $path = $request->getParameter("path");
    $dropbox = $this->getDropbox();
    $data = $dropbox->getFile($path);

    if(!$data){
      return $this->renderJSON(array('status' => 'error','message' => "Dropbox file download error"));
    }

    $filename = substr($path,5);

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $type = $finfo->buffer($data);
    $this->getResponse()->setHttpHeader('Content-Type',$type);
    //if(strpos($type,'application') !== FALSE || $type == "text/x-php"){
      $this->getResponse()->setHttpHeader('Content-Disposition','attachment; filename="'.$filename.'"');
    //}
    return $this->renderText($data);
  }
  public function executeShare(sfWebRequest $request)
  {
   
    $dropbox = $this->getDropbox();
    $path = $request->getParameter("path");
    $response = $dropbox->share($path);
    return $this->renderJSON(array('status' => 'success','data' => $response));
  }
  public function executeUpload(sfWebRequest $request)
  {
    $filename = basename($_FILES['upfile']['name']);
    if(!$filename){
      return $this->renderJSON(array('status' => 'error' ,'path' => $response, 'message' => "null file"));
    }

    $community_id = (int)$request->getParameter("community_id");
    if((int)$community_id >= 1){
      $community = Doctrine::getTable("Community")->find($community_id);
      if(!$community->isPrivilegeBelong($this->getUser()->getMember()->getId())){
        return $this->renderJSON(array('status' => 'error' ,'message' => "you are not this community member."));
      }
      
      $dirname = '/c'. $community_id;
    }else{

      $dirname = '/m'. $this->getUser()->getMember()->getId(); 
    }
   
    //validate $filepath
    if(!preg_match('/^\/m[0-9]+/',$dirname)){
      return $this->renderJSON(array('status' => 'error' ,'message' => "file path error. " . $dirname));
    }
    $dropbox = $this->getDropbox();
    try{
      $response = $dropbox->createFolder($dirname);
    }catch(Exception $e){
      error_log($e, 3, "/tmp/bootstrap.log"); 
    }
    $response = $dropbox->putFile($dirname .'/'.$filename , $_FILES['upfile']['tmp_name']);
    if($response === true){
      return $this->renderJSON(array('status' => 'success' , 'message' => "file up success " . $response));
    }else{
      return $this->renderJSON(array('status' => 'error','message' => "Dropbox file upload error"));
    }
  }
}
