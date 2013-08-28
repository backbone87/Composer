<?php

use Composer\DependencyResolver\Pool;
use Composer\Repository\CompositeRepository;
use Composer\Repository\PlatformRepository;
use Composer\Repository\RepositoryInterface;

/**
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CCCOverviewController implements CCCController {

	public static function create(CCCContext $ctx) {
		return new self($ctx);
	}

	const DEFAULT_TEMPLATE = 'ccc_overview';

	private $ctx;

	protected function __construct(CCCContext $ctx) {
		$this->ctx = $ctx;
		CCCUtil::_loadLanguageFile('ccc');
	}

	public function getContext() {
		return $this->ctx;
	}

	public function generate() {

		/** @var \Composer\Composer $composer */
		$composer = $this->composer;
		/** @var \Composer\Package\RootPackage $package */
		$package = $composer->getPackage();
		/** @var \Composer\Repository\RepositoryManager $repositoryManager */
		$repositoryManager = $composer->getRepositoryManager();
		/** @var \Composer\Repository\RepositoryInterface $localRepository */
		$localRepository = $repositoryManager->getLocalRepository();
		/** @var array $requires */
		$requires = $package->getRequires();
		/** @var array $stabilityFlags */
		$stabilityFlags = $package->getStabilityFlags();
		/** @var array $stabilitiesReverseMap */
		$stabilitiesReverseMap = array_flip(\Composer\Package\BasePackage::$stabilities);
		/** @var array $dependencyMap */
		$dependencyMap = $this->dependencyMap;
		/** @var array $localPackages */
		$localPackages = $localRepository->getPackages();
		foreach ($localPackages as $index => $localPackage) {
			if ($localPackage instanceof \Composer\Package\AliasPackage) {
				unset($localPackages[$index]);
			}
		}
		usort(
		$localPackages,
		function(\Composer\Package\PackageInterface $packageA, \Composer\Package\PackageInterface $packageB) use ($dependencyMap) {
			$packageAisDependency = isset($dependencyMap[$packageA->getName()]);
			$packageBisDependency = isset($dependencyMap[$packageB->getName()]);
			if (count($packageA->getReplaces())) {
				foreach ($packageA->getReplaces() as $replace => $constraint) {
					$packageAisDependency = $packageAisDependency || isset($dependencyMap[$replace]);
				}
			}
			if (count($packageB->getReplaces())) {
				foreach ($packageB->getReplaces() as $replace => $constraint) {
					$packageBisDependency = $packageBisDependency || isset($dependencyMap[$replace]);
				}
			}
			if ($packageAisDependency && !$packageBisDependency) {
				return 1;
			}
			else if (!$packageAisDependency && $packageBisDependency) {
				return -1;
			}
			return strcasecmp($packageA->getName(), $packageB->getName());
		}
		);
		$dependencyCount = 0;


		$dependencyMap = $this->calculateDependencyMap(
			$this->composer
			->getRepositoryManager()
			->getLocalRepository()
		);

		return $this->createTemplate()->parse();
	}

	/**
	 * @param RepositoryInterface $repository
	 * @param bool $inverted
	 * @return array<string, array<string, string>>
	 */
	protected function calculateDependencyMap(RepositoryInterface $repo, $inverted = false) {
		foreach($repo->getPackages() as $pkg) foreach($pkg->getRequires() as $link) {
			$a = $pkg->getName();
			$b = $link->getTarget();
			$inverted && list($a, $b) = array($b, $a);
			$map[$a][$b] = $link->getPrettyConstraint();
		}
		return (array) $map;
	}

	public function createPool(array $stabilityFlags = null) {
		$composer = $this->getContext()->getRuntime()->getComposer();
		$stabilityFlags === null && $stabilityFlags = $composer->getPackage()->getStabilityFlags();
		$pool = new Pool($composer->getPackage()->getMinimumStability(), $stabilityFlags);
		$pool->addRepository(new PlatformRepository());
		$pool->addRepository($composer->getRepositoryManager()->getLocalRepository());
		$pool->addRepository($composer->getRepositoryManager()->getRepositories());
		return $pool;
	}

	public function createTemplate($tpl = self::DEFAULT_TEMPLATE) {
		$tpl = new BackendTemplate($tpl);

		$chk = CCCCheck::create($this->getContext());

		$tpl->action		= Environment::getInstance()->request;
		$tpl->formSubmit	= 'ccc_check';

		return $tpl;
	}

}
