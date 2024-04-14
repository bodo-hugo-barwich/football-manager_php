# NAME

Football-Manager

# DESCRIPTION

This is a web development exercise that simulates a football manager game.

This combines fast template generated dynamic web sites powered by the _Symfony_ Engine with on-page
_Petite-Vue_ `JavaScript`  AJAX calls.

# REQUIREMENTS

To rebuild this web site the **Minimum Requirements** are to have _PHP_, `composer` and `symfony-cli` installed.

The site uses the libraries `Symfony`, `Twig` and `Twig Templates`.

# INSTALLATION

- composer

    The `composer` _PHAR_ Script will install the dependencies on local user level as they are found in the `composer.json`.
    To run the installation call the `composer` Command within the project directory:

            composer install

- database

	The test database can be set up with the `bin/console` script.
	To create and populate the test database with test data run the `bin/console` script in the project directory:

			APP_ENV=test php bin/console doctrine:database:create

			APP_ENV=test php bin/console doctrine:schema:create

			APP_ENV=test php bin/console doctrine:fixtures:load

- test web site

	The test web site can than be started with the `symfony` command line script.
	To start the development web service and visualise the test web site run the `symfony` command line script in
	the project directory:

			APP_ENV=test symfony server:start

- tests

	The automatted tests can be run with the `bin/phpunit` script.
	To execute the automatted tests run the `bin/phpunit` script in the project directory:

			php bin/phpunit

	It is recommended to restore the test database before each test run with the `doctrine:fixtures:load` command as
	described under _database_ above.
