<?php

namespace Lacus\MainBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Lacus\MainBundle\Form\Type\MapperConfigureFormType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Lacus\MainBundle\Entity\Account;
use Symfony\Component\Yaml\Yaml;
use JMS\DiExtraBundle\Annotation as DI;

class MapperAdminController extends CRUDController
{
    /**
     * @DI\Inject("lacus.content_provider.pool")
     *
     * @var \Lacus\MainBundle\Content\ProviderPool
     */
    protected $pool;

    /**
     * @DI\Inject("session")
     *
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $session;

    public function configureAction($id)
    {
        // the key used to lookup the template
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        /** @var $object \Lacus\MainBundle\Entity\Mapper */

        $provider = $this->pool->getProvider($object->getProvider());
        $contentTemplate = $provider->getContentTemplate();

        $this->admin->setSubject($object);

        $form = $this->createForm(new MapperConfigureFormType(), $object);

        if ($this->getRequest()->isMethod('post')) {
            $form->bind($this->getRequest());
            if ($form->isValid()) {
                $this->admin->update($object);
                $this->session->getFlashBag()->add('sonata_flash_success', 'flash_edit_success');
            }
        }

        return $this->render('MainBundle:MapperAdmin:configure.html.twig', array(
            'action' => 'configure',
            'object' => $object,
            'form' => $form->createView(),
            'content_template' => $contentTemplate,
        ));
    }

    public function getFieldsAction()
    {
        $mapperId = $this->getRequest()->request->getInt('id');
        $accountId = $this->getRequest()->request->getInt('account');

        $em = $this->getDoctrine()->getManager();
        /** @var $account \Lacus\MainBundle\Entity\Account */
        $account = $em->find('MainBundle:Account', $accountId);
        /** @var $mapper \Lacus\MainBundle\Entity\Mapper */
        $mapper = $em->find('MainBundle:Mapper', $mapperId);

        if ($mapper->getSite() === null) {
            return $this->renderJson(array(
                'error' => "This mapper doesn't have an assigned site.",
            ));
        }

        /** @var $poster \Lacus\MainBundle\Post\Poster */
        $poster = $this->get('lacus.post.poster');

        if ($mapper->getLoginRequired()) {
            if ($account === null) {
                return $this->renderJson(array(
                    'error' => "This mapper requires an account info to login.",
                ));
            }
            $poster->loginToSite($account, $mapper->getSite());
        }

        $submitForm = $poster->getSubmitForm($mapper->getSubmitUrl(), $mapper->getSubmitButton());

        $formData = array(
            'fields' => $submitForm->getValues(),
        );
        if ($submitForm->getFiles()) {
            $formData['files'] = $submitForm->getFiles();
        }

        foreach ($formData['fields'] as $fieldId => $fieldValue) {
            $formData['fields'][$fieldId] = array(
                'segment' => array(
                    'name' => $fieldId,
                    'options' => array(),
                ),
                'field' => array(),
            );
        }

        $data = Yaml::dump($formData, 4, 2);

        return $this->renderJson($data);
    }
}