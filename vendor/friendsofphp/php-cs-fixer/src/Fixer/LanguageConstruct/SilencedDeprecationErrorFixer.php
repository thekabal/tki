<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Fixer\LanguageConstruct;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Jules Pietri <jules@heahprod.com>
 */
final class SilencedDeprecationErrorFixer extends AbstractFixer
{
    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isTokenKindFound(T_STRING);
    }

    /**
     * {@inheritdoc}
     */
    public function fix(\SplFileInfo $file, Tokens $tokens)
    {
        for ($index = $tokens->count() - 1; $index >= 0; --$index) {
            $token = $tokens[$index];
            if (!$token->equals(array(T_STRING, 'trigger_error'), false)) {
                continue;
            }

            $start = $index;
            $prev = $tokens->getPrevMeaningfulToken($start);
            if ($tokens[$prev]->isGivenKind(T_NS_SEPARATOR)) {
                $start = $prev;
                $prev = $tokens->getPrevMeaningfulToken($start);
            }

            if ($tokens[$prev]->isGivenKind(T_STRING) || $tokens[$prev]->equals('@')) {
                continue;
            }

            $end = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $tokens->getNextTokenOfKind($index, array(T_STRING, '(')));
            if ($tokens[$tokens->getPrevMeaningfulToken($end)]->equals(array(T_STRING, 'E_USER_DEPRECATED'))) {
                $tokens->insertAt($start, new Token('@'));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Ensures deprecation notices are silenced.';
    }

    /**
     * {@inheritdoc}
     */
    public function isRisky()
    {
        return true;
    }
}
