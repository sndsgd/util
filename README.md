# sndsgd-util

## Requirements

You need **PHP >= 5.5.0** to use this library, however, the latest stable version of PHP is recommended.


## Install

Install `sndsgd/sndsgd-util` using Composer.

```
composer require sndsgd/sndsgd-util
```

## Testing

Run phpunit in the root directory of this repo.

Example: run tests and create an html coverage report
```
vendor/bin/phpunit --coverage-html ~/Downloads/coverage-report
```


## Documentation

Use ApiGen to create docs. The included config file ```apigen.neon``` will create ```/docs``` in the root directory of this repository.

```
apigen generate
```
