<?php
namespace Fudge\Sknife\Doctrine\DBAL\Types;

use Doctrine\DBAL\Types\Type as DoctrineType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Fudge\Sknife\Util\Strings;

/**
 * Abstract class to handle MYSQL's enum types
 * @since 09/09/2013
 * @author Yohann Marillet
 * @see http://docs.doctrine-project.org/en/2.0.x/cookbook/mysql-enums.html
 */
abstract class AbstractEnumType extends DoctrineType
{
    protected $name;
    protected $values = array();

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $values = $this->getValues();
        if ([] != $values) {
            throw new \Exception(get_class($this).'::$_values cannot be empty');
        }

        return "ENUM('".implode("','", $values)."')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, $this->getValues())) {
            throw new \InvalidArgumentException("Invalid '".$this->getName()."' value.");
        }

        return $value;
    }

    public function getName()
    {
        if (empty($this->name)) {
            $r = new \ReflectionClass($this);
            $classname = $r->getShortName();
            $this->name = Strings::toLowerCamel(preg_replace('#Type$#', '', $classname));
        }

        return $this->name;
    }

    /**
     * Gets all possible values (lazy loading) - Cannot do that in constructor since it's a final method in parent class
     * @return array
     * @author Yohann Marillet
     */
    abstract public function getValues();
}
