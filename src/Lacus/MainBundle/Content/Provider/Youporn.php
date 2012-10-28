<?php

namespace Lacus\MainBundle\Content\Provider;

use Lacus\MainBundle\Content\Provider\AbstractProvider;
use Symfony\Component\DomCrawler\Crawler;
use Lacus\MainBundle\Content\Category\Category;
use Lacus\MainBundle\Content\Sortable\SortableField;
use Lacus\MainBundle\Content\ContentCollection;
use Lacus\MainBundle\Content\Content;
use Lacus\MainBundle\Content\Category\CategoryContainer;
use Lacus\MainBundle\Content\Sortable\SortableFieldContainer;
use Lacus\MainBundle\Content\Segment;

class Youporn extends AbstractProvider
{
    public function populateSortable(SortableFieldContainer $sortable)
    {
        $sortable
            ->add(new SortableField('views', 'views'))
            ->add(new SortableField('rating', 'rating'))
            ->add(new SortableField('duration', 'duration'))
            ->add(new SortableField('date', 'time'))
            ->add(new SortableField('rated', 'top_rated', array(new SortableField('today', 'today'), new SortableField('yesterday', 'yesterday'), new SortableField('week', 'week'), new SortableField('month', 'month'), new SortableField('year', 'year'), new SortableField('all', 'all'))))
            ->add(new SortableField('viewed', 'most_viewed', array(new SortableField('today', 'today'), new SortableField('yesterday', 'yesterday'), new SortableField('week', 'week'), new SortableField('month', 'month'), new SortableField('year', 'year'), new SortableField('all', 'all'))))
            ->add(new SortableField('favorited', 'most_favorited', array(new SortableField('today', 'today'), new SortableField('yesterday', 'yesterday'), new SortableField('week', 'week'), new SortableField('month', 'month'), new SortableField('year', 'year'), new SortableField('all', 'all'))));
    }

    public function populateCategories(CategoryContainer $categories)
    {
        $url = 'http://www.youporn.com/recommended/';
        /** @var $response \Buzz\Message\Response */
        $response = $this->client->get($url, $this->getGenericHeaders());
        $crawler = new Crawler($response->getContent(), $url);
        $categoryNodes = $crawler->filterXPath("//div[@class='dropDownBody rounded']/div/ul/li/a");
        foreach ($categoryNodes as $categoryDomNode) {
            /** @var $categoryNode \Symfony\Component\DomCrawler\Crawler */
            $categoryNode = new Crawler($categoryDomNode, $url);
            preg_match('{/(\d+)/}', $categoryNode->attr('href'), $resultCategoryId);
            $categoryId = $resultCategoryId[1];
            $categories->add(new Category($categoryNode->text(), $categoryId));
        }
    }

    public function populateContentTemplate(Content $contentTemplate)
    {
        $contentTemplate
            ->addField(new Segment\Image('thumbnail', array(
            'width' => 160,
            'height' => 120,
        )))
            ->addField(new Segment\Title('title'))
            ->addField(new Segment\Duration('duration'))
            ->addField(new Segment\ImageCollection('thumbroll', array(
            'width' => 160,
            'height' => 120,
        )))
            ->addField(new Segment\Embed('embed_iframe'))
            ->addField(new Segment\Url('url', array(
            'display_on_list' => false,
        )))
            ->setWidth(160);
    }

    public function isSearchable()
    {
        return true;
    }

    public function populateContentCollection(ContentCollection $collection)
    {
        $url = $this->buildUrl();
        $this->setActiveUrl($url);
        /** @var $response \Buzz\Message\Response */
        $response = $this->client->get($url, $this->getGenericHeaders());
        $crawler = new Crawler($response->getContent(), $url);

        if ((!$this->getSearch() && $this->getCategory() && $this->getPage() === 1)
            || (!$this->getCategory() && !$this->getSort() && $this->getPage() > 1)
        ) {
            $xPath = "//div[contains(@class,'videoList')][2]/ul/li[contains(@class,'videoBox')]";
        } else {
            $xPath = "//div[contains(@class,'videoList')][1]/ul/li[contains(@class,'videoBox')]";
        }

        $h1 = $crawler->filterXPath('//h1');
        if ($h1->count()) {
            $this->setActiveTitle($h1->text());
        } else {
            $h2 = $crawler->filterXPath('//h2');
            if ($h2->count()) {
                $this->setActiveTitle($h2->text());
            }
        }

        $videoNodes = $crawler->filterXPath($xPath);


        foreach ($videoNodes as $videoDomNode) {
            $content = clone $this->getContentTemplate();
            $content->setProvider('youporn');
            /** @var $videoNode \Symfony\Component\DomCrawler\Crawler */
            $videoNode = new Crawler($videoDomNode, $url);
            $thumbnailNode = $videoNode->filterXPath("//div[@class='wrapping-video-box']/a/img[@class='flipbook']");
            $content->set('title', $thumbnailNode->attr('alt'));
            $url = $videoNode->filterXPath("//div[@class='wrapping-video-box']/a")->link()->getUri();
            $content->setUrl($url);
            $content->set('url', $url);
            $thumbnail = $thumbnailNode->attr('data-thumbnail');
            $content->set('thumbnail', $thumbnail);
            $content->set('duration', str_replace('length', '', $videoNode->filterXPath("//div[@class='duration']")->text()));
            $thumbrollBase = $thumbnailNode->attr('data-path');
            $thumbrollCount = $thumbnailNode->attr('data-max');
            $thumbroll = array();
            for ($i = 1; $i <= $thumbrollCount; $i++) {
                $thumbroll[] = str_replace('{index}', $i, $thumbrollBase);
            }
            $content->set('thumbroll', $thumbroll);
            /** @var $thumbnailField Segment\Image */
            $thumbnailField = $content->getField('thumbnail');
            $thumbnailField->setAlternatives($thumbroll);
            preg_match('{^/watch/((\d+)/[a-z0-9-]+)/}', $thumbnailNode->parents()->attr('href'), $resultEmbedId);
            $embedId = $resultEmbedId[1];
            $contentId = $resultEmbedId[2];
            $content->setId($contentId);
            $embedIframe = sprintf('<iframe src="http://www.youporn.com/embed/%s/" frameborder=0 height="481" width="608" scrolling="no" name="yp_embed_video"></iframe>', $embedId);
            $content->set('embed_iframe', $embedIframe);
            $collection->addContent($content);
        }
    }

    public function buildUrl()
    {
        $url = 'http://www.youporn.com/';
        $query = array();
        if ($this->getSearch()) {
            $url .= 'search/';
            if ($this->getSort() && !$this->getSort()->getParent()
            ) {
                $url .= $this->getSort()->getId() . '/';
            } else {
                $url .= 'relevance/';
            }
            $query['category_id'] = 0;
            $query['query'] = $this->getSearch();
        } elseif ($this->getCategory()) {
            $url .= 'category/' . $this->getCategory()->getId() . '/category/';
            if ($this->getSort() && $this->getSort()->getId() && !$this->getSort()->getParent()) {
                $url .= $this->getSort()->getId() . '/';
            }
        } elseif ($this->getSort() && $this->getSort()->getParent()) {
            $url .= $this->getSort()->getParent()->getId() . '/' . $this->getSort()->getId() . '/';
        } elseif ($this->getSort() && $this->getSort()->getId()) {
            $url .= 'browse/' . $this->getSort()->getId() . '/';
        }
        $query['page'] = $this->getPage();
        $query['type'] = 'straight';
        $url = $url . '?' . http_build_query($query);
        return $url;
    }

    public function populateContent(Content $content)
    {
        // TODO: Implement populateContent() method.
    }

}