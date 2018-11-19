<?php declare(strict_types=1);

namespace TwitchBot\Output\OutputTraits;

trait PokemonTextOutput
{
    /**
     * @param array $data
     *
     * @return string
     */
    protected function transformPokemon(array $data): string {
        return $this->printPokemon($data['species'], $data['pokemon'], $data['form']);
    }

    /**
     * @param array $species
     * @param array $pokemon
     * @param array $form
     *
     * @return string
     */
    private function printPokemon(array $species, array $pokemon, array $form): string {
        $forms = [];

        foreach ($species['varieties'] as $variety) {
            if ($variety['pokemon']['name'] !== $pokemon['name']) {
                $shortForm = str_replace("{$species['name']}-", '', $variety['pokemon']['name']);
                $forms[] = ucfirst($shortForm);
            }
        }

        $nameEn = $this->getName($species, 'en');

        if (!$pokemon['is_default']) {
            $name = $this->getName($form, 'en');
        } else {
            $name = $nameEn;
        }

        $text = sprintf("%s, the %s. It is %s type.",
            $name,
            $this->getGenus($species, 'en'),
            $this->enumerate($this->getType($pokemon))
        );

        $best = $this->getBestStats($pokemon);
        $worst = $this->getWorstStats($pokemon);

        if (count($best) < 6) {
            $bestRating = current($best);
            $worstRating = current($worst);
            $text .= sprintf(' It has %s %s but %s %s.',
                $bestRating,
                $this->enumerate(array_keys($best)),
                $worstRating,
                $this->enumerate(array_keys($worst))
            );
        } else {
            $text .= ' It is perfectly balanced.';
        }

        if (!empty($forms)) {
            $text .= sprintf(' It has different forms: %s.', $this->enumerate($forms));
        }

        $nameDe = $this->getName($species, 'de');

        if ($nameEn != $nameDe) {
            $text .= sprintf(' In germany it is more commonly known as %s.', $nameDe);
        }

        return $text;
    }

    /**
     * @param array $stats
     *
     * @return array
     */
    private function getStatsInWords(array $stats): array {
        return array_map([$this, 'rateStat'], $stats);
    }

    /**
     * @param array $pokemon
     *
     * @return array
     */
    private function getStats(array $pokemon): array {
        $result = [];

        foreach (array_reverse($pokemon['stats']) as $stat) {
            $name = ucfirst($stat['stat']['name']);
            $value = $stat['base_stat'];
            $result[$name] = $value;
        }

        return $result;
    }

    /**
     * @param array $pokemon
     *
     * @return array
     */
    private function getBestStats(array $pokemon): array {
        $result = [];
        $stats = $this->getStats($pokemon);
        arsort($stats);
        $stats = $this->getStatsInWords($stats);

        $prev = null;
        foreach ($stats as $name => $value) {
            if ($prev !== null && $value !== $prev) {
                break;
            }

            $result[$name] = $value;
            $prev = $value;
        }

        return $result;
    }

    /**
     * @param array $pokemon
     *
     * @return array
     */
    private function getWorstStats(array $pokemon): array {
        $result = [];
        $stats = $this->getStats($pokemon);
        asort($stats);
        $stats = $this->getStatsInWords($stats);

        $prev = null;
        foreach ($stats as $name => $value) {
            if ($prev !== null && $value !== $prev) {
                break;
            }

            $result[$name] = $value;
            $prev = $value;
        }

        return $result;
    }

    /**
     * @param int $value
     *
     * @return string
     */
    private function rateStat(int $value): string {
        if ($value < 25) {
            return 'horrible';
        } else if ($value < 50) {
            return 'bad';
        } else if ($value < 75) {
            return 'poor';
        } else if ($value < 100) {
            return 'below average';
        } else if ($value < 125) {
            return 'above average';
        } else if ($value < 150) {
            return 'good';
        } else if ($value < 175) {
            return 'very good';
        } else if ($value < 200) {
            return 'excellent';
        } else if ($value < 225) {
            return 'fantastic';
        } else {
            return 'amazing';
        }
    }

    /**
     * @param array $pokemon
     *
     * @return int
     */
    private function getStatTotal(array $pokemon): int {
        $result = 0;

        /** @var int $stat */
        foreach ($pokemon['stats'] as $stat) {
            $result += $stat['base_stat'];
        }

        return $result;
    }

    /**
     * @param array $pokemon
     *
     * @return array
     */
    private function getType(array $pokemon): array {
        $result = [];

        foreach ($pokemon['types'] as $type) {
            $result[] = ucfirst($type['type']['name']);
        }

        return $result;
    }

    /**
     * @param array $species
     * @param string $lang
     *
     * @return string|null
     */
    private function getGenus(array $species, string $lang): ?string {
        foreach ($species['genera'] as $genus) {
            if ($genus['language']['name'] === $lang) {
                return $genus['genus'];
            }
        }
    }

    /**
     * @param array $species
     * @param string $lang
     *
     * @return string|null
     */
    private function getName(array $species, string $lang): ?string {
        foreach ($species['names'] as $name) {
            if ($name['language']['name'] === $lang) {
                return $name['name'];
            }
        }
    }

    /**
     * @param array $words
     *
     * @return string
     */
    protected function enumerate(array $words): string {
        $result = '';
        $cnt = 0;
        $max = count($words);

        foreach ($words as $name) {
            $result .= "{$name}";

            $cnt++;

            if ($cnt === $max) {
                continue;
            } else if ($cnt === $max - 1) {
                $result .= ' and ';
            } else {
                $result .= ', ';
            }
        }

        return $result;
    }
}
