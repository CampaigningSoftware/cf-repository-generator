<?php

namespace CampaigningBureau\CfRepositoryGenerator\Models;


use Contentful\Delivery\Resource\ContentType\Field;
use Illuminate\Support\Str;

/**
 * Class ContentfulTypeFieldDecorator
 *
 * decorate a contentful type field with additional functionality
 *
 * @package CampaigningBureau\CfRepositoryGenerator\Models
 */
class ContentfulTypeFieldDecorator
{
    /**
     * @var Field
     */
    protected $contentTypeField;

    /**
     * File manager.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $fileManager;

    /**
     * ContentfulTypeFieldDecorator constructor.
     *
     * @param Field $contentfulTypeField
     */
    public function __construct(Field $contentfulTypeField)
    {
        $this->contentTypeField = $contentfulTypeField;
        $this->fileManager = app('files');
    }

    /**
     * Returns true if this field is required.
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->contentTypeField->isRequired();
    }

    /**
     * get the getter function name for the field.
     *
     * will be prepended with get
     *
     * @return string
     */
    public function getGetterName()
    {
        return 'get' . Str::studly($this->contentTypeField->getId()) . '()';
    }

    /**
     * get the checker function name for the field.
     *
     * will be prepended with has
     *
     * @return string
     */
    public function getCheckerName()
    {
        return 'has' . Str::studly($this->contentTypeField->getId()) . '()';
    }

    /**
     * get the function name for the url getter
     *
     * @return string
     */
    public function getUrlGetterName()
    {
        return 'get' . Str::studly($this->contentTypeField->getId()) . 'Url';
    }

    /**
     * get the variable name of the field.
     * if the param is set to true, the dollar sign is not prepended
     *
     * @param bool $withoutVariableIndicator
     *
     * @return string
     */
    public function getVariableName($withoutVariableIndicator = false)
    {
        if ($withoutVariableIndicator) {
            return $this->contentTypeField->getId();
        }

        return '$' . $this->contentTypeField->getId();
    }

    /**
     * get the php class/simple type of the variable.
     * if the `includeNull` param is set to true, it could return a list of types, if the variable is optional (might
     * also be null).
     *
     * @param bool $includeNull
     *
     * @return string
     */
    public function getType($includeNull = true)
    {
        $dataType = $this->getDataType($this->contentTypeField->getType(), $this->contentTypeField->getLinkType());

        // if the content type is not required, the field could also be null
        if ($includeNull && !$this->contentTypeField->isRequired()) {
            $dataType .= '|null';
        }

        return $dataType;
    }

    /**
     * get the php data type for the given contentful type
     *
     * @param string      $contentfulDataType
     * @param null|string $contentfulLinkType
     *
     * @return string
     */
    private function getDataType($contentfulDataType, $contentfulLinkType)
    {
        switch ($contentfulDataType) {
            case 'Symbol':
            case 'Text':
                return 'string';
            case 'Integer':
                return 'int';
            case 'Number':
                return 'double';
            case 'Boolean':
                return 'bool';
            case 'Link':
                return $this->getLinkDataType($contentfulLinkType);
            case 'Date':
                return 'Carbon';
            case 'Array':
            case 'Object':
                // TODO not yet implemented
                return '<tbd>';
            default:
                return '<invalid>';
        }
    }

    /**
     * get the php data type for the given contentful link type
     *
     * @param string $contentfulLinkType
     *
     * @return string
     */
    private function getLinkDataType($contentfulLinkType)
    {
        switch ($contentfulLinkType) {
            case 'Asset':
                return '\Contentful\Delivery\Resource\Asset';
            case 'Entry':
                // TODO not yet implemented
                return '<tbd>';
            default:
                return '<invalid>';
        }
    }

    /**
     * get the getter method(s) for the current field.
     * all fields return a getter.
     * image fields additionally return cached url and a boolean checker.
     */
    public function getMethods()
    {
        // load template
        $getterTemplate = $this->fileManager->get(__DIR__ . '/../stubs/methods/getter.stub');

        $replacements = [
            '%type%'         => $this->getType(true),
            '%methodName%'   => $this->getGetterName(),
            '%variableName%' => '$this->' . $this->getVariableName(true),
        ];

        $getterTemplate = str_replace(array_keys($replacements), array_values($replacements), $getterTemplate);

        // with no linked type, just return the getter
        if ($this->contentTypeField->getLinkType() === null) {
            return $getterTemplate;
        } elseif ($this->contentTypeField->getLinkType() === 'Asset') {
            // load template
            $assetGetterTemplate = $this->fileManager->get(__DIR__ . '/../stubs/methods/asset-methods.stub');

            $replacements = [
                '%urlGetterName%'   => $this->getUrlGetterName(),
                '%checkerName%'     => $this->getCheckerName(),
                '%variableName%'    => '$this->' . $this->getVariableName(true),
                '%rawVariableName%' => $this->getVariableName(true),
            ];

            $getterTemplate .= PHP_EOL . str_replace(array_keys($replacements), array_values($replacements),
                    $assetGetterTemplate);

            return $getterTemplate;
        }
    }
}