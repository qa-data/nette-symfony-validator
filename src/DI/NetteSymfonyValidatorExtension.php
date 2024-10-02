<?php

declare(strict_types = 1);

namespace QaData\NetteSymfonyValidator\DI;

use Doctrine\Common\Annotations\AnnotationReader;
use Nette;
use stdClass;
use Symfony\Component\Validator;

/** @method stdClass getConfig() */
final class NetteSymfonyValidatorExtension extends Nette\DI\CompilerExtension
{

	public const Validator = 'symfony.validator';

	public const ValidatorBuilder = 'symfony.validator.builder';

	public const AttributeLoader = 'symfony.validator.attribute.loader';

	public const AnnotationReader = 'doctrine.annotationReader';

	public function getConfigSchema(): Nette\Schema\Schema
	{
		return Nette\Schema\Expect::structure([
			'cache' => Nette\Schema\Expect::structure([
				'directory' => Nette\Schema\Expect::string('../temp/cache'),
				'lifetime' => Nette\Schema\Expect::int(0),
				'namespace' => Nette\Schema\Expect::string('validator.cache'),
			]),
		]);
	}

	public function loadConfiguration(): void
	{
		$config = $this->getConfig();

		$builder = $this->getContainerBuilder();

		// Register annotation reader if not already registered
		if ($builder->hasDefinition(self::AnnotationReader) === false) {
			$builder->addDefinition(self::AnnotationReader)
				->setType(AnnotationReader::class)
				->setAutowired(false);

		}

		// Register attribute loader
		$attributeLoaderDef = $builder->addDefinition(self::AttributeLoader)
			->setType(Validator\Mapping\Loader\LoaderInterface::class)
			->setFactory(Validator\Mapping\Loader\AttributeLoader::class, [
				new Nette\DI\Definitions\Statement('Doctrine\Common\Annotations\PsrCachedReader', [
					sprintf('@%s', self::AnnotationReader),
					new Nette\DI\Definitions\Statement('Symfony\Component\Cache\Adapter\FilesystemAdapter', [
						$config->cache->namespace,
						$config->cache->lifetime,
						$config->cache->directory,
					]),
				]),
			])
			->setAutowired(false);

		// Register validator builder
		$builder->addDefinition(self::ValidatorBuilder)
			->setType(Validator\ValidatorBuilder::class)
			->setFactory(Validator\ValidatorBuilder::class)
			->addSetup('enableAnnotationMapping', [$attributeLoaderDef])->setAutowired(false);

		// Register validator
		$builder->addDefinition(self::Validator)
			->setType(Validator\Validator\ValidatorInterface::class)
			->setFactory('@' . self::ValidatorBuilder . '::getValidator');

	}

}
