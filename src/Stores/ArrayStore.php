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

namespace Eufony\I18N\Stores;

use Eufony\I18N\StoreInterface;
use Eufony\I18N\Token;
use Eufony\I18N\TranslationException;

/**
 * Provides a token store implementation using a PHP array.
 */
class ArrayStore implements StoreInterface
{
    /**
     * @var \Eufony\I18N\Token[] $tokens
     */
    protected array $tokens;

    /**
     * Initializes a new array store from a JSON string.
     *
     * @param string $json
     * @return static
     */
    public static function fromJSON(string $json): static
    {
        return new static(json_decode($json, associative: true));
    }

    /**
     * Class constructor.
     * Creates a new store from a two-dimensional PHP array, where the first key
     * is the tag of the token and the second key is the language of each
     * translation.
     *
     * @param string[][] $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = array_map(fn($token) => new Token($token), $tokens);
    }

    /**
     * Getter for all tokens.
     *
     * Returns the array of tokens.
     *
     * @return \Eufony\I18N\Token[]
     */
    public function tokens(): array
    {
        return $this->tokens;
    }

    /**
     * @inheritDoc
     */
    public function has(string $token, array|string|null $lang = null): bool
    {
        return array_key_exists($token, $this->tokens) && (!isset($lang) || $this->tokens[$token]->has($lang));
    }

    /**
     * @inheritDoc
     */
    public function token(string $token): Token
    {
        if (!$this->has($token)) {
            throw new TranslationException("Unknown token");
        }

        return $this->tokens[$token];
    }

    /**
     * @inheritDoc
     */
    public function translate(string $string, string $from, array $to): Token
    {
        foreach ($this->tokens as $token) {
            if ($token->get($from) === $string) {
                return $token;
            }
        }

        throw new TranslationException("Could not translate. No token with matching message in origin language found");
    }
}
