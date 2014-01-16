<?php
namespace Fudge\Sknife\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Fudge\Sknife\Util\Strings;
use Fudge\Sknife\Model\Traits\HasTranslations;

/**
 * Translatable service (using Gedmo's Doctrine behaviour)
 * @author Yohann Marillet
 * @since 19/10/13
 */
class Translatable
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * @var array
     */
    protected static $translatableProperties = [];

    public function __construct(EntityManager $em, $defaultLocale)
    {
        $this->em = $em;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @return string
     * @author Yohann Marillet
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * @return TranslationRepository
     * @author Yohann Marillet
     */
    public function getRepository()
    {
        return $this->em->getRepository('Gedmo\Translatable\Entity\Translation');
    }

    /**
     * Refreshes an entity with real translations
     * @param $entity
     * @param $language
     *
     * @author Yohann Marillet
     */
    public function refreshEntity($entity, $language)
    {
        $translatableProperties = $this->getTranslatableProperties($entity);

        $setLocaleMethod = $translatableProperties['setLocaleMethod'];
        $entity->$setLocaleMethod($language);

        $this->em->refresh($entity);
        if ($this->defaultLocale != $language) {
            if (false !== $translatableProperties['usePersonalTranslations']) {
                /** @var HasTranslations $entity */
                $allTranslations = $entity->getTranslations();
                $translations=[];
                /** @var AbstractPersonalTranslation $translationEntity */
                foreach ($allTranslations as $translationEntity) {
                    if (!isset($translations[$translationEntity->getLocale()])) {
                        $translations[$translationEntity->getLocale()] = [];
                    }
                    $translations[$translationEntity->getLocale()][$translationEntity->getField()] = $translationEntity->getContent();
                }
            } else {
                $translations = $this->getRepository()->findTranslations($entity);
            }

            if (!isset($translations[$language])) {
                $translations[$language] = $translatableProperties['fields'];
            }

            foreach ($translations[$language] as $field => $value) {
                $setter = Strings::toSetter($field);
                $entity->$setter($value);
            }
        }
    }

    /**
     * @param Object|string $entity
     *
     * @return array
     * @author Yohann Marillet
     */
    public function getTranslatableFields($entity) {
        return array_keys($this->getTranslatableProperties($entity)['fields']);
    }

    /**
     * @param Object|string $param
     *
     * @return array
     * @author Yohann Marillet
     */
    public function getTranslatableProperties($param) {
        if('string' == gettype($param)) {
            $className = $param;
        } else {
            $className = get_class($param);
        }

        $this->buildTranslatableProperties($className);
        return static::$translatableProperties[$className];
    }

    /**
     * Builds the translatable properties for a defined class
     * @param $className
     *
     * @author Yohann Marillet
     */
    protected function buildTranslatableProperties($className) {
        if (!isset(static::$translatableProperties[$className])) {
            static::$translatableProperties[$className] = ['fields'=>[], 'setLocaleMethod'=>'', 'usePersonalTranslations'=>false];
            $refl = new \ReflectionClass($className);
            $reader = new AnnotationReader();
            foreach ($refl->getProperties() as $prop) {
                $annotations = $reader->getPropertyAnnotations($prop);
                foreach ($annotations as $annotation) {
                    if ($annotation instanceof \Gedmo\Mapping\Annotation\Translatable) {
                        $name = $prop->getName();
                        static::$translatableProperties[$className]['fields'][$name] = '';
                        break;
                    } elseif ($annotation instanceof \Gedmo\Mapping\Annotation\Locale) {
                        $name = $prop->getName();
                        static::$translatableProperties[$className]['setLocaleMethod'] = Strings::toSetter($name);
                        break;
                    }
                }
            }

            $usePersonalTranslations = $reader->getClassAnnotation($refl, '\\Gedmo\\Mapping\\Annotation\\TranslationEntity');
            if (null != $usePersonalTranslations) {
                static::$translatableProperties[$className]['usePersonalTranslations'] = $usePersonalTranslations;
            }
        }
    }

}
