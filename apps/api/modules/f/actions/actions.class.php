<?php
class fActions extends opJsonApiActions
{
  public function executeUpload(sfWebRequest $request)
  {
    $filename = basename($_FILES['upfile']['name']);
    if(!$filename){
      return $this->renderJSON(array('status' => 'error' , 'message' => "null file"));
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
    if(!preg_match('/^\/[mc][0-9]+/',$dirname)){
      return $this->renderJSON(array('status' => 'error' ,'message' => "file path error. " . $dirname));
    }
    
    $f = new File();
    $f->setOriginalFilename($_FILES['upfile']['name']);
    $f->setType($_FILES['upfile']['type']);
    $f->setName($dirname."/".time().$_FILES['upfile']['tmp_name']);
    $f->setFilesize($_FILES['upfile']['size']);
    if($stream = fopen($_FILES['upfile']['tmp_name']){
      $bin = new FileBin();
      $bin->setBin(stream_get_contents($stream));
      $f->setFileBin($bin);
      $f->save();
      $response = true;
    }else{
      //file open error
      $response = false;
    }

    if($response === true){
      return $this->renderJSON(array('status' => 'success' , 'message' => "file up success " . $response));
    }else{
      return $this->renderJSON(array('status' => 'error','message' => "Dropbox file upload error"));
    }


  }
  public function executeList(sfWebRequest $request)
  {
    $file_list = Doctrine_Query::create()
      ->from('File f')
      ->where('f.name LIKE ?','/m1/%')
      ->fetchArray();
     
    return $this->renderText(print_r($file_list,true));
  }
  public function executeFiles(sfWebRequest $request)
  {
    $path = $request->getParameter("path");
    if(!$path){
      $path = "/m1/1340943961FILENAME.txt";
    }
    //TODO アクセス権限管理

    $file = Doctrine::getTable("File")->findOneByName($path);
    
    $filebin = $file->getFileBin();
    $data = $filebin->getBin();

    if(!$data){
      return $this->renderJSON(array('status' => 'error','message' => "Dropbox file download error"));
    }

    $filename = substr($path,strpos($path,"/",1));

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $type = $finfo->buffer($data);
    $this->getResponse()->setHttpHeader('Content-Type',$type);
    //if(strpos($type,'application') !== FALSE || $type == "text/x-php"){
      $this->getResponse()->setHttpHeader('Content-Disposition','attachment; filename="'.$filename.'"');
    //}
    return $this->renderText($data);
  }

}
