<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;

require_once __DIR__ . '/Model/Article.php';
require_once __DIR__ . '/Model/Category.php';

class Bootstrap
{
	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function __construct() {
		$dbParams = array(
			'dbname' => 'demo',
			'memory' => 'true',
			'driver' => 'pdo_sqlite',
		);
		$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__ . '/Model'), true);
		$this->entityManager = EntityManager::create($dbParams, $config);
		$tool = new SchemaTool($this->entityManager);
		$tool->createSchema($this->entityManager->getMetadataFactory()->getAllMetadata());

        // Create data
		$article = new Article(1);
        $category = new Category(1);
        $article->setCategory($category);
        $category->addArticle($article);
        $this->entityManager->persist($article);
        $this->entityManager->persist($category);

        $article = new Article(2);
        $this->entityManager->persist($article);

		$this->entityManager->flush();
	}

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }
}
