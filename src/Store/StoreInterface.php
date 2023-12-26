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

namespace Eufony\I18N\Store;

use Eufony\I18N\Token;

/**
 * Provides a common interface for fetching tokens.
 */
interface StoreInterface
{
    /**
     * Returns if the requested token has an available translation.
     *
     * If a language is specified (either as a single string or as an array), the
     * availability of the token in the target language(s) will be checked. If no
     * language is specified, the availability of the token in any language will
     * be checked.
     *
     * @param string $token
     * @param string|array|null $lang
     * @return bool
     */
    public function has(string $token, string|array|null $lang = null): bool;

    /**
     * Returns the requested token in all available languages.
     *
     * If the requested token does not exist, a `\Eufony\I18N\TranslationException`
     * MUST be thrown.
     *
     * @param string $token
     * @return \Eufony\I18N\Token
     */
    public function token(string $token): Token;

    /**
     * Translates a string into the specified language(s).
     *
     * The origin language MUST be specified in `$from`. More than one target
     * language MAY be specified in `$to`. The resulting token MUST have
     * translations available in all requested languages.
     *
     * The store implementation MAY check if a predetermined translation is
     * available, or it MAY attempt to generate one upon request.
     *
     * @param string $string
     * @param string $from
     * @param array $to
     * @return \Eufony\I18N\Token
     */
    public function translate(string $string, string $from, array $to): Token;
}
