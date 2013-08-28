<?php

/**
 * PHP Version 5.2
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
interface CCCContext {

	public function getPHPMinVersion();

	public function getContaoMinVersion();

	public function getComposerHomePath($relative = false);

	public function getArtifactRepositoryPath($relative = false);

	public function getComposerCachePath($relative = false);

	public function getLegacyRepositoryURL();

	public function getComposerPharPath($relative = false);

	public function getComposerDownloadURL();

	public function getComposerJSONPath($relative = false);

	public function getComposerLockPath($relative = false);

	/**
	 * @return CCCRuntime
	 */
	public function getRuntime();

}
