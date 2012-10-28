<?php

namespace Lacus\MainBundle\Post;

use Lacus\MainBundle\Entity\Post;
use Buzz\Message\Form\FormUpload;
use Buzz\Message\Response;
use Buzz\Util\Url;
use Symfony\Component\DomCrawler\Crawler;
use Buzz\Message\Request;
use Buzz\Util\CookieJar;
use Lacus\MainBundle\Entity\Site;
use Lacus\MainBundle\Entity\Account;
use Buzz\Browser;

class Poster
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
        $submitRequest = $this->browser->getMessageFactory()->createFormRequest($form->getMethod());
        $submitRequest->setFields($form->getPhpValues());
        foreach ($post->getFileFields() as $fileFieldName => $file) {
            /** @var $file \Lacus\MainBundle\Entity\File */
            if ($form->has($fileFieldName)) {
                // @TODO move this somewhere else
                $file->upload();
                $formFile = new FormUpload($file->getAbsolutePath(), $file->getMimetype());
                $formFile->setName($file->getFileName());
                $submitRequest->setField($fileFieldName, $formFile);
            }
        }
        $submitUrl->applyToRequest($submitRequest);
        $this->cookieJar->addCookieHeaders($submitRequest);
        $submitResponse = new Response();
        $this->browser->send($submitRequest, $submitResponse);
    }

    public function loginToSite(Account $account, Site $site)
    {
        /** @var $response Response */
        $response = $this->browser->get($site->getLoginUrl());
        $this->cookieJar->processSetCookieHeaders(new Request(), $response);
        $crawler = new Crawler($response->getContent(), $site->getLoginUrl());
        $loginForm = $crawler->selectButton($site->getLoginButton())->form(array(
            $site->getLoginUsername() => $account->getUsername(),
            $site->getLoginPassword() => $account->getPassword(),
        ));

        $loginUrl = new Url($loginForm->getUri());
        $loginRequest = $this->browser->getMessageFactory()->createFormRequest($loginForm->getMethod());
        $loginRequest->setFields($loginForm->getPhpValues());
        $loginUrl->applyToRequest($loginRequest);

        $this->browser->getClient()->setMaxRedirects(0);
        $loginResponse = new Response();
        $this->browser->send($loginRequest, $loginResponse);
        $this->cookieJar->processSetCookieHeaders($loginRequest, $loginResponse);
    }

    public function getSubmitForm($submitUrl, $submitButton)
    {
        $submitRequest = new Request();
        $submitResponse = new Response();
        $submitUrlObject = new Url($submitUrl);
        $submitUrlObject->applyToRequest($submitRequest);
        $this->cookieJar->addCookieHeaders($submitRequest);
        $this->browser->send($submitRequest, $submitResponse);

        $submitCrawler = new Crawler($submitResponse->getContent(), $submitUrl);
        $submitForm = $submitCrawler->selectButton($submitButton)->form();
        return $submitForm;
    }
}