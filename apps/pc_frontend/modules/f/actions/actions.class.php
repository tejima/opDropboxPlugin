<?php

/**
 * dropbox actions.
 *
 * @package    OpenPNE
 * @subpackage dropbox
 * @author     Your name here
 */
class fActions extends sfActions
{

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
