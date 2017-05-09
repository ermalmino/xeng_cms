<?php

// src/Xeng/Cms/CoreBundle/Services/Content/NewsArticleManager.php

namespace Xeng\Cms\CoreBundle\Services\Content;

use Doctrine\Common\Persistence\ObjectManager;
use Xeng\Cms\CoreBundle\Doctrine\PaginatedResult;
use Xeng\Cms\CoreBundle\Doctrine\PaginatorUtil;
use Xeng\Cms\CoreBundle\Entity\Content\NewsArticle;
use Xeng\Cms\CoreBundle\Repository\Content\NewsArticleRepository;

/**
 * @author Ermal Mino <ermal.mino@gmail.com>
 *
 */
class NewsArticleManager {

    /** @var ObjectManager $manager */
    private $manager;

    /** @var ContentImageManager $imageManager */
    private $imageManager;

    /**
     * @param ObjectManager $manager
     * @param ContentImageManager $imageManager
     */
    public function __construct(ObjectManager $manager, ContentImageManager $imageManager) {
        $this->manager = $manager;
        $this->imageManager = $imageManager;
    }

    /**
     * @param int $nodeId
     * @return NewsArticle
     */
    public function getNewsArticle($nodeId){
        /** @var NewsArticleRepository $repository */
        $repository = $this->manager->getRepository('XengCmsCoreBundle:Content\NewsArticle');
        return $repository->getNewsArticle($nodeId);
    }

    /**
     * @param int $currentPage
     * @param int $pageSize
     * @return PaginatedResult
     */
    public function getAllNewsArticle($currentPage = 1, $pageSize = 30){
        /** @var NewsArticleRepository $repository */
        $repository = $this->manager->getRepository('XengCmsCoreBundle:Content\NewsArticle');
        /** @var PaginatorUtil $paginator */
        $paginator = new PaginatorUtil($repository->getAllNewsArticlesQuery(),$currentPage,$pageSize);
        return $paginator->getPaginatedResult();
    }

}