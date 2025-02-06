<?php declare(strict_types = 1);

namespace QaData\NetteSymfonyValidator;

use Nette\DI\Container;
use Symfony\Component\Validator;
use function class_exists;
use function get_class;
use function sprintf;

final class ContainerConstraintValidatorFactory implements Validator\ConstraintValidatorFactoryInterface
{

	/** @var array<Validator\ConstraintValidatorInterface> */
	private array $validators;

	public function __construct(private readonly Container $container)
	{
		$this->validators = [];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws Validator\Exception\ValidatorException      when the validator class does not exist
	 * @throws Validator\Exception\UnexpectedTypeException when the validator is not an instance of ConstraintValidatorInterface
	 */
	public function getInstance(Validator\Constraint $constraint): Validator\ConstraintValidatorInterface
	{
		/** @var class-string<Validator\ConstraintValidatorInterface> $name */
		$name = $constraint->validatedBy() === 'validator.expression'
			? Validator\Constraints\ExpressionValidator::class
			: $constraint->validatedBy();

		if (!isset($this->validators[$name])) {
			$validator = $this->container->getByType($name, false);

			if ($validator !== null) {
				$this->validators[$name] = $validator;

			} else {
				if (!class_exists($name)) {
					throw new Validator\Exception\ValidatorException(sprintf('Constraint validator "%s" does not exist or is not enabled. Check the "validatedBy" method in your constraint class "%s".', $name, get_class($constraint)));
				}

				$this->validators[$name] = $this->container->createInstance($name);
			}
		}

		if (!$this->validators[$name] instanceof Validator\ConstraintValidatorInterface) {
			throw new Validator\Exception\UnexpectedTypeException($this->validators[$name], Validator\ConstraintValidatorInterface::class);
		}

		return $this->validators[$name];
	}

}
