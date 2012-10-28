<?php

namespace Lacus\MainBundle\Post;

use Lacus\MainBundle\Entity\Post;
use Lacus\MainBundle\Entity\File;
use Symfony\Component\Yaml\Yaml;
use Lacus\MainBundle\Mapper\MapperDataTree;
use Doctrine\ORM\EntityManager;
use Lacus\MainBundle\Content\Content;
use Lacus\MainBundle\Post\FieldNameConverterTrait;

class Manager
{
    use FieldNameConverterTrait;

    protected $em;

    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function populatePostWithContentData(Post $post, Content $content)
    {
        $mapperData = MapperDataTree::filter(Yaml::parse($post->getMapper()->getData()));
        $contentData = $this->getPostFields($mapperData['fields'], $content);
        $post->setContent($contentData);

        $files = $this->getFileFields($mapperData['files'], $content);
        foreach ($files as $file) {
            $post->addFile($file);
        }
    }

    public function getPostFields($fields, Content $content)
    {
        $contentData = array();
        foreach ($fields as $fieldName => $fieldInfo) {
            $safeFieldName = $this->getSafeFieldName($fieldName);
            if ($fieldInfo['field']['value']) {
                $contentData[$safeFieldName] = $fieldInfo['field']['value'];
            } elseif ($fieldInfo['segment']['name']) {
                $contentData[$safeFieldName] = $content->get($fieldInfo['segment']['name'], $fieldInfo['segment']['options']);
            } else {
                $contentData[$safeFieldName] = $fieldInfo['field']['default'];
            }
        }
        return $contentData;
    }

    /**
     * @param $fields
     * @param \Lacus\MainBundle\Content\Content $content
     * @return File[]
     */
    public function getFileFields($fields, Content $content)
    {
        $files = array();
        foreach ($fields as $fieldName => $fieldInfo) {
            $file = new File();
            $file->setFieldName($this->getSafeFieldName($fieldName));
            $remotePath = $content->get($fieldInfo['segment']['name'], $fieldInfo['segment']['options']);
            $file->setRemotePath($remotePath);
            $files[] = $file;
        }
        return $files;
    }
}
