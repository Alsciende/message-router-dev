
security: vendor/bin ## (PHP) Check if application uses dependencies with known security vulnerabilities
	@echo
	./bin/console security:check

check: cs stan ## (PHP) Launch all lint tools. A good choice for pre-commit hook

cs: vendor/bin ## (PHP) Code style checker
	@echo
	./vendor/bin/php-cs-fixer fix -v --dry-run --allow-risky=yes --using-cache=no

fix: vendor/bin ## (PHP) Code style fixer
	@echo
	./vendor/bin/php-cs-fixer fix --verbose --allow-risky=yes

stan: vendor/bin ## (PHP) Static analysis
	@echo
	./vendor/bin/phpstan analyse -c phpstan.neon -l 7 src

test: unit behat ## (PHP) Launch all test tools

unit: vendor/bin ## (PHP) Unit tests
	@echo
	./vendor/bin/phpunit

behat: vendor/bin ## (PHP) Unit tests
	@echo
	./vendor/bin/behat

behat-list: vendor/bin ## (PHP) Unit tests
	@echo
	./vendor/bin/behat -d l

vendor/bin:
	@echo
	composer install

clean:
	rm -rf vendor/ public/bundles/ var/

