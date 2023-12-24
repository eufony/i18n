<h1 align="center">The Eufony I18N Package</h1>

<p align="center">
    <a href="https://packagist.org/packages/eufony/i18n">
        <img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/eufony/i18n?label=Packagist%20Downloads">
    </a>
    <a href="https://github.com/eufony/i18n">
        <img alt="GitHub Stars" src="https://img.shields.io/github/stars/eufony/i18n?label=GitHub%20Stars">
    </a>
    <a href="https://github.com/eufony/i18n/issues">
        <img alt="Issues" src="https://img.shields.io/github/issues/eufony/i18n/open?label=Issues">
    </a>
    <br>
    <a href="https://github.com/eufony/i18n#license">
        <img alt="License" src="https://img.shields.io/github/license/eufony/i18n?label=License">
    </a>
    <a href="https://github.com/eufony/i18n#contributing">
        <img alt="Community Built" src="https://img.shields.io/badge/Made%20with-%E2%9D%A4-red">
    </a>
</p>

*eufony/i18n provides an easy-to-use but limited token-based approach to internationalization.*

*eufony/i18n* is a PHP library that allows defining and retrieving translated messages, called tokens. It provides a
common interface for fetching tokens from different backends; with the explicit goal of providing a naive approach to
varying grammatical rules, which is simpler than a more complete system such as
GNU [`gettext`](https://www.gnu.org/software/gettext/).

Interested? [Here's how to get started.](#getting-started)

## Getting started

### Installation

*eufony/i18n* is released as a [Packagist](https://packagist.org/) package and can be easily installed
via [Composer](https://getcomposer.org/) with:

    composer require "eufony/i18n:v1.x-dev"

> **Warning**: This package ***does not have any stable releases*** yet (not even a v0.x pre-release) and is currently
> ***unstable***. Expect frequent breaking changes and instability!

### Basic Usage

*eufony/i18n* provides two main classes to provide translation facilities: `Stores` and a `Translator`. Stores provide
the actual `Tokens` that contain the translated messages. The `Translator` class acts as a front-end to using a store.
To get started, choose a store implementation and pass it to a new translator, like so:

```php
$store = /* ... */;
$translator = new Translator($store);
```

Out of the box, *eufony/i18n* provides two different store implementations:

```php
// Fetch tokens from PHP arrays
// You could also for example parse this array from a JSON string
$store = new ArrayStore(["greetings.weather.good" => ["en" => "...", "de" => "...", "ru" => "..."]]);
$store = ArrayStore::fromJSON(File::read("/extras/i18n/tokens.json"));  // `File` requires `eufony/filesystem`

// Fetch tokens from an SQL database using the `eufony/dbal` abstraction layer
$connection = new Connection(/* ... */);  // a Connection instance from `eufony/dbal`
$store = new SQLStore($connection);
```

Once the translator is initialized, you can start using it to translate messages or fetch tokens. Tokens are
pre-translated messages that have been fed into the store, whereas translations may happen on-the-fly depending on the
store implementation.

```php
$greeting = $translator->translate("Hello, {user}.", ["en" => "de"]);  // if we only need a single target language
$greeting = $translator->translate("Hello, {user}.", ["en" => ["de", "ru"]]);  // for multiple target languages
$weather = $translator->token("greetings.weather.good");

// Once we have our tokens, we can use `get()` to extract the messages in the target languages
echo $greeting->interpolate(["user" => "Euphie"])->get("de");
echo $weather->get("de");
```

The constructor of the `Translator` class can also take an option `preferredLanguage` parameter, which will specify a
default translation language. You can use this to simplify some of these calls if you already know the target language
beforehand.

```php
$translator = new Translator($store, preferredLanguage: "de");

// Now, we can assume the target language to be the preferred language by default
// But we can still override it for specific situations like above
$greeting = $translator->translate("Hello, {user}.", "en");
$weather = $translator->token("greetings.weather.good");

// If the preferred language is set, tokens can be converted to strings automatically
// No more need to call `get()` every time
echo $greeting->interpolate(["user" => "Euphie"]);
echo $weather;
```

## Contributing

Found a bug or a missing feature? You can report it over at the [issue tracker](https://github.com/eufony/i18n/issues).

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
details.

You should have received a copy of the GNU Lesser General Public License along with this program. If not,
see <https://www.gnu.org/licenses/>.
