<?php

/**
 * opDropboxPlugin actions.
 *
 * @package    OpenPNE
 * @subpackage opDropboxPlugin
 * @author     Your name here
 */
class opDropboxPluginActions extends sfActions
{
  
  private function getConfig(){
    sfContext::getInstance()->getConfiguration()->loadHelpers('Url');
    return array(
      'callbackUrl' => sfConfig::get('op_base_url') . url_for("opDropboxPlugin/callback"),
      'siteUrl' => 'https://api.dropbox.com/1/oauth',
      'consumerKey' => sfConfig::get("app_opdropboxplugin_consumer"),
      'consumerSecret' => sfConfig::get("app_opdropboxplugin_consumer_secret"),
    );
  }
  
 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    if($request->getAttribute("message")){
      $this->message = $request->getAttribute("message");
      return;
    }
    
    if(opConfig::get("opdropboxplugin_oauth_token")){
      $this->message = "OAuth Token = OK";
    }else{
      $this->message = "OAuth Token not set. Please set first";
    }
  }

  public function executeAuth(sfWebRequest $request)
  {
    $consumer = new Zend_Oauth_Consumer($this->getConfig());
    $token = $consumer->getRequestToken();
    $_SESSION['TWITTER_REQUEST_TOKEN'] = serialize($token);
    $consumer->redirect();
  }

  public function executeCallback(sfWebRequest $request)
  {
    $consumer = new Zend_Oauth_Consumer($this->getConfig());
    if(!empty($_GET) && isset($_SESSION['TWITTER_REQUEST_TOKEN'])){
      $token = $consumer->getAccessToken(
        $_GET,
        unserialize($_SESSION['TWITTER_REQUEST_TOKEN'])
      );
      $_SESSION['TWITTER_ACCESS_TOKEN'] = serialize($token);
      $_SESSION['TWITTER_REQUEST_TOKEN'] = null;
      Doctrine::getTable("SnsConfig")->set("opdropboxplugin_oauth_token",$token->oauth_token);
      Doctrine::getTable("SnsConfig")->set("opdropboxplugin_oauth_token_secret",$token->oauth_token_secret);
      $this->getRequest()->setAttribute('message', 'TOKEN SUCCESS.');
      $this->forward("opDropboxPlugin", "index"); 
    }else{
      return sfView::ERROR;
    }
  }
}
