<?php
class fComponents extends sfComponents
{


  public function executeFBox()
  {
    $this->member = $this->getUser()->getMember();
  }

}
