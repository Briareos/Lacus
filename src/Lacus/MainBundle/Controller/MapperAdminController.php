<?php

namespace Lacus\MainBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Lacus\MainBundle\Post\Exception\LoginFormNotFoundException;
use Lacus\MainBundle\Post\Exception\SubmitFormNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Lacus\MainBundle\Form\Type\MapperConfigureFormType;
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

    /**
     * @DI\Inject("doctrine.orm.default_entity_manager")
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function configureAction($id)
    {
        // the key used to lookup the template
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException('Mapper not found.');
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

                return $this->redirect($this->admin->generateObjectUrl('configure', $object));
            }
        }

        return $this->render(
            'MainBundle:MapperAdmin:configure.html.twig',
            array(
                'action' => 'configure',
                'object' => $object,
                'form' => $form->createView(),
                'content_template' => $contentTemplate,
            )
        );
    }

    public function getFieldsAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new HttpException(400, 'This method is only available via XML-HTTP request.');
        }

        $mapperId = $this->getRequest()->request->getInt('id');
        $accountId = $this->getRequest()->request->getInt('account');


        /** @var $account \Lacus\MainBundle\Entity\Account */
        $account = $this->em->find('MainBundle:Account', $accountId);
        /** @var $mapper \Lacus\MainBundle\Entity\Mapper */
        $mapper = $this->em->find('MainBundle:Mapper', $mapperId);

        if ($mapper === null) {
            throw $this->createNotFoundException('Mapper not found.');
        }

        if ($mapper->getSite() === null) {
            return $this->renderJson(
                array(
                    'status' => "KO",
                    'message' => "This mapper doesn't have an assigned site.",
                )
            );
        }

        /** @var $poster \Lacus\MainBundle\Post\PostPoster */
        $poster = $this->get('lacus.post.poster');

        if ($mapper->getLoginRequired()) {
            if ($account === null) {
                return $this->renderJson(
                    array(
                        'status' => "KO",
                        'message' => "This mapper requires an account info to login.",
                    )
                );
            }
            try {
                $poster->loginToSite($account, $mapper->getSite());
            } catch (LoginFormNotFoundException $e) {
                return $this->renderJson(
                    array(
                        'status' => "KO",
                        'message' => "Unable to find the login form. Are you sure you provided the right login URL?",
                    )
                );
            }
        }

        try {
            $submitForm = $poster->getSubmitForm($mapper->getSubmitUrl(), $mapper->getSubmitButton());
        } catch (SubmitFormNotFoundException $e) {
            return $this->renderJson(
                array(
                    'status' => "KO",
                    'message' => "Unable to find the submit form. Perhaps the login credentials were invalid.",
                )
            );
        }

        $mapperData = array(
            'fields' => $submitForm->getValues(),
        );
        if ($submitForm->getFiles()) {
            $mapperData['files'] = $submitForm->getFiles();
        }

        foreach ($mapperData['fields'] as $fieldName => $fieldValue) {
            $mapperData['fields'][$fieldName] = array(
                'segment' => array(
                    'name' => $fieldName,
                    'options' => array(),
                ),
                'field' => array(),
            );
            if ($fieldValue) {
                $mapperData['fields'][$fieldName]['field']['default'] = $fieldValue;
            }
        }

        if (!empty($mapperData['files'])) {
            foreach ($mapperData['files'] as $fileFieldName => $fileField) {
                $mapperData['files'][$fileFieldName] = array(
                    'segment' => array(
                        'name' => $fileFieldName,
                        'options' => array(),
                    ),
                );
            }
        }

        $mapperDataYaml = Yaml::dump($mapperData, 4, 2);

        return $this->renderJson(
            array(
                'status' => "OK",
                'message' => "Fields successfully fetched. Please ensure that this looks like the correct submit form.",
                'mapper_data' => $mapperDataYaml,
            )
        );
    }
}