Contributing
=======

Prior to contributing to this repository make sure a JIRA case exists. All
development must follow generic git-flow with rolling pull-requests.

Branching and PR's (pull requests)
-------
Every new feature and/or change must reside on it's own branch, called feature
branch derived from the `develop` branch which is the main development stream.

No direct commits do `develop` branch and, of most importance, - `master`.

`develop` branch commits are only allowed in case of a hotfix implemented,
that adds a very tiny change-set.
Commits of such kind must keep the following commit message:
```
git commit -m "Hotfix - Brief descpription of the change."
```

Any other scenario of sources change must follow the branching principle
described above.

No branch is merged directly either to `develop` or `master`.
Create PR's (pull requests) for that.
However, develop can and must be merged directly to feature branches
(**DO NOT** omit the `--no-ff` to maintain history), to make
sure that new development is made upon the latest change-set. This also
includes regular rebases in case of concurrent development.

Coding standards
-------
The sources follow the Symfony coding standards and generic `PSR-2` standard.  
PSR2: https://www.php-fig.org/psr/psr-2/  
Symfony CS: https://symfony.com/doc/3.4/contributing/code/standards.html

Always use `phpcs` to validate your code prior to pushing. This tool is bundled
with `PSR-2` standard by default. To run a check against whole `/src` directory:
```bash
phpcs src/ --standard=PSR2 --extensions=php
```

Obviously, not every block of code can or should comply with the reports
that this tool generates, however the goal is minify the error messages.

General changes
-------
The source code is divided into three bundles:  
`AdminBundle` - for administrative related code
`ApiBundle` - for REST api related code
`RestMediaTypeBundle` - for Rest media types code

Make sure to add/edit the changes in the appropriate bundle to keep things
decoupled.

The TDD driven development is highly encouraged and in its's final state,
at least REST endpoint should be covered with functional tests as much as
possible. This will help finding issues and bugs way earlier than a certain
scenario will fail on production.

Bundled tests are invoked via the `run-tests.sh` file in the sources root.

This file is configured to generate test coverage reports to guide how well
the tests cover the actual code.

Documenting code
-------
Several thing to outline when documenting code:
1. Aim for a docblock for every function and/or method;
2. Try to expand with some comment tricky places;
3. Always use present tense (i.e. `Adds ...` NOT `Added...`)
when documenting functions/methods;
4. Use imperative mood (i.e. `Fetch records for further processing`
NOT `Fetches records...`);
5. Get familiar with `@see` to outline related portions of code;
6. Use `@deprecated` for code is prior to be removed in next release, or
an alternative routine is implemented;
7. Always place `TODO: SHORT_DESCRIPTION` when a certain block of
code needs rework;
8. Large but understandable code block is better that an award-winning shortest
line of magic;
9. Last but not least - write code that is not shameful to get back to after
a long period;

Database related changes
-------
In case of database/doctrine related changes, i.e. when altering entities, or
records stored in mongodb require changes, use the migrations mechanism.
Database migrations is covered by the respective bundle:
https://packagist.org/packages/doesntmattr/mongodb-migrations-bundle

To create a new migration scenario, generate a blank migration script:
```bash
php5.6 bin/console mongodb:migrations:generate
```

This command will generate a blank migration class in `src/Bpi/ApiBundle/Resources/config/Migrations/MongoDB/VersionYYYYMMDDHHIISS.php`,
i.e.:
```bash
Generated new migration class to "/var/www/dev/rest-api/app/../src/Bpi/ApiBundle/Resources/config/Migrations/MongoDB/Version20190207125042.php"
```

Add your database altering queries into the `up()` and `down()` method of the
class, for upgrading and downgrading schema respectively.

To execute existing migrations, run:
```bash
php5.6 bin/console mongodb:migrations:migrate
```
