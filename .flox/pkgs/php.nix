{ pkgs, ... }:
let
	php = pkgs.php83;
in
php.buildEnv {
	extensions = ({ enabled, all }:
		enabled ++ [
			all.xsl
			all.intl
			all.soap
			all.zip
      all.xdebug
		]
	);

	extraConfig = ''
		; Avoid deprecation output in HTTP responses before headers.
		display_errors = Off
		display_startup_errors = Off
		log_errors = On
		error_reporting = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED

		; Required for PHPUnit --coverage-* commands.
		xdebug.mode = coverage
	'';
}
