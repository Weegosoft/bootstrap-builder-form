<?php

namespace Weegosoft\Form;

/**
 * Bootstrap Design Class Form
 *
 * This class helps generate Bootstrap-styled HTML form elements.
 */
class Form
{
    /**
     * @var array|object The data to populate the form fields. Can be an array or an object.
     */
    private array|object $data = [];

    /**
     * @var array The validation errors.
     */
    private array $errors = [];

    /**
     * Form constructor.
     *
     * @param array|object $data The data to pre-fill the form fields. Can be an array or an object.
     */
    public function __construct(array|object $data, private ?array $errors = null)
    {
        $this->data = $data;
    }


    public function csrf(string $token): string
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '" />';
    }



    /**
     * Prevents fatal errors when the Form object is accidentally treated as a string.
     *
     * @return string Empty string
     */
    public function __toString(): string
    {
        return "";
    }

    /**
     * Retrieves the raw value for a given form field name from the data source.
     * Handles both array and object data sources (e.g., models with getters).
     *
     * @param string $name The name of the form field.
     * @return mixed|null The raw value of the field, or null if not found.
     */
    private function getValue(string $name)
    {
        // If data is an array (e.g., $_POST data)
        if (is_array($this->data)) {
            return $this->data[$name] ?? null;
        }

        // If data is an object (e.g., an Eloquent model or a DTO)
        // Convert snake_case (e.g., 'first_name') to CamelCase (e.g., 'FirstName') for getter method
        $methodName = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

        if (method_exists($this->data, $methodName)) {
            return $this->data->$methodName();
        }

        return null;
    }

    /**
     * Generates the CSS class string for an input field, adding 'is-invalid' if there are errors.
     *
     * @param string $name The name of the input field.
     * @return string The compiled CSS class string.
     */
    private function getInputClass(string $name): string
    {
        $inputClass = 'form-control custom-input'; // Default classes

        if (isset($this->errors[$name])) {
            $inputClass .= ' is-invalid'; // Add invalid class if errors exist
        }
        return $inputClass;
    }

    public function csrf(): string
    {
        return csrf();
    }

    /**
     * Generates the HTML for error feedback for a given field.
     *
     * @param string $name The name of the input field.
     * @return string The HTML for the error feedback, or an empty string if no errors.
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
            // Use 'd-block' to ensure the feedback is always visible when present
            return '<div class="invalid-feedback d-block">' . $errorMessage . '</div>';
        }
        return '';
    }

    /**
     * Opens a new HTML form tag.
     *
     * @param string $action The form submit URL.
     * @param string $method The HTTP method ('POST', 'GET').
     * @param bool $isMultipart Whether the form handles file uploads.
     * @param array|null $options Additional HTML attributes.
     * @return string The opening <form> tag.
     */
    public function open(string $action = '', string $method = 'POST', bool $isMultipart = false, ?array $options = null): string
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

        return "<form action=\"{$safeAction}\" method=\"{$safeMethod}\"{$enctype}{$attributes}>";
    }

    /**
     * Closes the HTML form tag.
     *
     * @return string The closing </form> tag.
     */
    public function close(): string
    {
        return "</form>";
    }

    /**
     * Core method to generate various HTML input types.
     *
     * @param string $type The HTML input type (e.g., 'text', 'email', 'textarea').
     * @param string $name The name attribute for the input.
     * @param string|null $label The label text for the input.
     * @param array|null $options An associative array of additional HTML attributes.
     * @return string The generated HTML for the input field.
     */
    private function input(string $type, string $name, ?string $label = null, ?array $options = null): string
    {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeType = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');

        $labelHtml = '';
        if ($label !== null) {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $labelHtml = "<label for=\"input{$safeName}\" class=\"control-label\">{$safeLabel}</label>";
        }

        $errorFeedback = $this->getErrorFeedback($name);
        $inputClasses = $this->getInputClass($name);

        if (!empty($errorFeedback)) {
            $inputClasses .= ' error-feedback border border-danger';
        }

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
                    if (isset($value['prepend'])) {
                        $inputGroupPrepend = '<div class="input-group-prepend">' . $value['prepend'] . '</div>';
                    }
                    if (isset($value['append'])) {
                        $inputGroupAppend = '<div class="input-group-append">' . $value['append'] . '</div>';
                    }
                } elseif ($key === 'help') {
                    $helpHtml = '<small class="form-text text-muted">' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '</small>';
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

        $rawValue = $this->getValue($name);
        $value = is_scalar($rawValue) ? htmlspecialchars((string)$rawValue, ENT_QUOTES, 'UTF-8') : '';

        $inputHtml = '';

        switch ($type) {
            case 'textarea':
                $inputHtml = "<textarea name=\"{$safeName}\" id=\"input{$safeName}\" class=\"{$inputClasses}\"{$attributes}>{$value}</textarea>";
                break;

            case 'hidden':
                $hiddenValue = array_key_exists('value', $options ?? []) ? $options['value'] : $rawValue;
                if ($hiddenValue instanceof \DateTimeInterface) {
                    $formattedValue = $hiddenValue->format('Y-m-d H:i:s');
                } else {
                    $formattedValue = is_scalar($hiddenValue) ? (string)$hiddenValue : '';
                }
                $safeHiddenVal = htmlspecialchars($formattedValue, ENT_QUOTES, 'UTF-8');
                return "<input type=\"{$safeType}\" name=\"{$safeName}\" id=\"input{$safeName}\" value=\"{$safeHiddenVal}\"{$attributes} />";

            case 'file':
                $fileInputClasses = 'custom-file-input';
                if (!empty($errorFeedback)) {
                    $fileInputClasses .= ' is-invalid';
                }
                $fileLabel = $label !== null ? htmlspecialchars($label, ENT_QUOTES, 'UTF-8') : 'Choose file';
                return "<div class=\"custom-file\">
                    <input type=\"{$safeType}\" name=\"{$safeName}\" id=\"input{$safeName}\" class=\"{$fileInputClasses}\"{$attributes} />
                    <label class=\"custom-file-label\" for=\"input{$safeName}\">
                        {$fileLabel}
                    </label>
                    {$errorFeedback}
                </div>";

            case 'checkbox':
                $defaultValue = $options['value'] ?? '1';
                $safeDefaultValue = htmlspecialchars((string)$defaultValue, ENT_QUOTES, 'UTF-8');
                $id = "check{$safeName}-" . preg_replace('/[^a-zA-Z0-9_-]/', '', $safeDefaultValue);
                $checked = (is_array($rawValue) && in_array($defaultValue, $rawValue)) || ($rawValue == $defaultValue) ? 'checked' : '';
                $checkboxClasses = 'form-check-input';
                if (!empty($errorFeedback)) {
                    $checkboxClasses .= ' is-invalid';
                }
                $cbLabel = htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8');
                return "<div class=\"checkbox-container form-check\">
                    <input class=\"{$checkboxClasses}\" type=\"{$safeType}\" name=\"{$safeName}\" id=\"{$id}\" value=\"{$safeDefaultValue}\" {$checked}{$attributes} />
                    <label for=\"{$id}\" class=\"form-check-label\">
                        <span style=\"margin-left: 30px\">{$cbLabel}</span>
                    </label>
                    {$errorFeedback}
                </div>";

            case 'radio':
                $defaultValue = $options['value'] ?? '';
                $safeDefaultValue = htmlspecialchars((string)$defaultValue, ENT_QUOTES, 'UTF-8');
                $id = "radio{$safeName}-" . preg_replace('/[^a-zA-Z0-9_-]/', '', $safeDefaultValue);
                $checked = ($rawValue == $defaultValue) ? 'checked' : '';
                $radioClasses = 'radio radio-filled';
                if (!empty($errorFeedback)) {
                    $radioClasses .= ' is-invalid';
                }
                $radioLabel = htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8');
                return "<div class=\"radio-container\">
                    <input type=\"{$safeType}\" name=\"{$safeName}\" id=\"{$id}\" value=\"{$safeDefaultValue}\" {$checked} class=\"{$radioClasses}\"{$attributes} />
                    <label for=\"{$id}\"> {$radioLabel} </label>
                    {$errorFeedback}
                </div>";

            case 'date':
                $formattedValue = ($rawValue instanceof \DateTimeInterface) ? $rawValue->format('Y-m-d') : (is_string($rawValue) && !empty($rawValue) ? date('Y-m-d', strtotime($rawValue)) : '');
                $safeFormattedValue = htmlspecialchars($formattedValue, ENT_QUOTES, 'UTF-8');
                $inputHtml = "<input type=\"{$safeType}\" name=\"{$safeName}\" id=\"input{$safeName}\" value=\"{$safeFormattedValue}\" class=\"{$inputClasses}\"{$attributes} />";
                break;

            case 'time':
                $formattedValue = ($rawValue instanceof \DateTimeInterface) ? $rawValue->format('H:i') : (is_string($rawValue) && !empty($rawValue) ? date('H:i', strtotime($rawValue)) : '');
                $safeFormattedValue = htmlspecialchars($formattedValue, ENT_QUOTES, 'UTF-8');
                $inputHtml = "<input type=\"{$safeType}\" name=\"{$safeName}\" id=\"input{$safeName}\" value=\"{$safeFormattedValue}\" class=\"{$inputClasses}\"{$attributes} />";
                break;

            case 'datetime-local':
                $formattedValue = ($rawValue instanceof \DateTimeInterface) ? $rawValue->format('Y-m-d\TH:i') : (is_string($rawValue) && !empty($rawValue) ? date('Y-m-d\TH:i', strtotime($rawValue)) : '');
                $safeFormattedValue = htmlspecialchars($formattedValue, ENT_QUOTES, 'UTF-8');
                $inputHtml = "<input type=\"{$safeType}\" name=\"{$safeName}\" id=\"input{$safeName}\" value=\"{$safeFormattedValue}\" class=\"{$inputClasses}\"{$attributes} />";
                break;

            case 'week':
                $formattedValue = ($rawValue instanceof \DateTimeInterface) ? $rawValue->format('Y-\WW') : (is_string($rawValue) && !empty($rawValue) ? date('Y-\WW', strtotime($rawValue)) : '');
                $safeFormattedValue = htmlspecialchars($formattedValue, ENT_QUOTES, 'UTF-8');
                $inputHtml = "<input type=\"{$safeType}\" name=\"{$safeName}\" id=\"input{$safeName}\" value=\"{$safeFormattedValue}\" class=\"{$inputClasses}\"{$attributes} />";
                break;

            case 'month':
                $formattedValue = ($rawValue instanceof \DateTimeInterface) ? $rawValue->format('Y-m') : (is_string($rawValue) && !empty($rawValue) ? date('Y-m', strtotime($rawValue)) : '');
                $safeFormattedValue = htmlspecialchars($formattedValue, ENT_QUOTES, 'UTF-8');
                $inputHtml = "<input type=\"{$safeType}\" name=\"{$safeName}\" id=\"input{$safeName}\" value=\"{$safeFormattedValue}\" class=\"{$inputClasses}\"{$attributes} />";
                break;

            default:
                $inputHtml = "<input type=\"{$safeType}\" name=\"{$safeName}\" id=\"input{$safeName}\" value=\"{$value}\" class=\"{$inputClasses}\"{$attributes} />";
                break;
        }

        $wrapperHtml = '';
        if (!empty($inputGroupPrepend) || !empty($inputGroupAppend)) {
            $wrapperHtml = "<div class=\"input-group\">{$inputGroupPrepend}{$inputHtml}{$inputGroupAppend}</div>";
        } else {
            $wrapperHtml = $inputHtml;
        }

        return "<div class=\"form-group\">
            {$labelHtml}
            {$wrapperHtml}
            {$helpHtml} 
            {$errorFeedback}
        </div>";
    }

    public function text(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input("text", $name, $label, $options);
    }

    public function password(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input("password", $name, $label, $options);
    }

    public function switch(string $name, string $label, $value = 1): string
    {
        $currentValue = $this->getValue($name);
        $checked = ($currentValue == $value) ? 'checked' : '';
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $safeValue = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        $id = "switch-{$safeName}";

        return "
    <div class=\"custom-control custom-switch form-group\">
        <input type=\"checkbox\" name=\"{$safeName}\" class=\"custom-control-input\" id=\"{$id}\" value=\"{$safeValue}\" {$checked}>
        <label class=\"custom-control-label\" for=\"{$id}\">{$safeLabel}</label>
    </div>";
    }

    public function email(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input("email", $name, $label, $options);
    }

    public function date(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input("date", $name, $label, $options);
    }

    public function datetimeLocal(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input("datetime-local", $name, $label, $options);
    }

    public function time(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input("time", $name, $label, $options);
    }

    public function tel(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input("tel", $name, $label, $options);
    }

    public function url(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input("url", $name, $label, $options);
    }

    public function number(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input("number", $name, $label, $options);
    }

    public function file(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input("file", $name, $label, $options);
    }

    public function image(string $name, ?string $label = null, ?array $options = null): string
    {
        $options = $options ?? [];
        $options['accept'] = 'image/jpeg,image/png,image/gif,image/svg+xml';
        return $this->input("file", $name, $label, $options);
    }

    public function hidden(string $name, $value = null, array $options = []): string
    {
        if (isset($value) && !is_array($value)) {
            $options['value'] = $value;
        } elseif (is_array($value)) {
            $options = array_merge($options, $value);
        }
        return $this->input("hidden", $name, "", $options);
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
        return $this->input("checkbox", $name, $label, ['value' => $value]);
    }

    public function radio(string $name, string $label, ?array $options = null): string
    {
        return $this->input("radio", $name, $label, $options);
    }

    public function select(string $name, ?string $label, array $options, ?array $attributes = null): string
    {
        $currentValue = $this->getValue($name);
        $errorFeedback = $this->getErrorFeedback($name);
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        $inputClasses = 'form-control';
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
                        if (isset($value['prepend'])) {
                            $inputGroupPrepend = '<div class="input-group-prepend">' . $value['prepend'] . '</div>';
                        }
                        if (isset($value['append'])) {
                            $inputGroupAppend = '<div class="input-group-append">' . $value['append'] . '</div>';
                        }
                    } elseif ($key === 'help') {
                        $helpHtml = '<small class="form-text text-muted">' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '</small>';
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
                $selected = in_array($k, (array)$currentValue, false) ? ' selected' : '';
            } else {
                $selected = ((string)$k === (string)$currentValue) ? ' selected' : '';
            }

            $safeKey = htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8');
            $safeValue = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

            $optionHtml .= "<option value=\"{$safeKey}\"{$selected}>{$safeValue}</option>";
        }

        $nameAttribute = $isArraySelect ? "{$safeName}[]" : $safeName;

        $html = '<div class="form-group">';

        if ($label !== null && $label !== '') {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $html .= "<label for=\"select-{$safeName}\" class=\"control-label\">{$safeLabel}</label>";
        }

        $selectHtml = "<select id=\"select-{$safeName}\" name=\"{$nameAttribute}\" class=\"{$inputClasses}\"{$attrHtml}>{$optionHtml}</select>";

        if (!empty($inputGroupPrepend) || !empty($inputGroupAppend)) {
            $html .= "<div class=\"input-group\">{$inputGroupPrepend}{$selectHtml}{$inputGroupAppend}</div>";
        } else {
            $html .= $selectHtml;
        }

        if (!empty($helpHtml)) {
            $html .= $helpHtml;
        }

        if (!empty($errorFeedback)) {
            $html .= $errorFeedback;
        }

        $html .= '</div>';

        return $html;
    }

    public function textarea(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input("textarea", $name, $label, $options);
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
        return $this->input("search", $name, $label, $options);
    }

    public function color(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input("color", $name, $label, $options);
    }

    public function currency(string $name, ?string $label = null, string $currency = 'FCFA', ?array $options = null): string
    {
        $options = $options ?? [];
        $safeCurrency = htmlspecialchars($currency, ENT_QUOTES, 'UTF-8');
        $options['input_group'] = [
            'append' => '<span class="input-group-text">' . $safeCurrency . '</span>'
        ];
        $options['step'] = $options['step'] ?? 'any';

        return $this->number($name, $label, $options);
    }

    public function images(string $name, ?string $label = null, ?array $options = null): string
    {
        $options = $options ?? [];
        $options['multiple'] = 'multiple';
        return $this->image($name . '[]', $label, $options);
    }

    public function range(string $name, ?string $label = null, ?array $options = null): string
    {
        $options['class'] = ($options['class'] ?? '') . ' custom-range';
        return $this->input("range", $name, $label, $options);
    }

    public function week(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input("week", $name, $label, $options);
    }

    public function month(string $name, ?string $label = null, ?array $options = null): string
    {
        return $this->input("month", $name, $label, $options);
    }

    public function button(string $type, string $label, ?array $options = null): string
    {
        $attr = '';
        $iconHtml = '';

        if ($options !== null) {
            if (isset($options['icon'])) {
                $safeIcon = htmlspecialchars((string)$options['icon'], ENT_QUOTES, 'UTF-8');
                $iconHtml = '<i class="' . $safeIcon . ' mr-1"></i> ';
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

        return "<button type=\"{$safeType}\"{$attr}>{$iconHtml}{$safeLabel}</button>";
    }

    public function buttonGroup(string $name, ?string $label, array $options): string
    {
        $currentValue = $this->getValue($name);
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        $html = '<div class="form-group">';
        if ($label) {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $html .= "<label class=\"d-block control-label\">{$safeLabel}</label>";
        }

        $html .= '<div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">';
        foreach ($options as $k => $v) {
            $active = ($currentValue == $k) ? 'active' : '';
            $checked = ($currentValue == $k) ? 'checked' : '';
            $safeK = htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8');
            $safeV = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
            $id = "btn-grp-{$safeName}-" . preg_replace('/[^a-zA-Z0-9_-]/', '', $safeK);

            $html .= "
        <label class=\"btn btn-outline-primary flex-fill {$active}\">
            <input type=\"radio\" name=\"{$safeName}\" id=\"{$id}\" value=\"{$safeK}\" {$checked} autocomplete=\"off\"> {$safeV}
        </label>";
        }
        $html .= '</div></div>';
        return $html;
    }

    public function autocomplete(string $name, ?string $label, string $ajaxUrl, ?array $options = null): string
    {
        $options['data-ajax-url'] = $ajaxUrl;
        $options['autocomplete'] = 'off';
        $options['class'] = ($options['class'] ?? '') . ' js-autocomplete';

        return $this->text($name, $label, $options);
    }

    public function dropzone(string $name, ?string $label = "Glissez vos fichiers ici ou cliquez pour parcourir"): string
    {
        $errorFeedback = $this->getErrorFeedback($name);
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeLabel = htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8');

        return "
    <div class=\"form-group\">
        <div class=\"border-dashed border-2 rounded p-4 text-center bg-light dropzone-wrapper position-relative\" style=\"border-style: dashed;\">
            <i class=\"fas fa-cloud-upload-alt fa-3x text-muted mb-2\"></i>
            <p class=\"mb-0\">{$safeLabel}</p>
            <input type=\"file\" name=\"{$safeName}\" class=\"position-absolute w-100 h-100 top-0 start-0 opacity-0 cursor-pointer\" style=\"opacity: 0; left: 0; top: 0;\" />
        </div>
        {$errorFeedback}
    </div>";
    }

    public function creditCard(string $type, string $label, ?array $options = null): string
    {
        return $this->button($type, $label, $options);
    }

    public function starRating(string $type, string $label, ?array $options = null): string
    {
        return $this->button($type, $label, $options);
    }

    public function tags(string $type, string $label, ?array $options = null): string
    {
        return $this->button($type, $label, $options);
    }

    public function toggle(string $name, string $label, $value = 1): string
    {
        $currentValue = $this->getValue($name);
        $checked = ($currentValue == $value) ? 'checked' : '';
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $safeValue = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        $id = "toggle-{$safeName}";
        $errorFeedback = $this->getErrorFeedback($name);

        return "
        <div class=\"custom-control custom-switch form-group\">
            <input type=\"checkbox\" name=\"{$safeName}\" class=\"custom-control-input\" id=\"{$id}\" value=\"{$safeValue}\" {$checked}>
            <label class=\"custom-control-label\" for=\"{$id}\">{$safeLabel}</label>
            {$errorFeedback}
        </div>";
    }

    public function checkboxList(string $name, ?string $label, array $options): string
    {
        $currentValue = $this->getValue($name);
        if (!is_array($currentValue)) {
            $currentValue = [];
        }

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $html = '';
        if ($label !== null) {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $html .= "<label class=\"control-label\">{$safeLabel}</label><br/>";
        }

        $optionHtml = [];
        foreach ($options as $k => $v) {
            $safeK = htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8');
            $safeV = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
            $id = "check{$safeName}-" . preg_replace('/[^a-zA-Z0-9_-]/', '', $safeK);
            $checked = in_array($k, $currentValue) ? 'checked' : '';

            $optionHtml[] = "<div class=\"checkbox-container form-check position-relative\">
                <input type=\"checkbox\" name=\"{$safeName}[]\" id=\"{$id}\" value=\"{$safeK}\" class=\"form-check-input filled-in\" {$checked} />
                <label for=\"{$id}\" class=\"form-check-label\">
                    <span style=\"margin-left:30px\">{$safeV}</span>
                </label>
            </div>";
        }

        $html .= implode('', $optionHtml);
        $html .= $this->getErrorFeedback($name);
        return $html;
    }

    public function radioList(string $name, ?string $label, array $options): string
    {
        $currentValue = $this->getValue($name);
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        $html = '<div class="form-group form-inline">';
        if ($label !== null) {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $html .= "<label class=\"control-label\">{$safeLabel}</label>";
        }

        foreach ($options as $k => $v) {
            $safeK = htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8');
            $safeV = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
            $id = "radio{$safeName}-" . preg_replace('/[^a-zA-Z0-9_-]/', '', $safeK);
            $checked = ($currentValue == $k) ? 'checked' : '';

            $html .= "<div class=\"radio-container d-inline-block ml-2\">
                <input type=\"radio\" name=\"{$safeName}\" id=\"{$id}\" value=\"{$safeK}\" {$checked} class=\"radio radio-filled\" />
                <label for=\"{$id}\"> {$safeV} </label>
            </div>";
        }
        $html .= $this->getErrorFeedback($name);
        $html .= '</div>';

        return $html;
    }
}
