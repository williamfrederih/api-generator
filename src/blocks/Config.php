<?php
namespace rjapi\blocks;

use rjapi\controllers\ControllersTrait;
use rjapi\exception\AttributesException;
use rjapi\extension\JSONApiInterface;
use rjapi\helpers\Classes;
use rjapi\helpers\Console;
use rjapi\helpers\MigrationsHelper;
use rjapi\types\ConfigInterface;
use rjapi\types\CustomsInterface;
use rjapi\types\ModelsInterface;
use rjapi\types\ModulesInterface;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;

class Config implements ConfigInterface
{
    use ContentManager, ConfigTrait;

    protected $sourceCode = '';
    /** @var ControllersTrait generator */
    protected $generator = null;
    protected $className = null;

    /**
     * Config constructor.
     * @param $generator
     */
    public function __construct($generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
    }

    public function create()
    {
        $this->setContent();
        // create config file
        $file      = $this->generator->formatConfigPath() .
            ModulesInterface::CONFIG_FILENAME . PhpInterface::PHP_EXT;
        $isCreated = FileManager::createFile($file, $this->sourceCode, true);
        if($isCreated) {
            Console::out($file . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    /**
     * @param string $name Version name aka: V1, V2 etc
     */
    private function setName(string $name)
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 .
            PhpInterface::QUOTES . ModulesInterface::KEY_NAME
            . PhpInterface::QUOTES . PhpInterface::DOUBLE_ARROW . PhpInterface::QUOTES .
            ucfirst($name) . PhpInterface::QUOTES . PhpInterface::COMMA . PHP_EOL;
    }

    /**
     * @param int $limit
     */
    private function setLimit(int $limit)
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::QUOTES . ModelsInterface::PARAM_LIMIT . PhpInterface::QUOTES
            . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW .
            PhpInterface::SPACE . $limit . PhpInterface::COMMA . PHP_EOL;
    }

    /**
     * @param string $sort
     */
    private function setSort(string $sort)
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::QUOTES . ModelsInterface::PARAM_SORT . PhpInterface::QUOTES
            . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW .
            PhpInterface::SPACE . PhpInterface::QUOTES . $sort
            . PhpInterface::QUOTES . PhpInterface::COMMA . PHP_EOL;
    }

    /**
     * @param int $page
     */
    private function setPage(int $page)
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::QUOTES . ModelsInterface::PARAM_PAGE . PhpInterface::QUOTES
            . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW .
            PhpInterface::SPACE . $page . PhpInterface::COMMA . PHP_EOL;
    }

    /**
     * @param string $entity
     */
    private function setTable(string $entity)
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::QUOTES . ModelsInterface::MIGRATION_TABLE . PhpInterface::QUOTES
            . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW
            . PhpInterface::SPACE . PhpInterface::QUOTES . MigrationsHelper::getTableName($entity)
            . PhpInterface::QUOTES . PhpInterface::COMMA . PHP_EOL;
    }

    private function setEnabled()
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::QUOTES . ConfigInterface::ENABLED . PhpInterface::QUOTES
            . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW .
            PhpInterface::SPACE . PhpInterface::PHP_TYPES_BOOL_TRUE . PhpInterface::COMMA . PHP_EOL;
    }

    /**
     * @param int $time
     */
    private function setActivate(int $time)
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::QUOTES . ConfigInterface::ACTIVATE . PhpInterface::QUOTES
            . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW .
            PhpInterface::SPACE . $time . PhpInterface::COMMA . PHP_EOL;
    }

    /**
     * @param int $time
     */
    private function setExpires(int $time)
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::QUOTES . ConfigInterface::EXPIRES . PhpInterface::QUOTES
            . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW .
            PhpInterface::SPACE . $time . PhpInterface::COMMA . PHP_EOL;
    }

    /**
     * @param string $token
     */
    private function setAccessToken(string $token)
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::QUOTES . JSONApiInterface::PARAM_ACCESS_TOKEN . PhpInterface::QUOTES
            . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW .
            PhpInterface::SPACE . PhpInterface::QUOTES . $token . PhpInterface::QUOTES . PhpInterface::COMMA . PHP_EOL;
    }

    /**
     * Constructs the config structure
     */
    private function setContent()
    {
        $this->setTag();
        $this->openRoot();
        $this->setName($this->generator->version);
        $this->setQueryParams();
        $this->setTrees();
        $this->setJwtContent();
        $this->setFsmContent();
        $this->closeRoot();
    }

    private function setQueryParams()
    {
        if(empty($this->generator->types[CustomsInterface::CUSTOM_TYPES_QUERY_PARAMS][RamlInterface::RAML_PROPS]) === false) {
            $queryParams = $this->generator->types[CustomsInterface::CUSTOM_TYPES_QUERY_PARAMS][RamlInterface::RAML_PROPS];
            $this->openParams();
            if(empty($queryParams[ModelsInterface::PARAM_LIMIT][RamlInterface::RAML_KEY_DEFAULT]) === false) {
                $this->setLimit($queryParams[ModelsInterface::PARAM_LIMIT][RamlInterface::RAML_KEY_DEFAULT]);
            }
            if(empty($queryParams[ModelsInterface::PARAM_SORT][RamlInterface::RAML_KEY_DEFAULT]) === false) {
                $this->setSort($queryParams[ModelsInterface::PARAM_SORT][RamlInterface::RAML_KEY_DEFAULT]);
            }
            if(empty($queryParams[ModelsInterface::PARAM_PAGE][RamlInterface::RAML_KEY_DEFAULT]) === false) {
                $this->setPage($queryParams[ModelsInterface::PARAM_PAGE][RamlInterface::RAML_KEY_DEFAULT]);
            }
            if(empty($queryParams[JSONApiInterface::PARAM_ACCESS_TOKEN][RamlInterface::RAML_KEY_DEFAULT]) === false) {
                $this->setAccessToken($queryParams[JSONApiInterface::PARAM_ACCESS_TOKEN][RamlInterface::RAML_KEY_DEFAULT]);
            }
            $this->closeEntity();
        }
    }

    /**
     *  Sets JWT config array
     *  Ex.:
     *    'jwt'                  => [
     *      'enabled'  => true,
     *      'table'    => 'user',
     *      'activate' => 30,
     *      'expires'  => 3600,
     *    ],
     */
    private function setJwtContent()
    {
        foreach($this->generator->types as $objName => $objData) {
            if(in_array($objName, $this->generator->customTypes) === false) { // if this is not a custom type generate resources
                $excluded = false;
                foreach($this->generator->excludedSubtypes as $type) {
                    if(strpos($objName, $type) !== false) {
                        $excluded = true;
                    }
                }
                // if the type is among excluded - continue
                if($excluded === true) {
                    continue;
                }
                $this->setJwtOptions($objName);
            }
        }
    }

    /**
     *  Sets Finite State Machine config array
     *  Ex.:
     * 'state_machine' => [
     *  'article' => [ // table
     *      'status' => [ // column
     *          'enabled' => true,
     *              'states' => [
     *                  'draft' => [
     *                      'initial' => true,
     *                      'published',
     *                  ],
     *                  'published' => [
     *                      'draft',
     *                      'postponed',
     *                  ],
     *                  'postponed' => [
     *                      'published',
     *                      'archived',
     *                  ],
     *                  'archived' => [],
     *              ]
     *      ]
     *  ]
     * ],
     */
    private function setFsmContent()
    {
        foreach($this->generator->types as $objName => $objData) {
            if(in_array($objName, $this->generator->customTypes) === false) { // if this is not a custom type generate resources
                $excluded = false;
                foreach($this->generator->excludedSubtypes as $type) {
                    if(strpos($objName, $type) !== false) {
                        $excluded = true;
                    }
                }
                // if the type is among excluded - continue
                if($excluded === true) {
                    continue;
                }
                $this->setFsmOptions($objName);
            }
        }
    }

    /**
     * @param string $objName
     * @throws AttributesException
     */
    private function setFsmOptions(string $objName)
    {
        if(empty($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][RamlInterface::RAML_PROPS]) === false) {
            foreach($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][RamlInterface::RAML_PROPS] as $propKey => $propVal) {
                if(is_array($propVal)) {// create jwt config setting
                    if(empty($propVal[RamlInterface::RAML_FACETS][ConfigInterface::STATE_MACHINE]) === false) {
                        // found FSM definition
                        $this->openFsm($objName, $propKey);
                        $initial = null;
                        foreach($propVal[RamlInterface::RAML_FACETS][ConfigInterface::STATE_MACHINE] as $key => &$val) {
                            if($key === ConfigInterface::INITIAL) {
                                $initial = $val;
                                continue;
                            }
                            $this->setTabs(5);
                            if(is_array($val) === false) { // here value can be only an array, even with one value
                                throw new AttributesException('FSM attributes value can be only an array.');
                            }
                            if($key === $initial) { // key corresponds to initial field
                                array_push($val, ConfigInterface::INITIAL);
                                $this->setArrayProperty(PhpInterface::QUOTES . $key . PhpInterface::QUOTES, $val);
                            } else {
                                $this->setArrayProperty(PhpInterface::QUOTES . $key . PhpInterface::QUOTES, $val);
                            }
                        }
                        $this->closeFsm();
                    }
                }
            }
        }
    }

    /**
     * Sets jwt config options
     * @param string $objName
     */
    private function setJwtOptions(string $objName)
    {
        if(empty($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][RamlInterface::RAML_PROPS]) === false) {
            foreach($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][RamlInterface::RAML_PROPS] as $propKey => $propVal) {
                if(is_array($propVal) && $propKey === CustomsInterface::CUSTOM_PROP_JWT) {// create jwt config setting
                    $this->openJwt();
                    $this->setEnabled();
                    $this->setTable($objName);
                    $this->setActivate(ConfigInterface::DEFAULT_ACTIVATE);
                    $this->setExpires(ConfigInterface::DEFAULT_EXPIRES);
                    $this->closeEntity();
                }
            }
        }
    }

    /**
     *  Sets config trees structure
     */
    private function setTrees()
    {
        if(empty($this->generator->types[CustomsInterface::CUSTOM_TYPES_TREES][RamlInterface::RAML_PROPS]) === false) {
            foreach($this->generator->types[CustomsInterface::CUSTOM_TYPES_TREES][RamlInterface::RAML_PROPS] as $propKey => $propVal) {
                if(is_array($propVal) && empty($this->generator->types[ucfirst($propKey)]) === false) {
                    // ensure that there is a type of propKey ex.: Menu with parent_id field set
                    $this->openTrees();
                    $this->setParamDefault($propKey, $propVal[RamlInterface::RAML_KEY_DEFAULT]);
                    $this->closeEntity();
                }
            }
        }
    }
}