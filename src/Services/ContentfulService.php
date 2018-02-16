<?php
/**
 * Created by PhpStorm.
 * User: stefanschindler
 * Date: 15.02.18
 * Time: 17:01
 */

namespace CampaigningBureau\CfRepositoryGenerator\Services;


use CampaigningBureau\CfRepositoryGenerator\Models\ContentfulTypeFieldDecorator;
use Contentful\Delivery\Client;
use Contentful\Delivery\ContentType;
use Contentful\Delivery\ContentTypeField;
use Contentful\ResourceArray;
use Illuminate\Support\Collection;

class ContentfulService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * holds the loaded content types
     * @var ResourceArray
     */
    private $contentTypes;

    public function __construct()
    {
        $this->client = new Client('6511adaf0dd5a05148b56387e1779055c594301d03e458442a101dac845d8576', 'r7zhbomppch0');
    }

    /**
     * get all available content types for the initialized space.
     *
     * @return Collection
     */
    public function getAvailableContentTypes()
    {
        // only load content types via the api if they werent already loaded
        if (!$this->contentTypes) {
            $this->contentTypes = $this->client->getContentTypes();
        }

        return collect($this->contentTypes->getItems());
    }

    /**
     * get the id for the given content type name
     *
     * @param string $contentTypeName
     *
     * @return string
     */
    public function getIdByName($contentTypeName)
    {
        return $this->getAvailableContentTypes()
                    ->filter(function (ContentType $contentType) use ($contentTypeName)
                    {
                        return $contentType->getName() === $contentTypeName;
                    })
                    ->first()
                    ->getId();
    }

    /**
     * get all fields for the content type with the given id
     *
     * @param string $contentTypeId
     *
     * @return Collection
     */
    public function getFieldsForId($contentTypeId)
    {
        /** @var ContentType $contentType */
        $contentType = $this->getAvailableContentTypes()
                            ->filter(function (ContentType $contentType) use ($contentTypeId)
                            {
                                return $contentType->getId() === $contentTypeId;
                            })
                            ->first();

        // build a collection of decorated fields
        $fields = collect($contentType->getFields())
            ->map(function (ContentTypeField $contentTypeField)
            {
                return new ContentfulTypeFieldDecorator($contentTypeField);
            })
            ->flatten();

        // add the id as 'field' as we also want to add it to the collection
        $fields->prepend(new ContentfulTypeFieldDecorator(new ContentTypeField('id', 'id', 'Symbol', null, null, null,
            true, false, false)));

        return $fields;
    }

    /**
     * return the model getter list (required for the factory) as string.
     * returns all getters separated by comma.
     *
     * @param Collection $contentfulFields
     *
     * @return string
     */
    public function getModelGetterList($contentfulFields)
    {
        return $contentfulFields->map(function (ContentfulTypeFieldDecorator $type)
        {
            return '$entry->' . $type->getGetterName();
        })
                                ->implode(', ');
    }

    /**
     * get the argument list for the constructor.
     *
     * @param Collection $contentfulFields
     *
     * @return mixed
     */
    public function getConstructorArgumentList($contentfulFields)
    {
        return $contentfulFields->map(function (ContentfulTypeFieldDecorator $type)
        {
            // if the field is set to required in contentful, we know, data will exist, and typehinting is allowed
            if ($type->isRequired()) {
                return $type->getType() . ' ' . $type->getVariableName();
            }

            return $type->getVariableName();
        })
                                ->implode(', ');
    }

    /**
     * return the constructor initialization lines
     *
     * @param Collection $contentfulFields
     *
     * @return string
     */
    public function getConstructorInitialization($contentfulFields)
    {
        return $contentfulFields->reduce(function ($carry, ContentfulTypeFieldDecorator $type)
        {
            return $carry . '$this->' . $type->getVariableName(true) . ' = ' . $type->getVariableName() . ';' . PHP_EOL;
        });
    }

    /**
     * get the doc block for the constructor arguments
     *
     * @param Collection $contentfulFields
     *
     * @return string
     */
    public function getConstructorArgumentDoc($contentfulFields)
    {
        return $contentfulFields->reduce(function ($carry, ContentfulTypeFieldDecorator $type)
        {
            return $carry . PHP_EOL . '* @param ' . $type->getType(true) . ' ' . $type->getVariableName();
        });
    }

    /**
     * get the instance variables block
     *
     * @param Collection $contentfulFields
     *
     * @return string
     */
    public function getInstanceVariables($contentfulFields)
    {
        return $contentfulFields->reduce(function ($carry, ContentfulTypeFieldDecorator $type)
        {
            return $carry . PHP_EOL . '/**' . PHP_EOL . '* @var ' . $type->getType(true) . PHP_EOL . '*/' . PHP_EOL .
                   'private ' . $type->getVariableName() . ';' . PHP_EOL;
        });
    }
}