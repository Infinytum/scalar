<?php
/**
 * (C) 2017 by Michael Teuscher (mk.teuscher@gmail.com)
 * as part of the Scalar PHP framework
 *
 * Released under the AGPL v3.0 license
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Handlebars parser (based on mustache)
 *
 * This class is responsible for turning raw template source into a set of
 * Handlebars tokens.
 *
 * @category  Xamin
 * @package   Handlebars
 * @author    fzerorubigd <fzerorubigd@gmail.com>
 * @author    Behrooz Shabani <everplays@gmail.com>
 * @author    Mardix <https://github.com/mardix>
 * @copyright 2012 (c) ParsPooyesh Co
 * @copyright 2013 (c) Behrooz Shabani
 * @copyright 2013 (c) Mardix
 * @license   MIT
 * @link      http://voodoophp.org/docs/handlebars
 */

namespace Handlebars;

use ArrayIterator;
use LogicException;

class Parser
{
    /**
     * Process array of tokens and convert them into parse tree
     *
     * @param array $tokens Set of
     *
     * @return array Token parse tree
     */
    public function parse(Array $tokens = [])
    {
        return $this->buildTree(new ArrayIterator($tokens));
    }

    /**
     * Helper method for recursively building a parse tree.
     *
     * @param \ArrayIterator $tokens Stream of tokens
     *
     * @throws \LogicException when nesting errors or mismatched section tags
     * are encountered.
     * @return array Token parse tree
     *
     */
    private function buildTree(ArrayIterator $tokens)
    {
        $stack = [];

        do {
            $token = $tokens->current();
            $tokens->next();

            if ($token === null) {
                continue;
            } else {
                switch ($token[Tokenizer::TYPE]) {
                    case Tokenizer::T_END_SECTION:
                        $newNodes = [];
                        do {
                            $result = array_pop($stack);
                            if ($result === null) {
                                throw new LogicException(
                                    'Unexpected closing tag: /' . $token[Tokenizer::NAME]
                                );
                            }

                            if (!array_key_exists(Tokenizer::NODES, $result)
                                && isset($result[Tokenizer::NAME])
                                && $result[Tokenizer::NAME] == $token[Tokenizer::NAME]
                            ) {
                                $result[Tokenizer::NODES] = $newNodes;
                                $result[Tokenizer::END] = $token[Tokenizer::INDEX];
                                array_push($stack, $result);
                                break 2;
                            } else {
                                array_unshift($newNodes, $result);
                            }
                        } while (true);
                        break;
                    default:
                        array_push($stack, $token);
                }
            }

        } while ($tokens->valid());

        return $stack;

    }

}
