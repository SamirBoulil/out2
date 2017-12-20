run_tests:
		vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php --using-cache=no
		vendor/bin/phpunit
