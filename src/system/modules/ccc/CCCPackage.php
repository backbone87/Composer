<?php

use Composer\Package\AliasPackage;
use Composer\Package\Package;

class CCCPackage {

	public static function associatePackageNames(array $pkgs) {
		return array_combine(array_map(function(Package $pkg) { return $pkg->getName(); }, $pkgs), $pkgs);
	}

	public static function createAll(CCCContext $ctx) {
		$composer = $ctx->getRuntime()->getComposer();
		$pkgs = self::associatePackageNames($composer->getRepositoryManager()->getLocalRepository()->getPackages());
		$pkgs = array_map(function(Package $pkg) use($ctx) { return self::create($ctx, $pkg); }, $pkgs);
		$pkgs = array_filter($pkgs, function(CCCPackage $pkg) { return !$pkg->isAliasPackage(); });
		return $pkgs;
	}

	public static function create(CCCContext $ctx, Package $pkg = null) {
		return new self($ctx, $pkg);
	}

	private $ctx;

	private $pkg;

	protected function __construct(CCCContext $ctx, Package $pkg = null) {
		$this->ctx = $ctx;
		$this->pkg = $pkg;
	}

	public function getContext() {
		return $this->ctx;
	}

	public function getPackage() {
		return $this->pkg;
	}

	public function isInstalled() {
		return $this->getContext()
			->getRuntime()
			->getComposer()
			->getRepositoryManager()
			->getLocalRepository()
			->hasPackage($this->getPackage());
	}

	public function isAliasPackage() {
		return $this->getPackage() instanceof AliasPackage;
	}

}

