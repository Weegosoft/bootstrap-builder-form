<?php

namespace Weegosoft\Form\Tailwind;

/**
 * Tailwind CSS Form Builder
 *
 * Cette classe génère des champs HTML stylés avec Tailwind CSS.
 */
class Form
{
    /**
     * @var array|object Données utilisées pour pré-remplir les champs.
     */
    private array|object $data = [];

    /**
     * @var array Erreurs de validation.
     */
    private array $errors = [];

    public function __construct(array|object $data = [], ?array $errors = null)
    {
        $this->data = $data;
        $this->errors = $errors ?? [];
    }

    /**
     * Nettoie le nom d'un champ pour générer un ID HTML valide.
     */
    private function sanitizeId(string $name): string
    {
        return (string) preg_replace(
            '/[^a-zA-Z0-9_-]/',
            '',
            str_replace(['[]', '[', ']'], ['', '-', ''], $name)
        );
    }

    /**
     * Génère un champ caché CSRF.
     */
    public function csrf(string $token): string
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '" />';
    }

    /**
     * Évite les erreurs si l'objet est utilisé comme string.
     */
    public function __toString(): string
    {
        return '';
    }

    /**
     * Récupère la valeur brute d'un champ depuis la source de données.
     */
    private function getValue(string $name)
    {
        if (is_array($this->data)) {
            return $this->data[$name] ?? null;
        }

        if ($this->data instanceof \ArrayAccess && $this->data->offsetExists($name)) {
            return $this->data->offsetGet($name);
        }

        if (is_object($this->data)) {
            $methodName = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
            if (method_exists($this->data, $methodName)) {
                return $this->data->$methodName();
            }

            if (isset($this->data->{$name})) {
                return $this->data->{$name};
            }

            if (property_exists($this->data, $name)) {
                try {
                    return $this->data->{$name};
                } catch (\Throwable $e) {
                    return null;
                }
            }
        }

        return null;
    }

    private function hasError(string $name): bool
    {
        return isset($this->errors[$name]);
    }

    /**
     * Compare deux valeurs de manière sûre, sans comparaison lâche.
     */
    private function valueEquals($left, $right): bool
    {
        if ($left instanceof \DateTimeInterface || $right instanceof \DateTimeInterface) {
            return false;
        }

        return (string) $left === (string) $right;
    }

    /**
     * Vérifie si une valeur est présente dans un tableau, en comparant les strings.
     */
    private function inArrayString($needle, array $haystack): bool
    {
        $needle = (string) $needle;

        foreach ($haystack as $item) {
            if (is_scalar($item) && (string) $item === $needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Construit les attributs HTML.
     */
    private function buildAttributes(array $attributes, array $skip = []): string
    {
        $html = '';

        foreach ($attributes as $key => $value) {
            if (in_array((string) $key, $skip, true)) {
                continue;
            }

            if ($value === true) {
                $html .= ' ' . htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8');
                continue;
            }

            if ($value === false || $value === null || is_array($value)) {
                continue;
            }

            $html .= ' ' . htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8')
                . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
        }

        return $html;
    }

    /**
     * Génère le message d'erreur Tailwind.
     */
    private function getErrorFeedback(string $name): string
    {
        if (!isset($this->errors[$name])) {
            return '';
        }

        $messages = is_array($this->errors[$name]) ? $this->errors[$name] : [$this->errors[$name]];
        $escaped = [];

        foreach ($messages as $message) {
            $escaped[] = htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8');
        }

        return '<p class="mt-1 text-sm text-red-600">' . implode('<br>', $escaped) . '</p>';
    }

    /**
     * Classes Tailwind pour les inputs standards.
     */
    private function standardInputClasses(bool $hasError = false, string $rounded = 'rounded-md'): string
    {
        $state = $hasError
            ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
            : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500';

        return "block w-full {$rounded} border px-3 py-2 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:outline-none focus:ring-1 {$state}";
    }

    /**
     * Classes Tailwind pour input file.
     */
    private function fileInputClasses(bool $hasError): string
    {
        $state = $hasError ? 'border-red-500' : 'border-gray-300';

        return "block w-full text-sm text-gray-500 border {$state} rounded-md cursor-pointer file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500";
    }

    /**
     * Classes Tailwind pour checkbox / radio.
     */
    private function choiceInputClasses(string $type, bool $hasError): string
    {
        $state = $hasError
            ? 'border-red-500 text-red-600 focus:ring-red-500'
            : 'border-gray-300 text-indigo-600 focus:ring-indigo-500';

        $rounded = $type === 'radio' ? 'rounded-full' : 'rounded';

        return "h-4 w-4 {$rounded} border {$state} focus:ring-2";
    }

    /**
     * Génère un élément prepend/append pour input group Tailwind.
     */
    private function inputGroupPart($html, string $position): string
    {
        if (!is_scalar($html)) {
            return '';
        }

        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $isAlreadyStyledContainer = preg_match('/^<(span|div|button|a)\b/i', $html)
            && preg_match('/class\s*=/i', $html);

        if ($isAlreadyStyledContainer) {
            return $html;
        }

        $classes = $position === 'prepend'
            ? 'inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm'
            : 'inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm';

        $inner = strip_tags($html) === $html
            ? htmlspecialchars($html, ENT_QUOTES, 'UTF-8')
            : $html;

        return '<span class="' . $classes . '">' . $inner . '</span>';
    }

    /**
     * Ouvre un formulaire.
     */
    public function open(
        string $action = '',
        string $method = 'POST',
        bool $isMultipart = false,
        ?array $options = null,
        ?string $csrfToken = null
    ): string {
        $attributes = $this->buildAttributes($options ?? [], []);
        $enctype = $isMultipart ? ' enctype="multipart/form-data"' : '';

        $safeAction = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
        $safeMethod = htmlspecialchars($method, ENT_QUOTES, 'UTF-8');

        $formTag = "<form action=\"{$safeAction}\" method=\"{$safeMethod}\"{$enctype}{$attributes}>";

        if (strtoupper($method) === 'POST' && $csrfToken !== null) {
            $formTag .= $this->csrf($csrfToken);
        }

        return $formTag;
    }

    /**
     * Ferme le formulaire.
     */
    public function close(): string
    {
        return '</form>';
    }

    /**
     * Méthode principale de génération des champs.
     */
    private function input(string $type, string $name, ?string $label = null, ?array $options = null): string
    {
        $options = $options ?? [];

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeType = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
        $safeId = 'input-' . $this->sanitizeId($name);

        $hasError = $this->hasError($name);
        $errorFeedback = $this->getErrorFeedback($name);

        $labelHtml = '';

        if ($label !== null && $type !== 'hidden' && !in_array($type, ['checkbox', 'radio'], true)) {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $labelHtml = '<label for="' . $safeId . '" class="block text-sm font-medium text-gray-700 mb-1">' . $safeLabel . '</label>';
        }

        $customClasses = (string) ($options['class'] ?? '');

        $prepend = '';
        $append = '';
        $helpHtml = '';

        if (isset($options['input_group']) && is_array($options['input_group'])) {
            $prepend = $options['input_group']['prepend'] ?? '';
            $append = $options['input_group']['append'] ?? '';
        }

        if (is_scalar($prepend)) {
            $prepend = (string) $prepend;
        } else {
            $prepend = '';
        }

        if (is_scalar($append)) {
            $append = (string) $append;
        } else {
            $append = '';
        }

        if (isset($options['help'])) {
            $helpHtml = '<p class="mt-1 text-sm text-gray-500">'
                . htmlspecialchars((string) $options['help'], ENT_QUOTES, 'UTF-8')
                . '</p>';
        }

        $attributes = $this->buildAttributes($options, [
            'class',
            'input_group',
            'help',
            'value',
            'type',
            'id',
        ]);

        $rawValue = array_key_exists('value', $options) ? $options['value'] : $this->getValue($name);
        $value = is_scalar($rawValue) ? htmlspecialchars((string) $rawValue, ENT_QUOTES, 'UTF-8') : '';

        /**
         * Hidden input.
         */
        if ($type === 'hidden') {
            $hiddenValue = array_key_exists('value', $options) ? $options['value'] : $rawValue;

            if ($hiddenValue instanceof \DateTimeInterface) {
                $formattedValue = $hiddenValue->format('Y-m-d H:i:s');
            } else {
                $formattedValue = is_scalar($hiddenValue) ? (string) $hiddenValue : '';
            }

            return '<input type="hidden" name="' . $safeName . '" value="'
                . htmlspecialchars($formattedValue, ENT_QUOTES, 'UTF-8') . '"' . $attributes . '>';
        }

        /**
         * Checkbox.
         */
        if ($type === 'checkbox') {
            $defaultValue = $options['value'] ?? '1';
            $safeDefaultValue = htmlspecialchars((string) $defaultValue, ENT_QUOTES, 'UTF-8');
            $id = 'check-' . $this->sanitizeId($name) . '-' . $this->sanitizeId((string) $defaultValue);

            $checked = (is_array($rawValue) && $this->inArrayString($defaultValue, $rawValue))
                || $this->valueEquals($rawValue, $defaultValue);

            $classes = $this->choiceInputClasses('checkbox', $hasError);

            if ($customClasses !== '') {
                $classes .= ' ' . htmlspecialchars($customClasses, ENT_QUOTES, 'UTF-8');
            }

            $cbLabel = htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8');

            return '
            <div class="mb-4">
                <label for="' . $id . '" class="inline-flex items-center">
                    <input
                        id="' . $id . '"
                        type="checkbox"
                        name="' . $safeName . '"
                        value="' . $safeDefaultValue . '"
                        class="' . $classes . '"
                        ' . ($checked ? 'checked' : '') . '
                        ' . $attributes . '
                    >
                    <span class="ml-2 text-sm text-gray-700">' . $cbLabel . '</span>
                </label>
                ' . $helpHtml . '
                ' . $errorFeedback . '
            </div>';
        }

        /**
         * Radio.
         */
        if ($type === 'radio') {
            $defaultValue = $options['value'] ?? '';
            $safeDefaultValue = htmlspecialchars((string) $defaultValue, ENT_QUOTES, 'UTF-8');
            $id = 'radio-' . $this->sanitizeId($name) . '-' . $this->sanitizeId((string) $defaultValue);

            $checked = $this->valueEquals($rawValue, $defaultValue);

            $classes = $this->choiceInputClasses('radio', $hasError);

            if ($customClasses !== '') {
                $classes .= ' ' . htmlspecialchars($customClasses, ENT_QUOTES, 'UTF-8');
            }

            $radioLabel = htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8');

            return '
            <div class="mb-4">
                <label for="' . $id . '" class="inline-flex items-center">
                    <input
                        id="' . $id . '"
                        type="radio"
                        name="' . $safeName . '"
                        value="' . $safeDefaultValue . '"
                        class="' . $classes . '"
                        ' . ($checked ? 'checked' : '') . '
                        ' . $attributes . '
                    >
                    <span class="ml-2 text-sm text-gray-700">' . $radioLabel . '</span>
                </label>
                ' . $helpHtml . '
                ' . $errorFeedback . '
            </div>';
        }

        /**
         * File input.
         */
        if ($type === 'file') {
            $classes = $this->fileInputClasses($hasError);

            if ($customClasses !== '') {
                $classes .= ' ' . htmlspecialchars($customClasses, ENT_QUOTES, 'UTF-8');
            }

            return '
            <div class="mb-4">
                ' . $labelHtml . '
                <input
                    type="file"
                    name="' . $safeName . '"
                    id="' . $safeId . '"
                    class="' . $classes . '"
                    ' . $attributes . '
                >
                ' . $helpHtml . '
                ' . $errorFeedback . '
            </div>';
        }

        /**
         * Formatage des valeurs date / time.
         */
        if (in_array($type, ['date', 'time', 'datetime-local', 'week', 'month'], true)) {
            $format = match ($type) {
                'date' => 'Y-m-d',
                'time' => 'H:i',
                'datetime-local' => 'Y-m-d\TH:i',
                'week' => 'Y-\WW',
                'month' => 'Y-m',
                default => 'Y-m-d',
            };

            if ($rawValue instanceof \DateTimeInterface) {
                $formatted = $rawValue->format($format);
            } elseif (is_string($rawValue) && trim($rawValue) !== '') {
                $timestamp = strtotime($rawValue);
                $formatted = $timestamp ? date($format, $timestamp) : '';
            } else {
                $formatted = '';
            }

            $value = htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8');
        }

        /**
         * Gestion des coins arrondis pour input group.
         */
        $rounded = 'rounded-md';

        if ($prepend !== '' || $append !== '') {
            if ($prepend !== '' && $append !== '') {
                $rounded = 'rounded-none';
            } elseif ($prepend !== '') {
                $rounded = 'rounded-r-md';
            } else {
                $rounded = 'rounded-l-md';
            }
        }

        /**
         * Classes selon le type de champ.
         */
        $baseClass = match ($type) {
            'range' => 'w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer '
                . ($hasError ? 'accent-red-600' : 'accent-indigo-600'),

            'color' => 'h-10 w-14 border border-gray-300 rounded-md p-1 cursor-pointer',

            default => $this->standardInputClasses($hasError, $rounded),
        };

        if ($customClasses !== '') {
            $baseClass .= ' ' . htmlspecialchars($customClasses, ENT_QUOTES, 'UTF-8');
        }

        $inputHtml = '<input type="' . $safeType . '" name="' . $safeName . '" id="' . $safeId . '" value="' . $value . '" class="' . $baseClass . '"' . $attributes . '>';

        $wrapperHtml = $inputHtml;

        if ($prepend !== '' || $append !== '') {
            $wrapperHtml = '<div class="flex">'
                . $this->inputGroupPart($prepend, 'prepend')
                . $inputHtml
                . $this->inputGroupPart($append, 'append')
                . '</div>';
        }

        return '
        <div class="mb-4">
            ' . $labelHtml . '
            ' . $wrapperHtml . '
            ' . $helpHtml . '
            ' . $errorFeedback . '
        </div>';
    }

    public function text(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input('text', $name, $label, $options);
    }

    public function password(string $name, ?string $label = null, ?array $options = null): string
    {
        $options = $options ?? [];

        // Évite d'afficher accidentellement un mot de passe existant.
        $options['value'] = $options['value'] ?? '';

        return $this->input('password', $name, $label, $options);
    }

    public function switch(string $name, string $label, $value = 1): string
    {
        return $this->toggle($name, $label, $value);
    }

    public function email(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input('email', $name, $label, $options);
    }

    public function date(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input('date', $name, $label, $options);
    }

    public function datetimeLocal(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input('datetime-local', $name, $label, $options);
    }

    public function time(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input('time', $name, $label, $options);
    }

    public function tel(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input('tel', $name, $label, $options);
    }

    public function url(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input('url', $name, $label, $options);
    }

    public function number(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input('number', $name, $label, $options);
    }

    public function file(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input('file', $name, $label, $options);
    }

    public function image(string $name, ?string $label = null, ?array $options = null): string
    {
        $options = $options ?? [];
        $options['accept'] = 'image/jpeg,image/png,image/gif,image/svg+xml';

        return $this->input('file', $name, $label, $options);
    }

    public function hidden(string $name, $value = null, array $options = []): string
    {
        if (isset($value) && !is_array($value)) {
            $options['value'] = $value;
        } elseif (is_array($value)) {
            $options = array_merge($options, $value);
        }

        return $this->input('hidden', $name, '', $options);
    }

    public function submit(string $label, ?array $options = null): string
    {
        return $this->button('submit', $label, $options);
    }

    public function reset(string $label, ?array $options = null): string
    {
        return $this->button('reset', $label, $options);
    }

    public function checkbox(string $name, string $label, $value = null): string
    {
        return $this->input('checkbox', $name, $label, ['value' => $value]);
    }

    public function radio(string $name, string $label, ?array $options = null): string
    {
        return $this->input('radio', $name, $label, $options);
    }

    public function select(string $name, ?string $label, array $options, ?array $attributes = null): string
    {
        $attributes = $attributes ?? [];

        $currentValue = $this->getValue($name);
        $hasError = $this->hasError($name);
        $errorFeedback = $this->getErrorFeedback($name);

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeId = 'select-' . $this->sanitizeId($name);

        $customClasses = (string) ($attributes['class'] ?? '');

        $prepend = '';
        $append = '';
        $helpHtml = '';

        if (isset($attributes['input_group']) && is_array($attributes['input_group'])) {
            $prepend = $attributes['input_group']['prepend'] ?? '';
            $append = $attributes['input_group']['append'] ?? '';
        }

        if (is_scalar($prepend)) {
            $prepend = (string) $prepend;
        } else {
            $prepend = '';
        }

        if (is_scalar($append)) {
            $append = (string) $append;
        } else {
            $append = '';
        }

        if (isset($attributes['help'])) {
            $helpHtml = '<p class="mt-1 text-sm text-gray-500">'
                . htmlspecialchars((string) $attributes['help'], ENT_QUOTES, 'UTF-8')
                . '</p>';
        }

        $isArraySelect = !empty($attributes['multiple']);

        if ($isArraySelect && !is_array($currentValue)) {
            $currentValue = [];
        }

        $attrHtml = $this->buildAttributes($attributes, [
            'class',
            'input_group',
            'help',
            'id',
        ]);

        $optionHtml = '';

        foreach ($options as $k => $v) {
            if ($isArraySelect) {
                $selected = $this->inArrayString($k, (array) $currentValue);
            } else {
                $selected = $this->valueEquals($k, $currentValue);
            }

            $safeKey = htmlspecialchars((string) $k, ENT_QUOTES, 'UTF-8');
            $safeValue = htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

            $optionHtml .= '<option value="' . $safeKey . '"' . ($selected ? ' selected' : '') . '>' . $safeValue . '</option>';
        }

        $rounded = 'rounded-md';

        if ($prepend !== '' || $append !== '') {
            if ($prepend !== '' && $append !== '') {
                $rounded = 'rounded-none';
            } elseif ($prepend !== '') {
                $rounded = 'rounded-r-md';
            } else {
                $rounded = 'rounded-l-md';
            }
        }

        $state = $hasError
            ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
            : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500';

        $classes = "block w-full {$rounded} border px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-1 {$state}";

        if ($customClasses !== '') {
            $classes .= ' ' . htmlspecialchars($customClasses, ENT_QUOTES, 'UTF-8');
        }

        $nameAttribute = $isArraySelect ? $safeName . '[]' : $safeName;

        $selectHtml = '<select id="' . $safeId . '" name="' . $nameAttribute . '" class="' . $classes . '"' . $attrHtml . '>' . $optionHtml . '</select>';

        $wrapperHtml = $selectHtml;

        if ($prepend !== '' || $append !== '') {
            $wrapperHtml = '<div class="flex">'
                . $this->inputGroupPart($prepend, 'prepend')
                . $selectHtml
                . $this->inputGroupPart($append, 'append')
                . '</div>';
        }

        $labelHtml = '';

        if ($label !== null && $label !== '') {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $labelHtml = '<label for="' . $safeId . '" class="block text-sm font-medium text-gray-700 mb-1">' . $safeLabel . '</label>';
        }

        return '
        <div class="mb-4">
            ' . $labelHtml . '
            ' . $wrapperHtml . '
            ' . $helpHtml . '
            ' . $errorFeedback . '
        </div>';
    }

    public function textarea(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input('textarea', $name, $label, $options);
    }

    public function paragraph(string $name, string $label, ?array $options = null): string
    {
        return $this->textarea($name, $label, $options);
    }

    public function address(string $name, string $label, ?array $options = null): string
    {
        return $this->textarea($name, $label, $options);
    }

    public function search(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input('search', $name, $label, $options);
    }

    public function color(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input('color', $name, $label, $options);
    }

    public function currency(string $name, ?string $label = null, string $currency = 'FCFA', ?array $options = null): string
    {
        $options = $options ?? [];

        $options['step'] = $options['step'] ?? 'any';

        $options['input_group']['append'] =
            '<span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">'
            . htmlspecialchars($currency, ENT_QUOTES, 'UTF-8')
            . '</span>';

        return $this->number($name, $label, $options);
    }

    public function images(string $name, ?string $label = null, ?array $options = null): string
    {
        $options = $options ?? [];
        $options['multiple'] = true;

        return $this->image($name . '[]', $label, $options);
    }

    public function range(string $name, ?string $label = null, ?array $options = null): string
    {
        $options = $options ?? [];

        return $this->input('range', $name, $label, $options);
    }

    public function week(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input('week', $name, $label, $options);
    }

    public function month(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input('month', $name, $label, $options);
    }

    public function button(string $typeOrLabel, string|array|null $labelOrOptions = null, ?array $options = null): string
    {
        if (is_array($labelOrOptions) || $labelOrOptions === null) {
            $options = $labelOrOptions;
            $label = $typeOrLabel;
            $type = 'button';
        } else {
            $type = $typeOrLabel;
            $label = (string)$labelOrOptions;
        }

        $options = $options ?? [];

        $iconHtml = '';

        if (isset($options['icon'])) {
            $icon = (string) $options['icon'];

            if (stripos($icon, '<') === 0) {
                $iconHtml = $icon . ' ';
            } else {
                $iconHtml = '<i class="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . ' mr-2"></i>';
            }

            unset($options['icon']);
        }

        $customClass = $options['class'] ?? null;
        unset($options['class']);

        $base = 'inline-flex items-center justify-center rounded-md border border-transparent px-4 py-2 text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2';

        $classes = $customClass !== null
            ? $base . ' ' . $customClass
            : $base . ' bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500';

        $attr = $this->buildAttributes($options, []);

        $safeType = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

        return "<button type=\"{$safeType}\" class=\"{$classes}\"{$attr}>{$iconHtml}{$safeLabel}</button>";
    }

    public function buttonGroup(string $name, ?string $label, array $options): string
    {
        $currentValue = $this->getValue($name);
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        $html = '<div class="mb-4">';

        if ($label) {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $html .= '<span class="block text-sm font-medium text-gray-700 mb-1">' . $safeLabel . '</span>';
        }

        $html .= '<div class="inline-flex divide-x divide-gray-300 rounded-md border border-gray-300 shadow-sm overflow-hidden">';

        foreach ($options as $k => $v) {
            $checked = $this->valueEquals($currentValue, $k);

            $safeK = htmlspecialchars((string) $k, ENT_QUOTES, 'UTF-8');
            $safeV = htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

            $id = 'btn-grp-' . $this->sanitizeId($name) . '-' . $this->sanitizeId((string) $k);

            $html .= '
            <label class="relative inline-flex items-center cursor-pointer">
                <input
                    type="radio"
                    name="' . $safeName . '"
                    id="' . $id . '"
                    value="' . $safeK . '"
                    class="sr-only peer"
                    ' . ($checked ? 'checked' : '') . '
                >
                <span class="px-4 py-2 text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 peer-checked:bg-indigo-600 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-inset peer-focus-visible:ring-indigo-500">
                    ' . $safeV . '
                </span>
            </label>';
        }

        $html .= '</div>';
        $html .= $this->getErrorFeedback($name);
        $html .= '</div>';

        return $html;
    }

    public function autocomplete(string $name, ?string $label, string $ajaxUrl, ?array $options = null): string
    {
        $options = $options ?? [];

        $options['data-ajax-url'] = $ajaxUrl;
        $options['autocomplete'] = 'off';
        $options['class'] = trim(($options['class'] ?? '') . ' js-autocomplete');

        return $this->text($name, $label, $options);
    }

    public function dropzone(string $name, ?string $label = 'Glissez vos fichiers ici ou cliquez pour parcourir'): string
    {
        $errorFeedback = $this->getErrorFeedback($name);

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeLabel = htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8');
        $safeId = 'dropzone-' . $this->sanitizeId($name);

        $borderClass = $this->hasError($name)
            ? 'border-red-500'
            : 'border-gray-300';

        return '
        <div class="mb-4">
            <label for="' . $safeId . '" class="relative flex flex-col items-center justify-center border-2 border-dashed ' . $borderClass . ' rounded-lg p-6 text-center bg-gray-50 hover:bg-gray-100 cursor-pointer">
                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                <p class="text-sm text-gray-600">' . $safeLabel . '</p>
                <input
                    id="' . $safeId . '"
                    type="file"
                    name="' . $safeName . '"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                >
            </label>
            ' . $errorFeedback . '
        </div>';
    }

    public function creditCard(string $name, ?string $label = null, ?array $options = null): string
    {
        $options = $options ?? [];

        $options['placeholder'] = $options['placeholder'] ?? '1234 5678 9012 3456';
        $options['maxlength'] = $options['maxlength'] ?? '19';

        $options['input_group']['prepend'] =
            '<span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500">'
            . '<i class="fas fa-credit-card"></i>'
            . '</span>';

        return $this->text($name, $label ?? 'Carte bancaire', $options);
    }

    public function starRating(string $name, ?string $label = null, ?array $options = null): string
    {
        $stars = [
            1 => '⭐',
            2 => '⭐⭐',
            3 => '⭐⭐⭐',
            4 => '⭐⭐⭐⭐',
            5 => '⭐⭐⭐⭐⭐',
        ];

        return $this->radioList($name, $label ?? 'Note', $stars);
    }

    public function tags(string $name, ?string $label = null, ?array $options = null): string
    {
        $options = $options ?? [];

        $options['placeholder'] = $options['placeholder'] ?? 'Entrez les tags séparés par des virgules';

        return $this->text($name, $label ?? 'Tags', $options);
    }

    public function toggle(string $name, string $label, $value = 1): string
    {
        $currentValue = $this->getValue($name);
        $checked = $this->valueEquals($currentValue, $value);

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $safeValue = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

        $id = 'switch-' . $this->sanitizeId($name);
        $errorFeedback = $this->getErrorFeedback($name);

        $switchTrackClasses = "relative w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500 peer-focus-visible:ring-offset-2";

        return '
        <div class="mb-4">
            <label class="inline-flex items-center cursor-pointer">
                <input
                    id="' . $id . '"
                    name="' . $safeName . '"
                    type="checkbox"
                    value="' . $safeValue . '"
                    class="sr-only peer"
                    ' . ($checked ? 'checked' : '') . '
                >
                <div class="' . $switchTrackClasses . '"></div>
                <span class="ml-3 text-sm font-medium text-gray-700">' . $safeLabel . '</span>
            </label>
            ' . $errorFeedback . '
        </div>';
    }

    public function checkboxList(string $name, ?string $label, array $options): string
    {
        $currentValue = $this->getValue($name);

        if (!is_array($currentValue)) {
            $currentValue = [];
        }

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $hasError = $this->hasError($name);

        $html = '<div class="mb-4">';

        if ($label !== null) {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $html .= '<span class="block text-sm font-medium text-gray-700 mb-1">' . $safeLabel . '</span>';
        }

        $html .= '<div class="space-y-2">';

        foreach ($options as $k => $v) {
            $safeK = htmlspecialchars((string) $k, ENT_QUOTES, 'UTF-8');
            $safeV = htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

            $id = 'check-' . $this->sanitizeId($name) . '-' . $this->sanitizeId((string) $k);

            $checked = $this->inArrayString($k, $currentValue);

            $classes = $this->choiceInputClasses('checkbox', $hasError);

            $html .= '
            <label for="' . $id . '" class="inline-flex items-center">
                <input
                    type="checkbox"
                    name="' . $safeName . '[]"
                    id="' . $id . '"
                    value="' . $safeK . '"
                    class="' . $classes . '"
                    ' . ($checked ? 'checked' : '') . '
                >
                <span class="ml-2 text-sm text-gray-700">' . $safeV . '</span>
            </label>';
        }

        $html .= '</div>';
        $html .= $this->getErrorFeedback($name);
        $html .= '</div>';

        return $html;
    }

    public function radioList(string $name, ?string $label, array $options): string
    {
        $currentValue = $this->getValue($name);

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $hasError = $this->hasError($name);

        $html = '<div class="mb-4">';

        if ($label !== null) {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $html .= '<span class="block text-sm font-medium text-gray-700 mb-1">' . $safeLabel . '</span>';
        }

        $html .= '<div class="flex flex-wrap gap-4">';

        foreach ($options as $k => $v) {
            $safeK = htmlspecialchars((string) $k, ENT_QUOTES, 'UTF-8');
            $safeV = htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

            $id = 'radio-' . $this->sanitizeId($name) . '-' . $this->sanitizeId((string) $k);

            $checked = $this->valueEquals($currentValue, $k);

            $classes = $this->choiceInputClasses('radio', $hasError);

            $html .= '
            <label for="' . $id . '" class="inline-flex items-center">
                <input
                    type="radio"
                    name="' . $safeName . '"
                    id="' . $id . '"
                    value="' . $safeK . '"
                    class="' . $classes . '"
                    ' . ($checked ? 'checked' : '') . '
                >
                <span class="ml-2 text-sm text-gray-700">' . $safeV . '</span>
            </label>';
        }

        $html .= '</div>';
        $html .= $this->getErrorFeedback($name);
        $html .= '</div>';

        return $html;
    }
}