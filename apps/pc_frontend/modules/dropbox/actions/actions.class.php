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
    $oauth = new Dropbox_OAuth_PEAR(sfConfig::get('app_opdropboxplugin_consumer'),sfConfig::get('app_opdropboxplugin_consumer_secret'));
    $oauth->setToken(opConfig::get('opdropboxplugin_oauth_token'),opConfig::get('opdropboxplugin_oauth_token_secret'));

    $dropbox = new Dropbox_API($oauth,'sandbox');
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
    $dropbox = $this->getDropbox();
    try{
      $data = $dropbox->getFile($path);
    }catch(Exception $e)
    {
      return $this->renderText(json_encode(array('status' => 'error','message' => 'Dropbox connection Error' . $path .$e->getMessage())));
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
