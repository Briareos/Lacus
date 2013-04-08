<?php

namespace Lacus\MainBundle\Content\Provider;

use Lacus\MainBundle\Content\Provider\AbstractProvider;
use Lacus\MainBundle\Content\Sortable\SortableField;
use Lacus\MainBundle\Content\Category\Category;
use Symfony\Component\DomCrawler\Crawler;
use Lacus\MainBundle\Content\ContentCollection;
use Lacus\MainBundle\Content\Content;
use Lacus\MainBundle\Content\Category\CategoryContainer;
use Lacus\MainBundle\Content\Sortable\SortableFieldContainer;
use Lacus\MainBundle\Content\Segment;

class Spankwire extends AbstractProvider
{
    public function populateSortable(SortableFieldContainer $sortable)
    {
        $sortable
          ->add(
            new SortableField('featured', 'Featured', array(
                new SortableField('viewed', 'Views', array(
                    new SortableField('today', 'Today'),
                    new SortableField('yesterday', 'Yesterday'),
                    new SortableField('week', 'Week'),
                    new SortableField('month', 'Month'),
                    new SortableField('year', 'Year'),
                    new SortableField('all', 'All_Time'),
                )),
                new SortableField('rated', 'Rating', array(
                    new SortableField('today', 'Today'),
                    new SortableField('yesterday', 'Yesterday'),
                    new SortableField('week', 'Week'),
                    new SortableField('month', 'Month'),
                    new SortableField('year', 'Year'),
                    new SortableField('all', 'All_Time'),
                )),
                new SortableField('duration', 'Duration', array(
                    new SortableField('today', 'Today'),
                    new SortableField('yesterday', 'Yesterday'),
                    new SortableField('week', 'Week'),
                    new SortableField('month', 'Month'),
                    new SortableField('year', 'Year'),
                    new SortableField('all', 'All_Time'),
                )),
                new SortableField('date', 'Submitted', array(
                    new SortableField('today', 'Today'),
                    new SortableField('yesterday', 'Yesterday'),
                    new SortableField('week', 'Week'),
                    new SortableField('month', 'Month'),
                    new SortableField('year', 'Year'),
                    new SortableField('all', 'All_Time'),
                )),
                new SortableField('commented', 'Comments', array(
                    new SortableField('today', 'Today'),
                    new SortableField('yesterday', 'Yesterday'),
                    new SortableField('week', 'Week'),
                    new SortableField('month', 'Month'),
                    new SortableField('year', 'Year'),
                    new SortableField('all', 'All_Time'),
                )),
            ))
        )
          ->add(
            new SortableField('new', 'Upcoming', array(
                new SortableField('viewed', 'Views', array(
                    new SortableField('today', 'Today'),
                    new SortableField('yesterday', 'Yesterday'),
                    new SortableField('week', 'Week'),
                    new SortableField('month', 'Month'),
                    new SortableField('year', 'Year'),
                    new SortableField('all', 'All_Time'),
                    new SortableField('votes_left', 'VotesLeft'),
                )),
                new SortableField('rated', 'Rating', array(
                    new SortableField('today', 'Today'),
                    new SortableField('yesterday', 'Yesterday'),
                    new SortableField('week', 'Week'),
                    new SortableField('month', 'Month'),
                    new SortableField('year', 'Year'),
                    new SortableField('all', 'All_Time'),
                    new SortableField('votes_left', 'VotesLeft'),
                )),
                new SortableField('duration', 'Duration', array(
                    new SortableField('today', 'Today'),
                    new SortableField('yesterday', 'Yesterday'),
                    new SortableField('week', 'Week'),
                    new SortableField('month', 'Month'),
                    new SortableField('year', 'Year'),
                    new SortableField('all', 'All_Time'),
                    new SortableField('votes_left', 'VotesLeft'),
                )),
                new SortableField('date', 'Submitted', array(
                    new SortableField('today', 'Today'),
                    new SortableField('yesterday', 'Yesterday'),
                    new SortableField('week', 'Week'),
                    new SortableField('month', 'Month'),
                    new SortableField('year', 'Year'),
                    new SortableField('all', 'All_Time'),
                    new SortableField('votes_left', 'VotesLeft'),
                )),
                new SortableField('commented', 'Comments', array(
                    new SortableField('today', 'Today'),
                    new SortableField('yesterday', 'Yesterday'),
                    new SortableField('week', 'Week'),
                    new SortableField('month', 'Month'),
                    new SortableField('year', 'Year'),
                    new SortableField('all', 'All_Time'),
                    new SortableField('votes_left', 'VotesLeft'),
                )),

            ))
        )
          ->add(
            new SortableField('search', 'search', array(
                new SortableField('relevance', 'Relevance'),
                new SortableField('viewed', 'Views'),
                new SortableField('rated', 'Rating'),
                new SortableField('duration', 'Duration'),
                new SortableField('date', 'Submitted'),
                new SortableField('commented', 'Comments')
            ))
        )
          ->add(
            new SortableField('category', 'category', array(
                new SortableField('viewed', 'Views'),
                new SortableField('rated', 'Rating'),
                new SortableField('duration', 'Duration'),
                new SortableField('date', 'Submitted'),
                new SortableField('title', 'Title')
            ))
        )
          ->add(
            new SortableField('rated', 'Rating', array(
                new SortableField('today', 'Today'),
                new SortableField('yesterday', 'Yesterday'),
                new SortableField('week', 'Week'),
                new SortableField('month', 'Month'),
                new SortableField('year', 'Year'),
                new SortableField('all', 'All_Time'),
            ))
        )
          ->add(
            new SortableField('viewed', 'Views', array(
                new SortableField('today', 'Today'),
                new SortableField('yesterday', 'Yesterday'),
                new SortableField('week', 'Week'),
                new SortableField('month', 'Month'),
                new SortableField('year', 'Year'),
                new SortableField('all', 'All_Time'),
            ))
        )
          ->add(
            new SortableField('commented', 'Comments', array(
                new SortableField('today', 'Today'),
                new SortableField('yesterday', 'Yesterday'),
                new SortableField('week', 'Week'),
                new SortableField('month', 'Month'),
                new SortableField('year', 'Year'),
                new SortableField('all', 'All_Time'),
            ))
        );
    }

    public function populateCategories(CategoryContainer $categories)
    {
        $url = 'http://www.spankwire.com/categories/Straight';
        /** @var $response \Buzz\Message\Response */
        $response = $this->client->get($url);
        $crawler = new Crawler($response->getContent(), $url);
        $categoryNodes = $crawler->filterXPath("//div[@class='category-thumb']");
        foreach ($categoryNodes as $categoryDomNode) {
            $categoryNode = new Crawler($categoryDomNode, $url);
            $categoryName = $categoryNode->filterXPath('//h2/a')->text();
            $categoryId = basename($categoryNode->filterXPath('//h2/a')->attr('href'));
            $categories->add(new Category($categoryName, $categoryId));
        }
    }

    public function populateContentTemplate(Content $contentTemplate)
    {
        $contentTemplate
          ->addField(
            new Segment\Image('thumbnail', array(
                'width' => 320,
                'height' => 240,
            ))
        )
          ->addField(
            new Segment\ImageCollection('thumbroll', array(
                'width' => 320,
                'height' => 240,
            ))
        )
          ->addField(
            new Segment\Title('title', array(
                'visible_on_list' => false,
            ))
        )
          ->addField(
            new Segment\Url('url', array(
                'display_on_list' => false,
            ))
        )
          ->setWidth(320);
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
        $this->setActiveTitle($crawler->filterXPath('//h1')->last()->text());
        $videoNodes = $crawler->filterXPath("//div[@class='main-container']/div[@id='main-area']/ul[@class='ggs-list-videos']/li/div[@class='thumb-info-wrapper']");
        foreach ($videoNodes as $videoDomNode) {
            $content = clone $this->getContentTemplate();
            $content->setProvider('spankwire');
            $videoNode = new Crawler($videoDomNode, $url);
            $thumbnail = $videoNode->filterXPath("//div[@class='thmb-wrapper']/a/img")->attr('src');
            $content->set('thumbnail', $thumbnail);
            $script = $videoNode->filterXPath("//div[@class='thmb-wrapper']/script")->text();
            preg_match('{"indexes": \[(.*?)\], "path": "(.*?)"}', $script, $thumbrollResult);
            $thumbroll = $this->getThumbroll($thumbrollResult[2], explode(',', $thumbrollResult[1]));
            $content->set('thumbroll', $thumbroll);
            /** @var $thumbnailField Segment\Image */
            $thumbnailField = $content->getField('thumbnail');
            $thumbnailField->setAlternatives($thumbroll);
            $contentUrl = $videoNode->filterXPath("//div[@class='thmb-wrapper']/a")->link()->getUri();
            $content->setUrl($contentUrl);
            $content->set('url', $contentUrl);
            preg_match('{/video(\d+)/?$}', $contentUrl, $resultId);
            $content->setId($resultId[1]);
            $collection->addContent($content);
        }
    }

    public function getThumbroll($thumbrollBase, $thumbrollIndexes)
    {
        $thumbroll = array();
        foreach ($thumbrollIndexes as $index) {
            $thumbroll[] = str_replace('{index}', $index, $thumbrollBase);
        }

        return $thumbroll;
    }

    public function buildUrl()
    {
        $url = 'http://www.spankwire.com/';
        $query = array();

        if ($this->getSearch()) {
            $url .= 'search/straight/keyword/' . rawurlencode($this->getSearch());
            if ($this->getSort() && $this->getSort()->getParent() && $this->getSort()->getParent()->getId() === 'search') {
                $query['Sort'] = $this->getSort()->getId();
            }
        } elseif ($this->getCategory()) {
            $sortBy = 'Submitted';
            if ($this->getSort() && $this->getSort()->getParent() && $this->getSort()->getParent()->getId() === 'category') {
                $sortBy = $this->getSort()->getId();
            }
            $url .= 'categories/Straight/' . urlencode($this->getCategory()->getName()) . '/' . $sortBy . '/' . $this->getCategory()->getId();
        } elseif ($this->getSort() && $this->getSort()->getDepth() === 2) {
            $url .= sprintf('home2/Straight/%s/%s/%s', $this->getSort()->getParent()->getParent()->getId(), $this->getSort()->getId(), $this->getSort()->getParent()->getId());
        } else {
            $url .= 'home2/Straight/Featured/All_Time/Submitted';
        }

        if ($this->getPage() > 1) {
            $query['Page'] = $this->getPage();
        }
        if (count($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    public function populateContent(Content $content)
    {
        /** @var $response \Buzz\Message\Response */
        $response = $this->client->get($content->getUrl());
        $crawler = new Crawler($response->getContent(), $content->getUrl());

        $title = $crawler->filterXPath('//h1')->eq(2)->text();

        $content->set('title', $title);
    }

}
