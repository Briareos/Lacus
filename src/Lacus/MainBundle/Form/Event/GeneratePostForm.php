<?php

namespace Lacus\MainBundle\Form\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;
use Lacus\MainBundle\Admin\PostAdmin;
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

    private $postAdmin;

    public function __construct(FormFactoryInterface $factory, Content $content, PostAdmin $postAdmin)
    {
        $this->factory = $factory;
        $this->content = $content;
        $this->postAdmin = $postAdmin;
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
            $this->addStatusField($event);
            $this->addAccountField($event);
        }
    }

    public function addStatusField(FormEvent $event)
    {
        /** @var $post Post */
        $post = $event->getData();
        $form = $event->getForm();

        $statuses = array();

        foreach (array(Post::STATUS_DRAFT, Post::STATUS_REVIEW, Post::STATUS_QUEUE, Post::STATUS_PUBLISH, Post::STATUS_ARCHIVE, Post::STATUS_FAILURE) as $status) {
            if ($this->postAdmin->canSetStatus($post, $status)) {
                $statuses[$status] = $status;
            }
        }


        if ($statuses) {
            $selectedStatus = end($statuses);
            if ($post->getStatus() !== Post::STATUS_TEMPORARY) {
                $selectedStatus = $post->getStatus();
            }
            $form->add(
                $this->factory->createNamed(
                    'status',
                    'choice',
                    null,
                    array(
                        'expanded' => true,
                        'choices' => $statuses,
                        'data' => $selectedStatus,
                    )
                )
            );
        }
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

        $filesBuilder = $this->factory->createNamed(
            'post_files',
            'form',
            null,
            array()
        );
        $form->add($filesBuilder);

        $published = ($post->getStatus() === Post::STATUS_PUBLISH || $post->getStatus() === Post::STATUS_ARCHIVE);
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
        /** @var $contentField \Lacus\MainBundle\Content\Segment\File */
        $contentField = $this->content->getField($segment['name']);
        $fieldOptions = array(
            'required' => false,
            'read_only' => $readOnly,
            'disabled' => $readOnly,
            'file_type' => $contentField->getType(),
            'by_reference' => false,
        );
        if ($options['show_alternatives'] && $contentField->hasAlternatives()) {
            $fieldOptions['show_alternatives'] = true;
            $fieldOptions['alternatives'] = $contentField->getAlternatives();
        }
        $field = $this->factory->createNamed(
            $name,
            'mapper_file',
            null,
            $fieldOptions
        );

        return $field;
    }

    public function createField($name, $options, $segment, $readOnly = false)
    {
        if (isset($options['value'])) {
            return $this->factory->createNamed(
                $name,
                'hidden',
                null,
                array(
                    'required' => false,
                    'read_only' => true,
                )
            );
        } elseif (isset($options['choices']) && is_array($options['choices'])) {
            return $this->factory->createNamed(
                $name,
                'choice',
                null,
                array(
                    'choices' => $options['choices'],
                    'read_only' => $readOnly,
                    'disabled' => $readOnly,
                )
            );
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

    private function addAccountField(FormEvent $event)
    {
        $post = $event->getData();
        if (!$post instanceof Post) {
            return;
        }
        $mapper = $post->getMapper();
        if (!$mapper->getLoginRequired()) {
            return;
        }
        $account = $this->factory->createNamed(
            'account',
            'entity',
            null,
            array(
                'class' => 'Lacus\MainBundle\Entity\Account',
                'property' => 'username',
                'query_builder' => function (EntityRepository $er) use ($mapper) {
                    return $er->createQueryBuilder('a')
                      ->where('a.site = :site')
                      ->setParameter('site', $mapper->getSite())
                      ->orderBy('a.id', 'ASC');
                },
                'disabled' => ($post->getStatus() === Post::STATUS_PUBLISH || $post->getStatus() === Post::STATUS_ARCHIVE),
            )
        );
        $event->getForm()->add($account);
    }
}