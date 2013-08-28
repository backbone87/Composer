<?php

use Composer\Composer;
use Composer\Factory;
use Composer\Installer;
use Composer\Console\HtmlOutputFormatter;
use Composer\IO\BufferIO;
use Composer\Json\JsonFile;
use Composer\Package\BasePackage;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Package\CompletePackageInterface;
use Composer\Package\Version\VersionParser;
use Composer\Repository\ComposerRepository;
use Composer\Repository\CompositeRepository;
use Composer\Repository\PlatformRepository;
use Composer\Repository\RepositoryInterface;
use Composer\Util\ConfigValidator;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\Solver;
use Composer\DependencyResolver\Request;
use Composer\DependencyResolver\SolverProblemsException;
use Composer\DependencyResolver\DefaultPolicy;
use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Repository\InstalledArrayRepository;
use Composer\Util\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Class ComposerClientBackend
 *
 * Composer client interface.
 */
class ComposerClientBackend extends BackendModule
{

	/**
	 * The template name
	 *
	 * @var string
	 */
	protected $strTemplate = 'be_composer_client';

	/**
	 * The pathname to the composer config file.
	 *
	 * @var string
	 */
	protected $configPathname = null;

	/**
	 * The io system.
	 *
	 * @var BufferIO
	 */
	protected $io = null;

	/**
	 * The composer instance.
	 *
	 * @var Composer
	 */
	protected $composer = null;

	/**
	 * Compile the current element
	 */
	protected function compile()
	{
		$this->loadLanguageFile('composer_client');

		$input = Input::getInstance();

		// check the local environment
		if (!$this->checkEnvironment($input)) {
			return;
		}

		// load composer and the composer class loader
		$this->loadComposer();
		$extra = $this->composer
			->getPackage()
			->getExtra();

		if (!array_key_exists('contao', $extra) ||
			!array_key_exists('migrated', $extra['contao']) ||
			!$extra['contao']['migrated']
		) {
			$this->migrationWizard($input);
			return;
		}

		if ($input->get('migrate') == 'undo') {
			$this->undoMigration($input);
			return;
		}

		if ($input->get('update') == 'database') {
			$this->updateDatabase($input);
			return;
		}

		if ($input->get('clear') == 'composer-cache') {
			$this->clearComposerCache($input);
			return;
		}

		if ($input->get('settings') == 'dialog') {
			$this->showSettingsDialog($input);
			return;
		}

		if ($input->get('settings') == 'experts') {
			$this->showExpertsEditor($input);
			return;
		}

		if ($input->get('show') == 'dependency-graph') {
			$this->showDependencyGraph($input);
			return;
		}

		// do search
		if ($input->get('keyword')) {
			$this->doSearch($input);
			return;
		}

		// do install
		if ($input->get('install')) {
			$this->showDetails($input);
			return;
		}

		// do solve
		if ($input->get('solve')) {
			$this->solveDependencies($input);
			return;
		}

		if ($input->get('update') == 'packages' || $input->post('update') == 'packages') {
			$this->updatePackages();
			return;
		}

		/**
		 * Remove package
		 */
		if ($input->post('remove')) {
			$this->removePackage($input);
			$this->redirect('contao/main.php?do=composer');
		}

		// update contao version if needed
		$this->checkContaoVersion();

		// calculate dependency graph
		$dependencyMap = $this->calculateDependencyMap(
			$this->composer
				->getRepositoryManager()
				->getLocalRepository()
		);

		$this->Template->dependencyMap = $dependencyMap;
		$this->Template->output        = $_SESSION['COMPOSER_OUTPUT'];

		unset($_SESSION['COMPOSER_OUTPUT']);

		chdir(TL_ROOT);
	}

	/**
	 * Check the local environment, return false if there are problems.
	 *
	 * @param \Input $input
	 *
	 * @return bool
	 */
	protected function checkEnvironment(Input $input)
	{
		$errors = array();

		if ($GLOBALS['TL_CONFIG']['useFTP']) {
			$errors[] = $GLOBALS['TL_LANG']['composer_client']['ftp_mode'];
		}

		// check for php version
		if (version_compare(PHP_VERSION, '5.3.4', '<')) {
			$errors[] = sprintf($GLOBALS['TL_LANG']['composer_client']['php_version'], PHP_VERSION);
		}

		// check for curl
		if (!function_exists('curl_init')) {
			$errors[] = $GLOBALS['TL_LANG']['composer_client']['curl_missing'];
		}

		if (count($errors)) {
			$this->Template->setName('be_composer_client_errors');
			$this->Template->errors = $errors;
			return false;
		}

		/*
		 * Use composer.phar only, if composer is not installed locally
		 */
		if (!file_exists(TL_ROOT . '/composer/vendor/composer/composer/src/Composer/Composer.php') ||
			!file_exists(TL_ROOT . '/composer/vendor/autoload.php')
		) {
			if (!file_exists(TL_ROOT . '/composer/composer.phar')) {
				// switch template
				$this->Template->setName('be_composer_client_install_composer');

				// do install composer library
				if ($input->post('install')) {
					$this->updateComposer();
					$this->reload();
				}

				return false;
			}

			if ($input->get('update') == 'composer') {
				$this->updateComposer();
				$this->redirect('contao/main.php?do=composer');
			}
		}

		return true;
	}

	/**
	 * Undo migration
	 */
	protected function undoMigration(Input $input)
	{
		if ($input->post('FORM_SUBMIT') == 'tl_composer_migrate_undo') {
			$requires = $this->composer
				->getPackage()
				->getRequires();
			foreach ($requires as $package => $constraint) {
				if ($package != 'contao-community-alliance/composer') {
					unset($requires[$package]);
				}
			}
			$this->composer
				->getPackage()
				->setRequires($requires);

			$lockPathname = preg_replace('#\.json$#', '.lock', $this->configPathname);

			$this->composer
				->getDownloadManager()
				->setOutputProgress(false);
			$installer = Installer::create($this->io, $this->composer);

			if (file_exists(TL_ROOT . '/' . $lockPathname)) {
				$installer->setUpdate(true);
			}

			if ($installer->run()) {
				$_SESSION['COMPOSER_OUTPUT'] .= $this->io->getOutput();
			}
			else {
				$_SESSION['COMPOSER_OUTPUT'] .= $this->io->getOutput();

				$this->redirect('contao/main.php?do=composer&migrate=undo');
			}

			// load config
			$json   = new JsonFile(TL_ROOT . '/' . $this->configPathname);
			$config = $json->read();

			// remove migration status
			unset($config['extra']['contao']['migrated']);

			// write config
			$json->write($config);

			// disable composer client and enable repository client
			$inactiveModules   = deserialize($GLOBALS['TL_CONFIG']['inactiveModules']);
			$inactiveModules[] = '!composer';
			foreach (array('rep_base', 'rep_client', 'repository') as $module) {
				$pos = array_search($module, $inactiveModules);
				if ($pos !== false) {
					unset($inactiveModules[$pos]);
				}
			}
			if (version_compare(VERSION, '3', '>=')) {
				$skipFile = new File('system/modules/!composer/.skip');
				$skipFile->write('Remove this file to enable the module');
				$skipFile->close();
			}
			if (file_exists(TL_ROOT . '/system/modules/repository/.skip')) {
				$skipFile = new File('system/modules/repository/.skip');
				$skipFile->delete();
			}
			$this->Config->update("\$GLOBALS['TL_CONFIG']['inactiveModules']", serialize($inactiveModules));

			$this->redirect('contao/main.php?do=repository_manager');
		}

		$this->Template->setName('be_composer_client_migrate_undo');
		$this->Template->output = $_SESSION['COMPOSER_OUTPUT'];

		unset($_SESSION['COMPOSER_OUTPUT']);
	}

	/**
	 * Show graph of dependencies.
	 *
	 * @param \Input $input
	 */
	protected function showDependencyGraph(Input $input)
	{
		$localRepository = $this->composer
			->getRepositoryManager()
			->getLocalRepository();

		$dependencyMap = $this->calculateDependencyMap($localRepository);

		$dependencyGraph = array();

		$localPackages = $localRepository->getPackages();

		$localPackages = array_filter(
			$localPackages,
			function ($localPackage) use ($dependencyMap) {
				return !isset($dependencyMap[$localPackage->getName(
				)]) && !($localPackage instanceof \Composer\Package\AliasPackage);
			}
		);

		$allLocalPackages = $localRepository->getPackages();
		$allLocalPackages = array_combine(
			array_map(
				function ($localPackage) {
					return $localPackage->getName();
				},
				$allLocalPackages
			),
			$allLocalPackages
		);

		$localPackagesCount = count($localPackages);
		$index              = 0;

		/** @var \Composer\Package\PackageInterface $package */
		foreach ($localPackages as $package) {
			$this->buildDependencyGraph(
				$allLocalPackages,
				$localRepository,
				$package,
				null,
				$package->getPrettyVersion(),
				$dependencyGraph,
				++$index == $localPackagesCount
			);
		}

		$this->Template->setName('be_composer_client_dependency_graph');
		$this->Template->dependencyGraph = $dependencyGraph;
	}

	/**
	 * Build the dependency graph with installed packages.
	 *
	 * @param RepositoryInterface $repository
	 * @param PackageInterface    $package
	 * @param array               $dependencyGraph
	 */
	protected function buildDependencyGraph(
		array $localPackages,
		RepositoryInterface $repository,
		PackageInterface $package,
		$requiredFrom,
		$requiredConstraint,
		array &$dependencyGraph,
		$isLast,
		$parents = 0
	) {
		$current           = (object) array(
			'package'     => $package,
			'required'    => (object) array(
				'from'       => $requiredFrom,
				'constraint' => $requiredConstraint,
				'parents'    => $parents,
			),
			'lastInLevel' => $isLast ? $parents - 1 : -1
		);
		$dependencyGraph[] = $current;

		$requires      = $package->getRequires();
		$requiresCount = count($requires);
		$index         = 0;
		/** @var string $requireName */
		/** @var \Composer\Package\Link $requireLink */
		foreach ($requires as $requireName => $requireLink) {
			if (isset($localPackages[$requireName])) {
				$this->buildDependencyGraph(
					$localPackages,
					$repository,
					$localPackages[$requireName],
					$package,
					$requireLink->getPrettyConstraint(),
					$dependencyGraph,
					++$index == $requiresCount,
					$parents + 1
				);
			}
			else {
				$dependencyGraph[] = (object) array(
					'package'     => $requireName,
					'required'    => (object) array(
						'from'       => $package,
						'constraint' => $requireLink->getPrettyConstraint(),
						'parents'    => $parents + 1,
					),
					'lastInLevel' => ++$index == $requiresCount ? $parents : -1
				);
			}
		}
	}

	/**
	 * Show package details.
	 *
	 * @param Input $input
	 */
	protected function showDetails(Input $input)
	{
		$packageName = $input->get('install');

		if ($input->post('version')) {
			$version = $input->post('version');

			$this->redirect(
				'contao/main.php?' . http_build_query(
					array(
						 'do'      => 'composer',
						 'solve'   => $packageName,
						 'version' => $version
					)
				)
			);
		}

		$installationCandidates = $this->searchPackage($packageName);

		if (empty($installationCandidates)) {
			$_SESSION['TL_ERROR'][] = sprintf(
				$GLOBALS['TL_LANG']['composer_client']['noInstallationCandidates'],
				$packageName
			);

			$_SESSION['COMPOSER_OUTPUT'] = $this->io->getOutput();
			$this->redirect('contao/main.php?do=composer');
		}

		$this->Template->setName('be_composer_client_install');
		$this->Template->packageName = $packageName;
		$this->Template->candidates  = $installationCandidates;
	}

	/**
	 * Solve package dependencies.
	 *
	 * @param Input $input
	 */
	protected function solveDependencies(Input $input)
	{
		$rootPackage = $this->composer->getPackage();

		$installedRootPackage = clone $rootPackage;
		$installedRootPackage->setRequires(array());
		$installedRootPackage->setDevRequires(array());

		$localRepository     = $this->composer
			->getRepositoryManager()
			->getLocalRepository();
		$platformRepo        = new PlatformRepository;
		$installedRepository = new CompositeRepository(
			array(
				 $localRepository,
				 new InstalledArrayRepository(array($installedRootPackage)),
				 $platformRepo
			)
		);

		$packageName = $input->get('solve');
		$version     = base64_decode(rawurldecode($input->get('version')));

		$versionParser = new VersionParser();
		$constraint    = $versionParser->parseConstraints($version);
		$stability     = $versionParser->parseStability($version);

		$aliases = $this->getRootAliases($rootPackage);
		$this->aliasPlatformPackages($platformRepo, $aliases);

		$stabilityFlags               = $rootPackage->getStabilityFlags();
		$stabilityFlags[$packageName] = BasePackage::$stabilities[$stability];

		$pool = $this->getPool($rootPackage->getMinimumStability(), $stabilityFlags);
		$pool->addRepository($installedRepository, $aliases);

		$policy = new DefaultPolicy($rootPackage->getPreferStable());

		$request = new Request($pool);

		// add root package
		$rootPackageConstraint = new VersionConstraint('=', $rootPackage->getVersion());
		$rootPackageConstraint->setPrettyString($rootPackage->getPrettyVersion());
		$request->install($rootPackage->getName(), $rootPackageConstraint);

		// add requirements
		$links = $rootPackage->getRequires();
		foreach ($links as $link) {
			if ($link->getTarget() != $packageName) {
				$request->install($link->getTarget(), $link->getConstraint());
			}
		}
		foreach ($installedRepository->getPackages() as $package) {
			$request->install($package->getName(), new VersionConstraint('=', $package->getVersion()));
		}

		$operations = array();
		try {
			$solver = new Solver($policy, $pool, $installedRepository);

			$beforeOperations = $solver->solve($request);

			$request->install($packageName, $constraint);

			$operations = $solver->solve($request);

			/** @var \Composer\DependencyResolver\Operation\SolverOperation $beforeOperation */
			foreach ($beforeOperations as $beforeOperation) {
				/** @var \Composer\DependencyResolver\Operation\InstallOperation $operation */
				foreach ($operations as $index => $operation) {
					if ($operation
							->getPackage()
							->getName() != $packageName &&
						$beforeOperation->__toString() == $operation->__toString()
					) {
						unset($operations[$index]);
					}
				}
			}

			if ($input->post('mark') || $input->post('install')) {
				// make a backup
				copy(TL_ROOT . '/' . $this->configPathname, TL_ROOT . '/' . $this->configPathname . '~');

				// update requires
				$json   = new JsonFile(TL_ROOT . '/' . $this->configPathname);
				$config = $json->read();
				if (!array_key_exists('require', $config)) {
					$config['require'] = array();
				}
				$config['require'][$packageName] = $version;
				$json->write($config);

				$_SESSION['TL_INFO'][] = sprintf(
					$GLOBALS['TL_LANG']['composer_client']['added_candidate'],
					$packageName,
					$version
				);

				$_SESSION['COMPOSER_OUTPUT'] .= $this->io->getOutput();

				if ($input->post('install')) {
					$this->redirect('contao/main.php?do=composer&update=packages');
				}
				$this->redirect('contao/main.php?do=composer');
			}
		}
		catch (SolverProblemsException $e) {
			$_SESSION['TL_ERROR'][] = sprintf(
				'<span style="white-space: pre-line">%s</span>',
				trim($e->getMessage())
			);
		}

		$this->Template->setName('be_composer_client_solve');
		$this->Template->packageName    = $packageName;
		$this->Template->packageVersion = $version;
		$this->Template->operations     = $operations;
	}

	/**
	 * Search for a single packages versions.
	 *
	 * @param string $packageName
	 *
	 * @return PackageInterface[]
	 */
	protected function searchPackage($packageName)
	{
		$rootPackage = $this->composer->getPackage();

		$pool = $this->getPool();

		$versions = array();
		$seen     = array();
		$matches  = $pool->whatProvides($packageName);
		foreach ($matches as $package) {
			// skip providers/replacers
			if ($package->getName() !== $packageName) {
				continue;
			}
			// add each version only once to skip installed version.
			if (!in_array($package->getPrettyVersion(), $seen)) {
				$seen[]     = $package->getPrettyVersion();
				$versions[] = $package;
			}
		}

		usort(
			$versions,
			function (PackageInterface $packageA, PackageInterface $packageB) {
				// is this a wise idea?
				if (($dsa = $packageA->getReleaseDate()) && ($dsb = $packageB->getReleaseDate())) {
					return $dsb->getTimestamp() - $dsa->getTimestamp();
				}

				/*
				$versionA = $this->reformatVersion($packageA);
				$versionB = $this->reformatVersion($packageB);

				$classicA = preg_match('#^\d(\.\d+)*$#', $versionA);
				$classicB = preg_match('#^\d(\.\d+)*$#', $versionB);

				$branchA = 'dev-' == substr($packageA->getPrettyVersion(), 0, 4);
				$branchB = 'dev-' == substr($packageB->getPrettyVersion(), 0, 4);

				if ($branchA && $branchB) {
					return strcasecmp($branchA, $branchB);
				}
				if ($classicA && $classicB) {
					if ($packageA->getPrettyVersion() == 'dev-master') {
						return -1;
					}
					if ($packageB->getPrettyVersion() == 'dev-master') {
						return 1;
					}
					return version_compare($versionB, $versionA);
				}
				if ($classicA) {
					return -1;
				}
				if ($classicB) {
					return 1;
				}
				return 0;
				*/
			}
		);

		return $versions;
	}

	/**
	 * Remove a package from the requires list.
	 *
	 * @param Input $input
	 */
	protected function removePackage(Input $input)
	{
		$removeName = $input->post('remove');

		// make a backup
		copy(TL_ROOT . '/' . $this->configPathname, TL_ROOT . '/' . $this->configPathname . '~');

		// update requires
		$json   = new JsonFile(TL_ROOT . '/' . $this->configPathname);
		$config = $json->read();
		if (!array_key_exists('require', $config)) {
			$config['require'] = array();
		}
		unset($config['require'][$removeName]);
		$json->write($config);

		$_SESSION['TL_INFO'][] = sprintf(
			$GLOBALS['TL_LANG']['composer_client']['removeCandidate'],
			$removeName
		);

		$_SESSION['COMPOSER_OUTPUT'] .= $this->io->getOutput();
	}

	/**
	 * Check the contao version in the config file and update if necessary.
	 */
	protected function checkContaoVersion()
	{
		/** @var \Composer\Package\RootPackage $package */
		$package       = $this->composer->getPackage();
		$versionParser = new VersionParser();
		$version       = VERSION . (is_numeric(BUILD) ? '.' . BUILD : '-' . BUILD);
		$prettyVersion = $versionParser->normalize($version);
		if ($package->getVersion() !== $prettyVersion) {
			$configFile            = new JsonFile(TL_ROOT . '/' . $this->configPathname);
			$configJson            = $configFile->read();
			$configJson['version'] = $version;
			$configFile->write($configJson);

			$_SESSION['COMPOSER_OUTPUT'] .= $this->io->getOutput();
			$this->reload();
		}
	}

	private function getRootAliases(RootPackageInterface $rootPackage)
	{
		$aliases = $rootPackage->getAliases();

		$normalizedAliases = array();

		foreach ($aliases as $alias) {
			$normalizedAliases[$alias['package']][$alias['version']] = array(
				'alias'            => $alias['alias'],
				'alias_normalized' => $alias['alias_normalized']
			);
		}

		return $normalizedAliases;
	}

	private function aliasPlatformPackages(PlatformRepository $platformRepo, $aliases)
	{
		foreach ($aliases as $package => $versions) {
			foreach ($versions as $version => $alias) {
				$packages = $platformRepo->findPackages($package, $version);
				foreach ($packages as $package) {
					$aliasPackage = new AliasPackage($package, $alias['alias_normalized'], $alias['alias']);
					$aliasPackage->setRootPackageAlias(true);
					$platformRepo->addPackage($aliasPackage);
				}
			}
		}
	}
}
