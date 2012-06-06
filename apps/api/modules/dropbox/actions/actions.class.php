<?php
class dropboxActions extends opJsonApiActions
{

  public function getDropbox(){
    $oauth = new Dropbox_OAuth_PEAR(sfConfig::get('app_consumer'),sfConfig::get('app_consumer_secret'));
    $oauth->setToken(sfConfig::get('app_token'),sfConfig::get('app_token_secret'));

    $dropbox = new Dropbox_API($oauth);
    return $dropbox;
  }

  public function executeList(sfWebRequest $request)
  {
    $dropbox = $this->getDropbox();
    $response = $dropbox->getMetaData('/PNE/');
    return $this->renderJSON(array('status' => 'success','data' => $response));
  }
  public function executeFiles(sfWebRequest $request)
  {
    $path = $request->getParameter("path");
    if(strpos($path, "/PNE/") !== 0){
      return $this->renderJSON(array('status' => 'error','message' => 'only accept /PNE/ directory,' . $path));
    }
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
    if(strpos($path, "/PNE") !== 0){
      return $this->renderJSON(array('status' => 'error','message' => 'only accept /PNE/ directory,' . $path));
    }
    $response = $dropbox->share($path);
    return $this->renderJSON(array('status' => 'success','data' => $response));
  }
  public function executeUpload(sfWebRequest $request)
  {
    $filename = basename($_FILES['upfile']['name']);
    if(!$filename){
      return $this->renderJSON(array('status' => 'error' ,'path' => $response, 'message' => "null file"));
    }
    $dropbox = $this->getDropbox();

    $response = $dropbox->putFile('/PNE/'.$filename, $_FILES['upfile']['tmp_name']);
    if($response === true){
      //$response = $dropbox->share('/PNE/'.$filename);
      return $this->renderJSON(array('status' => 'success' , 'message' => "file up success"));
    }else{
      return $this->renderJSON(array('status' => 'error','message' => "Dropbox file upload error"));
    }
  }
}
