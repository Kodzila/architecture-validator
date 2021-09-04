# Architecture validator

## Motivation
Most projects are monoliths with some rules maintained to allow projects to scale
if needed. Those rules must normally be enforced during code review and are exhausting to check each time.

This library aims to specify those rules explicitly by having a tool to check the rules automatically.

Inspiration was taken from Java [ArchUnit](https://github.com/TNG/ArchUnit) and not maintained anymore
[PhpArch](https://github.com/j6s/phparch).

## Installation
Via composer:
```bash
composer require kodzila/architecture-validator --dev
```

## Running
Library itself does not provide any runner - it is only set of functionalities to validate rules. You need to write runner
of your own choice.

### Runner: PHPUnit
Personally I recommend using this runner as it is quite easy to configure, and almost each project is shipped with that
library.

* (If not already) Install PHPUnit:
```bash
composer require phpunit/phpunit --dev
```

* Create a test case in a directory of your choice. I prefer to configure it in `tests/Architecture/ArchitectureTest.php`:
```php
use Kodzila\ArchValidator\Architecture;
use Kodzila\ArchValidator\Rule\CoreModuleRule;
use PHPUnit\Framework\TestCase;

final class ArchitectureTest extends TestCase
{
    public function test(): void
    {
        $architecture = Architecture::build()
            ->defineModule(
                'Core',
                'Isav\\Core\\',
                'src/Core'
            )
            ->defineModule(
                'Armap',
                'Isav\\Armap\\',
                'src/Armap'
            )
            ->defineModule(
                'Import',
                'Isav\\Import\\',
                'src/Import'
            )
        ;

        $architecture->checkRule(new CoreModuleRule('Core'));
        $this->assertTrue(true);
    }

}
```

* Create a new test suite in `phpunit.xml`:
```xml
<testsuites>
        <testsuite name="Architecture">
            <directory>tests/Architecture</directory>
        </testsuite>
    </testsuites>
```

* Add script to `composer.json`:
```json
"scripts": {
    "test:arch": [
        "bin/phpunit --testsuite Architecture"
    ]
}
```

* Now you can run validator with command:
```bash
composer test:arch
```

## Concept
Unlike other modern OOP languages like Java and C#, PHP has no concept of packaging inside one project. The only thing 
are folders, but they cannot be restricted by scope modifiers.

As mentioned earlier, project can be a monolith application, but without any structure projects can quickly 
become a spaghetti code.

### Modular approach
The idea is that project source code can be bundled in *Modules*. Each module has a *Path* and corresponding PHP 
*Namespace*.

Consider such project:
```text
.
+-- config
|   +-- services.yaml
|   +-- routing.yaml
+-- src
|   +-- Armap
|      +-- SyncService.php
|   +-- Core
|      +-- CoreEntity.php
|   +-- DoctrineMigrations
|      +-- Migration214312412.php
+-- tests
|   +-- E2E.php
```

You can see that project has been divided in modules: `Armap` and `Core`. Architecture validator can reflect such 
situation:
```php
        $architecture = Architecture::build()
            ->defineModule(
                'Core',
                'Isav\\Core\\',
                'src/Core'
            )
            ->defineModule(
                'Armap',
                'Isav\\Armap\\',
                'src/Armap'
            )
        ;
```

Each module can contain set of PHP `classes`, `interfaces` and `traits` that will be analysed.

### Rules
Architecture validator provides set of validators to check for common used approaches. They are called `Rules` and
they can be found in `src/Rule/Extension`

#### CoreModuleRule
Rule is assigning one module as `Core` module. Rest of registered modules are treated as `Submodule`.

Checks:
* `Core` module cannot depend on any `Submodule`.
* `Submodule` can depend on `Core` module, but cannot on any other `Submodule`.

This approach allows creating easy to maintain packages. Dependencies are only in
one direction.

#### DomainDrivenDesignRule
The rule is enforcing structures typical for this module architecture. Typical structure looks like that:
```text
.
+-- Core
|   +-- Domain
|   +-- Application
|   +-- Infrastructure
|   +-- Presentation
```
You can read more about the structure [here](https://herbertograca.com/2017/09/07/domain-driven-design/).

Further, {Domain, Application, Infrastructure, Presentation} shall be called `Layers`

Checks:
* Domain layer cannot depend on any other layer.
* Application layer can depend only on Domain layer.

#### DomainForbiddenDependenciesRule
Domain is a heart of the system, and should not be polluted with unstable libraries. It should be well-tought process. 
The rule ensures that developers *thought* about implications of adding dependency into Domain layer.

[Quote:](https://www.infoq.com/articles/ddd-in-practice/)
```text
Domain is the heart of the business application and should be well isolated from the other layers of the application. 
Also, it should not be dependent on the application frameworks used in the other layers (JSP/JSF, Struts, EJB, Hibernate, 
XMLBeans and so-on).
```

You need to add dependency whitelisted to ignore the error.
```php
$architecture->checkRules([
    new DomainForbiddenDependenciesRule(['Core'], [
          'Doctrine\ORM\Mapping',
          'Doctrine\Common\Collections',
          'Ramsey\Uuid\UuidInterface',
    ])
]);
```

## Development
* Clone the repository

* Install dependencies
```bash
composer install
```

### Release a new version
* Run `composer mr` (static code analysis) to make sure library code is up to standard.
* Add changes
* Commit changes
* Tag the changes
* Push to master
