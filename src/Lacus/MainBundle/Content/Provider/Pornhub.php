<?php

namespace Lacus\MainBundle\Content\Provider;

use Lacus\MainBundle\Content\Provider\AbstractProvider;
use Symfony\Component\DomCrawler\Crawler;
use Lacus\MainBundle\Content\ContentCollection;
use Lacus\MainBundle\Content\Content;
use Lacus\MainBundle\Content\Category\Category;
use Lacus\MainBundle\Content\Category\CategoryContainer;
use Lacus\MainBundle\Content\Sortable\SortableField;
use Lacus\MainBundle\Content\Sortable\SortableFieldContainer;
use Lacus\MainBundle\Content\Segment;

class Pornhub extends AbstractProvider
{
    public function populateContentCollection(ContentCollection $collection)
    {
        $url = $this->buildUrl();
        $this->setActiveUrl($url);

        /** @var $response \Buzz\Message\Response */
        $response = $this->client->get($url, $this->getGenericHeaders());

        if ($response->getStatusCode() !== 200) {
            return;
        }

        $crawler = new Crawler($response->getContent(), $url);
        $videoNodes = $crawler->filterXPath("//ul[contains(@class,'videos row-4-thumbs')]/li/div[@class='wrap']");
        $pageTitle = $crawler->filterXPath("//h1[contains(@class,'section_title')]");
        if ($pageTitle->count()) {
            $this->setActiveTitle($pageTitle->text());
        }
        foreach ($videoNodes as $videoDomNode) {
            $content = clone $this->getContentTemplate();
            $videoNode = new Crawler($videoDomNode, $url);

            // Get the anchor for ID, title and URL.
            $videoAnchorNode = $videoNode->filterXPath("//a");
            parse_str(parse_url($videoAnchorNode->attr('href'), PHP_URL_QUERY), $linkQuery);
            $content->setUrl($videoAnchorNode->attr('href'));
            $content->set('viewkey', $linkQuery['viewkey']);
            $content->set('url', $videoAnchorNode->attr('href'));
            $content->set('title', $videoAnchorNode->attr('title'));

            // Get the thumbnail.
            $videoThumbnailNode = $videoNode->filterXPath("//a/img");
            $thumbnail = $videoThumbnailNode->attr('data-mediumthumb');
            $content->set('thumbnail', $thumbnail);

            preg_match('{startThumbChange\((\d+)}', $videoThumbnailNode->attr('onmouseover'), $contentIdResult);
            $contentId = $contentIdResult[1];
            $content->setId($contentId);

            // Get the duration.
            $duration = $videoNode->filterXPath('//var[@class="duration"]')->text();
            $content->set('duration', $duration);

            // Get the thumbroll.
            preg_match('{\'(http.*?)\'\);}', $videoThumbnailNode->attr('onmouseover'), $thumbrollBaseResult);
            preg_match('{, (\d+),}', $videoThumbnailNode->attr('onmouseover'), $thumbrollCountResult);
            $thumbrollBase = $thumbrollBaseResult[1];
            $thumbrollCount = $thumbrollCountResult[1];
            $thumbroll = $this->getThumbroll($content->getId(), $thumbrollBase, $thumbrollCount);

            $content->set('thumbroll', $thumbroll);
            /** @var $thumbnailField Segment\Image */
            $thumbnailField = $content->getField('thumbnail');
            $thumbnailField->setAlternatives($thumbroll);
            $content->setProvider('pornhub');

            $embedIframe = sprintf('<iframe src="http://www.pornhub.com/embed/%s" frameborder=0 height="481" width="608" scrolling="no"></iframe>', $content->get('viewkey'));
            $embed = sprintf('<object wmode="opaque" type="application/x-shockwave-flash" data="http://cdn1.static.pornhub.phncdn.com/flash/embed_player.swf" width="608" height="481"><param name="movie" value="http://cdn1.static.pornhub.phncdn.com/flash/embed_player.swf" /><param name="bgColor" value="#000000" /><param name="allowfullscreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="FlashVars" value="options=http://www.pornhub.com/embed_player.php?id=%s"/></object>', $contentId);
            $content->set('embed_iframe', $embedIframe);
            $content->set('embed', $embed);

            $collection->addContent($content);
        }
    }

    public function getThumbroll($contentId, $thumbrollBase, $thumbrollCount)
    {
        // Taken from http://cdn1.static.pornhub.phncdn.com/js/phub.js
        for ($i = 1; $i <= $thumbrollCount; $i++) {
            if (strpos($thumbrollBase, '{i}') !== false) {
                // Old method:
                $thumbrollPath = str_replace('{i}', $i, $thumbrollBase);
            } elseif (strpos($thumbrollBase, '{index}') !== false) {
                // Old method:
                $thumbrollPath = str_replace('{index}', $i, $thumbrollBase);
            } else {
                // Latest method:
                if (strpos($thumbrollBase, 'cdn1a.image.pornhub.phncdn.com') !== false
                    || strpos($thumbrollBase, 'cdn1.image.keezmovies.phncdn.com') !== false
                    || strpos($thumbrollBase, 'cdn1.videothumbs.xtube.com') !== false
                ) {
                    $imageName = '';
                    $thumbrollBase = str_replace('160x120', '240x180', $thumbrollBase);
                } else {
                    $imageName = ($contentId > 2400000) ? 'medium' : 'small';
                }
                if (substr($thumbrollBase, -4) === '.jpg') {
                    $thumbrollPath = dirname($thumbrollBase) . '/' . $i . '.jpg';
                } else {
                    $thumbrollPath = $thumbrollBase . $imageName . $i . '.jpg';
                }
            }
            $thumbroll[] = $thumbrollPath;
        }
        return $thumbroll;
    }

    public function buildUrl()
    {
        $url = "http://www.pornhub.com/video";
        $query = array();

        if ($this->getPage() > 1) {
            $query['page'] = $this->getPage();
        }

        if ($this->getSearch()) {
            $url = "http://www.pornhub.com/video/search";
            $query['search'] = $this->getSearch();
        } elseif ($this->getCategory()) {
            $query['c'] = $this->getCategory()->getId();
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

        $finalUrl = $url;
        if (count($query)) {
            $finalUrl .= '?' . http_build_query($query);
        }
        return $finalUrl;
    }

    public function populateContent(Content $content)
    {
    }

    public function isSearchable()
    {
        return true;
    }

    public function populateSortable(SortableFieldContainer $sortable)
    {
        $sortable
            ->add(new SortableField('recent', 'mr'))
            ->add(new SortableField('viewed', 'mv', array(new SortableField('day', 't'), new SortableField('week', 'w'), new SortableField('month', 'm'), new SortableField('all', 'a'))))
            ->add(new SortableField('rated', 'tr', array(new SortableField('day', 't'), new SortableField('week', 'w'), new SortableField('month', 'm'), new SortableField('all', 'a'))))
            ->add(new SortableField('longest', 'lg'));
    }

    public function populateContentTemplate(Content $contentTemplate)
    {
        $contentTemplate
            ->addField(new Segment\Image('thumbnail', array(
            'width' => 240,
            'height' => 180,
        )))
            ->addField(new Segment\Title('title', array()))
            ->addField(new Segment\Text('viewkey', array(
            'display_on_list' => false,
        )))
            ->addField(new Segment\Url('url', array(
            'display_on_list' => false,
        )))
            ->addField(new Segment\Duration('duration', array()))
            ->addField(new Segment\ImageCollection('thumbroll', array(
            'width' => 240,
            'height' => 180,
        )))
            ->addField(new Segment\Embed('embed', array(
            'display_on_list' => false,
        )))
            ->addField(new Segment\Embed('embed_iframe', array(
            'display_on_list' => false,
        )))
            ->setWidth(240);
    }

    public function populateCategories(CategoryContainer $categories)
    {
        $url = "http://www.pornhub.com/video";
        /** @var $response \Buzz\Message\Response */
        $response = $this->client->get($url, $this->getGenericHeaders());
        $crawler = new Crawler($response->getContent(), $url);
        $categoryNodes = $crawler->filterXPath("//ul[contains(@class,'nf-categories')]/li/a[starts-with(@href,'/video?c=')]");
        $categoryInfo = $categoryNodes->extract(array('_text', 'href'));
        foreach ($categoryInfo as $category) {
            $queryRaw = parse_url($category[1], PHP_URL_QUERY);
            parse_str($queryRaw, $queryParsed);
            $categories->add(new Category($category[0], $queryParsed['c']));
        }
    }
}
