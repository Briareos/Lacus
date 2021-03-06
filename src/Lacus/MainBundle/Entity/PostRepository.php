<?php

namespace Lacus\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * PostRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PostRepository extends EntityRepository
{
    public function fetchMappedPosts($uuids)
    {
        if (empty($uuids)) {
            return array();
        }
        $posts = $this->_em
            ->createQuery('Select p From MainBundle:Post p Inner Join p.mapper m Where p.uuid In (:uuids)')
            ->execute(array('uuids' => $uuids));
        $mappedPosts = array();
        foreach ($posts as $post) {
            /** @var $post Post */
            $mappedPosts[$post->getMapper()->getId()][$post->getUuid()] = $post;
        }
        return $mappedPosts;
    }
}
