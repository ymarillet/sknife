<?php
namespace Fudge\Sknife\EventListener;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Fudge\Sknife\Annotation\Guard;
use Fudge\Sknife\Exception\PermissionRequiredException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * ControllerListener
 * @author Yohann Marillet
 * @since 12/12/13
 * @see http://php-and-symfony.matthiasnoback.nl/2012/12/prevent-controller-execution-with-annotations-and-return-a-custom-response/
 */
class ControllerListener
{
    /** @var Reader */
    protected $annotationReader;

    /** @var SecurityContext */
    protected $security;

    public function __construct(SecurityContext $security, Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
        $this->security = $security;
    }

    public function onFilterController(FilterControllerEvent $event)
    {
        list($object, $method) = $event->getController();
        // the controller could be a proxy
        $className = ClassUtils::getClass($object);

        $reflectionClass = new \ReflectionClass($className);
        $reflectionMethod = $reflectionClass->getMethod($method);

        $allControllerAnnotations = $this->annotationReader->getClassAnnotations($reflectionClass);
        $allMethodAnnotations = $this->annotationReader->getMethodAnnotations($reflectionMethod);

        $guardAnnotationsFilter = function($annotation) {
            return ($annotation instanceof Guard);
        };

        $controllerGuardAnnotations = array_filter($allControllerAnnotations, $guardAnnotationsFilter);
        $methodGuardAnnotations = array_filter($allMethodAnnotations, $guardAnnotationsFilter);

        $guardAnnotations = array_merge($controllerGuardAnnotations,$methodGuardAnnotations);

        $permissions = [];
        foreach ($guardAnnotations as $guardAnnotation) {
            $value = $guardAnnotation->value;
            if(!is_array($value)) {
                $value = [$value];
            }
            $permissions = array_merge($value, $permissions);
        }
        $permissions = array_unique($permissions);

        if(!empty($permissions) && !$this->security->isGranted($permissions)) {
            $e = new PermissionRequiredException();
            $e->setRequiredPermissions($permissions)
              ->setCurrentPermissions($this->security->getToken()->getUser()->getPermissions());
            throw $e;
        }
    }
}