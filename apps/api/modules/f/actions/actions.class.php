<?php
class fActions extends opJsonApiActions
{
  public function executeUpload(sfWebRequest $request)
  {
    $f = new File();
    $f->setOriginalFilename(time()."FILENAME.txt");
    $f->setType("application/octet-stream");
    $f->setName("/m1/".time()."FILENAME.txt");
    $f->setFilesize("100");

    $bin = new FileBin();
    $bin->setBin("aaaaaaaaaabbbbbbbbbbaaaaaaaaaabbbbbbbbbbaaaaaaaaaabbbbbbbbbbaaaaaaaaaabbbbbbbbbbaaaaaaaaaabbbbbbbbbb");
    $f->setFileBin($bin);

    $f->save();
    return $this->renderText("DONE");
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
