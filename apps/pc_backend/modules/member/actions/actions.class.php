<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * member actions.
 *
 * @package    OpenPNE
 * @subpackage member
 * @author     Kousuke Ebihara <ebihara@tejimaya.com>
 */
class memberActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('member', 'list');
  }

 /**
  * Executes list action
  *
  * @param sfRequest $request A request object
  */
  public function executeList(sfWebRequest $request)
  {
    $params = $request->getParameter('member', array());

    $this->form = new opMemberProfileSearchForm();
    $this->form->bind($params);

    $this->pager = new sfPropelPager('Member', 20);
    if ($params)
    {
      $this->pager->setCriteria($this->form->getCriteria());
    }
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();

    $this->profiles = ProfilePeer::retrievesAll();

    return sfView::SUCCESS;
  }

 /**
  * Executes invite action
  *
  * @param sfRequest $request A request object
  */
  public function executeInvite(sfWebRequest $request)
  {
    $this->plugins = opInstalledPluginManager::getAdminInviteAuthPlugins();
    if (empty($this->plugins))
    {
      return sfView::ERROR;
    }

    $options = array(
      'authModes' => $this->plugins,
      'is_link' => false,
    );
    $this->form = new AdminInviteForm(null, $options);

    if ($request->isMethod(sfWebRequest::POST))
    {
      $this->form->bind($request->getParameter('member_config'));
      if ($this->form->isValid())
      {
        $this->form->save();
        $this->redirect('member/invite');
      }
    }

    return sfView::SUCCESS;
  }

 /**
  * Executes blacklist action
  *
  * @param sfRequest $request A request object
  */
  public function executeBlacklist(sfWebRequest $request)
  {
    $this->pager = new sfPropelPager('Blacklist', 20);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();

    $this->form = new BlacklistForm();
    if ($request->isMethod(sfWebRequest::POST))
    {
      $result = $this->form->bindAndSave($request->getParameter('blacklist'));
      $this->redirectIf($result, 'member/blacklist');
    }

    return sfView::SUCCESS;
  }

 /**
  * Executes blacklistDelete action
  *
  * @param sfRequest $request A request object
  */
  public function executeBlacklistDelete(sfWebRequest $request)
  {
    $this->blacklist = BlacklistPeer::retrieveByPK($request->getParameter('id'));
    $this->forward404Unless($this->blacklist);

    $this->form = new sfForm();
    if ($request->isMethod(sfWebRequest::POST))
    {
      $field = sfForm::getCSRFFieldName();
      $this->form->bind(array($field => $request->getParameter($field)));
      if ($this->form->isValid())
      {
        $this->blacklist->delete();
        $this->redirect('member/blacklist');
      }
    }

    return sfView::SUCCESS;
  }
}