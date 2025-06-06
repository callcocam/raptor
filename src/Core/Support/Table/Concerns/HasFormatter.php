<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\Raptor\Core\Support\Table\Concerns;


use InvalidArgumentException;

trait HasFormatter
{

    public string|array|null $formatter = null;
    public mixed $formatterOptions = null;

    
    // 🔧 Tipos de formatter válidos
    public const VALID_FORMATTERS = [
        'formatDate',
        'formatCurrency',
        'formatBadge',
        'formatBoolean',
    ];

    // 🎨 Variações válidas para badges
    public const VALID_BADGE_VARIANTS = [
        'default', 'secondary', 'destructive', 'outline'
    ];

    // 🌈 Cores válidas para badges
    public const VALID_BADGE_COLORS = [
        'success', 'error', 'warning', 'info', 'primary', 'secondary'
    ];

    // 📏 Tamanhos válidos
    public const VALID_SIZES = [
        'sm', 'default', 'lg'
    ];

    public function getFormatter(): string|array|null
    {
        return $this->formatter;
    }

    public function formatBadge(mixed $options = null): self
    {
        $this->formatter('formatBadge');
        $this->formatterOptions = $options;
        return $this;
    }

    public function formatDate(mixed $options = null): self
    {
        $this->formatter('formatDate');
        $this->formatterOptions = $options;
        return $this;
    }

    public function formatCurrency(mixed $options = null): self
    {
        $this->formatter('formatCurrency');
        $this->formatterOptions = $options;
        return $this;
    }

    /**
     * @param string|array $formatter
     * @return $this
     * @throws InvalidArgumentException
     */
    public function formatter(string|array $formatter): self
    {
        if (is_string($formatter)) {
            // Formatter simples (string)
            if (!in_array($formatter, self::VALID_FORMATTERS)) {
                throw new InvalidArgumentException(
                    "Formatter '{$formatter}' não é válido. Formatters válidos: " .
                        implode(', ', self::VALID_FORMATTERS)
                );
            }
            $this->formatter = $formatter;
        } else {
            // Formatter avançado (array)
            $this->validateAdvancedFormatter($formatter);
            $this->formatter = $formatter;
        }

        return $this;
    }

    /**
     * Validar formatter avançado em formato de array
     * 
     * @param array $formatter
     * @throws InvalidArgumentException
     */
    private function validateAdvancedFormatter(array $formatter): void
    {
        // Verificar se tem a chave 'type'
        if (!isset($formatter['type'])) {
            throw new InvalidArgumentException(
                "Formatter em array deve ter a chave 'type'. Exemplo: ['type' => 'formatBadge', 'variant' => 'outline']"
            );
        }

        // Verificar se o tipo é válido
        if (!in_array($formatter['type'], self::VALID_FORMATTERS)) {
            throw new InvalidArgumentException(
                "Formatter type '{$formatter['type']}' não é válido. Tipos válidos: " .
                    implode(', ', self::VALID_FORMATTERS)
            );
        }

        // Validações específicas por tipo
        switch ($formatter['type']) {
            case 'formatBadge':
                $this->validateBadgeFormatter($formatter);
                break;

            case 'formatDate':
                $this->validateDateFormatter($formatter);
                break;
        }
    }

    /**
     * Validar opções específicas para formatBadge
     * 
     * @param array $formatter
     * @throws InvalidArgumentException
     */
    private function validateBadgeFormatter(array $formatter): void
    {
        // Validar variant se presente
        if (isset($formatter['variant']) && !in_array($formatter['variant'], self::VALID_BADGE_VARIANTS)) {
            throw new InvalidArgumentException(
                "Badge variant '{$formatter['variant']}' não é válido. Variants válidos: " .
                    implode(', ', self::VALID_BADGE_VARIANTS)
            );
        }

        // Validar color se presente
        if (isset($formatter['color']) && !in_array($formatter['color'], self::VALID_BADGE_COLORS)) {
            throw new InvalidArgumentException(
                "Badge color '{$formatter['color']}' não é válido. Cores válidas: " .
                    implode(', ', self::VALID_BADGE_COLORS)
            );
        }

        // Validar size se presente
        if (isset($formatter['size']) && !in_array($formatter['size'], self::VALID_SIZES)) {
            throw new InvalidArgumentException(
                "Badge size '{$formatter['size']}' não é válido. Tamanhos válidos: " .
                    implode(', ', self::VALID_SIZES)
            );
        }

        // Validar mapping se presente
        if (isset($formatter['mapping']) && !is_array($formatter['mapping'])) {
            throw new InvalidArgumentException(
                "Badge mapping deve ser um array. Exemplo: ['active' => ['color' => 'success'], 'inactive' => ['color' => 'secondary']]"
            );
        }

        // Validar cada item do mapping
        if (isset($formatter['mapping'])) {
            foreach ($formatter['mapping'] as $key => $options) {
                if (!is_array($options)) {
                    throw new InvalidArgumentException(
                        "Badge mapping para '{$key}' deve ser um array com opções como ['color' => 'success', 'variant' => 'outline']"
                    );
                }

                // Validar opções dentro do mapping
                if (isset($options['variant']) && !in_array($options['variant'], self::VALID_BADGE_VARIANTS)) {
                    throw new InvalidArgumentException(
                        "Badge variant '{$options['variant']}' no mapping '{$key}' não é válido"
                    );
                }

                if (isset($options['color']) && !in_array($options['color'], self::VALID_BADGE_COLORS)) {
                    throw new InvalidArgumentException(
                        "Badge color '{$options['color']}' no mapping '{$key}' não é válido"
                    );
                }
            }
        }
    }

    /**
     * Validar opções específicas para formatDate
     * 
     * @param array $formatter
     * @throws InvalidArgumentException
     */
    private function validateDateFormatter(array $formatter): void
    {
        // Validar format se presente
        if (isset($formatter['format']) && !is_string($formatter['format'])) {
            throw new InvalidArgumentException(
                "Date format deve ser uma string. Exemplo: ['type' => 'formatDate', 'format' => 'dd/MM/yyyy HH:mm']"
            );
        }
    }

    /**
     * Obter lista de formatters válidos
     * 
     * @return array
     */
    public static function getValidFormatters(): array
    {
        return self::VALID_FORMATTERS;
    }

    /**
     * Verificar se um formatter é válido
     * 
     * @param string $formatter
     * @return bool
     */
    public static function isValidFormatter(string $formatter): bool
    {
        return in_array($formatter, self::VALID_FORMATTERS);
    }
}
