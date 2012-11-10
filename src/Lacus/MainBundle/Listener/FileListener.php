<?php

namespace Lacus\MainBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Lacus\MainBundle\Entity\File;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class FileListener
{
    public function postPersist(LifecycleEventArgs $args)
    {
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        if (!$args->getEntity() instanceof File) {
            return;
        }

        /** @var $file File */
        $file = $args->getEntity();
        if ($file === null && $args->hasChangedField('remotePath')) {
            $file->removeUpload();
            if ($file->getRemotePath()) {
                $file->setFile($file->downloadRemoteFile($file->getRemotePath()));
            }
        }
    }
}