<?php

namespace Lacus\MainBundle\Form\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lacus\MainBundle\Post\Manager;
use Lacus\MainBundle\Mapper\MapperDataTree;
use Lacus\MainBundle\Entity\Post;
use Lacus\MainBundle\Content\Content;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormEvents;

class GeneratePostForm implements EventSubscriberInterface
{
    private $factory;

    private $content;

    public function __construct(FormFactoryInterface $factory, Content $content)
    {
        $this->factory = $factory;
        $this->content = $content;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'postSetData',
        );
    }

    public function postSetData(FormEvent $event)
    {
        if ($event->getData()) {
            $this->addFields($event);
            $this->disablePublishOption($event);
        }
    }

    public function disablePublishOption(FormEvent $event)
    {
        /** @var $post Post */
        $post = $event->getData();
        $form = $event->getForm();

        $form->add(
            $this->factory->createNamed('status', 'choice', null, array(
                'expanded' => true,
                'choices' => array(
                    Post::STATUS_DRAFT => Post::STATUS_DRAFT,
                    Post::STATUS_REVIEW => Post::STATUS_REVIEW,
                    Post::STATUS_QUEUE => Post::STATUS_QUEUE,
                    Post::STATUS_PUBLISH => Post::STATUS_PUBLISH,
                ),
                'read_only' => ($post->getStatus() === Post::STATUS_PUBLISH),
                'disabled' => ($post->getStatus() === Post::STATUS_PUBLISH),
            ))
        );
    }

    public function addFields(FormEvent $event)
    {
        /** @var $post Post */
        $post = $event->getData();
        $mapper = $post->getMapper();
        $form = $event->getForm();

        $mapperData = MapperDataTree::filter(Yaml::parse($mapper->getData()));

        $contentBuilder = $this->factory->createNamed('content');
        $form->add($contentBuilder);

        $filesBuilder = $this->factory->createNamed('post_files', 'form', null, array(
        ));
        $form->add($filesBuilder);

        $published = ($post->getStatus() === Post::STATUS_PUBLISH);
        foreach ($mapperData['fields'] as $mapperFieldName => $mapperField) {
            $formField = $this->createField($post->getSafeFieldName($mapperFieldName), $mapperField['field'], $mapperField['segment'], $published);
            $contentBuilder->add($formField);
        }
        foreach ($mapperData['files'] as $mapperFileFieldName => $mapperFileField) {
            $formFileField = $this->createFileField($post->getSafeFieldName($mapperFileFieldName), $mapperFileField['field'], $mapperFileField['segment'], $published);
            $filesBuilder->add($formFileField);
        }
    }

    public function createFileField($name, $options, $segment, $readOnly = false)
    {
        $field = $this->factory->createNamed($name, 'mapper_file', null, array(
            'required' => false,
            'read_only' => $readOnly,
            'disabled' => $readOnly,
        ));
        return $field;
    }

    public function createField($name, $options, $segment, $readOnly = false)
    {
        if (isset($options['value'])) {
            return $this->factory->createNamed($name, 'hidden', null, array(
                'required' => false,
                'read_only' => true,
            ));
        } elseif (isset($options['default'])) {
            $fieldOptions = array(
                'required' => false,
                'read_only' => $readOnly,
                'disabled' => $readOnly,
            );
            $fieldType = 'text';
            if ($options['rows'] > 1) {
                $fieldType = 'textarea';
                $fieldOptions['attr']['rows'] = $options['rows'];
            }
            return $this->factory->createNamed($name, $fieldType, null, $fieldOptions);
        } elseif (isset($segment['name'])) {
            $contentField = $this->content->getField($segment['name']);
            $fieldType = 'text';
            $fieldOptions = array(
                'required' => false,
                'read_only' => $readOnly,
                'disabled' => $readOnly,
            );
            if ($contentField->getType() === 'image') {
                /** @var $contentField \Lacus\MainBundle\Content\Segment\Image */
                $fieldType = 'mapper_image';
                if ($options['show_alternatives'] && $contentField->hasAlternatives()) {
                    $fieldOptions['show_alternatives'] = true;
                    $fieldOptions['alternatives'] = $contentField->getAlternatives();
                }
            } elseif ($options['rows'] > 1 || $options['wysiwyg']) {
                $fieldType = 'mapper_textarea';
                $fieldOptions['attr']['rows'] = $options['rows'];
                if ($options['wysiwyg']) {
                    $fieldOptions['wysiwyg'] = true;
                }
            }
            return $this->factory->createNamed($name, $fieldType, null, $fieldOptions);
        } else {
            throw new \RuntimeException('No field.value, field.default or segment.name options provided.');
        }
    }
}