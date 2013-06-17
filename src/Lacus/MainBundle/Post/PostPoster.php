<?php

namespace Lacus\MainBundle\Post;

use Lacus\MainBundle\Entity\Post;
use Lacus\MainBundle\Post\Exception\LoginFormNotFoundException;
use Lacus\MainBundle\Post\Exception\SubmitFormNotFoundException;
use Buzz\Message\Form\FormUpload;
use Buzz\Message\Response;
use Buzz\Util\Url;
use Symfony\Component\DomCrawler\Crawler;
use Buzz\Message\Request;
use Buzz\Util\CookieJar;
use Lacus\MainBundle\Entity\Site;
use Lacus\MainBundle\Entity\Account;
use Buzz\Browser;

class PostPoster
{
    private $browser;

    public function __construct(Browser $browser)
    {
        $this->browser = $browser;
        $this->cookieJar = new CookieJar();
    }

    public function post(Post $post)
    {
        $mapper = $post->getMapper();
        if ($mapper->getLoginRequired()) {
            $this->loginToSite($post->getAccount(), $mapper->getSite());
        }
        $form = $this->getSubmitForm($mapper->getSubmitUrl(), $mapper->getSubmitButton());

        foreach ($post->getContentFields() as $fieldName => $fieldValue) {
            if ($form->has($fieldName)) {
                $form[$fieldName] = $fieldValue;
            }
        }

        $submitUrl = new Url($form->getUri());
        $request = $this->browser->getMessageFactory()->createFormRequest($form->getMethod());
        $request->setFields($form->getPhpValues());
        foreach ($post->getFileFields() as $fileFieldName => $file) {
            /** @var $file \Lacus\MainBundle\Entity\File */
            if ($form->has($fileFieldName)) {
                $formFile = new FormUpload($file->getAbsolutePath(), $file->getMimetype());
                $formFile->setName($file->getFileName());
                $request->setField($fileFieldName, $formFile);
            }
        }
        $submitUrl->applyToRequest($request);
        $this->cookieJar->addCookieHeaders($request);
        $response = new Response();
        $this->browser->send($request, $response);

        return $response;
    }

    public function loginToSite(Account $account, Site $site)
    {
        /** @var $response Response */
        $response = $this->browser->get($site->getLoginUrl());
        $this->cookieJar->processSetCookieHeaders(new Request(), $response);
        $content = $response->getContent();
        $crawler = new Crawler($content, $site->getLoginUrl());
        try {
            $loginButton = $crawler->selectButton($site->getLoginButton());
            $loginForm = $loginButton->form(
                array(
                    $site->getLoginUsername() => $account->getUsername(),
                    $site->getLoginPassword() => $account->getPassword(),
                )
            );
        } catch (\InvalidArgumentException $e) {
            die(json_encode(array('m'=>$content)));
            throw new LoginFormNotFoundException();
        }

        $loginUrl = new Url($loginForm->getUri());
        $loginRequest = $this->browser->getMessageFactory()->createFormRequest($loginForm->getMethod());
        $loginRequest->setFields($loginForm->getPhpValues());
        $loginUrl->applyToRequest($loginRequest);

        $this->browser->getClient()->setMaxRedirects(0);
        $loginResponse = new Response();
        $this->browser->send($loginRequest, $loginResponse);
        $this->cookieJar->processSetCookieHeaders($loginRequest, $loginResponse);
    }

    public function getSubmitForm($url, $submitButton)
    {
        $request = new Request();
        $response = new Response();
        $submitUrl = new Url($url);
        $submitUrl->applyToRequest($request);
        $this->cookieJar->clearExpiredCookies();
        $this->cookieJar->addCookieHeaders($request);
        $this->browser->send($request, $response);

        $submitCrawler = new Crawler($response->getContent(), $url);
        try {
            $submitForm = $submitCrawler->selectButton($submitButton)->form();
        } catch (\InvalidArgumentException $e) {
            throw new SubmitFormNotFoundException();
        }

        return $submitForm;
    }
}
