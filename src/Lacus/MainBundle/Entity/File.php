<?php

namespace Lacus\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File as HttpFile;

/**
 * Lacus\MainBundle\Entity\File
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Lacus\MainBundle\Entity\FileRepository")
 * @ORM\HasLifecycleCallbacks
 */
class File
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="fieldName", type="string", length=255)
     */
    private $fieldName;

    /**
     * @var string
     *
     * @ORM\Column(name="fileName", type="string", length=255)
     */
    private $fileName;

    /**
     * @var string $localPath
     *
     * @ORM\Column(name="localPath", type="text")
     */
    private $localPath;

    /**
     * @var string $remotePath
     *
     * @ORM\Column(name="remotePath", type="text", nullable=true)
     */
    private $remotePath;

    /**
     * @var string $mimetype
     *
     * @ORM\Column(name="mimetype", type="string", length=255)
     */
    private $mimetype;

    /**
     * @var int $size
     *
     * @ORM\Column(name="size", type="integer")
     */
    private $size;

    /**
     * @var Post
     *
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="files")
     * @ORM\JoinColumn(name="post", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $post;

    /**
     * @var HttpFile
     */
    private $file;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updatedAt", type="datetime")
     */
    private $updatedAt;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set localPath
     *
     * @param string $localPath
     * @return File
     */
    public function setLocalPath($localPath)
    {
        $this->localPath = $localPath;

        return $this;
    }

    /**
     * Get localPath
     *
     * @return string
     */
    public function getLocalPath()
    {
        return $this->localPath;
    }

    /**
     * Set remotePath
     *
     * @param string $remotePath
     * @return File
     */
    public function setRemotePath($remotePath)
    {
        $this->remotePath = $remotePath;

        return $this;
    }

    /**
     * Get remotePath
     *
     * @return string
     */
    public function getRemotePath()
    {
        return $this->remotePath;
    }

    /**
     * Set mimetype
     *
     * @param string $mimetype
     * @return File
     */
    public function setMimetype($mimetype)
    {
        $this->mimetype = $mimetype;

        return $this;
    }

    /**
     * Get mimetype
     *
     * @return string
     */
    public function getMimetype()
    {
        return $this->mimetype;
    }

    /**
     * Set size
     *
     * @param string $size
     * @return File
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return \Lacus\MainBundle\Entity\Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param \Lacus\MainBundle\Entity\Post $post
     */
    public function setPost($post)
    {
        $this->post = $post;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param string $fieldName
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function getUploadRootDir()
    {
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    public function getUploadDir()
    {
        return 'uploads/post';
    }

    public function getAbsolutePath()
    {
        return null === $this->localPath ? null : realpath($this->getUploadRootDir() . '/' . $this->localPath);
    }

    public function getWebPath()
    {
        return null === $this->localPath ? null : $this->getUploadDir() . '/' . $this->localPath;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(HttpFile $file)
    {
        $this->updatedAt = new \DateTime();
        $this->file = $file;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function prePersist()
    {
        if (null === $this->file) {
            if ($this->remotePath) {
                $this->file = $this->downloadRemoteFile($this->remotePath);
            } else {
                return;
            }
        } else {
            if ($this->remotePath) {
                $this->remotePath = null;
            }
            if ($this->localPath) {
                $this->removeUpload();
            }
        }
        // do whatever you want to generate a unique name
        $fileName = sha1(uniqid(mt_rand(), true));
        $guessedExtension = $this->file->guessExtension();
        if ($guessedExtension === null) {
            $guessedExtension = 'bin';
        }
        $this->localPath = $fileName . '.' . $guessedExtension;

        $this->mimetype = $this->file->getMimeType();
        $this->size = $this->file->getSize();

        if ($this->file instanceof UploadedFile) {
            $this->fileName = $this->file->getClientOriginalName();
        }
        if ($this->fileName === null) {
            $this->fileName = $this->localPath;
        }
    }

    public function downloadRemoteFile($remotePath)
    {
        $ch = curl_init($remotePath);
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
            )
        );
        $fileContents = curl_exec($ch);
        $fileLocation = tempnam('lacus', 'file');
        $fileSaved = file_put_contents($fileLocation, $fileContents);
        if ($fileSaved === false) {
            throw new \RuntimeException(sprintf('Could not save the file to location "%s".', $fileLocation));
        }

        return new HttpFile($fileLocation);
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->file->move($this->getUploadRootDir(), $this->localPath);

        $this->file = null;
    }

    /**
     * @ORM\PreRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
            $this->setFileName(null);
            $this->setLocalPath(null);
            $this->setSize(null);
            $this->setMimetype(null);
        }
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }
}
