<?php

namespace CampaigningSoftware\CfRepositoryGenerator\Services;


use CampaigningSoftware\CfRepositoryGenerator\Models\ContentfulTypeFieldDecorator;
use Contentful\Core\Resource\ResourceArray;
use Contentful\Delivery\Client;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\ContentType\Field;
use Illuminate\Support\Collection;

/**
 * Class ContentfulService
 * @package CampaigningSoftware\CfRepositoryGenerator\Services
 */
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
        $this->client = new Client(
            config('cf-repository-generator.contentful_delivery_token'),
            config('cf-repository-generator.contentful_delivery_space'),
            config('cf-repository-generator.contentful_environment', 'master')
        );
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
            ->map(function (Field $contentTypeField)
            {
                return new ContentfulTypeFieldDecorator($contentTypeField);
            })
            ->flatten();

        // add the id as 'field' as we also want to add it to the collection
        $fields->prepend(new ContentfulTypeFieldDecorator(new Field('id', 'id', 'Symbol')));

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
            switch ($type->getType(false)) {
                case 'Carbon':
                    return 'Carbon::createFromTimestamp($entry->' . $type->getGetterName() . '->getTimestamp())';
                default:
                    return '$entry->' . $type->getGetterName();
            }
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

    /**
     * get the method list for all fields
     *
     * @param Collection $contentfulFields
     *
     * @return mixed
     */
    public function getMethodList($contentfulFields)
    {
        return $contentfulFields->reduce(function ($carry, ContentfulTypeFieldDecorator $contentfulField)
        {
            return $carry . PHP_EOL . $contentfulField->getMethods();
        });
    }

    /**
     * get the argument list for the faker. depending on the type another fake is used
     *
     * @param Collection $contentfulFields
     *
     * @return mixed
     */
    public function getFakerArgumentList($contentfulFields)
    {
        return $contentfulFields->map(function (ContentfulTypeFieldDecorator $type)
        {
            switch ($type->getType(false)) {
                case 'string':
                    return '$this->faker->words(3, true)';
                case 'int':
                    return '$this->faker->numberBetween(0,100)';
                case 'double':
                    return '$this->faker->randomFloat(2)';
                case 'bool':
                    return '$this->faker->boolean';
                case 'Carbon':
                    return 'Carbon::now()->addDays($this->faker->numberBetween(-10, 30))';
                case '\Contentful\Delivery\Resource\Asset':
                    return 'new FakeAsset([])';
                default:
                    return '<tbd>';
            }
        })->implode(', ');
    }
}