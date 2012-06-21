<?php
class dropboxComponents extends sfComponents
{


  public function executeDropboxBox()
  {
    $this->member = $this->getUser()->getMember();
  }

}
