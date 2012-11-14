<?php

namespace Lacus\MainBundle\Content\Provider;

use Lacus\MainBundle\Content\Provider\AbstractProvider;
use Lacus\MainBundle\Content\Category\Category;
use Symfony\Component\DomCrawler\Crawler;
use Lacus\MainBundle\Content\Sortable\SortableField;
use Lacus\MainBundle\Content\ContentCollection;
use Lacus\MainBundle\Content\Content;
use Lacus\MainBundle\Content\Category\CategoryContainer;
use Lacus\MainBundle\Content\Sortable\SortableFieldContainer;
use Lacus\MainBundle\Content\Segment;

class Extremetube extends AbstractProvider
{
    public function populateSortable(SortableFieldContainer $sortable)
    {
        $sortable
          ->add(new SortableField('viewed', 'mv', array(new SortableField('today', 't'), new SortableField('yesterday', 'y'), new SortableField('week', 'w'), new SortableField('month', 'm'), new SortableField('all', 'a'))))
          ->add(new SortableField('rated', 'tr', array(new SortableField('today', 't'), new SortableField('yesterday', 'y'), new SortableField('week', 'w'), new SortableField('month', 'm'), new SortableField('all', 'a'))))
          ->add(new SortableField('longest', 'lg'));
    }

    public function populateCategories(CategoryContainer $categories)
    {
        $url = 'http://www.extremetube.com/video-categories';
        /** @var $response \Buzz\Message\Response */
        $response = $this->client->get($url, $this->getGenericHeaders());
        $crawler = new Crawler($response->getContent());
        $categoryImages = $crawler->filterXPath("//img[@class='thumb-list-img']");
        foreacH ($categoryImages as $categoryImageDomNode) {
            $categoryImageNode = new Crawler($categoryImageDomNode, $url);
            $categoryName = $categoryImageNode->attr('alt');
            $categoryId = basename($categoryImageNode->parents()->attr('href'));
            $categories->add(new Category($categoryName, $categoryId));
        }
    }

    public function populateContentTemplate(Content $contentTemplate)
    {
        $contentTemplate
          ->addField(
            new Segment\Image('thumbnail', array(
                'width' => 240,
                'height' => 180,
            ))
        )
          ->addField(new Segment\Title('title', array()))
          ->addField(
            new Segment\Url('url', array(
                'display_on_list' => false,
            ))
        )
          ->addField(new Segment\Duration('duration', array()))
          ->addField(
            new Segment\ImageCollection('thumbroll', array(
                'width' => 240,
                'height' => 180,
            ))
        )
          ->addField(
            new Segment\Embed('embed', array(
                'display_on_list' => false,
            ))
        )
          ->addField(
            new Segment\Embed('embed_iframe', array(
                'display_on_list' => false,
            ))
        )
          ->setWidth(240);

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

        if ($response->getStatusCode() === 404) {
            return;
        }

        $crawler = new Crawler($response->getContent(), $url);

        if ($this->getPage() > 1) {
            $pageTitle = $crawler->filterXPath("//div[@class='text-browse-result text-left bold relative']");
            if ($pageTitle->count()) {
                $this->setActiveTitle($pageTitle->text());
            }
        }

        $videoNodes = $crawler->filterXPath("//ul[contains(@class,'section03')][1]/li/div[descendant::h2]");
        foreach ($videoNodes as $videoDomNode) {
            $content = clone $this->getContentTemplate();
            $content->setProvider('extremetube');
            $videoNode = new Crawler($videoDomNode, $url);
            $thumbnailNode = $videoNode->filterXPath("//a/img");
            $content->set('title', $thumbnailNode->attr('alt'));
            preg_match('{, (\d+), \'(http.*?)\'}', $thumbnailNode->parents()->attr('onmouseover'), $thumbrollResult);
            $thumbrollCount = $thumbrollResult[1];
            $thumbrollPath = $thumbrollResult[2];
            $thumbroll = $this->getThumbroll($thumbrollPath, $thumbrollCount);
            $content->set('thumbroll', $thumbroll);
            /** @var $thumbnailField Segment\Image */
            $thumbnailField = $content->getField('thumbnail');
            $thumbnailField->setAlternatives($thumbroll);
            $duration = $videoNode->filterXPath("//div[@class='absolute duration']")->text();
            $content->set('duration', $duration);
            $thumbnail = $thumbnailNode->attr('src');
            $content->set('thumbnail', $thumbnail);
            $contentUrl = $thumbnailNode->parents()->link()->getUri();
            $content->setUrl($contentUrl);
            $content->set('url', $contentUrl);
            $embedId = basename($contentUrl);
            $contentId = preg_replace('{[^\d]}', '', $videoNode->parents()->attr('id'));
            $content->setId($contentId);
            $embedIframe = sprintf('<iframe src="http://www.extremetube.com/embed/%s" frameborder="0" height="481" width="608" scrolling="no" name="extremetube_embed_video"></iframe>', $embedId);
            $embed = sprintf(
                '<object type="application/x-shockwave-flash" data="http://cdn1.static.extremetube.phncdn.com/flash/player_embed.swf" width="608" height="481"><param name="movie" value="http://cdn1.static.extremetube.phncdn.com/flash/player_embed.swf" /><param name="bgColor" value="#000000" /><param name="allowfullscreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="FlashVars" value="options=http://www.extremetube.com/embed_player.php?id=%s"/></object>',
                $contentId
            );
            $content->set('embed', $embed);
            $content->set('embed_iframe', $embedIframe);
            $collection->addContent($content);
        }
    }

    public function getThumbroll($thumbrollPath, $thumbrollCount)
    {
        $thumbroll = array();
        for ($i = 1; $i <= $thumbrollCount; $i++) {
            $thumbroll[] = str_replace('{index}', $i, $thumbrollPath);
        }

        return $thumbroll;
    }

    public function buildUrl()
    {
        $url = 'http://www.extremetube.com/';
        $query = array();

        if ($this->getPage() > 1) {
            $query['page'] = $this->getPage();
        }

        if ($this->getSearch()) {
            $url .= 'videos';
            $query['search'] = $this->getSearch();
        } elseif ($this->getCategory()) {
            $url .= 'category/' . $this->getCategory()->getId();
        } else {
            $url .= 'videos';
        }

        if ($this->getSort()) {
            $sortBy = $this->getSort();
            if ($sortBy->getParent()) {
                $sortMain = $sortBy->getParent();
                $sortSub = $sortBy;
            } else {
                $sortMain = $sortBy;
                $sortSub = false;
            }
            if ($sortMain->getId()) {
                $query['o'] = $sortMain->getId();
                if ($sortSub) {
                    $query['t'] = $sortSub->getId();
                }
            }
        }

        if (count($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    public function populateContent(Content $content)
    {
    }

}