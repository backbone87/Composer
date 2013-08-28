<?php

/**
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CCCER2Migration {

	public static function create(CCCContext $ctx) {
		return new self($ctx);
	}

	private $ctx;

	protected function __construct(CCCContext $ctx) {
		$this->ctx = $ctx;
	}

	public function getContext() {
		return $this->ctx;
	}

	public function convertER2ExtensionNameToComposerPackageName($er2Name) {
		// TODO force lc?
		return 'contao-legacy/' . $er2Name;
	}

	public function convertER2VersionToComposerVersion($er2Version, $er2Build) {
		if(!preg_match('/^[0-9]+$/', $er2Version) || !preg_match('/^[0-9]+$/', $er2Build)) {
			throw new Exception(); // TODO
		}

		$stability	= $er2Version % 10;

		$composerVersion = '';
		$composerVersion .= $er2Version / 10000000 % 1000; // major

		$composerVersion .= '.';
		$composerVersion .= $er2Version / 10000 % 1000; // minor

		$composerVersion .= '.';
		$composerVersion .= $er2Version / 10 % 1000; // release

		$composerVersion .= '.';
		$composerVersion .= $stability * 1000 + $er2Build; // build

		if($stability < 3) {
			$composerVersion .= '-alpha';
		} elseif($stability < 6) {
			$composerVersion .= '-beta';
		} elseif($stability < 9) {
			$composerVersion .= '-RC';
		}

		return $composerVersion;
	}

	public function convertER2VersionToComposerConstraint($er2Version, $er2Build) {
		$composerVersion = self::convertER2VersionToComposerVersion($er2Version, $er2Build);
		$upperBound = array_slice(explode('.', $composerVersion, 3), 0, 2);
		$upperBound[1]++;
		$upperBound = implode('.', $upperBound);
		list(, $stability) = explode('-', $composerVersion, 2);
		$stability && $minimumStability = '@' . $stability;
		return '>=' . $composerVersion . ',<' . $upperBound . $minimumStability;
	}

	public function formatER2Version($version) {
		$formatted = '';

		$formatted .= $version / 10000000 % 1000; // major
		$formatted .= '.';

		$formatted .= $version / 10000 % 1000; // minor
		$formatted .= '.';

		$formatted .= $version / 10 % 1000; // release

		$stability = $version % 10;
		if($stability < 9) {
			if($stability < 3) {
				$formatted .= ' alpha';
			} elseif($stability < 6) {
				$formatted .= ' beta';
			} else {
				$formatted .= ' RC';
			}
			$formatted .= ($stability % 3);
		}

		return $formatted;
	}

	public function getInstalledPortableExtensions() {
		$sql = 'SELECT * FROM tl_repository_installs WHERE lickey = \'\'';
		$result = Database::getInstance()->query($sql);
		while($result->next()) {
			$extensions[$result->extension] = $result->row();
		}
		return (array) $extensions;
	}

	public function getCommericalOrPrivateExtensionNames() {
		$sql = 'SELECT extension FROM tl_repository_installs WHERE lickey != \'\'';
		return Database::getInstance()->query($sql)->fetchEach('extension');
	}

	public function getComposerRequires(array $extensionNames = null) {
		$extensions = $this->getInstalledPortableExtensions();
		$extensionNames === null || $extensions = array_intersect_key($extensions, array_flip($extensionNames));
		foreach($extensions as $extensionName => $extension) {
			$name = $this->convertER2ExtensionNameToComposerPackageName($extensionName);
			$constraint = $this->convertER2VersionToComposerConstraint($extension['version'], $extension['build']);
			$requires[$name] = $contraint;
		}
		return (array) $requires;
	}

	public function removeER2Files() {
		$sql = <<<SQL
SELECT		*
FROM		tl_repository_instfiles
WHERE		filename = 'F'
ORDER BY	filename DESC
SQL;
		$er2Files = Database::getInstance()->query($sql);
		while($er2Files->next()) {
			$path = TL_ROOT . '/' . $er2Files->filename;
			if(is_file($path)) {
				$fileIDs[] = $er2Files->id;
				$installIDs[$er2Files->pid] = true;
				unlink($path);
			}
		}

		// TODO OH: y?
		if($fileIDs) {
			$wildcards = rtrim(str_repeat('?,', count($fileIDs)), ',');
			$sql = 'UPDATE tl_repository_instfiles SET flag = \'D\' WHERE id IN (' . $wildcards . ')';
			Database::getInstance()->prepare($sql)->executeUncached($fileIDs);
		}

		$sql = <<<SQL
SELECT		*
FROM		tl_repository_instfiles
WHERE		filename = 'D'
ORDER BY	filename DESC
SQL;
		$er2Dirs = Database::getInstance()->query($sql);
		$skipDots = array('CCCUtil', 'skipDots');
		while($er2Dirs->next()) {
			$path = TL_ROOT . '/' . $er2Dirs->filename;
			if(is_dir($path) && !array_filter(scandir($path), $skipDots)) {
				$installIDs[$er2Dirs->pid] = true;
				rmdir($path);
			}
		}

		if($installIDs) {
			$wildcards = rtrim(str_repeat('?,', count($installIDs)), ',');
			$sql = 'UPDATE tl_repository_installs SET error = 1 WHERE id IN (' . $wildcards . ')';
			Database::getInstance()->prepare($sql)->executeUncached(array_keys($installIDs));
		}
	}

}
