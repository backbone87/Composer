<?php

namespace ContaoCommunityAlliance\Contao\Composer\Controller;

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
use ContaoCommunityAlliance\ComposerInstaller\ConfigUpdateException;
use ContaoCommunityAlliance\Contao\Composer\Controller\AbstractController;
use ContaoCommunityAlliance\Contao\Composer\Controller\ClearComposerCacheController;
use ContaoCommunityAlliance\Contao\Composer\Controller\DependencyGraphController;
use ContaoCommunityAlliance\Contao\Composer\Controller\ExpertsEditorController;
use ContaoCommunityAlliance\Contao\Composer\Controller\MigrationWizardController;
use ContaoCommunityAlliance\Contao\Composer\Controller\SearchController;
use ContaoCommunityAlliance\Contao\Composer\Controller\SettingsController;
use ContaoCommunityAlliance\Contao\Composer\Controller\UndoMigrationController;
use ContaoCommunityAlliance\Contao\Composer\Controller\UpdateDatabaseController;

/**
 * Class DetailsController
 */
class DetailsController extends AbstractController
{
	/**
	 * {@inheritdoc}
	 */
	public function handle(\Input $input)
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

		$template              = new \BackendTemplate('be_composer_client_install');
		$template->composer    = $this->composer;
		$template->packageName = $packageName;
		$template->candidates  = $installationCandidates;
		return $template->parse();
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
			/** @var PackageInterface $package */
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
					/** @var \DateTime $dsa */
					/** @var \DateTime $dsb */
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
}
