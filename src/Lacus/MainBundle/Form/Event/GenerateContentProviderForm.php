<?php

namespace Lacus\MainBundle\Form\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lacus\MainBundle\Content\Provider\AbstractProvider;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormEvents;

class GenerateContentProviderForm implements EventSubscriberInterface
{
    private $factory;

    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'addFields',
        );
    }

    public function addFields(FormEvent $event)
    {
        /** @var $provider AbstractProvider */
        $provider = $event->getData();
        $form = $event->getForm();

        $this->addSearchField($form, $provider);
        $this->addCategoryField($form, $provider);
        $this->addSortField($form, $provider);
        $this->addPageField($form, $provider);

    }

    protected function addSearchField(FormInterface $form, AbstractProvider $provider)
    {
        $form->add(
            $this->factory->createNamed('search', 'text', null, array(
                'required' => false,
                'disabled' => !$provider->isSearchable(),
            ))
        );
    }

    protected function addCategoryField(FormInterface $form, AbstractProvider $provider)
    {
        $categories = $provider->getCategories();
        $categoryChoices = array();
        foreach ($categories as $category) {
            /** @var $category \Lacus\MainBundle\Content\Category\Category */
            $categoryChoices[$category->getId()] = $category->getName();
        }

        $form->add(
            $this->factory->createNamed('category', 'choice', null, array(
                'required' => false,
                'choices' => $categoryChoices,
            ))
        );
    }

    protected function addSortField(FormInterface $form, AbstractProvider $provider)
    {
        $sortable = $provider->getSortable();
        $sortChoices = $this->getSortableChildren($sortable);

        $form->add(
            $this->factory->createNamed('sort', 'choice', null, array(
                'required' => false,
                'choices' => $sortChoices,
            ))
        );
    }

    protected function getSortableChildren($sortable, $level = 0)
    {
        $choices = array();
        foreach ($sortable as $sort) {
            /** @var $sort \Lacus\MainBundle\Content\Sortable\SortableField */
            $choices[$sort->getPath()] = str_repeat('|--', $sort->getDepth()) . ' ' . $sort->getName();
        }
        return $choices;
    }

    protected function addPageField(FormInterface $form, AbstractProvider $provider)
    {
        $form->add(
            $this->factory->createNamed('page', 'text', null, array(
                'empty_data' => 1,
                'required' => false,
            ))
        );
    }
}