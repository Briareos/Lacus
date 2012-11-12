<?php

namespace Lacus\MainBundle\Content\Provider;

use Lacus\MainBundle\Content\Provider\AbstractProvider;
use Symfony\Component\DomCrawler\Crawler;
use Lacus\MainBundle\Content\ContentCollection;
use Lacus\MainBundle\Content\Content;
use Lacus\MainBundle\Content\Category\CategoryContainer;
use Lacus\MainBundle\Content\Sortable\SortableFieldContainer;
use Lacus\MainBundle\Content\Segment;
use Zend\Filter\StripTags;

class Talkshoes extends AbstractProvider
{
    public function populateSortable(SortableFieldContainer $sortable)
    {
        // TODO: Implement populateSortable() method.
    }

    public function populateCategories(CategoryContainer $categories)
    {
        // TODO: Implement populateCategories() method.
    }

    public function populateContentTemplate(Content $contentTemplate)
    {
        $contentTemplate
          ->addField(new Segment\Title('title'))
          ->addField(
            new Segment\Image('image', array(
                'width' => 540,
            ))
        )
          ->addField(new Segment\Tags('tags'))
          ->addField(
            new Segment\Url('url', array(
                'display_on_list' => false,
            ))
        )
          ->addField(new Segment\Article('article_excerpt'))
          ->addField(
            new Segment\Article('article', array(
                'visible_on_list' => false,
            ))
        )
          ->setWidth(540);
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
        $response = $this->client->get($url);
        $crawler = new Crawler($response->getContent(), $url);

        $posts = $crawler->filterXPath("//div[@class='entry']");
        foreach ($posts as $postDomNode) {
            $content = clone $this->getContentTemplate();
            $content->setProvider('talkshoes');
            $postNode = new Crawler($postDomNode, $url);
            $content->set('title', $postNode->filterXPath('//h2')->text());
            $content->setUrl($postNode->filterXPath('//h2/a')->link()->getUri());
            $content->set('url', $content->getUrl());
            $imageNode = $postNode->filterXPath('//p[1]//img');
            if ($imageNode->count()) {
                $imageHeight = $imageNode->attr('height');
                $imageWidth = $imageNode->attr('width');
                /** @var $imageField Segment\Image */
                $imageField = $content->getField('image');
                $imageField->setOption('height', $imageHeight);
                $imageField->setOption('width', $imageWidth);
                $content->set('image', $imageNode->attr('src'));
            }
            $categories = $postNode->filterXPath('//a[@rel="category tag"]');
            $arrayCategories = array();
            foreach ($categories as $category) {
                /** @var $category \DOMNode */
                $arrayCategories[] = $category->nodeValue;
            }
            $content->set('tags', $arrayCategories);
            $paragraphs = $postNode->filterXPath('//div[@class="post"]/p');
            $article = '';
            foreach ($paragraphs as $j => $paragraphDomNode) {
                /** @var $paragraph \DOMNode */
                $paragraphNode = new Crawler($paragraphDomNode, $url);
                $article .= $this->filterParagraph($paragraphNode, $j, $paragraphs->count());
            }
            if (empty($article)) {
                $article = '<em>Empty.</em>';
            }
            $content->set('article_excerpt', $article);
            preg_match('{//www\.talkshoes\.com/(\d+)/}', $content->getUrl(), $resultId);
            $content->setId($resultId[1]);
            $collection->addContent($content);
        }
    }

    /**
     * StripTags::filter does some heavy preg_match-ing; if the script fails silently, it might be due to
     * too large pcre.recursion_limit in php.ini
     * @see http://stackoverflow.com/a/7627962/232947
     *
     * @param \Symfony\Component\DomCrawler\Crawler $paragraph
     * @param int $index
     * @param int $total
     * @return string
     */
    public function filterParagraph(Crawler $paragraph, $index = 0, $total = 0, $fullView = false)
    {
        $text = '';
        foreach ($paragraph as $p) {
            if ($index === 0) {
                // First paragraph may contain /a/img or /img, which is already saved as a separate field.
                /** @var $p \DOMElement */
                if ($p->hasChildNodes() && $p->firstChild->tagName === 'img' || ($p->firstChild->hasChildNodes() && $p->firstChild->firstChild->tagName === 'img')) {
                    $p->removeChild($p->firstChild);
                }
            }
            $paragraphText = $p->ownerDocument->saveXML($p);
            if ($index === $total - 1) {
                // Last paragraph in listing can be a "read more" link, in full view it's always "related posts".
                if ($fullView) {
                    continue;
                } else {
                    if (strpos($paragraphText, 'class="more-link"') !== false) {
                        continue;
                    }
                }
            }

            // First paragraph MAY contain text when stripped off of /a/img or /img, so we should clean it if it's empty.
            // This is also true for the "view-more" span, which sits alone in its own paragraph
            $paragraphText = str_replace(array('<p/>', '<p></p>'), '', $paragraphText);

            $text .= $paragraphText;
        }

        $tagsFilter = new StripTags(array(
            'p',
            'img' => 'src',
            'a' => 'href',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'br',
            'em',
            'strong',
            'cite',
            'blockquote',
            'code',
            'ul',
            'ol',
            'li',
            'dl',
            'dt',
            'dd',
        ));
        $text = $tagsFilter->filter($text);

        return $text;
    }

    public function buildUrl()
    {
        $url = 'http://www.talkshoes.com/';
        if ($this->getPage() > 1) {
            $url .= 'page/' . $this->getPage() . '/';
        }

        return $url;
    }

    public function populateContent(Content $content)
    {
        $url = $content->getUrl();
        /** @var $response \Buzz\Message\Response */
        $response = $this->client->get($url);
        $crawler = new Crawler($response->getContent(), $url);
        $paragraphs = $crawler->filterXPath("//div[contains(@class, 'post')]/p");
        $article = '';
        foreach ($paragraphs as $i => $paragraphDomNode) {
            /** @var $paragraph \DOMNode */
            $paragraphNode = new Crawler($paragraphDomNode, $url);
            $article .= $this->filterParagraph($paragraphNode, $i, $paragraphs->count(), true);
        }
        $content->set('article', $article);
    }

}