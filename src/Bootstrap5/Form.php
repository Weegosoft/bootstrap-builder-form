<?php

namespace Weegosoft\Form\Bootstrap5;

/**
 * Bootstrap 5 Design Class Form
 *
 * This class helps generate Bootstrap 5-styled HTML form elements.
 */
class Form
{
    /**
     * @var array|object The data to populate the form fields.
     */
    private array|object $data = [];

    /**
     * @var array The validation errors.
     */
    private array $errors = [];

    /**
     * Form constructor.
     *
     * @param array|object $data The data to pre-fill the form fields.
     * @param array|null $errors Validation errors.
     */
    public function __construct(array|object $data = [], ?array $errors = null)
    {
        $this->data = $data;
        $this->errors = $errors ?? [];
    }

    /**
     * Sanitize a field name to create a valid HTML ID.
     */
    private function sanitizeId(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(['[]', '[', ']'], ['', '-', ''], $name));
    }

    /**
     * Generates a hidden CSRF token input.
     */
    public function csrf(string $token): string
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '" />';
    }

    /**
     * Prevents fatal errors when the Form object is accidentally treated as a string.
     */
    public function __toString(): string
    {
        return "";
    }

    /**
     * Retrieves the raw value for a given form field name from the data source.
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

    /**
     * Generates the CSS class string for an input field.
     */
    private function getInputClass(string $name): string
    {
        $inputClass = 'form-control';
        if (isset($this->errors[$name])) {
            $inputClass .= ' is-invalid';
        }
        return $inputClass;
    }

    /**
     * Generates the HTML for error feedback for a given field.
     */
    private function getErrorFeedback(string $name): string
    {
        if (isset($this->errors[$name])) {
            $errorMessage = '';
            if (is_array($this->errors[$name])) {
                $errorMessage = implode('<br>', array_map('htmlspecialchars', $this->errors[$name]));
            } else {
                $errorMessage = htmlspecialchars((string)$this->errors[$name], ENT_QUOTES, 'UTF-8');
            }
            return '<div class="invalid-feedback d-block">' . $errorMessage . '</div>';
        }
        return '';
    }

    /**
     * Opens a new HTML form tag.
     */
    public function open(string $action = '', string $method = 'POST', bool $isMultipart = false, ?array $options = null, ?string $csrfToken = null): string
    {
        $attributes = '';
        $enctype = $isMultipart ? ' enctype="multipart/form-data"' : '';
        
        if ($options !== null) {
            foreach ($options as $k => $v) {
                $safeK = htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8');
                $safeV = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
                $attributes .= " {$safeK}=\"{$safeV}\"";
            }
        }
        
        $safeAction = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
        $safeMethod = htmlspecialchars($method, ENT_QUOTES, 'UTF-8');
        $formTag = "<form action=\"{$safeAction}\" method=\"{$safeMethod}\"{$enctype}{$attributes}>";
        
        if (strtoupper($method) === 'POST' && $csrfToken !== null) {
            $formTag .= $this->csrf($csrfToken);
        }
        
        return $formTag;
    }

    /**
     * Closes the HTML form tag.
     */
    public function close(): string
    {
        return "</form>";
    }

    /**
     * Core method to generate various HTML input types (Bootstrap 5 compliant).
     */
    private function input(string $type, string $name, ?string $label = null, ?array $options = null): string
    {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeType = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
        $safeId = 'input' . $this->sanitizeId($name);
        
        $labelHtml = '';
        if ($label !== null) {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $labelHtml = "<label for=\"{$safeId}\" class=\"form-label\">{$safeLabel}</label>";
        }
        
        $errorFeedback = $this->getErrorFeedback($name);
        $inputClasses = $this->getInputClass($name);
        
        $attributes = '';
        $customClasses = '';
        $inputGroupPrepend = '';
        $inputGroupAppend = '';
        $helpHtml = '';
        
        if ($options !== null) {
            foreach ($options as $key => $value) {
                if ($key === 'class') {
                    $customClasses = (string)$value;
                } elseif ($key === 'input_group' && is_array($value)) {
                    // BS5: No more input-group-prepend/append wrappers, just direct HTML
                    $inputGroupPrepend = $value['prepend'] ?? '';
                    $inputGroupAppend = $value['append'] ?? '';
                } elseif ($key === 'help') {
                    $helpHtml = '<div class="form-text">' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '</div>';
                } elseif ($key !== 'type' && $key !== 'value') {
                    $safeKey = htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8');
                    $safeVal = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
                    $attributes .= " {$safeKey}=\"{$safeVal}\"";
                }
            }
        }
        
        if (!empty($customClasses)) {
            $inputClasses .= ' ' . htmlspecialchars($customClasses, ENT_QUOTES, 'UTF-8');
        }
        
        $rawValue = (isset($options['value']) && $options['value'] !== null) ? $options['value'] : $this->getValue($name);
        $value = is_scalar($rawValue) ? htmlspecialchars((string)$rawValue, ENT_QUOTES, 'UTF-8') : '';
        
        $inputHtml = '';
        
        switch ($type) {
            case 'textarea':
                $inputHtml = "<textarea name=\"{$safeName}\" id=\"{$safeId}\" class=\"{$inputClasses}\"{$attributes}>{$value}</textarea>";
                break;
                
            case 'hidden':
                $hiddenValue = array_key_exists('value', $options ?? []) ? $options['value'] : $rawValue;
                if ($hiddenValue instanceof \DateTimeInterface) {
                    $formattedValue = $hiddenValue->format('Y-m-d H:i:s');
                } else {
                    $formattedValue = is_scalar($hiddenValue) ? (string)$hiddenValue : '';
                }
                $safeHiddenVal = htmlspecialchars($formattedValue, ENT_QUOTES, 'UTF-8');
                return "<input type=\"{$safeType}\" name=\"{$safeName}\" id=\"{$safeId}\" value=\"{$safeHiddenVal}\"{$attributes} />";
                
            case 'file':
                $fileInputClasses = 'form-control'; // BS5 uses form-control for files
                if (!empty($errorFeedback)) $fileInputClasses .= ' is-invalid';
                return "<div class=\"mb-3\">
                    {$labelHtml}
                    <input type=\"{$safeType}\" name=\"{$safeName}\" id=\"{$safeId}\" class=\"{$fileInputClasses}\"{$attributes} />
                    {$errorFeedback}
                </div>";
                
            case 'checkbox':
                $defaultValue = $options['value'] ?? '1';
                $safeDefaultValue = htmlspecialchars((string)$defaultValue, ENT_QUOTES, 'UTF-8');
                $id = "check" . $this->sanitizeId($name) . '-' . $this->sanitizeId((string)$defaultValue);
                $checked = (is_array($rawValue) && in_array($defaultValue, $rawValue, true)) || ((string)$rawValue === (string)$defaultValue) ? 'checked' : '';
                $checkboxClasses = 'form-check-input';
                if (!empty($errorFeedback)) $checkboxClasses .= ' is-invalid';
                $cbLabel = htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8');
                
                return "<div class=\"form-check mb-2\">
                    <input class=\"{$checkboxClasses}\" type=\"{$safeType}\" name=\"{$safeName}\" id=\"{$id}\" value=\"{$safeDefaultValue}\" {$checked}{$attributes} />
                    <label for=\"{$id}\" class=\"form-check-label\">{$cbLabel}</label>
                    {$errorFeedback}
                </div>";
                
            case 'radio':
                $defaultValue = $options['value'] ?? '';
                $safeDefaultValue = htmlspecialchars((string)$defaultValue, ENT_QUOTES, 'UTF-8');
                $id = "radio" . $this->sanitizeId($name) . '-' . $this->sanitizeId((string)$defaultValue);
                $checked = ((string)$rawValue === (string)$defaultValue) ? 'checked' : '';
                $radioClasses = 'form-check-input';
                if (!empty($errorFeedback)) $radioClasses .= ' is-invalid';
                $radioLabel = htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8');
                
                return "<div class=\"form-check mb-2\">
                    <input type=\"{$safeType}\" name=\"{$safeName}\" id=\"{$id}\" value=\"{$safeDefaultValue}\" {$checked} class=\"{$radioClasses}\"{$attributes} />
                    <label for=\"{$id}\" class=\"form-check-label\">{$radioLabel}</label>
                    {$errorFeedback}
                </div>";
                
            case 'date':
            case 'time':
            case 'datetime-local':
            case 'week':
            case 'month':
                $format = match($type) {
                    'date' => 'Y-m-d',
                    'time' => 'H:i',
                    'datetime-local' => 'Y-m-d\TH:i',
                    'week' => 'Y-\WW',
                    'month' => 'Y-m',
                    default => 'Y-m-d'
                };
                $formattedValue = ($rawValue instanceof \DateTimeInterface) ? $rawValue->format($format) : (is_string($rawValue) && !empty($rawValue) ? date($format, strtotime($rawValue)) : '');
                $safeFormattedValue = htmlspecialchars($formattedValue, ENT_QUOTES, 'UTF-8');
                $inputHtml = "<input type=\"{$safeType}\" name=\"{$safeName}\" id=\"{$safeId}\" value=\"{$safeFormattedValue}\" class=\"{$inputClasses}\"{$attributes} />";
                break;
                
            default:
                $inputHtml = "<input type=\"{$safeType}\" name=\"{$safeName}\" id=\"{$safeId}\" value=\"{$value}\" class=\"{$inputClasses}\"{$attributes} />";
                break;
        }
        
        $wrapperHtml = '';
        if (!empty($inputGroupPrepend) || !empty($inputGroupAppend)) {
            $wrapperHtml = "<div class=\"input-group\">{$inputGroupPrepend}{$inputHtml}{$inputGroupAppend}</div>";
        } else {
            $wrapperHtml = $inputHtml;
        }
        
        return "<div class=\"mb-3\">
            {$labelHtml}
            {$wrapperHtml}
            {$helpHtml}
            {$errorFeedback}
        </div>";
    }

    public function text(string $name, ?string $label = null, ?array $options = null): string { return $this->input("text", $name, $label, $options); }
    public function password(string $name, ?string $label = null, ?array $options = null): string { return $this->input("password", $name, $label, $options); }
    public function email(string $name, ?string $label = null, ?array $options = null): string { return $this->input("email", $name, $label, $options); }
    public function date(string $name, ?string $label = null, ?array $options = null): string { return $this->input("date", $name, $label, $options); }
    public function datetimeLocal(string $name, ?string $label = null, ?array $options = null): string { return $this->input("datetime-local", $name, $label, $options); }
    public function time(string $name, ?string $label = null, ?array $options = null): string { return $this->input("time", $name, $label, $options); }
    public function tel(string $name, ?string $label = null, ?array $options = null): string { return $this->input("tel", $name, $label, $options); }
    public function url(string $name, ?string $label = null, ?array $options = null): string { return $this->input("url", $name, $label, $options); }
    public function number(string $name, ?string $label = null, ?array $options = null): string { return $this->input("number", $name, $label, $options); }
    public function file(string $name, ?string $label = null, ?array $options = null): string { return $this->input("file", $name, $label, $options); }
    public function search(string $name, ?string $label = null, ?array $options = null): string { return $this->input("search", $name, $label, $options); }
    public function color(string $name, ?string $label = null, ?array $options = null): string { return $this->input("color", $name, $label, $options); }
    public function week(string $name, ?string $label = null, ?array $options = null): string { return $this->input("week", $name, $label, $options); }
    public function month(string $name, ?string $label = null, ?array $options = null): string { return $this->input("month", $name, $label, $options); }
    public function textarea(string $name, ?string $label = null, ?array $options = null): string { return $this->input("textarea", $name, $label, $options); }
    public function paragraph(string $name, string $label, ?array $options = null): string { return $this->textarea($name, $label, $options); }
    public function address(string $name, string $label, ?array $options = null): string { return $this->textarea($name, $label, $options); }

    public function image(string $name, ?string $label = null, ?array $options = null): string {
        $options = $options ?? [];
        $options['accept'] = 'image/jpeg,image/png,image/gif,image/svg+xml';
        return $this->input("file", $name, $label, $options);
    }

    public function images(string $name, ?string $label = null, ?array $options = null): string {
        $options = $options ?? [];
        $options['multiple'] = 'multiple';
        return $this->image($name . '[]', $label, $options);
    }

    public function range(string $name, ?string $label = null, ?array $options = null): string {
        $options = $options ?? [];
        $options['class'] = ($options['class'] ?? '') . ' form-range'; // BS5 class
        return $this->input("range", $name, $label, $options);
    }

    public function hidden(string $name, $value = null, array $options = []): string {
        if (isset($value) && !is_array($value)) {
            $options['value'] = $value;
        } elseif (is_array($value)) {
            $options = array_merge($options, $value);
        }
        return $this->input("hidden", $name, "", $options);
    }

    public function submit(string $label, ?array $options = null): string { return $this->button('submit', $label, $options); }
    public function reset(string $label, ?array $options = null): string { return $this->button('reset', $label, $options); }

    public function checkbox(string $name, string $label, $value = null): string {
        return $this->input("checkbox", $name, $label, ['value' => $value]);
    }

    public function radio(string $name, string $label, ?array $options = null): string {
        return $this->input("radio", $name, $label, $options);
    }

    public function currency(string $name, ?string $label = null, string $currency = 'FCFA', ?array $options = null): string {
        $options = $options ?? [];
        $safeCurrency = htmlspecialchars($currency, ENT_QUOTES, 'UTF-8');
        $options['input_group'] = [
            'append' => '<span class="input-group-text">' . $safeCurrency . '</span>'
        ];
        $options['step'] = $options['step'] ?? 'any';
        return $this->number($name, $label, $options);
    }

    /**
     * BS5 Switch / Toggle
     */
    public function switch(string $name, string $label, $value = 1): string {
        return $this->toggle($name, $label, $value);
    }

    public function toggle(string $name, string $label, $value = 1): string {
        $currentValue = $this->getValue($name);
        $checked = ((string)$currentValue === (string)$value) ? 'checked' : '';
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $safeValue = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        $id = "switch-" . $this->sanitizeId($name);
        $errorFeedback = $this->getErrorFeedback($name);
        
        return "
        <div class=\"form-check form-switch mb-3\">
            <input type=\"checkbox\" name=\"{$safeName}\" class=\"form-check-input\" id=\"{$id}\" value=\"{$safeValue}\" {$checked} role=\"switch\">
            <label class=\"form-check-label\" for=\"{$id}\">{$safeLabel}</label>
            {$errorFeedback}
        </div>";
    }

    /**
     * Select field (BS5 compliant)
     */
    public function select(string $name, ?string $label, array $options, ?array $attributes = null): string {
        $currentValue = $this->getValue($name);
        $errorFeedback = $this->getErrorFeedback($name);
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeId = 'select-' . $this->sanitizeId($name);
        
        $inputClasses = 'form-select'; // BS5 uses form-select instead of form-control for selects
        if (!empty($attributes['class'])) {
            $inputClasses .= ' ' . htmlspecialchars($attributes['class'], ENT_QUOTES, 'UTF-8');
        }
        if (!empty($errorFeedback)) {
            $inputClasses .= ' is-invalid';
        }
        
        $attrHtml = '';
        $inputGroupPrepend = '';
        $inputGroupAppend = '';
        $helpHtml = '';
        
        if ($attributes !== null) {
            foreach ($attributes as $key => $value) {
                if ($key !== 'class') {
                    if ($key === 'input_group' && is_array($value)) {
                        $inputGroupPrepend = $value['prepend'] ?? '';
                        $inputGroupAppend = $value['append'] ?? '';
                    } elseif ($key === 'help') {
                        $helpHtml = '<div class="form-text">' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '</div>';
                    } else {
                        $safeK = htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8');
                        $safeV = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
                        $attrHtml .= " {$safeK}=\"{$safeV}\"";
                    }
                }
            }
        }
        
        $isArraySelect = isset($attributes['multiple']);
        if ($isArraySelect && !is_array($currentValue)) {
            $currentValue = [];
        }
        
        $optionHtml = '';
        foreach ($options as $k => $v) {
            $selected = '';
            if ($isArraySelect) {
                $selected = in_array($k, (array)$currentValue, true) ? ' selected' : '';
            } else {
                $selected = ((string)$k === (string)$currentValue) ? ' selected' : '';
            }
            $safeKey = htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8');
            $safeValue = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
            $optionHtml .= "<option value=\"{$safeKey}\"{$selected}>{$safeValue}</option>";
        }
        
        $nameAttribute = $isArraySelect ? "{$safeName}[]" : $safeName;
        $html = '<div class="mb-3">';
        
        if ($label !== null && $label !== '') {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $html .= "<label for=\"{$safeId}\" class=\"form-label\">{$safeLabel}</label>";
        }
        
        $selectHtml = "<select id=\"{$safeId}\" name=\"{$nameAttribute}\" class=\"{$inputClasses}\"{$attrHtml}>{$optionHtml}</select>";
        
        if (!empty($inputGroupPrepend) || !empty($inputGroupAppend)) {
            $html .= "<div class=\"input-group\">{$inputGroupPrepend}{$selectHtml}{$inputGroupAppend}</div>";
        } else {
            $html .= $selectHtml;
        }
        
        $html .= $helpHtml;
        $html .= $errorFeedback;
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Button Group (BS5 btn-check pattern)
     */
    public function buttonGroup(string $name, ?string $label, array $options): string {
        $currentValue = $this->getValue($name);
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $html = '<div class="mb-3">';
        
        if ($label) {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $html .= "<label class=\"form-label d-block\">{$safeLabel}</label>";
        }
        
        $html .= '<div class="btn-group" role="group">';
        foreach ($options as $k => $v) {
            $checked = ((string)$currentValue === (string)$k) ? 'checked' : '';
            $safeK = htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8');
            $safeV = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
            $id = "btn-grp-" . $this->sanitizeId($name) . '-' . $this->sanitizeId((string)$k);
            
            $html .= "
            <input type=\"radio\" class=\"btn-check\" name=\"{$safeName}\" id=\"{$id}\" value=\"{$safeK}\" autocomplete=\"off\" {$checked}>
            <label class=\"btn btn-outline-primary\" for=\"{$id}\">{$safeV}</label>";
        }
        $html .= '</div>';
        
        $html .= $this->getErrorFeedback($name);
        $html .= '</div>';
        return $html;
    }

    public function autocomplete(string $name, ?string $label, string $ajaxUrl, ?array $options = null): string {
        $options['data-ajax-url'] = $ajaxUrl;
        $options['autocomplete'] = 'off';
        $options['class'] = ($options['class'] ?? '') . ' js-autocomplete';
        return $this->text($name, $label, $options);
    }

    public function dropzone(string $name, ?string $label = "Glissez vos fichiers ici ou cliquez pour parcourir"): string {
        $errorFeedback = $this->getErrorFeedback($name);
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeLabel = htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8');
        $safeId = 'dropzone-' . $this->sanitizeId($name);
        
        return "
        <div class=\"mb-3\">
            <div class=\"border border-2 border-dashed rounded p-4 text-center bg-light position-relative\" style=\"border-style: dashed; cursor: pointer;\">
                <i class=\"fas fa-cloud-upload-alt fa-3x text-muted mb-2\"></i>
                <p class=\"mb-0\">{$safeLabel}</p>
                <input type=\"file\" name=\"{$safeName}\" id=\"{$safeId}\" class=\"position-absolute w-100 h-100 top-0 start-0 opacity-0\" style=\"cursor: pointer;\" />
            </div>
            {$errorFeedback}
        </div>";
    }

    public function creditCard(string $name, ?string $label = null, ?array $options = null): string {
        $options['placeholder'] = $options['placeholder'] ?? '1234 5678 9012 3456';
        $options['maxlength'] = '19';
        $options['input_group'] = ['prepend' => '<span class="input-group-text"><i class="fas fa-credit-card"></i></span>'];
        return $this->text($name, $label ?? 'Carte Bancaire', $options);
    }

    public function starRating(string $name, ?string $label = null, ?array $options = null): string {
        $stars = [1 => '⭐', 2 => '⭐⭐', 3 => '⭐⭐⭐', 4 => '⭐⭐⭐⭐', 5 => '⭐⭐⭐⭐⭐'];
        return $this->radioList($name, $label ?? 'Note', $stars);
    }

    public function tags(string $name, ?string $label = null, ?array $options = null): string {
        $options['placeholder'] = $options['placeholder'] ?? 'Entrez les tags séparés par des virgules';
        return $this->text($name, $label ?? 'Tags', $options);
    }

    public function checkboxList(string $name, ?string $label, array $options): string {
        $currentValue = $this->getValue($name);
        if (!is_array($currentValue)) {
            $currentValue = [];
        }
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $html = '<div class="mb-3">';
        
        if ($label !== null) {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $html .= "<label class=\"form-label d-block\">{$safeLabel}</label>";
        }
        
        foreach ($options as $k => $v) {
            $safeK = htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8');
            $safeV = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
            $id = "check" . $this->sanitizeId($name) . '-' . $this->sanitizeId((string)$k);
            $checked = in_array($k, $currentValue, true) ? 'checked' : '';
            
            $html .= "<div class=\"form-check\">
                <input type=\"checkbox\" name=\"{$safeName}[]\" id=\"{$id}\" value=\"{$safeK}\" class=\"form-check-input\" {$checked} />
                <label for=\"{$id}\" class=\"form-check-label\">{$safeV}</label>
            </div>";
        }
        
        $html .= $this->getErrorFeedback($name);
        $html .= '</div>';
        return $html;
    }

    public function radioList(string $name, ?string $label, array $options): string {
        $currentValue = $this->getValue($name);
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $html = '<div class="mb-3">';
        
        if ($label !== null) {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $html .= "<label class=\"form-label d-block\">{$safeLabel}</label>";
        }
        
        foreach ($options as $k => $v) {
            $safeK = htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8');
            $safeV = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
            $id = "radio" . $this->sanitizeId($name) . '-' . $this->sanitizeId((string)$k);
            $checked = ((string)$currentValue === (string)$k) ? 'checked' : '';
            
            $html .= "<div class=\"form-check\">
                <input type=\"radio\" name=\"{$safeName}\" id=\"{$id}\" value=\"{$safeK}\" {$checked} class=\"form-check-input\" />
                <label for=\"{$id}\" class=\"form-check-label\">{$safeV}</label>
            </div>";
        }
        
        $html .= $this->getErrorFeedback($name);
        $html .= '</div>';
        return $html;
    }

    public function button(string $typeOrLabel, string|array|null $labelOrOptions = null, ?array $options = null): string {
        if (is_array($labelOrOptions) || $labelOrOptions === null) {
            $options = $labelOrOptions;
            $label = $typeOrLabel;
            $type = 'button';
        } else {
            $type = $typeOrLabel;
            $label = (string)$labelOrOptions;
        }

        $attr = '';
        $iconHtml = '';
        $classes = 'btn btn-primary';
        
        if ($options !== null) {
            if (isset($options['class'])) {
                $classes = $options['class'];
                unset($options['class']);
            }
            if (isset($options['icon'])) {
                $iconHtml = '<i class="' . htmlspecialchars((string)$options['icon'], ENT_QUOTES, 'UTF-8') . ' me-1"></i> '; // BS5 uses me-1 instead of mr-1
                unset($options['icon']);
            }
            foreach ($options as $k => $v) {
                $safeK = htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8');
                $safeV = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
                $attr .= " {$safeK}=\"{$safeV}\"";
            }
        }
        
        $safeType = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        return "<button type=\"{$safeType}\" class=\"{$classes}\"{$attr}>{$iconHtml}{$safeLabel}</button>";
    }
}