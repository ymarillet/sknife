<?php
namespace Fudge\Sknife\Controller;

use Doctrine\ORM\EntityManager;
use Fudge\Sknife\ORM\Doctrine\Repository\Interfaces\SelectableRepositoryInterface;
use Fudge\Sknife\Service\AjaxResponseBuilder;
use Fudge\Sknife\Service\Datatable;
use Fudge\Sknife\Service\Translatable;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;

/**
 * Extends the basic SF2's controller with shortcuts (for type hinting and autocompletion) and general purpose functions
 * @author Yohann Marillet
 * @since 20/09/13
 */
abstract class AbstractController extends Controller
{
    /**
     * Shortcut to return the session service.
     *
     * @return Session
     * @author Yohann Marillet
     */
    public function getSession()
    {
        return $this->getRequest()->getSession();
    }

    /**
     * Shortcut to the Doctrine's EntityManagers
     *
     * @param  string|null   $name
     * @return EntityManager
     * @author Yohann Marillet
     */
    public function getEm($name = null)
    {
        return $this->getDoctrine()->getManager($name);
    }

    /**
     * Shortcut to add a flash message in the flashbag
     *
     * @param $type
     * @param $message
     * @return mixed
     * @author Yohann Marillet
     */
    public function addFlash($type, $message)
    {
        return $this->getSession()->getFlashBag()->add($type, $message);
    }

    /**
     * Shortcut to return the datatable service
     * @return Datatable
     * @author Yohann Marillet
     */
    public function getDatatable()
    {
        return $this->get('sknife.datatable');
    }

    /**
     * Shortcut to return the translator service
     * @return Translator
     * @author Yohann Marillet
     */
    public function getTranslator()
    {
        return $this->get('translator');
    }

    /**
     * @see Translator::trans()
     */
    public function trans($id, $parameters = array(), $domain = null, $locale = null)
    {
        return $this->getTranslator()->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Shortcut to return the router service
     * @return Router
     * @author Yohann Marillet
     */
    public function getRouter()
    {
        return $this->get('router');
    }

    /**
     * Saves an entity state in the session bag
     * @param string $key    key of the variable in the session bag
     * @param mixed  $entity
     * @author Yohann Marillet
     */
    protected function saveEntity($key, $entity)
    {
        $isPersisted = $this->getEm()->contains($entity);

        if ($isPersisted) {
            $className = get_class($entity);
            $metadata = $this->getEm()->getClassMetadata($className);
            $serialized = ['class' => $className, 'identifier' => $metadata->getIdentifierValues($entity)];
        } else {
            $serialized = serialize($entity);
        }

        $this->getSession()->set('entity_' . $key, $serialized);
        $this->getSession()->set('entity_state_' . $key, $isPersisted);
    }

    /**
     * Restores an entity from the session bag - for persisted entities, this function will query the database
     * Must have a performance impact if used many times
     * @param  string $key key of the variable in the session bag
     * @return mixed
     * @author Yohann Marillet
     */
    protected function restoreEntity($key)
    {
        $return = null;
        if ($this->getSession()->has('entity_' . $key)) {
            $entity = $this->getSession()->get('entity_' . $key);
            if ($this->getSession()->get('entity_state_' . $key)) {
                $return = $this->getEm()->find($entity['class'], $entity['identifier']);
            } else {
                $return = unserialize($entity);
            }
        }

        return $return;
    }

    /**
     * @see JsonResponse::create
     * @param null  $data
     * @param int   $status
     * @param array $headers
     *
     * @return JsonResponse
     * @author Yohann Marillet
     */
    public function createAjaxResponse($data = null, $status = null, $headers = array())
    {
        /** @var AjaxResponseBuilder $builder */
        $builder = $this->get('sknife.ajax.response.builder');

        if (null === $status) {
            $status = 200;
            if ($this->getSession()->getFlashBag()->has('danger')) {
                $status = 500;
            }
        }

        $return = $builder->get($data, $status, $headers);

        return $return;
    }

    /**
     * Shortcut to return the logger service
     * @return Logger
     * @author Yohann Marillet
     */
    public function getLogger()
    {
        return $this->get('logger');
    }

    /**
     * Shortcut to the Sknife's translatable service
     * @return Translatable
     * @author Yohann Marillet
     */
    public function getTranslatable()
    {
        return $this->get('sknife.translatable');
    }

    /**
     * @param int $previousSelectedCount
     * @param int $newSelectedCount
     *
     * @author Yohann Marillet
     */
    protected function checkSelectedDifferences($previousSelectedCount, $newSelectedCount)
    {
        $diff = $previousSelectedCount - $newSelectedCount;
        if ($diff > 0) {
            $this->addFlash('warning', $this->trans('Un autre utilisateur semble avoir supprimé %nb% de vos éléments sélectionnés.', ['%nb%'=>$diff]));
        }
    }

    /**
     * Refresh the datatable selected ids and return its new count
     * @param SelectableRepositoryInterface $repository
     * @param string               $token
     *
     * @see SelectableRepositoryInterface::refreshSelected
     *
     * @return int
     * @author Yohann Marillet
     */
    protected function refreshSelected(SelectableRepositoryInterface $repository, $token)
    {
        $ids = $this->getDatatable()->getSelected($token);
        $oldCount = count($ids);

        $newIds = $repository->refreshSelected($ids);
        $return = count($newIds);

        $this->checkSelectedDifferences($oldCount, $return);

        $this->getDatatable()->clearSelected($token);
        $this->getDatatable()->addSelected($token, $newIds);

        return $return;
    }

}
