<?php
/**
 * Created by PhpStorm.
 * User: stefanschindler
 * Date: 16.02.18
 * Time: 11:51
 */

namespace CampaigningBureau\CfRepositoryGenerator\Models;


use Contentful\Delivery\ContentTypeField;

class ContentfulTypeFieldDecorator
{
    /**
     * @var ContentTypeField
     */
    protected $contentTypeField;

    /**
     * ContentfulTypeFieldDecorator constructor.
     *
     * @param ContentTypeField $contentfulTypeField
     */
    public function __construct(ContentTypeField $contentfulTypeField)
    {
        $this->contentTypeField = $contentfulTypeField;
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
     * get the getter name for the field.
     *
     * will be prepended with get
     *
     * @return string
     */
    public function getGetterName()
    {
        return 'get' . studly_case($this->contentTypeField->getId()) . '()';
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
                return 'boolean';
            case 'Link':
                return $this->getLinkDataType($contentfulLinkType);
            case 'Date':
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
                return '\Contentful\Delivery\Asset';
            case 'Entry':
                // TODO not yet implemented
                return '<tbd>';
            default:
                return '<invalid>';
        }
    }
}