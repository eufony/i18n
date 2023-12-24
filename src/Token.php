<?php
/*
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Eufony\I18N;

use InvalidArgumentException;
use Stringable;

/**
 * Holds translations of a message in many different languages.
 *
 * Message may have placeholder values, which can be interpolated with real
 * values after the token has been received from the store.
 */
class Token implements Stringable
{
    /**
     * Key-value pairs of languages and translations of this token.
     *
     * @var string[] $translations
     */
    protected array $translations;

    /**
     * The default target language of this token.
     *
     * @var string|null $preferredLanguage
     */
    protected string|null $preferredLanguage;

    /**
     * Class constructor.
     * Creates a new token with the given translations and (optionally) a default
     * language.
     *
     * @param string[] $translations
     * @param string|null $preferredLanguage
     */
    public function __construct(array $translations, ?string $preferredLanguage = null)
    {
        $this->translations = $translations;
        $this->preferredLanguage = $preferredLanguage;
    }

    /**
     * Returns this token's translation in the preferred language, if one exists.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->get($this->preferredLanguage);
    }

    /**
     * Combined getter / setter for the preferred language.
     *
     * Returns the current preferred language, or null if none is set.
     * If `$preferredLanguage` is set, sets the new default language and returns
     * the previous instance.
     *
     * @param string|null $preferredLanguage
     * @return string|null
     */
    public function preferredLanguage(?string $preferredLanguage = null): string|null
    {
        $prev = $this->preferredLanguage;
        $this->preferredLanguage = $preferredLanguage ?? $this->preferredLanguage;
        return $prev;
    }

    /**
     * Returns if this token has an available translation.
     *
     * If multiple languages are specified in `$lang`, checks if translations are
     * available in all of them.
     *
     * @param string|array $lang
     * @return bool
     */
    public function has(string|array $lang): bool
    {
        if (is_string($lang)) {
            $lang = [$lang];
        }

        // Check if the requested languages are a subset of the available ones
        return !array_diff($lang, array_keys($this->translations));
    }

    /**
     * Returns this token's translation in the specified language, if one exists.
     *
     * If no language is specified, the preferred language will be defaulted to.
     *
     * @param string|null $lang
     * @return string
     */
    public function get(?string $lang = null): string
    {
        $lang = $lang ?? $this->preferredLanguage;
        $lang = $lang ?? throw new TranslationException("No preferred language has been set for this token");

        if (!$this->has($lang)) {
            throw new TranslationException("No translation exists for the requested language");
        }

        return $this->translations[$lang];
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * Returns a new Token with interpolated values for all translations.
     *
     * @param array $context
     * @param string|null $lang
     * @return Token
     */
    public function interpolate(array $context, ?string $lang = null): Token
    {
        // Build a replacement array with braces around the context keys
        $replace = [];

        foreach ($context as $key => $value) {
            // Ensure value can be typecast to string
            if (!is_string($value) && !($value instanceof Stringable)) {
                throw new InvalidArgumentException("Value in context array must be a string");
            }

            // Ensure objects are cast to strings
            /** @var string $value */
            $value = "$value";

            // Add key-value pair to replacement array
            $replace['{' . $key . '}'] = $value;
        }

        // Interpolate replacement values into the messages
        $translations = array_map(fn($translation) => strtr($translation, $replace), $this->translations);

        // Return new token with results
        return new Token($translations, $this->preferredLanguage);
    }
}
