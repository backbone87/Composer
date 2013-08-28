<?php

use Composer\Package\PackageInterface;

use Composer\Json\JsonFile;
use Composer\IO\NullIO;
use Composer\Util\ConfigValidator;

class CCCSearchController implements CCCController {

	public static function create(CCCContext $ctx) {
		return new self($ctx);
	}

	private $ctx;

	public function __construct(CCCContext $ctx) {
		$this->ctx = $ctx;
	}

	public function getContext() {
		return $this->ctx;
	}

	public function generate() {
		$query = Input::getInstance()->get('q');
		$pkgs = self::search($this->getContext(), $query);
		$pkgs = $this->compilePackages($pkgs);
		return json_encode($pkgs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}

	public function compilePackages(array $pkgs) {
		return array_map(function(PackageInterface $pkg) {
			return $pkg->getName();
		}, $pkgs);
	}

	/**
	 * @param CCCContext $ctx
	 * @param string $query
	 * @return array<\Composer\Package\PackageInterface>
	 */
	public static function search(CCCContext $ctx, $query) {
		$query = trim(preg_replace('@\s+@', ' ', $query));
		if(!$query) {
			return array();
		}

		$composer = $ctx->getRuntime()->getComposer();

		$repos[] = new PlatformRepository(); // TODO OH: y?
		$repos[] = $composer->getRepositoryManager()->getLocalRepository(); // TODO OH: y?
		$repos[] = $composer->getRepositoryManager()->getRepositories();
		$repos = new CompositeRepository($repos);

		$scope = preg_match('@^[a-z_-]+/[a-z_-]+$@i', $query)
			? RepositoryInterface::SEARCH_NAME
			: RepositoryInterface::SEARCH_FULLTEXT;

		$results = $repos->search($query, $scope);

		foreach($results as $result) if(!isset($packages[$result['name']])) {
			$packages[$result['name']] = $result;
		}

		return (array) $packages;
	}

}
