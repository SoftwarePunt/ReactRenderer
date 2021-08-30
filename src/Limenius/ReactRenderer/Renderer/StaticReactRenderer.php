<?php

namespace Limenius\ReactRenderer\Renderer;

use Psr\Cache\CacheItemPoolInterface;

class StaticReactRenderer extends AbstractReactRenderer
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var AbstractReactRenderer
     */
    private $renderer;

    public function __construct(?AbstractReactRenderer $renderer, CacheItemPoolInterface $cache = null)
    {
        $this->setRenderer($renderer);

        if ($cache)
            $this->setCache($cache);
    }

    public function setRenderer(?AbstractReactRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function setCache(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function render($componentName, $propsString, $uuid, $registeredStores = [], bool $trace = false)
    {
        if (!$this->renderer) {
            return null;
        }

        $cacheItem = null;

        if ($this->cache) {
            $propsHash = md5($propsString);
            $cacheKey = "{$componentName}_{$propsHash}_rendered";

            $cacheItem = $this->cache->getItem($cacheKey);

            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }
        }

        $rendered = $this->renderer->render($componentName, $propsString, $uuid, $registeredStores, $trace);

        if ($cacheItem) {
            $cacheItem->set($rendered);
            $this->cache->save($cacheItem);
        }

        return $rendered;
    }
}
