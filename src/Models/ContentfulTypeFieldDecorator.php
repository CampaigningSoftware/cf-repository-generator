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
    protected $contentfulTypeField;

    /**
     * ContentfulTypeFieldDecorator constructor.
     *
     * @param ContentTypeField $contentfulTypeField
     */
    public function __construct(ContentTypeField $contentfulTypeField)
    {
        $this->contentfulTypeField = $contentfulTypeField;
    }

    /**
     * get the getter name for the field.
     *
     * will be prepended with get
     */
    public function getGetterName()
    {
        return 'get' . studly_case($this->contentfulTypeField->getId()) . '()';
    }
}