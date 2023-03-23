<?php
/**
 * Autoloader for WordPress.
 */

spl_autoload_register(
	/**
	 * Closure for the autoloader.
	 *
	 * @param class-string $class_name The fully-qualified class name.
	 * @return void
	 */
	static function ( $class_name ) {
		$project_namespace = 'NerdPress_';
		$length            = strlen( $project_namespace );

		// Class is not in our namespace.
		if ( 0 !== strncmp( $project_namespace, $class_name, $length ) ) {
			return;
		}

		// Class file names should be based on the class name with "class-" prepended
		// and the underscores in the class name replaced with hyphens.
		// E.g. model/class-item.php.
		$name = strtolower( str_replace( '_', '-', $class_name ) );

		$file = sprintf(
			'%1$s/includes/class-%2$s.php',
			__DIR__,
			$name
		);

		if ( ! is_file( $file ) ) {
			return;
		}

		require $file;
	}
);
