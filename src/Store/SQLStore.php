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

use Eufony\DBAL\Connection;
use Eufony\DBAL\Query\Builder\Select;
use Eufony\DBAL\Query\Expr;
use Eufony\I18N\Token;
use Eufony\I18N\TranslationException;

/**
 * Provides a token store implementation using an SQL table.
 *
 * Requires the `eufony/dbal` package.
 *
 * @see https://packagist.org/packages/eufony/dbal
 */
class SQLStore implements StoreInterface
{
    /**
     * The Connection instance used to query the database.
     *
     * @var \Eufony\DBAL\Connection $connection
     */
    protected Connection $connection;

    /**
     * The table which the tokens will be read from.
     *
     * Defaults to `tokens`.
     *
     * @var string $table
     */
    protected string $table;

    /**
     * The name of the table field that acts as the primary key.
     *
     * Defaults to `id`.
     *
     * @var string $idField
     */
    protected string $idField;

    /**
     * The name of the table field that contains the names of the tokens.
     *
     * Defaults to `tag`.
     *
     * @var string $tagField
     */
    protected string $tagField;

    /**
     * Class constructor.
     * Creates a new token store using an SQL table, by default called `tokens`.
     *
     * The table MUST contain at least two fields: a primary key called `id` and a
     * unique text field called `tag`. Additionally, it MUST contain a text field
     * for every language which SHOULD default to null.
     *
     * The table will only ever be read from, it will never be modified.
     *
     * @param \Eufony\DBAL\Connection $connection
     * @param string $table
     * @see \Eufony\I18N\Store\SQLStore::ID_FIELD
     * @see \Eufony\I18N\Store\SQLStore::TAG_FIELD
     */
    public function __construct(
        Connection $connection,
        string $table = "tokens",
        string $idField = "id",
        string $tagField = "tag"
    ) {
        $this->connection = $connection;
        $this->table = $table;
        $this->idField = $idField;
        $this->tagField = $tagField;
        // TODO: Check if tokens table exists, and ensure the `tag` field is unique.
    }

    /**
     * @inheritDoc
     */
    public function has(string $token, array|string|null $lang = null): bool
    {
        try {
            $token = $this->token($token);
        } catch (TranslationException) {
            return false;
        }

        return !isset($lang) || $token->has($lang);
    }

    /**
     * @inheritDoc
     */
    public function token(string $token): Token
    {
        $query = Select::from($this->table)->where(Expr::eq($this->tagField, $token));
        $result = $this->connection->query($query);

        if (empty($result)) {
            throw new TranslationException("Unknown token");
        }

        return new Token($token, array_diff_key($result[0], array_flip([$this->idField, $this->tagField])));
    }

    /**
     * @inheritDoc
     */
    public function translate(string $string, string $from, array $to): Token
    {
        $query = Select::from($this->table)->fields([$this->tagField, ...$to])->where(Expr::eq($from, $string));
        $result = $this->connection->query($query);

        if (empty($result)) {
            $message = "Could not translate. No token with matching message in origin language found";
            throw new TranslationException($message);
        }

        return new Token($result[0][$this->tagField], array_intersect_key($result[0], array_flip($to)));
    }
}
