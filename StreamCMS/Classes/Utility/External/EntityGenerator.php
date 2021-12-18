<?php

declare(strict_types=1);

namespace StreamCMS\Utility\External;

use Carbon\CarbonImmutable;
use Doctrine\Common\Cache\Version;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

class EntityGenerator
{
    /**
     * Specifies class fields should be protected.
     */
    const FIELD_VISIBLE_PROTECTED = 'protected';
    /**
     * Specifies class fields should be private.
     */
    const FIELD_VISIBLE_PRIVATE = 'private';
    public $tokenizedFile = false;
    public $path = '';
    public $ast = false;
    public $lexer = null;
    /**
     * @var bool
     */
    protected $backupExisting = true;
    /**
     * The extension to use for written php files.
     *
     * @var string
     */
    protected $extension = '.php';
    /**
     * Whether or not the current ClassMetadataInfo instance is new or old.
     *
     * @var boolean
     */
    protected $isNew = true;
    /**
     * @var array
     */
    protected $staticReflection = [];
    /**
     * Number of spaces to use for indention in generated code.
     */
    protected $numSpaces = 4;
    /**
     * The actual spaces to use for indention.
     *
     * @var string
     */
    protected $spaces = '';
    /**
     * The class all generated entities should extend.
     *
     * @var string
     */
    protected $classToExtend;
    /**
     * Whether or not to generation annotations.
     *
     * @var boolean
     */
    protected $generateAnnotations = false;
    /**
     * @var string
     */
    protected $annotationsPrefix = '';
    /**
     * Whether or not to generate sub methods.
     *
     * @var boolean
     */
    protected $generateEntityStubMethods = false;
    /**
     * Whether or not to update the entity class if it exists already.
     *
     * @var boolean
     */
    protected $updateEntityIfExists = false;
    /**
     * Whether or not to re-generate entity class if it exists already.
     *
     * @var boolean
     */
    protected $regenerateEntityIfExists = false;
    /**
     * Visibility of the field
     *
     * @var string
     */
    protected $fieldVisibility = 'private';
    /**
     * Whether or not to make generated embeddables immutable.
     *
     * @var boolean.
     */
    protected $embeddablesImmutable = false;
    protected $imports = [];
    /**
     * Hash-map for handle types.
     *
     * @var array
     */
    protected $typeAlias = [
        Type::DATETIMETZ => '\DateTime',
        Type::DATETIME => 'CarbonImmutable',
        Type::DATE => 'CarbonImmutable',
        Type::TIME => 'CarbonImmutable',
        Type::OBJECT => '\stdClass',
        Type::INTEGER => 'int',
        Type::BIGINT => 'string',
        Type::SMALLINT => 'int',
        Type::TEXT => 'string',
        Type::BLOB => 'string',
        Type::DECIMAL => 'Decimal',
        Type::GUID => 'string',
        Type::JSON_ARRAY => 'array',
        Type::JSON => 'array',
        Type::SIMPLE_ARRAY => 'array',
        Type::BOOLEAN => 'bool',
        'encrypted_field' => 'string',
        'datetime_microseconds' => 'CarbonImmutable',
    ];
    private $commentCode = '//WINDY GENERATED CODE BELOW! INSERT CUSTOM CODE ABOVE THIS TAG//';
    /**
     * Hash-map to handle generator types string.
     *
     * @var array
     */
    protected static $generatorStrategyMap = [
        ClassMetadataInfo::GENERATOR_TYPE_AUTO => 'AUTO',
        ClassMetadataInfo::GENERATOR_TYPE_SEQUENCE => 'SEQUENCE',
        ClassMetadataInfo::GENERATOR_TYPE_TABLE => 'TABLE',
        ClassMetadataInfo::GENERATOR_TYPE_IDENTITY => 'IDENTITY',
        ClassMetadataInfo::GENERATOR_TYPE_NONE => 'NONE',
        ClassMetadataInfo::GENERATOR_TYPE_UUID => 'UUID',
        ClassMetadataInfo::GENERATOR_TYPE_CUSTOM => 'CUSTOM',
    ];
    /**
     * Hash-map to handle the change tracking policy string.
     *
     * @var array
     */
    protected static $changeTrackingPolicyMap = [
        ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT => 'DEFERRED_IMPLICIT',
        ClassMetadataInfo::CHANGETRACKING_DEFERRED_EXPLICIT => 'DEFERRED_EXPLICIT',
        ClassMetadataInfo::CHANGETRACKING_NOTIFY => 'NOTIFY',
    ];
    /**
     * Hash-map to handle the inheritance type string.
     *
     * @var array
     */
    protected static $inheritanceTypeMap = [
        ClassMetadataInfo::INHERITANCE_TYPE_NONE => 'NONE',
        ClassMetadataInfo::INHERITANCE_TYPE_JOINED => 'JOINED',
        ClassMetadataInfo::INHERITANCE_TYPE_SINGLE_TABLE => 'SINGLE_TABLE',
        ClassMetadataInfo::INHERITANCE_TYPE_TABLE_PER_CLASS => 'TABLE_PER_CLASS',
    ];
    /**
     * @var string
     */
    protected static $classTemplate = '<?php

<namespace>
<useStatement>
<entityAnnotation>
<entityClassName>
{
<entityBody>
}
';
    /**
     * @var string
     */
    protected static $getMethodTemplate = 'public function <methodName>(): <variableType>
{
<spaces><commentGen>
<spaces>return $this-><fieldName>;
}';
    protected static $createdAtTemplate = 'public function setCreatedAt(): void
{
<spaces><commentGen>
<spaces>$this->createdAt = $this->createdAt ?? CarbonImmutable::now();
}
';
    /**
     * @var string
     */
    protected static $setMethodTemplate = 'public function <methodName>(<variableType>$<variableName><variableDefault>): void
{
<spaces><commentGen>
<emptyToNull>
<spaces>$this-><fieldName> = $<variableName>;
}';
    /**
     * @var string
     */
    protected static $addMethodTemplate = 'public function <methodName>(<variableType>$<variableName>): bool
{
<spaces><commentGen>
<spaces>if ($this-><fieldName>->contains($<variableName>)) {
            return false;
        }
<spaces>$this-><fieldName>[] = $<variableName>;
<spaces>$<variableName>-><addmethodname>($this);
<spaces>return true;
}';
    /**
     * @var string
     */
    protected static $removeMethodTemplate = 'public function <methodName>(<variableType>$<variableName>): bool
{
<spaces><commentGen>
<spaces>if (! $this-><fieldName>->contains($<variableName>)) {
            return false;
        }

<spaces>$this-><fieldName>->removeElement($<variableName>);
<spaces>$<variableName>-><removemethodname>(<removeclass>);
<spaces>return true;
}';
    /**
     * @var string
     */
    protected static $lifecycleCallbackMethodTemplate = '/**
 * @<name>
 */
public function <methodName>()
{
<spaces>// Add your code here
}';
    /**
     * @var string
     */
    protected static $constructorMethodTemplate = 'public function __construct()
{
<spaces><collections>
}
';
    /**
     * @var string
     */
    protected static $embeddableConstructorMethodTemplate = '/**
 * Constructor
 *
 * <paramTags>
 */
public function __construct(<params>)
{
<spaces><fields>
}
';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->annotationsPrefix = 'ORM\\';
    }

    /**
     * Generates and writes entity classes for the given array of ClassMetadataInfo instances.
     *
     * @param array $metadatas
     * @param string $outputDirectory
     * @return void
     */
    public function generate(array $metadatas, $outputDirectory)
    {
        foreach ($metadatas as $metadata) {
            $this->writeEntityClass($metadata, $outputDirectory);
        }
    }

    /**
     * Generates and writes entity class to disk for the given ClassMetadataInfo instance.
     *
     * @param ClassMetadataInfo $metadata
     * @param string $outputDirectory
     * @return void
     * @throws \RuntimeException
     */
    public function writeEntityClass(ClassMetadataInfo $metadata, $outputDirectory)
    {
        $path = $outputDirectory . '/' . str_replace('\\', DIRECTORY_SEPARATOR, $metadata->name) . $this->extension;
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $this->isNew = ! file_exists($path) || $this->regenerateEntityIfExists;
        if (! $this->isNew) {
            $this->parseTokensInEntityFile(file_get_contents($path));
        } else {
            $this->staticReflection[$metadata->name] = [
                'properties' => [],
                'methods' => [],
            ];
        }
        if ($this->backupExisting && file_exists($path)) {
            $backupPath = dirname($path) . DIRECTORY_SEPARATOR . basename($path) . "~";
            if (! copy($path, $backupPath)) {
                throw new \RuntimeException("Attempt to backup overwritten entity file but copy operation failed.");
            }
        }
        // If entity doesn't exist or we're re-generating the entities entirely
        if ($this->isNew) {
            file_put_contents($path, $this->generateEntityClass($metadata));
            // If entity exists and we're allowed to update the entity class
        } elseif ($this->updateEntityIfExists) {
            file_put_contents($path, $this->generateUpdatedEntityClass($metadata, $path));
        }
        chmod($path, 0664);
    }

    /**
     * Generates a PHP5 Doctrine 2 entity class from the given ClassMetadataInfo instance.
     *
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    public function generateEntityClass(ClassMetadataInfo $metadata)
    {
        $this->tokenizedFile = false;
        $this->ast = false;
        $this->imports = [];
        $placeHolders = [
            '<namespace>',
            '<entityAnnotation>',
            '<entityClassName>',
            '<entityBody>',
            '<useStatement>',
        ];
        $replacements = [
            $this->generateEntityNamespace($metadata),
            $this->generateEntityDocBlock($metadata),
            $this->generateEntityClassName($metadata),
            $this->generateEntityBody($metadata),
            $this->generateEntityUse(),
        ];
        $code = str_replace($placeHolders, $replacements, static::$classTemplate);
        return str_replace('<spaces>', $this->spaces, $code);
    }

    /**
     * Generates the updated code for the given ClassMetadataInfo and entity at path.
     *
     * @param ClassMetadataInfo $metadata
     * @param string $path
     * @return string
     */
    public function generateUpdatedEntityClass(ClassMetadataInfo $metadata, $path)
    {
        $this->lexer = new Lexer(
            [
                'usedAttributes' => [
                    'comments',
                    'startLine',
                    'endLine',
                    'startTokenPos',
                    'endTokenPos',
                ],
            ]
        );
        $this->tokenizedFile = token_get_all(file_get_contents($path));
        $this->path = $path;
        $body = $this->generateEntityBody($metadata);
        $currentCode = file_get_contents($path);
        $body = str_replace('<spaces>', $this->spaces, $body);
        $last = strrpos($currentCode, '}');
        return substr($currentCode, 0, $last) . $body . ($body ? "\n" : '') . "}\n";
    }

    /**
     * Sets the number of spaces the exported class should have.
     *
     * @param integer $numSpaces
     * @return void
     */
    public function setNumSpaces($numSpaces)
    {
        $this->spaces = str_repeat(' ', $numSpaces);
        $this->numSpaces = $numSpaces;
    }

    /**
     * Sets the extension to use when writing php files to disk.
     *
     * @param string $extension
     * @return void
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * Sets whether or not to generate annotations for the entity.
     *
     * @param bool $bool
     * @return void
     */
    public function setGenerateAnnotations($bool)
    {
        $this->generateAnnotations = $bool;
    }

    /**
     * Sets the class fields visibility for the entity (can either be private or protected).
     *
     * @param bool $visibility
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setFieldVisibility($visibility)
    {
        if ($visibility !== static::FIELD_VISIBLE_PRIVATE && $visibility !== static::FIELD_VISIBLE_PROTECTED) {
            throw new \InvalidArgumentException(
                'Invalid provided visibility (only private and protected are allowed): ' . $visibility
            );
        }
        $this->fieldVisibility = $visibility;
    }

    /**
     * Sets whether or not to generate immutable embeddables.
     *
     * @param boolean $embeddablesImmutable
     */
    public function setEmbeddablesImmutable($embeddablesImmutable)
    {
        $this->embeddablesImmutable = (boolean) $embeddablesImmutable;
    }

    /**
     * Sets an annotation prefix.
     *
     * @param string $prefix
     * @return void
     */
    public function setAnnotationPrefix($prefix)
    {
        $this->annotationsPrefix = $prefix;
    }

    /**
     * Sets whether or not to try and update the entity if it already exists.
     *
     * @param bool $bool
     * @return void
     */
    public function setUpdateEntityIfExists($bool)
    {
        $this->updateEntityIfExists = $bool;
    }

    /**
     * Sets whether or not to regenerate the entity if it exists.
     *
     * @param bool $bool
     * @return void
     */
    public function setRegenerateEntityIfExists($bool)
    {
        $this->regenerateEntityIfExists = $bool;
    }

    /**
     * Sets whether or not to generate stub methods for the entity.
     *
     * @param bool $bool
     * @return void
     */
    public function setGenerateStubMethods($bool)
    {
        $this->generateEntityStubMethods = $bool;
    }

    /**
     * Should an existing entity be backed up if it already exists?
     *
     * @param bool $bool
     * @return void
     */
    public function setBackupExisting($bool)
    {
        $this->backupExisting = $bool;
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getType($type)
    {
        if (isset($this->typeAlias[$type])) {
            return $this->typeAlias[$type];
        }
        return $type;
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateEntityNamespace(ClassMetadataInfo $metadata)
    {
        if (! $this->hasNamespace($metadata)) {
            return '';
        }
        return 'namespace ' . $this->getNamespace($metadata) . ';';
    }

    /**
     * @return string
     */
    protected function generateEntityUse()
    {
        if (! $this->generateAnnotations) {
            return '';
        }
        $return = "\n" . 'use Doctrine\ORM\Mapping as ORM;' . "\n";
        $return .= "\n" . 'use Carbon\CarbonImmutable;' . "\n";
        $return .= "\n" . 'use Decimal\Decimal;' . "\n";
        foreach ($this->imports as $import) {
            $return .= 'use ' . $import . ';' . \PHP_EOL;
        }
        return $return;
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateEntityClassName(ClassMetadataInfo $metadata)
    {
        return 'class ' . $this->getClassName($metadata) . ($this->extendsClass(
            ) ? ' extends ' . $this->getClassToExtendName() : null);
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateEntityBody(ClassMetadataInfo $metadata)
    {
        $fieldMappingProperties = $this->generateEntityFieldMappingProperties($metadata);
        $embeddedProperties = $this->generateEntityEmbeddedProperties($metadata);
        $associationMappingProperties = $this->generateEntityAssociationMappingProperties($metadata);
        if ($this->path !== '') {
            $this->ast = (new ParserFactory())->create(ParserFactory::ONLY_PHP7, $this->lexer)->parse(
                file_get_contents($this->path)
            );
        }
        $stubMethods = $this->generateEntityStubMethods ? $this->generateEntityStubMethods($metadata) : null;
        $lifecycleCallbackMethods = $this->generateEntityLifecycleCallbackMethods($metadata);
        $code = [];
        if ($fieldMappingProperties) {
            $code[] = $fieldMappingProperties;
        }
        if ($embeddedProperties) {
            $code[] = $embeddedProperties;
        }
        if ($associationMappingProperties) {
            $code[] = $associationMappingProperties;
        }
        $code[] = $this->generateEntityConstructor($metadata);
        if ($stubMethods) {
            $code[] = $stubMethods;
        }
        if ($lifecycleCallbackMethods) {
            $code[] = $lifecycleCallbackMethods;
        }
        return implode("\n", $code);
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateEntityConstructor(ClassMetadataInfo $metadata)
    {
        if ($this->hasMethod('__construct', $metadata)) {
            return '';
        }
        if ($metadata->isEmbeddedClass && $this->embeddablesImmutable) {
            return $this->generateEmbeddableConstructor($metadata);
        }
        $collections = [];
        foreach ($metadata->associationMappings as $mapping) {
            if ($mapping['type'] & ClassMetadataInfo::TO_MANY) {
                $collections[] = '$this->' . $mapping['fieldName'] . ' = new ArrayCollection();';
                $this->imports['Doctrine\Common\Collections\ArrayCollection'] = 'Doctrine\Common\Collections\ArrayCollection';
            }
        }
        if ($collections) {
            return $this->prefixCodeWithSpaces(
                str_replace(
                    "<collections>",
                    implode("\n" . $this->spaces, $collections),
                    static::$constructorMethodTemplate
                )
            );
        }
        return '';
    }

    /**
     * @param string $src
     * @return void
     */
    protected function parseTokensInEntityFile($src)
    {
        $tokens = token_get_all($src);
        $tokensCount = count($tokens);
        $lastSeenNamespace = '';
        $lastSeenClass = false;
        $inNamespace = false;
        $inClass = false;
        for ($i = 0; $i < $tokensCount; $i++) {
            $token = $tokens[$i];
            if (in_array(
                $token[0],
                [
                    T_WHITESPACE,
                    T_COMMENT,
                    T_DOC_COMMENT,
                ],
                true
            )) {
                continue;
            }
            if ($inNamespace) {
                if (in_array(
                    $token[0],
                    [
                        T_NS_SEPARATOR,
                        T_STRING,
                    ],
                    true
                )) {
                    $lastSeenNamespace .= $token[1];
                } elseif (is_string($token) && in_array(
                        $token,
                        [
                            ';',
                            '{',
                        ],
                        true
                    )) {
                    $inNamespace = false;
                }
            }
            if ($inClass) {
                $inClass = false;
                $lastSeenClass = $lastSeenNamespace . ($lastSeenNamespace ? '\\' : '') . $token[1];
                $this->staticReflection[$lastSeenClass]['properties'] = [];
                $this->staticReflection[$lastSeenClass]['methods'] = [];
            }
            if (T_NAMESPACE === $token[0]) {
                $lastSeenNamespace = '';
                $inNamespace = true;
            } elseif (T_CLASS === $token[0] && T_DOUBLE_COLON !== $tokens[$i - 1][0]) {
                $inClass = true;
            } elseif (T_FUNCTION === $token[0]) {
                if (T_STRING === $tokens[$i + 2][0]) {
                    $this->staticReflection[$lastSeenClass]['methods'][] = strtolower($tokens[$i + 2][1]);
                } elseif ($tokens[$i + 2] == '&' && T_STRING === $tokens[$i + 3][0]) {
                    $this->staticReflection[$lastSeenClass]['methods'][] = strtolower($tokens[$i + 3][1]);
                }
            } elseif (in_array(
                    $token[0],
                    [
                        T_VAR,
                        T_PUBLIC,
                        T_PRIVATE,
                        T_PROTECTED,
                    ],
                    true
                ) && T_FUNCTION !== $tokens[$i + 2][0]) {
                $this->staticReflection[$lastSeenClass]['properties'][] = substr($tokens[$i + 2][1] ?? '', 1);
            }
        }
    }

    /**
     * @param string $property
     * @param ClassMetadataInfo $metadata
     * @return bool
     */
    protected function hasProperty($property, ClassMetadataInfo $metadata)
    {
        if ($this->extendsClass() || (! $this->isNew && class_exists($metadata->name))) {
            // don't generate property if its already on the base class.
            $reflClass = new \ReflectionClass($this->getClassToExtend() ?: $metadata->name);
            if ($reflClass->hasProperty($property)) {
                return true;
            }
        }
        // check traits for existing property
        foreach ($this->getTraits($metadata) as $trait) {
            if ($trait->hasProperty($property)) {
                return true;
            }
        }
        return (isset($this->staticReflection[$metadata->name]) && in_array(
                $property,
                $this->staticReflection[$metadata->name]['properties'],
                true
            ));
    }

    /**
     * @param string $method
     * @param ClassMetadataInfo $metadata
     * @return bool
     */
    protected function hasMethod($method, ClassMetadataInfo $metadata)
    {
        if ($this->extendsClass() || (! $this->isNew && class_exists($metadata->name))) {
            // don't generate method if its already on the base class.
            $reflClass = new \ReflectionClass($this->getClassToExtend() ?: $metadata->name);
            if ($reflClass->hasMethod($method)) {
                return true;
            }
        }
        // check traits for existing method
        foreach ($this->getTraits($metadata) as $trait) {
            if ($trait->hasMethod($method)) {
                return true;
            }
        }
        return (isset($this->staticReflection[$metadata->name]) && in_array(
                strtolower($method),
                $this->staticReflection[$metadata->name]['methods'],
                true
            ));
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return array
     * @throws \ReflectionException
     */
    protected function getTraits(ClassMetadataInfo $metadata)
    {
        if (! ($metadata->reflClass !== null || class_exists($metadata->name))) {
            return [];
        }
        $reflClass = $metadata->reflClass ?? new \ReflectionClass($metadata->name);
        $traits = [];
        while ($reflClass !== false) {
            $traits = array_merge($traits, $reflClass->getTraits());
            $reflClass = $reflClass->getParentClass();
        }
        return $traits;
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return bool
     */
    protected function hasNamespace(ClassMetadataInfo $metadata)
    {
        return (bool) strpos($metadata->name, '\\');
    }

    /**
     * @return bool
     */
    protected function extendsClass()
    {
        return (bool) $this->classToExtend;
    }

    /**
     * @return string
     */
    protected function getClassToExtend()
    {
        return $this->classToExtend;
    }

    /**
     * Sets the name of the class the generated classes should extend from.
     *
     * @param string $classToExtend
     * @return void
     */
    public function setClassToExtend($classToExtend)
    {
        $this->classToExtend = $classToExtend;
    }

    /**
     * @return string
     */
    protected function getClassToExtendName()
    {
        $refl = new \ReflectionClass($this->getClassToExtend());
        return '\\' . $refl->getName();
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function getClassName(ClassMetadataInfo $metadata)
    {
        return ($pos = strrpos($metadata->name, '\\')) ? substr(
            $metadata->name,
            $pos + 1,
            strlen($metadata->name)
        ) : $metadata->name;
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function getNamespace(ClassMetadataInfo $metadata)
    {
        return substr($metadata->name, 0, strrpos($metadata->name, '\\'));
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateEntityDocBlock(ClassMetadataInfo $metadata)
    {
        $lines = [];
        $lines[] = '/**';
        $lines[] = ' * ' . $this->getClassName($metadata);
        if ($this->generateAnnotations) {
            $lines[] = ' *';
            $methods = [
                'generateTableAnnotation',
                'generateInheritanceAnnotation',
                'generateDiscriminatorColumnAnnotation',
                'generateDiscriminatorMapAnnotation',
                'generateEntityAnnotation',
                'generateEntityListenerAnnotation',
            ];
            foreach ($methods as $method) {
                if ($code = $this->$method($metadata)) {
                    $lines[] = ' * ' . $code;
                }
            }
            if (isset($metadata->lifecycleCallbacks) && $metadata->lifecycleCallbacks) {
                $lines[] = ' * @' . $this->annotationsPrefix . 'HasLifecycleCallbacks';
            }
        }
        $lines[] = ' */';
        return implode("\n", $lines);
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateEntityAnnotation(ClassMetadataInfo $metadata)
    {
        $prefix = '@' . $this->annotationsPrefix;
        if ($metadata->isEmbeddedClass) {
            return $prefix . 'Embeddable';
        }
        $customRepository = $metadata->customRepositoryClassName ? '(repositoryClass="' . $metadata->customRepositoryClassName . '")' : '';
        return $prefix . ($metadata->isMappedSuperclass ? 'MappedSuperclass' : 'Entity') . $customRepository;
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateTableAnnotation(ClassMetadataInfo $metadata)
    {
        if ($metadata->isEmbeddedClass) {
            return '';
        }
        $table = [];
        if (isset($metadata->table['schema'])) {
            $table[] = 'schema="' . $metadata->table['schema'] . '"';
        }
        if (isset($metadata->table['name'])) {
            $table[] = 'name="' . $metadata->table['name'] . '"';
        }
        if (isset($metadata->table['options']) && $metadata->table['options']) {
            $table[] = 'options={' . $this->exportTableOptions((array) $metadata->table['options']) . '}';
        }
        if (isset($metadata->table['uniqueConstraints']) && $metadata->table['uniqueConstraints']) {
            $constraints = $this->generateTableConstraints('UniqueConstraint', $metadata->table['uniqueConstraints']);
            $table[] = 'uniqueConstraints={' . $constraints . '}';
        }
        if (isset($metadata->table['indexes']) && $metadata->table['indexes']) {
            $constraints = $this->generateTableConstraints('Index', $metadata->table['indexes']);
            $table[] = 'indexes={' . $constraints . '}';
        }
        return '@' . $this->annotationsPrefix . 'Table(' . implode(', ', $table) . ')';
    }

    /**
     * @param string $constraintName
     * @param array $constraints
     * @return string
     */
    protected function generateTableConstraints($constraintName, array $constraints)
    {
        $annotations = [];
        foreach ($constraints as $name => $constraint) {
            $columns = [];
            foreach ($constraint['columns'] as $column) {
                $columns[] = '"' . $column . '"';
            }
            $annotations[] = '@' . $this->annotationsPrefix . $constraintName . '(name="' . $name . '", columns={' . implode(
                    ', ',
                    $columns
                ) . '})';
        }
        return implode(', ', $annotations);
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateInheritanceAnnotation(ClassMetadataInfo $metadata)
    {
        if ($metadata->inheritanceType === ClassMetadataInfo::INHERITANCE_TYPE_NONE) {
            return '';
        }
        return '@' . $this->annotationsPrefix . 'InheritanceType("' . $this->getInheritanceTypeString(
                $metadata->inheritanceType
            ) . '")';
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateDiscriminatorColumnAnnotation(ClassMetadataInfo $metadata)
    {
        if ($metadata->inheritanceType === ClassMetadataInfo::INHERITANCE_TYPE_NONE) {
            return '';
        }
        $discrColumn = $metadata->discriminatorColumn;
        $columnDefinition = 'name="' . $discrColumn['name'] . '", type="' . $discrColumn['type'] . '", length=' . $discrColumn['length'];
        return '@' . $this->annotationsPrefix . 'DiscriminatorColumn(' . $columnDefinition . ')';
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateDiscriminatorMapAnnotation(ClassMetadataInfo $metadata)
    {
        if ($metadata->inheritanceType === ClassMetadataInfo::INHERITANCE_TYPE_NONE) {
            return null;
        }
        $inheritanceClassMap = [];
        foreach ($metadata->discriminatorMap as $type => $class) {
            $inheritanceClassMap[] .= '"' . $type . '" = "' . $class . '"';
        }
        return '@' . $this->annotationsPrefix . 'DiscriminatorMap({' . implode(', ', $inheritanceClassMap) . '})';
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateEntityStubMethods(ClassMetadataInfo $metadata)
    {
        $methods = [];
        foreach ($metadata->fieldMappings as $fieldMapping) {
            if (isset($fieldMapping['declaredField'], $metadata->embeddedClasses[$fieldMapping['declaredField']])) {
                continue;
            }
            $nullableField = $this->nullableFieldExpression($fieldMapping);
            if ((! $metadata->isEmbeddedClass || ! $this->embeddablesImmutable) && (! isset($fieldMapping['id']) || ! $fieldMapping['id'] || $metadata->generatorType === ClassMetadataInfo::GENERATOR_TYPE_NONE) && $code = $this->generateEntityStubMethod(
                    $metadata,
                    'set',
                    $fieldMapping['fieldName'],
                    $fieldMapping['type'],
                    $nullableField
                )) {
                $methods[] = $code;
            }
            if ($code = $this->generateEntityStubMethod(
                $metadata,
                'get',
                $fieldMapping['fieldName'],
                $fieldMapping['type'],
                $nullableField
            )) {
                $methods[] = $code;
            }
        }
        foreach ($metadata->embeddedClasses as $fieldName => $embeddedClass) {
            if (isset($embeddedClass['declaredField'])) {
                continue;
            }
            if (! $metadata->isEmbeddedClass || ! $this->embeddablesImmutable) {
                if ($code = $this->generateEntityStubMethod($metadata, 'set', $fieldName, $embeddedClass['class'])) {
                    $methods[] = $code;
                }
            }
            if ($code = $this->generateEntityStubMethod($metadata, 'get', $fieldName, $embeddedClass['class'])) {
                $methods[] = $code;
            }
        }
        foreach ($metadata->associationMappings as $associationMapping) {
            if ($associationMapping['type'] & ClassMetadataInfo::TO_ONE) {
                $nullable = $this->isAssociationIsNullable($associationMapping) ? 'null' : null;
                if ($code = $this->generateEntityStubMethod(
                    $metadata,
                    'set',
                    $associationMapping['fieldName'],
                    $associationMapping['targetEntity'],
                    $nullable
                )) {
                    $methods[] = $code;
                }
                if ($code = $this->generateEntityStubMethod(
                    $metadata,
                    'get',
                    $associationMapping['fieldName'],
                    $associationMapping['targetEntity'],
                    $nullable
                )) {
                    $methods[] = $code;
                }
            } elseif ($associationMapping['type'] & ClassMetadataInfo::TO_MANY) {
                if ($code = $this->generateEntityStubMethod(
                    $metadata,
                    'add',
                    $associationMapping['fieldName'],
                    $associationMapping['targetEntity'],
                    null,
                    $associationMapping
                )) {
                    $methods[] = $code;
                }
                if ($code = $this->generateEntityStubMethod(
                    $metadata,
                    'remove',
                    $associationMapping['fieldName'],
                    $associationMapping['targetEntity'],
                    null,
                    $associationMapping
                )) {
                    $methods[] = $code;
                }
                if ($code = $this->generateEntityStubMethod(
                    $metadata,
                    'get',
                    $associationMapping['fieldName'],
                    Collection::class
                )) {
                    $methods[] = $code;
                }
            }
        }
        return implode("\n\n", $methods);
    }

    /**
     * @param array $associationMapping
     * @return bool
     */
    protected function isAssociationIsNullable(array $associationMapping)
    {
        if (isset($associationMapping['id']) && $associationMapping['id']) {
            return false;
        }
        if (isset($associationMapping['joinColumns'])) {
            $joinColumns = $associationMapping['joinColumns'];
        } else {
            $joinColumns = [];
        }
        foreach ($joinColumns as $joinColumn) {
            if (isset($joinColumn['nullable']) && ! $joinColumn['nullable']) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateEntityLifecycleCallbackMethods(ClassMetadataInfo $metadata)
    {
        if (empty($metadata->lifecycleCallbacks)) {
            return '';
        }
        $methods = [];
        foreach ($metadata->lifecycleCallbacks as $name => $callbacks) {
            foreach ($callbacks as $callback) {
                $methods[] = $this->generateLifecycleCallbackMethod($name, $callback, $metadata);
            }
        }
        return implode("\n\n", array_filter($methods));
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateEntityAssociationMappingProperties(ClassMetadataInfo $metadata)
    {
        $lines = [];
        if ($this->tokenizedFile !== false) {
            $tokens = &$this->tokenizedFile;
            $tokensCount = count($tokens);
        }
        $fieldChanged = false;
        foreach ($metadata->associationMappings as $associationMapping) {
            if (isset($associationMapping['declaredField'], $metadata->embeddedClasses[$associationMapping['declaredField']]) || $metadata->isInheritedField(
                    $associationMapping['fieldName']
                )) {
                continue;
            }
            if ($this->hasProperty($associationMapping['fieldName'], $metadata)) {
                if (isset($this->staticReflection[$metadata->name]) && in_array(
                        $associationMapping['fieldName'],
                        $this->staticReflection[$metadata->name]['properties'],
                        true
                    )) {
                    $setFields = [];
                    for ($i = 0; $i < $tokensCount; $i++) {
                        if (is_array($tokens[$i])) {
                            if ($tokens[$i][0] === T_DOC_COMMENT) {
                                $lastComment = &$tokens[$i][1];
                            } elseif ($tokens[$i][0] === T_VARIABLE) {
                                if ($associationMapping['fieldName'] == substr(
                                        $tokens[$i][1],
                                        1
                                    ) && ! isset($setFields[substr($tokens[$i][1], 1)])) {
                                    $fieldChanged = true;
                                    $setFields[substr($tokens[$i][1], 1)] = true;
                                    //                                    dump($tokens[$i]);
                                    //                                    echo '============= BEFORE ===============' . \PHP_EOL;
                                    //                                    dump($lastComment);
                                    $lastComment = $this->generateAssociationMappingPropertyDocBlock(
                                        $associationMapping,
                                        $metadata
                                    );
                                    //                                    echo '============= AFTER ===============' . \PHP_EOL;
                                    //                                    dump($lastComment);
                                    //                                    echo '=======================================' . \PHP_EOL;
                                    //                                    echo '=======================================' . \PHP_EOL;
                                }
                                unset($lastComment);
                            }
                        }
                    }
                }
                continue;
            }
            $lines[] = $this->generateAssociationMappingPropertyDocBlock($associationMapping, $metadata);
            //SKIPPER BROKEN
            //$lines[] = $this->spaces . $this->fieldVisibility .' \Doctrine\Common\Collections\Collection'. ' $' . $associationMapping['fieldName'] . ($associationMapping['type'] == 'manyToMany' ? ' = array()' : null) . ";\n";
            $lines[] = $this->spaces . $this->fieldVisibility . ' $' . $associationMapping['fieldName'] . ($associationMapping['type'] == 'manyToMany' ? ' = array()' : null) . ";\n";
        }
        if ($fieldChanged !== false) {
            $newStr = '';
            for ($i = 0; $i < $tokensCount; $i++) {
                if (is_array($tokens[$i])) {
                    $newStr .= $tokens[$i][1];
                } else {
                    $newStr .= $tokens[$i];
                }
            }
            file_put_contents($this->path, $newStr);
        }
        return implode("\n", $lines);
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateEntityFieldMappingProperties(ClassMetadataInfo $metadata)
    {
        $lines = [];
        if ($this->tokenizedFile !== false) {
            $tokens = &$this->tokenizedFile;
            $tokensCount = count($tokens);
        }
        $fieldChanged = false;
        foreach ($metadata->fieldMappings as $fieldMapping) {
            if (isset($fieldMapping['declaredField'], $metadata->embeddedClasses[$fieldMapping['declaredField']]) || $metadata->isInheritedField(
                    $fieldMapping['fieldName']
                )) {
                continue;
            }
            if ($this->hasProperty($fieldMapping['fieldName'], $metadata)) {
                if (isset($this->staticReflection[$metadata->name]) && in_array(
                        $fieldMapping['fieldName'],
                        $this->staticReflection[$metadata->name]['properties'],
                        true
                    )) {
                    $setFields = [];
                    for ($i = 0; $i < $tokensCount; $i++) {
                        if (is_array($tokens[$i])) {
                            if ($tokens[$i][0] === T_DOC_COMMENT) {
                                $lastComment = &$tokens[$i][1];
                            } elseif ($tokens[$i][0] === T_VARIABLE) {
                                if ($fieldMapping['fieldName'] == substr(
                                        $tokens[$i][1],
                                        1
                                    ) && ! isset($setFields[substr($tokens[$i][1], 1)])) {
                                    $fieldChanged = true;
                                    $setFields[substr($tokens[$i][1], 1)] = true;
                                    //                                    dump($tokens[$i]);
                                    //                                    echo '============= BEFORE ===============' . \PHP_EOL;
                                    //                                    dump($lastComment);
                                    $lastComment = $this->generateFieldMappingPropertyDocBlock(
                                        $fieldMapping,
                                        $metadata
                                    );
                                    //                                    echo '============= AFTER ===============' . \PHP_EOL;
                                    //                                    dump($lastComment);
                                    //                                    echo '=======================================' . \PHP_EOL;
                                    //                                    echo '=======================================' . \PHP_EOL;
                                }
                                unset($lastComment);
                            }
                        }
                    }
                }
                continue;
            }
            $lines[] = $this->generateFieldMappingPropertyDocBlock($fieldMapping, $metadata);
            //$lines[] = $this->spaces . $this->fieldVisibility . ($this->nullableFieldExpression($fieldMapping) ? ' ?' : ' ') . $this->getType($fieldMapping['type'])  . ' $' . $fieldMapping['fieldName'] . ";\n";
            $lines[] = $this->spaces . $this->fieldVisibility . ' $' . $fieldMapping['fieldName'] . ";\n";
        }
        if ($fieldChanged !== false) {
            $newStr = '';
            for ($i = 0; $i < $tokensCount; $i++) {
                if (is_array($tokens[$i])) {
                    $newStr .= $tokens[$i][1];
                } else {
                    $newStr .= $tokens[$i];
                }
            }
            file_put_contents($this->path, $newStr);
        }
        return implode("\n", $lines);
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateEntityEmbeddedProperties(ClassMetadataInfo $metadata)
    {
        $lines = [];
        foreach ($metadata->embeddedClasses as $fieldName => $embeddedClass) {
            if (isset($embeddedClass['declaredField']) || $this->hasProperty($fieldName, $metadata)) {
                continue;
            }
            $lines[] = $this->generateEmbeddedPropertyDocBlock($embeddedClass);
            $lines[] = $this->spaces . $this->fieldVisibility . ' $' . $fieldName . ";\n";
        }
        return implode("\n", $lines);
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @param string $type
     * @param string $fieldName
     * @param string|null $typeHint
     * @param string|null $defaultValue
     * @return string
     */
    protected function generateEntityStubMethod(ClassMetadataInfo $metadata, $type, $fieldName, $typeHint = null, $defaultValue = null, $associationMapping = null, $checkExists = true)
    {
        if ($fieldName === 'metadata') {
            return '';
        }
        $methodName = $type . Inflector::classify($fieldName);
        //Overrride for Bool Methods
        $variableType = $typeHint ? $this->getType($typeHint) : null;
        if ($variableType === 'bool' && $type === 'get') {
            $methodName = 'is' . Inflector::classify($fieldName);
        }
        $variableName = Inflector::camelize($fieldName);
        if (in_array(
            $type,
            [
                "add",
                "remove",
            ]
        )) {
            $methodName = Inflector::singularize($methodName);
            $variableName = Inflector::singularize($variableName);
        }
        if ($this->hasMethod($methodName, $metadata) && $checkExists === true) {
            if ($this->ast !== false) {
                $calculatedPHP = $this->generateEntityStubMethod(
                    $metadata,
                    $type,
                    $fieldName,
                    $typeHint,
                    $defaultValue,
                    $associationMapping,
                    false
                );
                $calculatedPHP = str_replace('<spaces>', $this->spaces, $calculatedPHP);
                $traverser = new NodeTraverser();
                $tokens = $this->lexer->getTokens();
                $nameResolver = new NameResolver(
                    null, [
                        'preserveOriginalNames' => false,
                        'replaceNodes' => false,
                    ]
                );
                $traverser->addVisitor($nameResolver);
                $traverser->addVisitor(
                    new class extends NodeVisitorAbstract {
                        private $stack;

                        public function beforeTraverse(array $nodes)
                        {
                            $this->stack = [];
                        }

                        public function enterNode(Node $node)
                        {
                            if (! empty($this->stack)) {
                                $node->setAttribute('parent', $this->stack[count($this->stack) - 1]);
                            }
                            $this->stack[] = $node;
                        }

                        public function leaveNode(Node $node)
                        {
                            array_pop($this->stack);
                        }
                    }
                );
                $autogenSearch = $this->commentCode;
                $traverser->addVisitor(
                    new class($metadata, $methodName, $calculatedPHP, $tokens, $autogenSearch) extends NodeVisitorAbstract {
                        public $metadata;
                        public $methodName;
                        public $calculatedPHP;
                        private $tokens;
                        private $autogenSearch;

                        public function __construct($metadata = '', $methodName = '', $calculatedPHP, $tokens, $autogenSearch)
                        {
                            $this->metadata = $metadata;
                            $this->methodName = $methodName;
                            $this->calculatedPHP = $calculatedPHP;
                            $this->tokens = $tokens;
                            $this->autogenSearch = $autogenSearch;
                        }

                        public function leaveNode(Node $node)
                        {
                            if ($node instanceof Node\Stmt\ClassMethod && $node->name->name == $this->methodName) {
                                try {
                                    if ($node->getAttribute(
                                            'parent'
                                        ) instanceof Node\Stmt\Class_ && $node->getAttribute(
                                            'parent'
                                        )->namespacedName->toString() === $this->metadata->name) {
                                        $dumper = new NodeDumper();
                                        //var_dump($dumper->dump($node));
                                        $prettyPrinter = new Standard();
                                        $phpInCode = $prettyPrinter->prettyPrint([$node]);
                                        $phpInCodeSTRIPPED = substr(strstr($phpInCode, '{'), 1);
                                        $phpInCodeSTRIPPED = strstr($phpInCodeSTRIPPED, $this->autogenSearch, true);
                                        //var_dump($phpInCodeSTRIPPED);
                                        //Now Replace The Calculated PHP With The Custom Code
                                        $this->calculatedPHP = str_replace(
                                            $this->autogenSearch,
                                            $phpInCodeSTRIPPED . \PHP_EOL . $this->autogenSearch,
                                            $this->calculatedPHP
                                        );
                                        $this->calculatedPHP = '<?php
class TEST
{' . \PHP_EOL . $this->calculatedPHP . \PHP_EOL . '}';
                                        //var_dump($this->calculatedPHP);
                                        $newNode = (new ParserFactory())->create(ParserFactory::ONLY_PHP7)->parse(
                                            $this->calculatedPHP
                                        );
                                        return $newNode[0]->stmts[0] ?? null;
                                    }
                                } catch (\Throwable $exception) {
                                    echo $exception->getTraceAsString();
                                    echo $exception->getMessage();
                                    exit;
                                }
                            }
                        }
                    }
                );
                $this->ast = $traverser->traverse($this->ast);
                $prettyPrinter = new Standard();
                \file_put_contents($this->path, $prettyPrinter->prettyPrintFile($this->ast));
            }
            return '';
        }
        $this->staticReflection[$metadata->name]['methods'][] = strtolower($methodName);
        $var = sprintf('%sMethodTemplate', $type);
        $template = static::$$var;
        $methodTypeHint = null;
        $types = Type::getTypesMap();
        if ($typeHint && ! isset($types[$typeHint])) {
            $variableType = '\\' . ltrim($variableType, '\\');
            $methodTypeHint = '\\' . $typeHint . ' ';
        }
        //If type contains backslashes
        if (stripos($variableType, '\\') !== false) {
            $variableType = ltrim($variableType, '\\');
            $this->imports[$variableType] = $variableType;
            $variableTypeClone = explode('\\', $variableType);
            $variableType = array_pop($variableTypeClone);
        }
        $emptyToNull = '';
        if ($defaultValue !== null && $variableType === 'string') {
            $emptyToNull = 'if ($' . $variableName . ' === \'\') {
$' . $variableName . ' = null;
}
';
        }
        $replacements = [
            '<description>' => ucfirst($type) . ' ' . $variableName . '.',
            '<methodTypeHint>' => $methodTypeHint,
            '<variableType>' => (null !== $defaultValue ? ('?') : '') . $variableType . ' ',
            '<variableName>' => $variableName,
            '<methodName>' => $methodName,
            '<fieldName>' => $fieldName,
            '<variableDefault>' => ($defaultValue !== null) ? (' = ' . $defaultValue) : '',
            '<entity>' => $this->getClassName($metadata),
            '<emptyToNull>' => $emptyToNull,
            '<commentGen>' => $this->commentCode,
        ];
        if ($fieldName === 'createdAt' && $type === 'set') {
            $template = static::$createdAtTemplate;
        }
        if ($fieldName === 'updatedAt' && $type === 'set') {
            return;
        }
        if ($fieldName === 'id' && $metadata->generatorType === ClassMetadataInfo::GENERATOR_TYPE_IDENTITY) {
            $replacements['<variableType>'] = '?' . $variableType;
            $replacements['<commentGen>'] = ' ';
        }
        $replacements['<addOtherSide>'] = '';
        if ($associationMapping !== null) {
            //            if ($associationMapping['isOwningSide'] === false && $associationMapping['type'] !== ClassMetadataInfo::MANY_TO_MANY) {
            //                if (in_array($type, [
            //                    "add",
            //                    "remove",
            //                    "set",
            //                ])) {
            //                    return;
            //                }
            //            }
            $otherProperty = null;
            if (isset($associationMapping['inversedBy'])) {
                $otherProperty = $associationMapping['inversedBy'];
            }
            if (isset($associationMapping['mappedBy'])) {
                $otherProperty = $associationMapping['mappedBy'];
            }
            $replacements['<otherSide>'] = '';
            if ($associationMapping['type'] === ClassMetadataInfo::MANY_TO_MANY) {
                $otherMethodType = 'add';
                $otherMethodTypeDel = 'remove';
                $delPropertyClass = '$this';
            }
            if ($associationMapping['type'] === ClassMetadataInfo::MANY_TO_ONE) {
                $otherMethodType = 'add';
                $otherMethodTypeDel = 'delete';
                $delPropertyClass = '$this';
            }
            if ($associationMapping['type'] === ClassMetadataInfo::ONE_TO_MANY) {
                $otherMethodType = 'set';
                $otherMethodTypeDel = 'set';
                $delPropertyClass = 'null';
                if ($type === 'remove') {
                    return;
                }
            }
            $removemethodname = $otherMethodTypeDel . Inflector::classify($otherProperty);
            $removemethodname = Inflector::singularize($removemethodname);
            $addmethodname = $otherMethodType . Inflector::classify($otherProperty);
            if (substr_compare($addmethodname, 'Chassis', -strlen('Chassis')) === 0) {
                $addmethodname = $addmethodname;
            } else {
                $addmethodname = Inflector::singularize($addmethodname);
            }
            $replacements['<addmethodname>'] = $addmethodname;
            $replacements['<removemethodname>'] = $removemethodname;
            $replacements['<removeclass>'] = $delPropertyClass;
        }
        $method = str_replace(array_keys($replacements), array_values($replacements), $template);
        return $this->prefixCodeWithSpaces($method);
    }

    /**
     * @param string $name
     * @param string $methodName
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateLifecycleCallbackMethod($name, $methodName, ClassMetadataInfo $metadata)
    {
        if ($this->hasMethod($methodName, $metadata)) {
            return '';
        }
        $this->staticReflection[$metadata->name]['methods'][] = $methodName;
        $replacements = [
            '<name>' => $this->annotationsPrefix . ucfirst($name),
            '<methodName>' => $methodName,
        ];
        $method = str_replace(
            array_keys($replacements),
            array_values($replacements),
            static::$lifecycleCallbackMethodTemplate
        );
        return $this->prefixCodeWithSpaces($method);
    }

    /**
     * @param array $joinColumn
     * @return string
     */
    protected function generateJoinColumnAnnotation(array $joinColumn)
    {
        $joinColumnAnnot = [];
        if (isset($joinColumn['name'])) {
            $joinColumnAnnot[] = 'name="' . $joinColumn['name'] . '"';
        }
        if (isset($joinColumn['referencedColumnName'])) {
            $joinColumnAnnot[] = 'referencedColumnName="' . $joinColumn['referencedColumnName'] . '"';
        }
        if (isset($joinColumn['unique']) && $joinColumn['unique']) {
            $joinColumnAnnot[] = 'unique=' . ($joinColumn['unique'] ? 'true' : 'false');
        }
        if (isset($joinColumn['nullable'])) {
            $joinColumnAnnot[] = 'nullable=' . ($joinColumn['nullable'] ? 'true' : 'false');
        }
        if (isset($joinColumn['onDelete'])) {
            $joinColumnAnnot[] = 'onDelete="' . ($joinColumn['onDelete'] . '"');
        }
        if (isset($joinColumn['columnDefinition'])) {
            $joinColumnAnnot[] = 'columnDefinition="' . $joinColumn['columnDefinition'] . '"';
        }
        return '@' . $this->annotationsPrefix . 'JoinColumn(' . implode(', ', $joinColumnAnnot) . ')';
    }

    /**
     * @param array $associationMapping
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateAssociationMappingPropertyDocBlock(array $associationMapping, ClassMetadataInfo $metadata)
    {
        $lines = [];
        $lines[] = $this->spaces . '/**';
        if ($associationMapping['type'] & ClassMetadataInfo::TO_MANY) {
            $lines[] = $this->spaces . ' * @var \Doctrine\Common\Collections\Collection|\\' . ltrim(
                    $associationMapping['targetEntity']
                ) . '[]';
        } else {
            $null = '';
            if (($associationMapping['type'] & ClassMetadataInfo::TO_ONE) && $associationMapping['isOwningSide'] === true && $associationMapping['joinColumns'][0]['nullable'] === true) {
                $null = '|null';
            }
            $lines[] = $this->spaces . ' * @var \\' . ltrim($associationMapping['targetEntity'], '\\') . $null;
        }
        if ($this->generateAnnotations) {
            $lines[] = $this->spaces . ' *';
            if (isset($associationMapping['id']) && $associationMapping['id']) {
                $lines[] = $this->spaces . ' * @' . $this->annotationsPrefix . 'Id';
                if ($generatorType = $this->getIdGeneratorTypeString($metadata->generatorType)) {
                    $lines[] = $this->spaces . ' * @' . $this->annotationsPrefix . 'GeneratedValue(strategy="' . $generatorType . '")';
                }
            }
            $type = null;
            switch ($associationMapping['type']) {
                case ClassMetadataInfo::ONE_TO_ONE:
                    $type = 'OneToOne';
                    break;
                case ClassMetadataInfo::MANY_TO_ONE:
                    $type = 'ManyToOne';
                    break;
                case ClassMetadataInfo::ONE_TO_MANY:
                    $type = 'OneToMany';
                    break;
                case ClassMetadataInfo::MANY_TO_MANY:
                    $type = 'ManyToMany';
                    break;
            }
            $typeOptions = [];
            if (isset($associationMapping['targetEntity'])) {
                $typeOptions[] = 'targetEntity="' . $associationMapping['targetEntity'] . '"';
            }
            if (isset($associationMapping['inversedBy'])) {
                $typeOptions[] = 'inversedBy="' . $associationMapping['inversedBy'] . '"';
            }
            if (isset($associationMapping['mappedBy'])) {
                $typeOptions[] = 'mappedBy="' . $associationMapping['mappedBy'] . '"';
            }
            if ($associationMapping['cascade']) {
                $cascades = [];
                if ($associationMapping['isCascadePersist']) {
                    $cascades[] = '"persist"';
                }
                if ($associationMapping['isCascadeRemove']) {
                    $cascades[] = '"remove"';
                }
                if ($associationMapping['isCascadeDetach']) {
                    $cascades[] = '"detach"';
                }
                if ($associationMapping['isCascadeMerge']) {
                    $cascades[] = '"merge"';
                }
                if ($associationMapping['isCascadeRefresh']) {
                    $cascades[] = '"refresh"';
                }
                if (count($cascades) === 5) {
                    $cascades = ['"all"'];
                }
                $typeOptions[] = 'cascade={' . implode(',', $cascades) . '}';
            }
            if (isset($associationMapping['orphanRemoval']) && $associationMapping['orphanRemoval']) {
                $typeOptions[] = 'orphanRemoval=' . ($associationMapping['orphanRemoval'] ? 'true' : 'false');
            }
            if (isset($associationMapping['fetch']) && $associationMapping['fetch'] !== ClassMetadataInfo::FETCH_LAZY) {
                $fetchMap = [
                    ClassMetadataInfo::FETCH_EXTRA_LAZY => 'EXTRA_LAZY',
                    ClassMetadataInfo::FETCH_EAGER => 'EAGER',
                ];
                $typeOptions[] = 'fetch="' . $fetchMap[$associationMapping['fetch']] . '"';
            }
            $lines[] = $this->spaces . ' * @' . $this->annotationsPrefix . '' . $type . '(' . implode(
                    ', ',
                    $typeOptions
                ) . ')';
            if (isset($associationMapping['joinColumns']) && $associationMapping['joinColumns']) {
                $lines[] = $this->spaces . ' * @' . $this->annotationsPrefix . 'JoinColumns({';
                $joinColumnsLines = [];
                foreach ($associationMapping['joinColumns'] as $joinColumn) {
                    if ($joinColumnAnnot = $this->generateJoinColumnAnnotation($joinColumn)) {
                        $joinColumnsLines[] = $this->spaces . ' *   ' . $joinColumnAnnot;
                    }
                }
                $lines[] = implode(",\n", $joinColumnsLines);
                $lines[] = $this->spaces . ' * })';
            }
            if (isset($associationMapping['joinTable']) && $associationMapping['joinTable']) {
                $joinTable = [];
                $joinTable[] = 'name="' . $associationMapping['joinTable']['name'] . '"';
                if (isset($associationMapping['joinTable']['schema'])) {
                    $joinTable[] = 'schema="' . $associationMapping['joinTable']['schema'] . '"';
                }
                $lines[] = $this->spaces . ' * @' . $this->annotationsPrefix . 'JoinTable(' . implode(
                        ', ',
                        $joinTable
                    ) . ',';
                $lines[] = $this->spaces . ' *   joinColumns={';
                $joinColumnsLines = [];
                foreach ($associationMapping['joinTable']['joinColumns'] as $joinColumn) {
                    $joinColumnsLines[] = $this->spaces . ' *     ' . $this->generateJoinColumnAnnotation($joinColumn);
                }
                $lines[] = implode("," . PHP_EOL, $joinColumnsLines);
                $lines[] = $this->spaces . ' *   },';
                $lines[] = $this->spaces . ' *   inverseJoinColumns={';
                $inverseJoinColumnsLines = [];
                foreach ($associationMapping['joinTable']['inverseJoinColumns'] as $joinColumn) {
                    $inverseJoinColumnsLines[] = $this->spaces . ' *     ' . $this->generateJoinColumnAnnotation(
                            $joinColumn
                        );
                }
                $lines[] = implode("," . PHP_EOL, $inverseJoinColumnsLines);
                $lines[] = $this->spaces . ' *   }';
                $lines[] = $this->spaces . ' * )';
            }
            if (isset($associationMapping['orderBy'])) {
                $lines[] = $this->spaces . ' * @' . $this->annotationsPrefix . 'OrderBy({';
                foreach ($associationMapping['orderBy'] as $name => $direction) {
                    $lines[] = $this->spaces . ' *     "' . $name . '"="' . $direction . '",';
                }
                $lines[count($lines) - 1] = substr($lines[count($lines) - 1], 0, strlen($lines[count($lines) - 1]) - 1);
                $lines[] = $this->spaces . ' * })';
            }
        }
        $lines[] = $this->spaces . ' */';
        return implode("\n", $lines);
    }

    /**
     * @param array $fieldMapping
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    protected function generateFieldMappingPropertyDocBlock(array $fieldMapping, ClassMetadataInfo $metadata)
    {
        $lines = [];
        $lines[] = $this->spaces . '/**';
        $lines[] = $this->spaces . ' * @var ' . $this->getType($fieldMapping['type']) . ($this->nullableFieldExpression(
                $fieldMapping
            ) ? '|null' : '');
        if ($this->generateAnnotations) {
            $lines[] = $this->spaces . ' *';
            $column = [];
            if (isset($fieldMapping['columnName'])) {
                $column[] = 'name="' . $fieldMapping['columnName'] . '"';
            }
            if (isset($fieldMapping['type'])) {
                $column[] = 'type="' . $fieldMapping['type'] . '"';
            }
            if (isset($fieldMapping['length'])) {
                $column[] = 'length=' . $fieldMapping['length'];
            }
            if (isset($fieldMapping['precision'])) {
                $column[] = 'precision=' . $fieldMapping['precision'];
            }
            if (isset($fieldMapping['scale'])) {
                $column[] = 'scale=' . $fieldMapping['scale'];
            }
            if (isset($fieldMapping['nullable'])) {
                $column[] = 'nullable=' . var_export($fieldMapping['nullable'], true);
            }
            $options = [];
            if (isset($fieldMapping['options']['default']) && $fieldMapping['options']['default']) {
                $options[] = '"default"="' . $fieldMapping['options']['default'] . '"';
            }
            if (isset($fieldMapping['options']['unsigned']) && $fieldMapping['options']['unsigned']) {
                $options[] = '"unsigned"=true';
            }
            if (isset($fieldMapping['options']['fixed']) && $fieldMapping['options']['fixed']) {
                $options[] = '"fixed"=true';
            }
            if (isset($fieldMapping['options']['comment']) && $fieldMapping['options']['comment']) {
                $options[] = '"comment"="' . str_replace('"', '""', $fieldMapping['options']['comment']) . '"';
            }
            if (isset($fieldMapping['options']['collation']) && $fieldMapping['options']['collation']) {
                $options[] = '"collation"="' . $fieldMapping['options']['collation'] . '"';
            }
            if (isset($fieldMapping['options']['check']) && $fieldMapping['options']['check']) {
                $options[] = '"check"="' . $fieldMapping['options']['check'] . '"';
            }
            if ($options) {
                $column[] = 'options={' . implode(',', $options) . '}';
            }
            if (isset($fieldMapping['columnDefinition'])) {
                $column[] = 'columnDefinition="' . $fieldMapping['columnDefinition'] . '"';
            }
            if (isset($fieldMapping['unique'])) {
                $column[] = 'unique=' . var_export($fieldMapping['unique'], true);
            }
            $lines[] = $this->spaces . ' * @' . $this->annotationsPrefix . 'Column(' . implode(', ', $column) . ')';
            if (isset($fieldMapping['id']) && $fieldMapping['id']) {
                $lines[] = $this->spaces . ' * @' . $this->annotationsPrefix . 'Id';
                if ($generatorType = $this->getIdGeneratorTypeString($metadata->generatorType)) {
                    $lines[] = $this->spaces . ' * @' . $this->annotationsPrefix . 'GeneratedValue(strategy="' . $generatorType . '")';
                }
                if ($metadata->sequenceGeneratorDefinition) {
                    $sequenceGenerator = [];
                    if (isset($metadata->sequenceGeneratorDefinition['sequenceName'])) {
                        $sequenceGenerator[] = 'sequenceName="' . $metadata->sequenceGeneratorDefinition['sequenceName'] . '"';
                    }
                    if (isset($metadata->sequenceGeneratorDefinition['allocationSize'])) {
                        $sequenceGenerator[] = 'allocationSize=' . $metadata->sequenceGeneratorDefinition['allocationSize'];
                    }
                    if (isset($metadata->sequenceGeneratorDefinition['initialValue'])) {
                        $sequenceGenerator[] = 'initialValue=' . $metadata->sequenceGeneratorDefinition['initialValue'];
                    }
                    $lines[] = $this->spaces . ' * @' . $this->annotationsPrefix . 'SequenceGenerator(' . implode(
                            ', ',
                            $sequenceGenerator
                        ) . ')';
                }
            }
            if (isset($fieldMapping['version']) && $fieldMapping['version']) {
                $lines[] = $this->spaces . ' * @' . $this->annotationsPrefix . 'Version';
            }
        }
        $lines[] = $this->spaces . ' */';
        return implode("\n", $lines);
    }

    /**
     * @param array $embeddedClass
     * @return string
     */
    protected function generateEmbeddedPropertyDocBlock(array $embeddedClass)
    {
        $lines = [];
        $lines[] = $this->spaces . '/**';
        $lines[] = $this->spaces . ' * @var \\' . ltrim($embeddedClass['class'], '\\');
        if ($this->generateAnnotations) {
            $lines[] = $this->spaces . ' *';
            $embedded = ['class="' . $embeddedClass['class'] . '"'];
            if (isset($embeddedClass['columnPrefix'])) {
                if (is_string($embeddedClass['columnPrefix'])) {
                    $embedded[] = 'columnPrefix="' . $embeddedClass['columnPrefix'] . '"';
                } else {
                    $embedded[] = 'columnPrefix=' . var_export($embeddedClass['columnPrefix'], true);
                }
            }
            $lines[] = $this->spaces . ' * @' . $this->annotationsPrefix . 'Embedded(' . implode(', ', $embedded) . ')';
        }
        $lines[] = $this->spaces . ' */';
        return implode("\n", $lines);
    }

    /**
     * @param string $code
     * @param int $num
     * @return string
     */
    protected function prefixCodeWithSpaces($code, $num = 0)
    {
        $lines = explode("\n", $code);
        foreach ($lines as $key => $value) {
            if (! empty($value)) {
                $lines[$key] = str_repeat($this->spaces, $num) . $lines[$key];
            }
        }
        return implode("\n", $lines);
    }

    /**
     * @param integer $type The inheritance type used by the class and its subclasses.
     * @return string The literal string for the inheritance type.
     * @throws \InvalidArgumentException When the inheritance type does not exist.
     */
    protected function getInheritanceTypeString($type)
    {
        if (! isset(static::$inheritanceTypeMap[$type])) {
            throw new \InvalidArgumentException(sprintf('Invalid provided InheritanceType: %s', $type));
        }
        return static::$inheritanceTypeMap[$type];
    }

    /**
     * @param integer $type The policy used for change-tracking for the mapped class.
     * @return string The literal string for the change-tracking type.
     * @throws \InvalidArgumentException When the change-tracking type does not exist.
     */
    protected function getChangeTrackingPolicyString($type)
    {
        if (! isset(static::$changeTrackingPolicyMap[$type])) {
            throw new \InvalidArgumentException(sprintf('Invalid provided ChangeTrackingPolicy: %s', $type));
        }
        return static::$changeTrackingPolicyMap[$type];
    }

    /**
     * @param integer $type The generator to use for the mapped class.
     * @return string The literal string for the generator type.
     * @throws \InvalidArgumentException    When the generator type does not exist.
     */
    protected function getIdGeneratorTypeString($type)
    {
        if (! isset(static::$generatorStrategyMap[$type])) {
            throw new \InvalidArgumentException(sprintf('Invalid provided IdGeneratorType: %s', $type));
        }
        return static::$generatorStrategyMap[$type];
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return string
     */
    private function generateEmbeddableConstructor(ClassMetadataInfo $metadata)
    {
        $paramTypes = [];
        $paramVariables = [];
        $params = [];
        $fields = [];
        // Resort fields to put optional fields at the end of the method signature.
        $requiredFields = [];
        $optionalFields = [];
        foreach ($metadata->fieldMappings as $fieldMapping) {
            if (empty($fieldMapping['nullable'])) {
                $requiredFields[] = $fieldMapping;
                continue;
            }
            $optionalFields[] = $fieldMapping;
        }
        $fieldMappings = array_merge($requiredFields, $optionalFields);
        foreach ($metadata->embeddedClasses as $fieldName => $embeddedClass) {
            $paramType = '\\' . ltrim($embeddedClass['class'], '\\');
            $paramVariable = '$' . $fieldName;
            $paramTypes[] = $paramType;
            $paramVariables[] = $paramVariable;
            $params[] = $paramType . ' ' . $paramVariable;
            $fields[] = '$this->' . $fieldName . ' = ' . $paramVariable . ';';
        }
        foreach ($fieldMappings as $fieldMapping) {
            if (isset($fieldMapping['declaredField'], $metadata->embeddedClasses[$fieldMapping['declaredField']])) {
                continue;
            }
            $paramTypes[] = $this->getType($fieldMapping['type']) . (! empty($fieldMapping['nullable']) ? '|null' : '');
            $param = '$' . $fieldMapping['fieldName'];
            $paramVariables[] = $param;
            if ($fieldMapping['type'] === 'datetime') {
                $param = $this->getType($fieldMapping['type']) . ' ' . $param;
            }
            if (! empty($fieldMapping['nullable'])) {
                $param .= ' = null';
            }
            $params[] = $param;
            $fields[] = '$this->' . $fieldMapping['fieldName'] . ' = $' . $fieldMapping['fieldName'] . ';';
        }
        $maxParamTypeLength = max(array_map('strlen', $paramTypes));
        $paramTags = array_map(
            function ($type, $variable) use ($maxParamTypeLength) {
                return '@param ' . $type . str_repeat(' ', $maxParamTypeLength - strlen($type) + 1) . $variable;
            },
            $paramTypes,
            $paramVariables
        );
        // Generate multi line constructor if the signature exceeds 120 characters.
        if (array_sum(array_map('strlen', $params)) + count($params) * 2 + 29 > 120) {
            $delimiter = "\n" . $this->spaces;
            $params = $delimiter . implode(',' . $delimiter, $params) . "\n";
        } else {
            $params = implode(', ', $params);
        }
        $replacements = [
            '<paramTags>' => implode("\n * ", $paramTags),
            '<params>' => $params,
            '<fields>' => implode("\n" . $this->spaces, $fields),
        ];
        $constructor = str_replace(
            array_keys($replacements),
            array_values($replacements),
            static::$embeddableConstructorMethodTemplate
        );
        return $this->prefixCodeWithSpaces($constructor);
    }

    private function generateEntityListenerAnnotation(ClassMetadataInfo $metadata): string
    {
        if (0 === \count($metadata->entityListeners)) {
            return '';
        }
        $processedClasses = [];
        foreach ($metadata->entityListeners as $event => $eventListeners) {
            foreach ($eventListeners as $eventListener) {
                $processedClasses[] = '"' . $eventListener['class'] . '"';
            }
        }
        return \sprintf(
            '%s%s({%s})',
            '@' . $this->annotationsPrefix,
            'EntityListeners',
            \implode(',', \array_unique($processedClasses))
        );
    }

    /**
     * @param array $fieldMapping
     * @return string|null
     */
    private function nullableFieldExpression(array $fieldMapping)
    {
        if (isset($fieldMapping['nullable']) && true === $fieldMapping['nullable']) {
            return 'null';
        }
        return null;
    }

    /**
     * Exports (nested) option elements.
     *
     * @param array $options
     * @return string
     */
    private function exportTableOptions(array $options)
    {
        $optionsStr = [];
        foreach ($options as $name => $option) {
            if (is_array($option)) {
                $optionsStr[] = '"' . $name . '"={' . $this->exportTableOptions($option) . '}';
            } else {
                $optionsStr[] = '"' . $name . '"="' . (string) $option . '"';
            }
        }
        return implode(',', $optionsStr);
    }
}

