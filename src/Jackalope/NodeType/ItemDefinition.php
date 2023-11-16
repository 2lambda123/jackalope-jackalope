<?php

namespace Jackalope\NodeType;

use PHPCR\NodeType\ItemDefinitionInterface;
use Jackalope\FactoryInterface;
use PHPCR\NodeType\NoSuchNodeTypeException;
use PHPCR\RepositoryException;

/**
 * {@inheritDoc}
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @api
 */
class ItemDefinition implements ItemDefinitionInterface
{
    /**
     * The factory to instantiate objects
     *
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * Name of the declaring node type.
     *
     * @var string
     */
    protected $declaringNodeType;

    /**
     * Name of this node type.
     *
     * @var string
     */
    protected $name;

    /**
     * Whether this item is autocreated.
     *
     * @var boolean
     */
    protected $isAutoCreated;

    /**
     * Whether this item is mandatory.
     *
     * @var boolean
     */
    protected $isMandatory;

    /**
     * Whether this item is protected.
     *
     * @var boolean
     */
    protected $isProtected;

    /**
     * On parent version constant
     *
     * @var int
     */
    protected $onParentVersion;

    /**
     * Create a new item definition.
     *
     * TODO: document this format. Property and Node add more to this.
     *
     * @param FactoryInterface $factory         the object factory
     * @param array            $definition      The property definition data as array
     * @param NodeTypeManager  $nodeTypeManager
     */
    public function __construct(FactoryInterface $factory, array $definition, NodeTypeManager $nodeTypeManager)
    {
        $this->factory = $factory;
        $this->fromArray($definition);
        $this->nodeTypeManager = $nodeTypeManager;
    }

    /**
     * Load item definition from an array.
     *
     * Overwritten for property and node to add more information, with a call
     * to this parent method for the common things.
     *
     * @param array $data An array with the fields required by ItemDefinition
     */
    protected function fromArray(array $data)
    {
        $this->declaringNodeType = $data['declaringNodeType'];
        $this->name = $data['name'];
        $this->isAutoCreated = $data['isAutoCreated'];
        $this->isMandatory = $data['isMandatory'];
        $this->isProtected = $data['isProtected'];
        $this->onParentVersion = $data['onParentVersion'];
    }

    /**
     * {@inheritDoc}
     *
     * @return \PHPCR\NodeType\NodeTypeInterface a NodeType object
     *
     * @throws NoSuchNodeTypeException
     * @throws RepositoryException
     *
     * @api
     */
    public function getDeclaringNodeType()
    {
        return $this->nodeTypeManager->getNodeType($this->declaringNodeType);
    }

    /**
     * {@inheritDoc}
     *
     * @return string a string denoting the name or "*"
     *
     * @api
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool true, if the item is automatically created when its
     *              parent node is created, else false
     *
     * @api
     */
    public function isAutoCreated()
    {
        return $this->isAutoCreated;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool true, if the item is mandatory, else false
     *
     * @api
     */
    public function isMandatory()
    {
        return $this->isMandatory;
    }

    /**
     * {@inheritDoc}
     *
     * @return int an int constant member of OnParentVersionAction
     *
     * @api
     */
    public function getOnParentVersion()
    {
        return $this->onParentVersion;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool true, if the child item is protected, else false
     *
     * @api
     */
    public function isProtected()
    {
        return $this->isProtected;
    }
}
