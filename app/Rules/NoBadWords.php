<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoBadWords implements Rule
{
    /**
     * Bad words list in English, Tagalog, and Visayan
     */
    private array $badWords = [
        // English bad words
        'fuck', 'fucking', 'fucked', 'fucker', 'shit', 'shitting', 'damn', 'damned',
        'bitch', 'bitches', 'asshole', 'bastard', 'crap', 'piss', 'pissing',
        'dick', 'dicks', 'cock', 'cocks', 'pussy', 'pussies', 'cunt', 'cunts',
        'slut', 'sluts', 'whore', 'whores', 'fag', 'fags', 'faggot', 'faggots',
        'nigger', 'niggers', 'retard', 'retarded', 'idiot', 'idiots', 'stupid',
        'moron', 'morons', 'dumb', 'dumbass', 'ass', 'asses',
        
        // Tagalog bad words
        'putang', 'puta', 'putangina', 'tangina', 'tang ina', 'tangina mo', 'tanginamo',
        'gago', 'gaga', 'bobo', 'boba', 'ulol', 'tarantado', 'lintik', 'punyeta',
        'hayop', 'animal', 'walang hiya', 'walanghiya', 'pakyu', 'pak yu', 'pakyou',
        'leche', 'lechugas', 'pakshet', 'pakshet', 'pakshet mo', 'pakshetmo',
        'inutil', 'tanga', 'tangina', 'bwisit', 'bwiset',
        
        // Visayan/Cebuano bad words
        'yawa', 'yawaa', 'yawa ka', 'yawaka', 'buang', 'buanga', 'bogo', 'bogoa',
        'tanga', 'tangaha', 'kagwang', 'kagwanga', 'piste', 'pisteng', 'pisting',
        'pisting yawa', 'pistingyawa', 'salsal', 'salsalan', 'kupal', 'kupala',
        'yawit', 'yawit ka', 'yawitka', 'piste ka', 'pisteka',
        
        // Common variations and misspellings with special characters
        'f*ck', 'f**k', 'f***', 'sh*t', 's**t', 's***', 'b*tch', 'b**ch', 'b***h',
        'a**hole', 'a***hole', 'd*ck', 'd**k', 'p*ssy', 'p**sy', 'p***y',
        'c*nt', 'c**t', 'n*gg*r', 'n***r', 'f*g', 'f**g', 'r*tard', 'r**ard',
    ];

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        if (empty($value)) {
            return true;
        }

        return ! $this->containsBadWord($value);
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The :attribute contains inappropriate language. Please use respectful language.';
    }

    /**
     * Remove any bad words from the provided text while preserving other content.
     */
    public static function sanitize(?string $value): string
    {
        if ($value === null || $value === '') {
            return $value ?? '';
        }

        $instance = new self();
        $segments = preg_split('/(\s+)/u', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
        $modified = false;

        foreach ($segments as &$segment) {
            $trimmed = trim($segment);

            if ($trimmed === '') {
                continue;
            }

            if ($instance->matchesBadWordToken($segment)) {
                $segment = '';
                $modified = true;
            }
        }

        unset($segment);

        if (! $modified) {
            return $value;
        }

        $cleaned = implode('', $segments);
        $cleaned = preg_replace('/\s{2,}/', ' ', $cleaned ?? '');

        return trim($cleaned ?? '');
    }

    private function containsBadWord(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        foreach ($this->getBadWordPatterns() as $pattern) {
            if (preg_match($pattern['boundary'], $value)) {
                return true;
            }
        }

        return false;
    }

    private ?array $badWordPatternsCache = null;

    private function matchesBadWordToken(string $value): bool
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return false;
        }

        foreach ($this->getBadWordPatterns() as $pattern) {
            if (preg_match($pattern['token'], $trimmed)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeWord(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim($value)));
    }

    private function getBadWordPatterns(): array
    {
        if ($this->badWordPatternsCache !== null) {
            return $this->badWordPatternsCache;
        }

        $patterns = [];

        foreach ($this->badWords as $badWord) {
            $normalized = $this->normalizeWord($badWord);

            if ($normalized === '' || strlen($normalized) < 3) {
                continue;
            }

            $letters = preg_split('//u', $normalized, -1, PREG_SPLIT_NO_EMPTY);

            if (empty($letters)) {
                continue;
            }

            $parts = [];
            $count = count($letters);

            foreach ($letters as $index => $char) {
                $parts[] = '(?:' . preg_quote($char, '/') . '+)';

                if ($index < $count - 1) {
                    $parts[] = '[^a-z0-9]*';
                }
            }

            $patternBody = implode('', $parts);

            $patterns[] = [
                'boundary' => '/(?<![a-z0-9])' . $patternBody . '(?![a-z0-9])/iu',
                'token' => '/^[^a-z0-9]*' . $patternBody . '[^a-z0-9]*$/iu',
            ];
        }

        return $this->badWordPatternsCache = $patterns;
    }
}

