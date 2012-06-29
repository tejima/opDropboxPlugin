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
}
