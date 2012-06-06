<?php

/**
 * dropbox actions.
 *
 * @package    OpenPNE
 * @subpackage dropbox
 * @author     Your name here
 */
class dropboxActions extends sfActions
{
  public function getDropbox(){
    $oauth = new Dropbox_OAuth_PEAR(sfConfig::get('app_consumer'),sfConfig::get('app_consumer_secret'));
    $oauth->setToken(sfConfig::get('app_token'),sfConfig::get('app_token_secret'));

    $dropbox = new Dropbox_API($oauth);
    return $dropbox;
  }
 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $path = $request->getParameter("path");
    if(strpos($path, "/PNE/") !== 0){
      return $this->renderText(json_encode(array('status' => 'error','message' => 'only accept /PNE/ directory,' . $path)));
    }
    $dropbox = $this->getDropbox();
    try{
      $data = $dropbox->getFile($path);
    }catch(Exception $e)
    {
      return $this->renderText(json_encode(array('status' => 'error','message' => 'Dropbox connection Error' . $path)));
    }
    if(!$data){
      return $this->renderText(json_encode(array('status' => 'error','message' => "Dropbox file download error")));
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
}
