<?php

namespace Lacus\MainBundle\Content\Provider;

use Lacus\MainBundle\Content\Content;
use Lacus\MainBundle\Content\Exception\ValidationException;
use Lacus\MainBundle\Content\ContentCollection;
use Lacus\MainBundle\Content\Category\Category;
use Lacus\MainBundle\Content\Sortable\SortableField;
use Buzz\Browser;
use Lacus\MainBundle\Content\Category\CategoryContainer;
use Lacus\MainBundle\Content\Sortable\SortableFieldContainer;

abstract class AbstractProvider
{
    /**
     * @var Browser
     */
    protected $client;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var SortableField
     */
    protected $sort;

    /**
     * @var string
     */
    protected $search;

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var CategoryContainer
     */
    private $categories;

    /**
     * @var SortableFieldContainer
     */
    private $sortable;

    private $activeUrl;

    private $activeTitle;

    private $contentTemplate;

    private $contentCollection;

    protected function getGenericHeaders()
    {
        return array(
            'Accept: text/html',
            'Accept-Charset: UTF-8,*',
            'Accept-Language: en-US,en',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1',
        );
    }

    public function setClient(Browser $client)
    {
        $this->client = $client;
    }

    public function getSortable()
    {
        if ($this->sortable === null) {
            $this->sortable = new SortableFieldContainer();
            $this->populateSortable($this->sortable);
        }
        return $this->sortable;
    }

    public function getCategories()
    {
        if ($this->categories === null) {
            $this->categories = new CategoryContainer();
            $this->populateCategories($this->categories);
        }
        return $this->categories;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        if (is_scalar($category)) {
            if ($this->getCategories()->has($category)) {
                $this->category = $this->getCategories()->get($category);
            } else {
            }
        } elseif ($category instanceof Category) {
            $this->category = $category;
        } else {
            throw new \InvalidArgumentException();
        }
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setSort($sort)
    {
        if (is_scalar($sort)) {
            if ($this->getSortable()->has($sort)) {
                $this->sort = $this->getSortable()->get($sort);
            } else {
                throw new \InvalidArgumentException(sprintf('Invalid sort specified: %s', $sort));
            }
        } elseif ($sort instanceof SortableField) {
            $this->sort = $sort;
        } else {
            throw new \InvalidArgumentException();
        }
    }

    public function getSearch()
    {
        return $this->search;
    }

    public function setSearch($search)
    {
        $this->search = $search;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setPage($page)
    {
        $this->page = (int)$page;
    }

    public function getContentCollection()
    {
        if ($this->contentCollection === null) {
            $this->contentCollection = new ContentCollection();
            $this->populateContentCollection($this->contentCollection);
        }
        foreach ($this->contentCollection as $content) {
            $this->validateContent($content);
        }
        return $this->contentCollection;
    }

    public function getContent(Content $content)
    {
        $this->populateContent($content);
        $this->validateContent($content, false);
        return $content;
    }

    public function validateContent(Content $content, $list = true)
    {
        if (!$content->getId()) {
            throw new ValidationException('Content must have an ID specified.');
        }
        if (!$content->getProvider()) {
            throw new ValidationException('Content must have a provider specified.');
        }
        if (!$content->getUrl()) {
            throw new ValidationException('Content must have a URL specified.');
        }
    }

    public function getContentTemplate()
    {
        if ($this->contentTemplate === null) {
            $this->contentTemplate = new Content();
            $this->populateContentTemplate($this->contentTemplate);
        }
        return $this->contentTemplate;
    }

    public function getActiveUrl()
    {
        return $this->activeUrl;
    }

    public function setActiveUrl($activeUrl)
    {
        $this->activeUrl = $activeUrl;
    }

    abstract public function populateSortable(SortableFieldContainer $sortable);

    abstract public function populateCategories(CategoryContainer $categories);

    abstract public function populateContentTemplate(Content $contentTemplate);

    abstract public function isSearchable();

    abstract public function populateContentCollection(ContentCollection $collection);

    abstract public function populateContent(Content $content);

    public function getActiveTitle()
    {
        return $this->activeTitle;
    }

    public function setActiveTitle($activeTitle)
    {
        $this->activeTitle = $activeTitle;
    }
}