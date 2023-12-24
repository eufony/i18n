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

/**
 * Provides a front-end to use a token store.
 *
 * Implements preferred languages and provides syntactic sugar.
 */
class Translator
{
    /**
     * The backend for fetching or translating tokens.
     *
     * @var \Eufony\I18N\StoreInterface $store
     */
    private StoreInterface $store;

    /**
     * The default target language of this translator.
     *
     * @var string|null $preferredLanguage
     */
    private string|null $preferredLanguage;

    /**
     * Class constructor.
     * Creates a new translator using the given token backend.
     *
     * Optionally sets a preferred language to act as the default target
     * translation language.
     *
     * @param \Eufony\I18N\StoreInterface $store
     * @param string|null $preferredLanguage
     */
    public function __construct(StoreInterface $store, ?string $preferredLanguage = null)
    {
        $this->store = $store;
        $this->preferredLanguage = $preferredLanguage;
    }

    /**
     * Getter for the token store.
     *
     * Returns the current token store.
     *
     * @return \Eufony\I18N\StoreInterface
     */
    public function store(): StoreInterface
    {
        return $this->store;
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
     * Returns the requested token in all available languages.
     *
     * @param string $token
     * @return \Eufony\I18N\Token
     */
    public function token(string $token): Token
    {
        $token = $this->store->token($token);
        $token->preferredLanguage($this->preferredLanguage);
        return $token;
    }

    /**
     * Translates a string into the specified language(s).
     *
     * If `$lang` is an array, it contains a single key-value pair where the key is
     * the origin language and the value is either a single string or an array of
     * strings of target language(s).
     *
     * Alternatively, if the preferred target language is set, `$lang` may also be
     * a string indicating the origin language.
     *
     * @param string $string
     * @param array|string $lang
     * @return \Eufony\I18N\Token
     */
    public function translate(string $string, array|string $lang): Token
    {
        if (is_string($lang)) {
            $from = $lang;
            $to = $this->preferredLanguage ?? throw new TranslationException("No preferred language set");
            $to = [$to];
        } else {
            if (empty($lang)) {
                throw new TranslationException("Must specify origin and target languages");
            }

            if (count($lang) > 1) {
                throw new TranslationException("Cannot translate from multiple languages");
            }

            $from = array_key_first($lang);
            $to = array_values($lang)[0];

            if (!is_string($from)) {
                throw new InvalidArgumentException("Language specifier must be a string");
            }

            if (is_string($to)) {
                $to = [$to];
            }
        }

        $token = $this->store->translate($string, $from, $to);
        $token->preferredLanguage($this->preferredLanguage);
        return $token;
    }
}
