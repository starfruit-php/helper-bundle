<?php

namespace Starfruit\HelperBundle\Validator;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Pimcore\Translation\Translator;

class Validator
{
    CONST PREFIX_MESSAGE = 'validate.message.';
    CONST FILE_FIELDS = ['image', 'imageArray', 'file'];
    CONST REQUIRED_FUNCTION = 'required';

    protected $validator;
    protected $translator;

    public function __construct(ValidatorInterface $validator, Translator $translator)
    {
        $this->validator = $validator;
        $this->translator = $translator;
    }

    private function checkExist($request, $field, $isFile)
    {
        if ($request->getMethod() === 'POST' ) {
            return $isFile ? $request->files->has($field) : $request->request->has($field);
        }

        return $request->query->has($field) && !empty($request->get($field)) && !is_null($request->get($field));
    }

    /**
     * Return an error response.
     * 
     * @param array $parameters
     * @param Request $request
     *  
     */
    public function validate(array $parameters, $request)
    {
        $errorMessages = null;

        $constraintsArray = [];
        $violationsArray = [];
        
        foreach ($parameters as $field => $param) {
            $violationsArray[$field] = !is_array($request->get($field)) ? trim($request->get($field)) : $request->get($field);
            $constraintsArray[$field] = [];
            $arrayParam = explode('|', $param);

            foreach ($arrayParam as $node) {
                $arrNode = explode(':', $node); 
                $functionName = $arrNode[0];

                $isFile = false;
                if (in_array($functionName, self::FILE_FIELDS)) {
                    $isFile = true;
                    $violationsArray[$field] = $request->files->get($field);
                }

                $addToValidator = false;
                // nếu field CÓ truyền lên
                if ($this->checkExist($request, $field, $isFile)) {
                    $addToValidator = true;
                } else {
                    // KHÔNG truyền lên nhưng CÓ điều kiện required
                    if (in_array(self::REQUIRED_FUNCTION, $arrayParam)) {
                        $addToValidator = true;
                    }
                }

                if ($addToValidator && method_exists($this, $functionName)) {
                    $params = isset($arrNode[1]) ? $arrNode[1] : null;

                    array_push($constraintsArray[$field], $this->$functionName($field, $params));   
                } 
            }
        }

        $constraints = new Assert\Collection(['fields' => $constraintsArray]);
        $violations = $this->validator->validate($violationsArray, $constraints);
        
        if (count($violations) > 0) {
            $errorMessages = [];
            $accessor = PropertyAccess::createPropertyAccessor();

            foreach ($violations as $violation) {
                // $message = $this->translator->trans($violation->getMessage());
                $message = $violation->getMessage();

                $accessor->setValue($errorMessages, $violation->getPropertyPath(), $message);
                $errorMessages = [
                    "message" => $message,
                    "key" => substr($violation->getPropertyPath(), 1, -1),
                    "params" => $violation->getInvalidValue(),
                ];
            }
        }

        return $errorMessages;
    }

    /**
     * Return first error message.
     *
     * @param array $errorMessages
     *  
     */
    public function getError(array $errorMessages)
    {
        if (!empty($errorMessages)) {
            foreach ($errorMessages as $field => $message) {
                return [
                    'message' => $message,
                    'field' => $field
                ];
            }
        }

        return '';
    }

    // Basic Constraints -------------------------------------------------------------------

    /**
     * Format: 'required'
     */
    function required($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\NotBlank($options);
    }

    /**
     * Format: 'blank'
     */
    function blank($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Blank($options);
    }

    /**
     * Format: 'notNull'
     */
    function notNull($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\NotNull($options);
    }

    /**
     * Format: 'isNull'
     */
    function isNull($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\IsNull($options);
    }

    /**
     * Format: 'isTrue'
     */
    function isTrue($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\IsTrue($options);
    }

    /**
     * Format: 'isFalse'
     */
    function isFalse($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\IsFalse($options);
    }

    /**
     * Check if string consists of all letters or digits (azAZ09)
     * Format: 'alnum'
     */
    function alnum($key)
    {
        return $this->type(__FUNCTION__);
    }

    /**
     * Format: 'array'
     */
    function array($key)
    {
        return $this->type(__FUNCTION__);
    }

    /**
     * Format: 'numeric'
     */
    function numeric($key, $params = null)
    {
        return $this->type(__FUNCTION__);
    }

    /**
     * Format: 'string'
     */
    function string($key)
    {
        return $this->type(__FUNCTION__);
    }

    /**
     * Check type of input
     */
    function type($type, $function = null)
    {
        $function = $function ?? $type;

        $options = [
            "type" => $type,
            "message" => self::PREFIX_MESSAGE . $function,
        ];

        return new Assert\Type($options);
    }
    
    // String Constraints -------------------------------------------------------------------

    /**
     * Format: 'email'
     */
    function email($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
            "mode" => "strict"
        ];

        return new Assert\Email($options);
    }

    /**
     * Format: 'expressionSyntax'
     */
    function expressionSyntax($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\ExpressionSyntax($options);
    }
    
    /**
     * Format: 'length:min,1,max,5'
     */
    function length($key, $params = null)
    {
        $options = [];
        
        if ($params) {
            $arrParam = explode(',', $params);
            foreach ($arrParam as $key => $value) {
                if (($key % 2) == 0) {
                    $number = $arrParam[($key + 1)];
                    $options[$value] = $number;
                    $options[$value . 'Message'] = $this->translator->trans(
                        'validate.length.' . $value,
                        [
                            'number' => $number,
                        ]
                    );
                }
            }
        }
        return new Assert\Length($options);
    }
    
    /**
     * Format: 'url'
     */
    function url($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Url($options);
    }
    
    /**
     * Check if value valid with regex expression
     * Format: 'regex:expression'
     */
    function regex($key, $expression = null) {
        $options = [
            'message' => self::PREFIX_MESSAGE . __FUNCTION__,
            'pattern' => '/'. $expression . '/'
        ];

        return new Assert\Regex($options);
    }

    /**
     * Format: 'hostname'
     */
    function hostname($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Hostname($options);
    }

    /**
     * Format: 'ip'
     */
    function ip($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Ip($options);
    }

    /**
     * Format: 'cidr'
     */
    function cidr($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Cidr($options);
    }

    /**
     * Format: 'json'
     */
    function json($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Json($options);
    }

    /**
     * Format: 'uuid'
     */
    function uuid($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Uuid($options);
    }

    /**
     * Format: 'ulid'
     */
    function ulid($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Ulid($options);
    }

    /**
     * Format: 'userPassword'
     */
    function userPassword($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new SecurityAssert\UserPassword($options);
    }

    /**
     * Format: 'notCompromisedPassword'
     */
    function notCompromisedPassword($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\NotCompromisedPassword($options);
    }

    /**
     * Format: 'passwordStrength:<level number>'
     * level number: 0 | 1 | 2 | 3 | 4
     */
    function passwordStrength($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        if ($params) {
            $options["minScore"] = (int) $params;
        }

        return new Assert\PasswordStrength($options);
    }

    /**
     * Format: 'cssColor'
     */
    function cssColor($key, $params = null)
    {
        $options = [
            "formats" => Assert\CssColor::HEX_LONG,
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\CssColor($options);
    }

    /**
     * Format: 'noSuspiciousCharacters'
     */
    function noSuspiciousCharacters($key, $params = null)
    {
        $options = [
            // "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];
        
        return new Assert\NoSuspiciousCharacters($options);
    }

    // Comparison Constraints -------------------------------------------------------------------

    /**
     * Check if input == value
     * Format: 'confirm:<value>'
     * Example: confirm:123abc
     */
    function confirm($key, $params)
    {
        $arrParam = explode(',', $params);

        $options = [
            'message' => self::PREFIX_MESSAGE . __FUNCTION__,
            'value' => $arrParam[0]
        ];

        return new Assert\EqualTo($options);
    }

    /**
     * Check if input != value
     * Format: 'notEqualTo:<value>'
     */
    function notEqualTo($key, $params)
    {
        $arrParam = explode(',', $params);

        $options = [
            'message' => self::PREFIX_MESSAGE . __FUNCTION__,
            'value' => $arrParam[0]
        ];

        return new Assert\NotEqualTo($options);
    }

    /**
     * Check if input === value
     * Format: 'identicalTo:<value>'
     * Example: identicalTo:123abc
     */
    function identicalTo($key, $params)
    {
        $arrParam = explode(',', $params);

        $options = [
            'message' => self::PREFIX_MESSAGE . __FUNCTION__,
            'value' => $arrParam[0]
        ];

        return new Assert\IdenticalTo($options);
    }

    /**
     * Check if input !== value
     * Format: 'notIdenticalTo:<value>'
     */
    function notIdenticalTo($key, $params)
    {
        $arrParam = explode(',', $params);

        $options = [
            'message' => self::PREFIX_MESSAGE . __FUNCTION__,
            'value' => $arrParam[0]
        ];

        return new Assert\NotIdenticalTo($options);
    }

    /**
     * Format: 'lessthan:<value>'
     */
    function lessthan($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        if ($params) {
            $options["value"] = $params;
        }

        return new Assert\LessThan($options);
    }

    /**
     * Format: 'lessthanOrEqual:<value>'
     */
    function lessthanOrEqual($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        if ($params) {
            $options["value"] = $params;
        }

        return new Assert\LessThanOrEqual($options);
    }

    /**
     * Format: 'greaterThan:<value>'
     */
    function greaterThan($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        if ($params) {
            $options["value"] = $params;
        }

        return new Assert\GreaterThan($options);
    }

    /**
     * Format: 'greaterThanOrEqual:<value>'
     */
    function greaterThanOrEqual($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        if ($params) {
            $options["value"] = $params;
        }

        return new Assert\GreaterThanOrEqual($options);
    }
    
    /**
     * Format: 'range:min,1,max,5'
     */
    function range($key, $params = null)
    {
        $options = [];
        
        if ($params) {
            $arrParam = explode(',', $params);
            foreach ($arrParam as $key => $value) {
                if (($key % 2) == 0) {
                    $number = $arrParam[($key + 1)];
                    $options[$value] = $number;
                    $options[$value . 'Message'] = $this->translator->trans(
                        'validate.range.' . $value,
                        [
                            'number' => $number,
                        ]
                    );
                }
            }
        }
        return new Assert\Range($options);
    }

    /**
     * Format: 'divisibleBy:<value>'
     */
    function divisibleBy($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        if ($params) {
            $options["value"] = $params;
        }

        return new Assert\DivisibleBy($options);
    }

    // Number Constraints -------------------------------------------------------------------

    /**
     * Format: 'positive'
     */
    function positive($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Positive($options);
    }

    /**
     * Format: 'positiveOrZero'
     */
    function positiveOrZero($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\PositiveOrZero($options);
    }

    /**
     * Format: 'negative'
     */
    function negative($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Negative($options);
    }

    /**
     * Format: 'negativeOrZero'
     */
    function negativeOrZero($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\NegativeOrZero($options);
    }

    // Date Constraints -------------------------------------------------------------------

    /**
     * Format: 'date'
     */
    function date($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Date($options);
    }

    /**
     * Format: 'datetime'
     */
    function datetime($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\DateTime($options);
    }

    /**
     * Format: 'time'
     */
    function time($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Time($options);
    }

    /**
     * Format: 'timezone'
     */
    function timezone($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Timezone($options);
    }

    // Choice Constraints -------------------------------------------------------------------

    /**
     * Format: 'choice:<option1>,<option2>'
     * Example: 'choice:desc,asc'
     */
    function choice($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];
        
        if ($params) {
            $arrParam = explode(',', $params);
            $options["choices"] = $arrParam;
        }
        
        return new Assert\Choice($options);
    }

    /**
     * Format: 'language'
     */
    function language($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Language($options);
    }

    /**
     * Format: 'locale'
     */
    function locale($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Locale($options);
    }

    /**
     * Format: 'country'
     */
    function country($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Country($options);
    }

    // File Constraints -------------------------------------------------------------------

    /**
     * Format: 'file:maxSize,5M,mimeTypes,image/jpeg#image/png'
     */
    function file($key, $params = null)
    {
        $options = [
            "maxSizeMessage" => self::PREFIX_MESSAGE . __FUNCTION__ .'.maxSize',
            "mimeTypesMessage" => self::PREFIX_MESSAGE . __FUNCTION__ .'.mimeTypes',
        ];

        if ($params) {
            $arrParam = explode(',', $params);

            foreach ($arrParam as $key => $value) {
                if (($key % 2) == 0) {

                    if ($value == "mimeTypes") {
                        $options[$value] = explode("#", $arrParam[($key + 1)]);
                    } else {
                        $options[$value] = $arrParam[($key + 1)];
                    }
                }
            }
        }

        return new Assert\File($options);
    }

    /**
     * Format: 'image:maxSize,5M,mimeTypes,image/jpeg#image/png'
     */
    function image($key, $params = null)
    {
        $options = [
            "maxSizeMessage" => self::PREFIX_MESSAGE . __FUNCTION__ .'.maxSize',
            "mimeTypesMessage" => self::PREFIX_MESSAGE . __FUNCTION__ .'.mimeTypes',
        ];

        if ($params) {
            $arrParam = explode(',', $params);

            foreach ($arrParam as $key => $value) {
                if (($key % 2) == 0) {

                    if ($value == "mimeTypes") {
                        $options[$value] = explode("#", $arrParam[($key + 1)]);
                    } else {
                        $options[$value] = $arrParam[($key + 1)];
                    }
                }
            }
        }

        return new Assert\Image($options);
    }

    function imageArray($key, $params = null)
    {
        $options = [
            "maxSizeMessage" => self::PREFIX_MESSAGE . __FUNCTION__ .'.maxSize',
            "mimeTypesMessage" => self::PREFIX_MESSAGE . __FUNCTION__ .'.mimeTypes',
        ];

        if ($params) {
            $arrParam = explode(',', $params);

            foreach ($arrParam as $key => $value) {
                if (($key % 2) == 0) {

                    if ($value == "mimeTypes") {
                        $options[$value] = explode("#", $arrParam[($key + 1)]);
                    } else {
                        $options[$value] = $arrParam[($key + 1)];
                    }
                }
            }
        }

        return new Assert\All([new Assert\Image($options)]);
    }

    // Financial and other Number Constraints -------------------------------------------------------------------

    /**
     * Format: 'bic'
     */
    function bic($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Bic($options);
    }

    /**
     * Format: 'cardScheme'
     */
    function cardScheme($key, $params = null)
    {
        $options = [
            "schemes" => [Assert\CardScheme::VISA],
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\CardScheme($options);
    }
    
    /**
     * Format: 'currency'
     */
    function currency($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Currency($options);
    }

    /**
     * Format: 'luhn'
     */
    function luhn($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Luhn($options);
    }

    /**
     * Format: 'iban'
     */
    function iban($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Iban($options);
    }

    /**
     * Format: 'isbn'
     */
    function isbn($key, $params = null)
    {
        $options = [
            "type" => Assert\Isbn::ISBN_10,
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Isbn($options);
    }

    /**
     * Format: 'issn'
     */
    function issn($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Issn($options);
    }

    /**
     * Format: 'isin'
     */
    function isin($key, $params = null)
    {
        $options = [
            "message" => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new Assert\Isin($options);
    }

    // Other Constraints -------------------------------------------------------------------
    
    /**
     * Count elements of an array. Input value must be type of array.
     * Format: 'count:min,1,max,5'
     */
    function count($key, $params = null)
    {
        $options = [
            "minMessage" => self::PREFIX_MESSAGE . __FUNCTION__ . '.min',
            "maxMessage" => self::PREFIX_MESSAGE . __FUNCTION__ . '.max',
            "exactMessage" => self::PREFIX_MESSAGE . __FUNCTION__ . '.exact',
        ];

        if ($params) {
            $arrParam = explode(',', $params);

            foreach ($arrParam as $key => $value) {
                if (($key % 2) == 0) {
                    $options[$value] = (int) $arrParam[($key + 1)];
                }
            }
        }

        return new Assert\Count($options);
    }

    // CUSTOM -------------------------------------------------------------------

    /**
     * Format: 'whitespace'
     */
    function whitespace($key, $params)
    {
        $options = [
            'message' => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new ContainsWhitespace($options);
    }

    /**
     * Format: 'dateNotPast'
     */
    function dateNotPast($key, $params)
    {
        $options = [
            'message' => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new ContainsDateNotPast($options);
    }

    /**
     * Check if value already existed in a field in a class
     * Format: 'unique:<className>'
     * Example: unique:Product
     */
    function unique($key, $class = null) {
        $options = [
            'message' => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        if ($class) {
            $options['class'] = $class;
            $options['field'] = $key;
        }

        return new ContainsUniqueInClass($options);
    }

    /**
     * Check if value is valid with a field in Class
     * Format: existsValue:<className><-field>
     * Example: existsValue:Product || existsValue:Product-id
     */
    function existsValue($key, $classField) {
        $options = [
            'message' => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        if ($classField) {
            $array = explode('-', $classField);
            $options['class'] = $array[0];
            $options['field'] = count($array) == 2 ? $array[1] : $key;
        }

        return new ContainsExistValueInClass($options);
    }

    /**
     * Kiểm tra đầu số điện thoại
     * Format: 'isPhone'
     */
    function isPhone($key, $params)
    {
        $options = [
            'message' => self::PREFIX_MESSAGE . __FUNCTION__,
        ];

        return new ContainsIsPhone($options);
    }
}
