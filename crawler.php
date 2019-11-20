<?php

namespace Spatie\Crawler;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\UriInterface;
use Spatie\Browsershot\Browsershot;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlInternalUrls;
use Spatie\Crawler\CrawlProfile;
use Spatie\Crawler\CrawlSubdomains;
use Spatie\Crawler\Exception\InvalidCrawlRequestHandler;
use stdClass;

class CrawlObserver
{
	public function setCrawlObserver($crawlObservers): Crawler
    {
        if (! is_array($crawlObservers)) {
            $crawlObservers = [$crawlObservers];
        }
        return $this->setCrawlObservers($crawlObservers);
    }

    public function addCrawlObserver(CrawlObserver $crawlObserver): Crawler
    {
        $this->crawlObservers->addObserver($crawlObserver);
        return $this;
    }
    public function getCrawlObservers(): CrawlObserverCollection
    {
        return $this->crawlObservers;
    }

	public function setCrawlQueue(CrawlQueue $crawlQueue): Crawler
    {
        $this->crawlQueue = $crawlQueue;
        return $this;
    }
    public function getCrawlQueue(): CrawlQueue
    {
        return $this->crawlQueue;
    }

    public function finishedCrawling() {
    	echo "failed";
    }
    public function willCrawl()
    {	
    	$crawledUrls = [];
    	$baseUrl = 'http://www.batunet.com';
    	Crawler::create()
           	->setCrawlObserver(new CrawlLogger())
           	->setMaximumCrawlCount(20)
            ->setMaximumDepth(20)
            ->setCrawlProfile(new CrawlSubdomains($baseUrl))
            ->startCrawling($baseUrl);    
		
		$this->depthTree = new Node((string) $this->baseUrl);
        
        $this->startCrawlingQueue();
        
        foreach ($this->crawlObservers as $crawlObserver) {
            $crawlObserver->finishedCrawling();
        }
    }
 protected function getCrawlRequests(): Generator
    {
        while ($crawlUrl = $this->crawlQueue->getFirstPendingUrl()) {
            if (! $this->crawlProfile->shouldCrawl($crawlUrl->url)) {
                $this->crawlQueue->markAsProcessed($crawlUrl);
                continue;
            }
            if ($this->crawlQueue->hasAlreadyBeenProcessed($crawlUrl)) {
                continue;
            }
            foreach ($this->crawlObservers as $crawlObserver) {
                $crawlObserver->willCrawl($crawlUrl->url);
            }
            $this->crawlQueue->markAsProcessed($crawlUrl);
            yield $crawlUrl->getId() => new Request('GET', $crawlUrl->url);
        }
    }
    public function addToCrawlQueue(CrawlUrl $crawlUrl): Crawler
    {
        if (! $this->getCrawlProfile()->shouldCrawl($crawlUrl->url)) {
            return $this;
        }
        if ($this->getCrawlQueue()->has($crawlUrl->url)) {
            return $this;
        }
        $this->crawledUrlCount++;
        $this->crawlQueue->add($crawlUrl);
        return $this;
    }
}

$testCrawler = new CrawlObserver();

$testCrawler -> willCrawl();

?>